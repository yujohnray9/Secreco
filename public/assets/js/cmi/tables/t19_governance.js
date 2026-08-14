/**
 * t19_governance.js — Table 19: List of New Initiatives on Governance,
 * CY 2025 (January – December).
 * Columns: New Initiatives | Date Conducted/Implemented
 */

(function () {
  'use strict';

  const TABLE_NO = 'T19';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t19_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 19. List of New Initiatives on Governance, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:500px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">New Initiatives</th>
              <th class="group" style="width:220px">Date Conducted / Implemented</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t19_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T19.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T19.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t19_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t19_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t19_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t19-num" style="text-align:center;font-weight:600"></td>
      <td><textarea class="t19-initiative" rows="2" style="width:100%;resize:vertical"
            placeholder="New Initiative">${esc(data.initiative||'')}</textarea></td>
      <td><input type="date" class="t19-date" value="${esc(data.date||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T19._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t19_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t19-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t19_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t19_rows tr')].map(tr => ({
      initiative: tr.querySelector('.t19-initiative')?.value || '',
      date:       tr.querySelector('.t19-date')?.value       || '',
    }));
  }

  /* ─── STATUS (auto-derived) ───
     A row is "touched" if initiative or date has a value.
     "complete" if both fields are filled. */
  function isRowTouched(r) {
    return [r.initiative, r.date].some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.initiative, r.date].every(v => (v || '').trim() !== '');
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(status) {
    if (status === 'done') return 'Complete';
    if (status === 'draft') return 'In Progress';
    return 'Not Started';
  }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t19_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = {
      'done':        { bg: '#e6f4ea', fg: 'var(--green, #1e7e34)' },
      'draft':       { bg: '#fff4e5', fg: '#b06b00' },
      'not-started': { bg: '#f1f1f1', fg: '#777' },
    };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg;
    badge.style.color = c.fg;
  }

  function loadData() {
    const tbody = document.getElementById('t19_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}];
        rows.forEach((row, i) => tbody.appendChild(makeRow(row, i > 0)));
        renumber();
        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(rows);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        tbody.innerHTML = '';
        [{}].forEach((r, i) => tbody.appendChild(makeRow(r, i > 0)));
        renumber();
        updateStatusBadge('not-started');
      });
  }

  function save() {
    const rows = collectRows();
    const fields = ['initiative', 'date'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);

    setMsg('Saving…');
    fetch(API_SAVE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ table_no: TABLE_NO, status, meta: { images: _images }, rows }),
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const msgs = {
          'done':        'Table 19 saved — all rows complete!',
          'draft':       'Table 19 saved — some rows still need all fields filled.',
          'not-started': 'Table 19 saved.',
        };
        toast(msgs[status] || msgs['not-started']);
        setMsg(`Saved · ${new Date().toLocaleTimeString()}`);
        updateStatusBadge(status);
        CMI.updateStatus(TABLE_NO, status);
      } else { toast('❌ Save failed: ' + (res.error || 'Unknown')); setMsg('Save failed'); }
    })
    .catch(() => { toast('❌ Network error.'); setMsg(''); });
  }

  function updateBadge() {
    const b = document.getElementById('t19_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t19_status_msg'); if (e) e.textContent = m; }

  window.T19 = {
    addRow() { addRow({}, true); },
    save,
    openDocs,
    _renumber: renumber,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

