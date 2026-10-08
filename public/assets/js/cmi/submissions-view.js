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
    const canon     = tableNo.charAt(0).toUpperCase() + tableNo.slice(1).toLowerCase();
    const tableDef  = window.CMI_TABLES?.getTable(tableNo) || window.CMI_TABLES?.getTable(tableNo.toUpperCase()) || window.CMI_TABLES?.getTable(canon) || null;
    const tableInfo = window.CMI_TABLE_INDEX?.[tableNo] || window.CMI_TABLE_INDEX?.[tableNo.toUpperCase()] || window.CMI_TABLE_INDEX?.[canon] || tableDef || { title: tableNo };
    const meta      = window.SubState.meta[tableNo]
      || window.SubState.meta[tableNo.toUpperCase()]
      || window.SubState.meta[tableNo.toLowerCase()]
      || window.SubState.meta[canon]
      || {};

    document.getElementById('viewModalTableNo').textContent    = tableNo;
    document.getElementById('viewModalTableTitle').textContent = tableInfo.title || tableNo;
    const dateStr = meta.submitted_at || meta.updated_at;
    document.getElementById('viewModalDate').textContent       =
      dateStr ? 'Submitted: ' + formatDate(dateStr) : '';

    // Edit button hands off to full fillup form
    const editBtn = document.getElementById('viewModalEditBtn');
    if (editBtn) {
      editBtn.setAttribute('href', `/dashboard/cmi/fillup?table=${encodeURIComponent(tableNo)}&year=${window.SubState.selectedYear || 2026}`);
      editBtn.onclick = null;
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
    let rows       = meta.rows || [];
    if (rows && typeof rows === 'object' && !Array.isArray(rows)) {
      rows = Object.values(rows);
    }
    const metaData = meta.metaData || meta.meta || {};
    const canon    = tableNo.charAt(0).toUpperCase() + tableNo.slice(1).toLowerCase();
    const tableDef = window.CMI_TABLES?.getTable(tableNo) || window.CMI_TABLES?.getTable(tableNo.toUpperCase()) || window.CMI_TABLES?.getTable(canon) || null;
    const remark   = meta.remarks || window.SubState.remarks?.[tableNo] || window.SubState.remarks?.[tableNo.toUpperCase()] || window.SubState.remarks?.[canon] || '';

    let html = '';

    // PTA Remarks alert banner if returned
    if (remark) {
      html += `
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
          <div style="font-weight:700;color:#991b1b;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:4px">
            <span>⚠️</span> Returned for Revision by PTA Admin
          </div>
          <div style="font-size:13px;color:#7f1d1d;line-height:1.5"><strong>Remarks:</strong> ${escapeHtml(remark)}</div>
        </div>`;
    }

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
    const images = Array.isArray(metaData.images) ? metaData.images : (Array.isArray(meta.docs) ? meta.docs : []);
    if (images.length) {
      html += '<div class="view-attachments" style="margin-bottom:14px">';
      html += '<div class="view-meta-label" style="margin-bottom:6px"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Attachments</div>';
      html += '<div style="display:flex;gap:10px;flex-wrap:wrap">';
      images.forEach(img => {
        const path  = img.file_path || '';
        const cap   = img.caption   || '';
        const isImg = /\.(png|jpe?g|gif|webp)$/i.test(path);
        const isPdf = /\.pdf$/i.test(path);
        if (isImg) {
          html += `<a href="#" data-lightbox-src="${escapeHtml(path)}" data-lightbox-cap="${escapeHtml(cap)}"
               class="attachment-thumb" style="display:block;text-align:center">
              <img src="${escapeHtml(path)}" alt="${escapeHtml(cap)}"
                style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border,#e0e0e0)"/>
              <div style="font-size:11px;color:var(--text-muted);margin-top:4px;max-width:90px;
                overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(cap || 'View')}</div>
            </a>`;
        } else if (isPdf) {
          html += `<a href="${escapeHtml(path)}" target="_blank" rel="noopener noreferrer"
               style="width:90px;height:90px;border-radius:8px;border:1px solid #fecaca;background:#fff5f5;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;padding:6px;box-sizing:border-box;text-align:center">
               <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
               <span style="font-size:10px;font-weight:700;color:#dc2626;margin-top:2px">PDF FILE</span>
               <div style="font-size:9.5px;color:var(--text-muted);margin-top:2px;max-width:82px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(cap || 'View PDF')}</div>
             </a>`;
        } else {
          html += `<a href="${escapeHtml(path)}" target="_blank" rel="noopener"
               class="btn btn-sm btn-outline" style="font-size:12px">${escapeHtml(cap || 'Document')}</a>`;
        }
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
    const normNo = tableNo.toUpperCase().trim();
    const isInternalCat = ['T1', 'T2A', 'T2', 'T7A', 'T7', 'T7B', 'T8A', 'T8', 'T10', 'T15', 'T16', 'T20B', 'T20b'].includes(normNo);
    let columns = [];
    if (tableDef?.columns?.length) {
      columns = tableDef.columns.filter(c => c.type !== 'computed');
    } else {
      const tmpl = (window.SubState?.templates || []).find(t =>
        t.table_no.toUpperCase() === tableNo.toUpperCase() ||
        t.table_no.toUpperCase() === canon.toUpperCase()
      );
      if (tmpl && tmpl.columns_json && Array.isArray(tmpl.columns_json) && tmpl.columns_json.length > 0) {
        columns = tmpl.columns_json.map(c => ({
          key: c.key || c.name || c.id,
          label: c.label || c.title || c.key || 'Column'
        }));
      } else if (rows.length) {
        columns = Object.keys(rows[0] || {})
          .filter(k => !['_group', '_fixed', '_label'].includes(k) && !(isInternalCat && k === 'category'))
          .map(k => ({ key: k, label: k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ') }));
      }
    }
    if (isInternalCat) {
      columns = columns.filter(c => c.key !== 'category');
    }

    if (!columns.length)
      return html + '<div class="view-empty">No column definitions available.</div>';

    const ALIAS_MAP = {
      expenditures: ['expenditure', 'expense', 'amount', 'cost', 'budget'],
      expenditure: ['expenditures', 'expense', 'amount', 'cost', 'budget'],
      expense: ['expenditures', 'expenditure', 'amount', 'cost', 'budget'],
      budget: ['amount', 'cost', 'expenditures', 'expense'],
      funds: ['fundsource', 'source', 'fund', 'fund_source'],
      fundsource: ['funds', 'source', 'fund', 'fund_source'],
      title: ['activity', 'award', 'project', 'training', 'policy', 'name', 'item'],
      policy: ['title', 'policy_title', 'list_of_policies', 'ordinance', 'resolution'],
      award: ['title', 'activity', 'award_title'],
      event: ['activity', 'title', 'event_activity'],
      activity: ['title', 'event', 'project'],
      venue: ['date_venue', 'place', 'location', 'date'],
      date: ['date_awarded', 'published_date', 'award_date', 'venue'],
      author: ['researcher', 'lead'],
      description: ['desc', 'details', 'findings'],
      findings: ['finding', 'results'],
      precomm: ['pre_comm', 'precommercialization', 'activity', 'pre-commercialization'],
      pre_comm: ['precomm', 'precommercialization', 'activity', 'pre-commercialization'],
    };

    function getCellVal(row, key) {
      if (!row || typeof row !== 'object') return '';
      let v = row[key] ?? row[key?.toLowerCase()] ?? row[key?.toUpperCase()];
      if (v !== undefined && v !== null && String(v).trim() !== '') return String(v);

      const alts = ALIAS_MAP[key?.toLowerCase()] || [];
      for (const a of alts) {
        let av = row[a] ?? row[a?.toLowerCase()] ?? row[a?.toUpperCase()];
        if (av !== undefined && av !== null && String(av).trim() !== '') return String(av);
      }
      return '';
    }

    // Data table
    html += '<div class="tbl-wrap"><table class="dt view-tbl"><thead><tr>';
    columns.forEach(c => {
      html += `<th style="font-size:11.5px;white-space:normal;min-width:80px">${escapeHtml(c.label)}</th>`;
    });
    html += '</tr></thead><tbody>';
    rows.forEach((row, idx) => {
      html += `<tr${idx % 2 !== 0 ? ' style="background:var(--bg-soft,#f7faf7)"' : ''}>`;
      columns.forEach(c => {
        const raw = getCellVal(row, c.key);
        const val = (raw === null || raw === undefined || String(raw).trim() === '') ? '—' : String(raw);
        html += `<td style="font-size:12.5px;vertical-align:top;padding:8px 10px">${escapeHtml(val)}</td>`;
      });
      html += '</tr>';
    });
    return html;
  }

  /* ══════════════════════════════════════════
     EXPORT
  ══════════════════════════════════════════ */

  window.SubView      = { openViewModal, closeViewModal };
  window.closeViewModal = closeViewModal;   // PHP inline onclick

})();
