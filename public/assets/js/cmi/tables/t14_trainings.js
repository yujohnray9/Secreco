/**
 * t14_trainings.js — Table 14: Non-degree Trainings Conducted/Facilitated,
 * CY 2025 (January – December).
 * Columns: Title of Activity | Date/Venue | Number of Participants | Expenditures | Source of Funds
 */

(function () {
  'use strict';

  const TABLE_NO = 'T14';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t14_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 14. Non-degree Trainings Conducted/ Facilitated, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:700px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:36px">#</th>
              <th class="group" rowspan="2">Title of Activity</th>
              <th class="group" rowspan="2" style="width:160px">Date / Venue</th>
              <th class="group" rowspan="2" style="width:120px">Number of Participants</th>
              <th class="group" rowspan="2" style="width:130px">Expenditures</th>
              <th class="group" rowspan="2" style="width:140px">Source of Funds</th>
              <th class="group" rowspan="2" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t14_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T14.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T14.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t14_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t14_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t14_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t14-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t14-title"   placeholder="Title of Activity"       value="${esc(data.title||'')}"/></td>
      <td><textarea class="t14-venue" rows="2" style="width:100%;resize:vertical"
            placeholder="Date / Venue">${esc(data.venue||'')}</textarea></td>
      <td><input type="number" class="t14-participants" placeholder="No. of Participants"
            style="width:100%" value="${esc(data.participants||'')}"/></td>
      <td><input type="text" class="t14-expenditures" placeholder="Amount / Expenditures"
            value="${esc(data.expenditures||'')}"/></td>
      <td><input type="text" class="t14-funds"   placeholder="Source of Funds"         value="${esc(data.funds||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T14._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t14_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t14-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t14_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t14_rows tr')].map(tr => ({
      title:        tr.querySelector('.t14-title')?.value        || '',
      venue:        tr.querySelector('.t14-venue')?.value        || '',
      participants: tr.querySelector('.t14-participants')?.value || '',
      expenditures: tr.querySelector('.t14-expenditures')?.value || '',
      funds:        tr.querySelector('.t14-funds')?.value        || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t14_rows');
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
      });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function isRowTouched(r) { return ['title', 'venue', 'participants', 'expenditures', 'funds'].some(k => (r[k] || '').trim() !== ''); }
  function isRowComplete(r) { return (r.title || '').trim() !== '' && (r.venue || '').trim() !== ''; }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(status) { if (status === 'done') return 'Complete'; if (status === 'draft') return 'In Progress'; return 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t14_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { 'done': { bg: '#e6f4ea', fg: 'var(--green, #1e7e34)' }, 'draft': { bg: '#fff4e5', fg: '#b06b00' }, 'not-started': { bg: '#f1f1f1', fg: '#777' } };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg;
    badge.style.color = c.fg;
  }

  function save() {
    const rows = collectRows();
    const fields = ['title', 'venue', 'participants', 'expenditures', 'funds'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);
    const msgs = { 'done': 'Table 14 saved — all rows complete!', 'draft': 'Table 14 saved — some rows still incomplete.', 'not-started': 'Table 14 saved.' };

    setMsg('Saving…');
    fetch(API_SAVE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ table_no: TABLE_NO, status, meta: { images: _images }, rows }),
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        toast(msgs[status] || msgs['not-started']);
        setMsg(`Saved · ${new Date().toLocaleTimeString()}`);
        updateStatusBadge(status);
        CMI.updateStatus(TABLE_NO, status);
      } else { toast('❌ Save failed: ' + (res.error || 'Unknown')); setMsg('Save failed'); }
    })
    .catch(() => { toast('❌ Network error.'); setMsg(''); });
  }

  function updateBadge() {
    const b = document.getElementById('t14_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t14_status_msg'); if (e) e.textContent = m; }

  window.T14 = {
    addRow()   { addRow({}, true); },
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

