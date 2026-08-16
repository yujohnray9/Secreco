/**
 * submissions-edit-builders.js — CMI My Submissions
 * Pure HTML-builder functions for the Edit modal.
 * No fetch calls, no event listeners — only string/DOM construction.
 *
 * Depends on: submissions-helpers.js  (SubHelpers.escapeHtml)
 *             submissions-edit-state.js (SubEditState)
 * Exposed as: window.SubEditBuilders
 */

(function () {
  'use strict';

  const { escapeHtml } = window.SubHelpers;

  /* ─────────────────────────────────────────
     COLUMN RESOLVER
  ───────────────────────────────────────── */
  function resolveColumns(tableDef, rows) {
    if (tableDef?.columns)
      return tableDef.columns.filter(c => c.type !== 'computed');

    if (rows?.length)
      return Object.keys(rows[0])
        .filter(k => !['_group', '_fixed', '_label'].includes(k))
        .map(k => ({
          key  : k,
          label: k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' '),
          type : 'text',
        }));

    return [];
  }

  /* ─────────────────────────────────────────
     MAIN CONTENT
  ───────────────────────────────────────── */
  function buildEditContent(tableNo) {
    const state    = window.SubEditState;
    const tableDef = window.CMI_TABLES?.getTable(tableNo.toUpperCase()) ?? null;
    const columns  = resolveColumns(tableDef, state.rows);

    if (!state.rows.length && columns.length) {
      const blank = {};
      columns.forEach(c => blank[c.key] = '');
      state.rows.push(blank);
    }

    let html = buildAttachmentsSection(state.meta.images || []);

    if (tableDef?.meta?.length) {
      html += '<div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px">';
      tableDef.meta.forEach(m => {
        const isDate = m.type === 'date' || /date/i.test(m.key) || /date/i.test(m.label || '');
        html += `
          <div style="flex:1;min-width:180px">
            <label style="display:block;font-size:11px;text-transform:uppercase;
              letter-spacing:.05em;color:var(--text-muted);margin-bottom:4px">${escapeHtml(m.label)}</label>
            <input type="${isDate ? 'date' : 'text'}" class="edit-meta-input" data-meta-key="${escapeHtml(m.key)}"
              value="${escapeHtml(String(state.meta[m.key] || ''))}"
              style="width:100%;border:1px solid var(--border,#ddd);border-radius:4px;
                padding:6px 8px;font-size:13px;font-family:inherit;box-sizing:border-box"/>
          </div>`;
      });
      html += '</div>';
    }

    if (!columns.length)
      return html + '<div style="padding:24px;text-align:center;color:var(--text-muted)">No editable columns found for this table.</div>';

    html += '<div class="tbl-wrap"><table class="dt" id="editTableGrid" style="min-width:100%"><thead><tr>';
    columns.forEach(c => {
      html += `<th style="font-size:11px;white-space:nowrap;padding:8px 10px">${escapeHtml(c.label)}</th>`;
    });
    html += '<th style="width:36px"></th></tr></thead><tbody id="editTableBody">';
    state.rows.forEach((row, idx) => { html += buildEditRow(row, idx, columns); });
    html += '</tbody></table></div>';
    html += '<div style="margin-top:10px"><button class="btn btn-sm btn-outline" id="editAddRowBtn" type="button">+ Add Row</button></div>';

    return html;
  }

  /* ─────────────────────────────────────────
     ATTACHMENTS SECTION
  ───────────────────────────────────────── */
  function buildAttachmentsSection(images) {
    const groupCaption = images?.[0]?.caption || '';
    return `
      <div class="edit-attachments" style="margin-bottom:16px">
        <label style="display:block;font-size:11px;text-transform:uppercase;
          letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Attachments</label>
        <div id="editAttachmentsList" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px">
          ${images.map((img, i) => buildEditAttachmentThumb(img, i)).join('')
            || '<div id="editAttachmentsEmpty" style="font-size:12px;color:var(--text-muted)">No attachments yet.</div>'}
        </div>
        <label class="btn btn-sm btn-outline" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px"
          id="editAttachmentLabel">
          + Add Attachment
          <input type="file" id="editAttachmentInput" accept="image/*,application/pdf" style="display:none"/>
        </label>
        <span id="editAttachmentStatus" style="font-size:11px;color:var(--text-muted);margin-left:8px">
          Optional — only attach if you have a file to add.
        </span>
        <div style="margin-top:10px">
          <label style="display:block;font-size:11px;text-transform:uppercase;
            letter-spacing:.05em;color:var(--text-muted);margin-bottom:4px">Caption (optional)</label>
          <input type="text" id="editAttachmentGroupCaption" value="${escapeHtml(groupCaption)}"
            placeholder="Add a caption for these attachments..." maxlength="255"
            style="width:100%;border:1px solid var(--border,#ddd);border-radius:4px;
              padding:6px 8px;font-size:13px;font-family:inherit;box-sizing:border-box"/>
        </div>
      </div>`;
  }

  /* ─────────────────────────────────────────
     SINGLE THUMBNAIL
  ───────────────────────────────────────── */
  function buildEditAttachmentThumb(img, idx) {
    const path    = img.file_path || '';
    const docId   = img.doc_id || img.id || '';
    const isImage = /\.(png|jpe?g|gif|webp)$/i.test(path) || /^data:image\//i.test(path);
    const thumb   = isImage
      ? `<img src="${escapeHtml(path)}" alt="attachment"
           style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border,#e0e0e0)"/>`
      : `<div style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;
           border-radius:8px;border:1px solid var(--border,#e0e0e0);font-size:24px">📄</div>`;
    return `
      <div class="edit-attachment-thumb" data-idx="${idx}" data-doc-id="${escapeHtml(String(docId))}"
        style="position:relative;text-align:center">
        ${thumb}
        <button type="button" class="edit-attachment-remove" data-idx="${idx}" data-doc-id="${escapeHtml(String(docId))}"
          title="Remove attachment"
          style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;
            border:none;background:var(--danger,#c62828);color:#fff;cursor:pointer;font-size:12px;line-height:1">✕</button>
      </div>`;
  }

  /* ─────────────────────────────────────────
     SINGLE DATA ROW
  ───────────────────────────────────────── */
  function buildEditRow(row, idx, columns) {
    let cells = '';
    columns.forEach(c => {
      const isNum  = c.type === 'number';
      const isDate = c.type === 'date' || /date/i.test(c.key) || /date/i.test(c.label || '');
      const inputType = isNum ? 'number' : (isDate ? 'date' : 'text');
      cells += `<td style="padding:4px 6px">
        <input type="${inputType}" class="edit-cell-input"
          data-row="${idx}" data-key="${escapeHtml(c.key)}" value="${escapeHtml(String(row[c.key] ?? ''))}"
          style="${isNum ? 'width:60px;text-align:center;' : isDate ? 'min-width:130px;' : 'width:100%;'}border:1px solid var(--border,#ddd);
            border-radius:3px;padding:4px 6px;font-size:12px;font-family:inherit;box-sizing:border-box"/>
      </td>`;
    });
    cells += `<td style="padding:4px 6px;text-align:center">
      <button class="row-remove-btn edit-row-del" data-row="${idx}" type="button" title="Remove row"
        style="border:none;background:none;cursor:pointer;color:var(--danger,#c62828);font-size:14px">✕</button>
    </td>`;
    return `<tr id="edit-row-${idx}">${cells}</tr>`;
  }

  /* ─────────────────────────────────────────
     RE-RENDER ATTACHMENT STRIP
     Called after upload or remove — updates only the thumbnails list,
     NOT the whole modal body, so no new event listeners are needed.
  ───────────────────────────────────────── */
  function renderEditAttachments() {
    const list   = document.getElementById('editAttachmentsList');
    const images = Array.isArray(window.SubEditState.meta.images)
      ? window.SubEditState.meta.images : [];
    if (!list) return;
    list.innerHTML = images.map((img, i) => buildEditAttachmentThumb(img, i)).join('')
      || '<div id="editAttachmentsEmpty" style="font-size:12px;color:var(--text-muted)">No attachments yet.</div>';
  }

  /* ─────────────────────────────────────────
     EXPORT
  ───────────────────────────────────────── */
  window.SubEditBuilders = {
    resolveColumns,
    buildEditContent,
    buildAttachmentsSection,
    buildEditAttachmentThumb,
    buildEditRow,
    renderEditAttachments,
  };

})();
