/**
 * submissions-view.js — CMI My Submissions
 * View modal: opens a read-only display of a submitted table,
 * including meta fields, attachments, and the data grid.
 *
 * Depends on: submissions-helpers.js (SubHelpers)
 *             submissions-state.js   (SubState)
 * Exposed as: window.SubView
 *             window.closeViewModal  (PHP inline onclick)
 */

(function () {
  'use strict';

  const { formatDate, escapeHtml } = window.SubHelpers;

  /* ══════════════════════════════════════════
     OPEN / CLOSE
  ══════════════════════════════════════════ */

  function openViewModal(tableNo) {
    const tableDef  = window.CMI_TABLES?.getTable(tableNo.toUpperCase()) ?? null;
    const tableInfo = window.CMI_TABLE_INDEX?.[tableNo] || tableDef || { title: tableNo };
    const meta      = window.SubState.meta[tableNo] || {};

    document.getElementById('viewModalTableNo').textContent    = tableNo;
    document.getElementById('viewModalTableTitle').textContent = tableInfo.title || tableNo;
    document.getElementById('viewModalDate').textContent       =
      meta.updated_at ? 'Submitted: ' + formatDate(meta.updated_at) : '';

    // Edit button hands off to the write layer
    const editBtn = document.getElementById('viewModalEditBtn');
    if (editBtn) {
      editBtn.removeAttribute('href');
      editBtn.onclick = e => {
        e.preventDefault();
        closeViewModal();
        window.SubEdit?.openEditModal(tableNo);
      };
    }

    document.getElementById('viewModalBody').innerHTML = buildViewContent(tableNo, meta);

    const overlay = document.getElementById('modalViewTable');
    if (overlay) {
      overlay.style.display = 'flex';
      requestAnimationFrame(() => overlay.classList.add('modal-visible'));
    }
  }

  function closeViewModal() {
    const overlay = document.getElementById('modalViewTable');
    if (!overlay) return;
    overlay.classList.remove('modal-visible');
    setTimeout(() => { overlay.style.display = 'none'; }, 200);
  }

  /* ══════════════════════════════════════════
     BUILD VIEW CONTENT (read-only HTML)
  ══════════════════════════════════════════ */

  function buildViewContent(tableNo, meta) {
    const rows     = meta.rows     || [];
    const metaData = meta.metaData || {};
    const tableDef = window.CMI_TABLES?.getTable(tableNo.toUpperCase()) ?? null;

    let html = '';

    // Meta fields
    if (tableDef?.meta?.length) {
      const hasVals = tableDef.meta.some(m => metaData[m.key]);
      if (hasVals) {
        html += '<div class="view-meta-row">';
        tableDef.meta.forEach(m => {
          html += `
            <div class="view-meta-item">
              <span class="view-meta-label">${escapeHtml(m.label)}</span>
              <span class="view-meta-val">${escapeHtml(String(metaData[m.key] || '—'))}</span>
            </div>`;
        });
        html += '</div>';
      }
    }

    // Attachments
    const images = Array.isArray(metaData.images) ? metaData.images : [];
    if (images.length) {
      html += '<div class="view-attachments" style="margin-bottom:14px">';
      html += '<div class="view-meta-label" style="margin-bottom:6px">📎 Attachments</div>';
      html += '<div style="display:flex;gap:10px;flex-wrap:wrap">';
      images.forEach(img => {
        const path  = img.file_path || '';
        const cap   = img.caption   || '';
        const isImg = /\.(png|jpe?g|gif|webp)$/i.test(path);
        html += isImg
          ? `<a href="#" data-lightbox-src="${escapeHtml(path)}" data-lightbox-cap="${escapeHtml(cap)}"
               class="attachment-thumb" style="display:block;text-align:center">
              <img src="${escapeHtml(path)}" alt="${escapeHtml(cap)}"
                style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border,#e0e0e0)"/>
              <div style="font-size:11px;color:var(--text-muted);margin-top:4px;max-width:90px;
                overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(cap || 'View')}</div>
            </a>`
          // Non-image docs open in a new tab — browser handles downloads better in-page
          : `<a href="${escapeHtml(path)}" target="_blank" rel="noopener"
               class="btn btn-sm btn-outline" style="font-size:12px">${escapeHtml(cap || 'Document')}</a>`;
      });
      html += '</div></div>';
    }

    // Empty state
    if (!rows.length) {
      if (!images.length)
        html += `<div class="view-empty"><div style="font-size:22px;margin-bottom:8px">📄</div>No data was entered for this table.</div>`;
      return html;
    }

    // Column definitions
    let columns = [];
    if (tableDef?.columns) {
      columns = tableDef.columns.filter(c => c.type !== 'computed');
    } else {
      columns = Object.keys(rows[0] || {})
        .filter(k => !['_group', '_fixed', '_label'].includes(k))
        .map(k => ({ key: k, label: k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ') }));
    }

    if (!columns.length)
      return html + '<div class="view-empty">No column definitions available.</div>';

    // Data table
    html += '<div class="tbl-wrap"><table class="dt view-tbl"><thead><tr>';
    columns.forEach(c => {
      html += `<th style="font-size:11.5px;white-space:normal;min-width:80px">${escapeHtml(c.label)}</th>`;
    });
    html += '</tr></thead><tbody>';
    rows.forEach((row, idx) => {
      html += `<tr${idx % 2 !== 0 ? ' style="background:var(--bg-soft,#f7faf7)"' : ''}>`;
      columns.forEach(c => {
        const raw = row[c.key];
        const val = (raw === null || raw === undefined || String(raw).trim() === '') ? '—' : String(raw);
        html += `<td style="font-size:12.5px;vertical-align:top;padding:8px 10px">${escapeHtml(val)}</td>`;
      });
      html += '</tr>';
    });
    html += '</tbody></table></div>';

    return html;
  }

  /* ══════════════════════════════════════════
     EXPORT
  ══════════════════════════════════════════ */

  window.SubView      = { openViewModal, closeViewModal };
  window.closeViewModal = closeViewModal;   // PHP inline onclick

})();
