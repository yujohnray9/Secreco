/**
 * t3_projects_monitored.js — Table 3: List of Projects and Activities
 * Monitored and Evaluated.
 * Simple flat table: addable rows, save draft (status auto-derived), docs modal.
 */

(function () {
  'use strict';

  const TABLE_NO = 'T3';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  /* ─── RENDER ─── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t3_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 3. List of Projects and Activities monitored and evaluated.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:600px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Projects and Activities</th>
              <th class="group" style="width:160px">Ongoing or Completed</th>
              <th class="group" style="width:140px">Duration</th>
              <th class="group" style="width:160px">Source of Fund</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t3_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T3.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T3.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T3.openDocs()">
           Documentation <span id="t3_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t3_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t3_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS (auto-derived) ─── */
  function isRowTouched(r) {
    return ['project','status','duration','fund'].some(f => (r[f] || '').trim() !== '');
  }
  function isRowComplete(r) {
    return ['project','status','duration','fund'].every(f => (r[f] || '').trim() !== '');
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(s) { return s === 'done' ? 'Complete' : s === 'draft' ? 'In Progress' : 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t3_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { done:{bg:'#e6f4ea',fg:'var(--green,#1e7e34)'}, draft:{bg:'#fff4e5',fg:'#b06b00'}, 'not-started':{bg:'#f1f1f1',fg:'#777'} };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg; badge.style.color = c.fg;
  }

  /* ─── ROW ─── */
  function makeRow(data = {}, removable = false) {
    const valProject  = data.project  || data['Projects and Activities'] || data.field1 || '';
    const valStatus   = data.status   || data['Ongoing or Completed'] || data.field2 || '';
    const valDuration = data.duration || data['Duration'] || data.field3 || '';
    const valFund     = data.fund     || data['Source of Fund'] || data.field4 || '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t3-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t3-project" placeholder="Project / Activity title" value="${esc(valProject)}"/></td>
      <td>
        <select class="t3-status" style="width:100%">
          <option value="">— select —</option>
          <option value="Ongoing"    ${valStatus==='Ongoing'    ?'selected':''}>Ongoing</option>
          <option value="Completed"  ${valStatus==='Completed'  ?'selected':''}>Completed</option>
        </select>
      </td>
      <td><input type="text" class="t3-duration" placeholder="e.g. Jan–Dec 2025" value="${esc(valDuration)}"/></td>
      <td><input type="text" class="t3-fund" placeholder="e.g. DOST, PCAARRD" value="${esc(valFund)}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T3._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t3_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t3-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t3_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  /* ─── COLLECT ─── */
  function collectRows() {
    return [...document.querySelectorAll('#t3_rows tr')].map(tr => ({
      project:  tr.querySelector('.t3-project')?.value  || '',
      status:   tr.querySelector('.t3-status')?.value   || '',
      duration: tr.querySelector('.t3-duration')?.value || '',
      fund:     tr.querySelector('.t3-fund')?.value     || '',
    }));
  }

  /* ─── LOAD ─── */
  function loadData() {
    const tbody = document.getElementById('t3_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}];
        rows.forEach((row, i) => {
          tbody.appendChild(makeRow(row, i > 0));
        });
        renumber();
        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        const status = data.status || computeStatus(rows);
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

  /* ─── SAVE ─── */
  function save(requestedStatus) {
    const rows = collectRows();
    const fields = ['title', 'agency', 'commodity', 'sites', 'status'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status   = (requestedStatus === 'draft') ? 'draft' : 'done';

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
          done:        'Table 3 saved — all rows complete!',
          draft:       'Table 3 saved — some rows still need title, status, duration, and fund source.',
          'not-started':'Table 3 saved.',
        };
        toast(msgs[status] || msgs['not-started']);
        setMsg(`Saved · ${new Date().toLocaleTimeString()}`);
        updateStatusBadge(status);
        CMI.updateStatus(TABLE_NO, status);
      } else { toast('❌ Save failed: ' + (res.error || 'Unknown')); setMsg('Save failed'); }
    })
    .catch(() => { toast('❌ Network error.'); setMsg(''); });
  }

  /* ─── BADGE / DOCS ─── */
  function updateBadge() {
    const b = document.getElementById('t3_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  /* ─── HELPERS ─── */
  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t3_status_msg'); if (e) e.textContent = m; }

  /* ─── PUBLIC ─── */
  window.T3 = {
    addRow()       { addRow({}, true); },
    save,
    openDocs,
    _renumber:     renumber,
  };

  /* ─── REGISTER ─── */
  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

