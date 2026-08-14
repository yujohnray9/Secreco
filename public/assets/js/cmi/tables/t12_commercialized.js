/**
 * t12_commercialized.js — Table 12: List of Technologies Commercialized or
 * Pre-Commercialization Initiatives, CY 2025 (January – December).
 * Columns: Name of Technology | Technology Owner |
 *          Status → Pre-Commercialization Activity Undertaken |
 *                   Commercialized → Name of Person/Firm Adopters → Licensor | Start-Up | Spin-off
 */

(function () {
  'use strict';

  const TABLE_NO = 'T12';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t12_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 12. List of Technologies Commercialized or Pre-Commercialization Initiatives, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:820px">
          <thead>
            <tr>
              <th class="group" rowspan="3" style="width:36px">#</th>
              <th class="group" rowspan="3">Name of Technology</th>
              <th class="group" rowspan="3" style="width:160px">Technology Owner</th>
              <th class="group" colspan="4">Status</th>
              <th class="group" rowspan="3" style="width:36px"></th>
            </tr>
            <tr>
              <th class="sub" rowspan="2" style="width:180px">Pre-Commercialization Activity Undertaken</th>
              <th class="sub" colspan="3">Commercialized — Name of Person/Firm Adopters</th>
            </tr>
            <tr>
              <th class="sub" style="width:120px">Licensor</th>
              <th class="sub" style="width:120px">Start-Up</th>
              <th class="sub" style="width:120px">Spin-off</th>
            </tr>
          </thead>
          <tbody id="t12_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T12.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T12.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t12_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t12_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t12_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t12-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t12-tech"     placeholder="Name of Technology"                      value="${esc(data.tech||'')}"/></td>
      <td><input type="text" class="t12-owner"    placeholder="Technology Owner"                        value="${esc(data.owner||'')}"/></td>
      <td><textarea class="t12-precomm" placeholder="Pre-Commercialization Activity Undertaken"
            rows="2" style="width:100%;resize:vertical">${esc(data.precomm||'')}</textarea></td>
      <td><input type="text" class="t12-licensor" placeholder="Licensor (person / firm)"               value="${esc(data.licensor||'')}"/></td>
      <td><input type="text" class="t12-startup"  placeholder="Start-Up (person / firm)"               value="${esc(data.startup||'')}"/></td>
      <td><input type="text" class="t12-spinoff"  placeholder="Spin-off (person / firm)"               value="${esc(data.spinoff||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T12._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t12_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t12-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t12_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t12_rows tr')].map(tr => ({
      tech:     tr.querySelector('.t12-tech')?.value     || '',
      owner:    tr.querySelector('.t12-owner')?.value    || '',
      precomm:  tr.querySelector('.t12-precomm')?.value  || '',
      licensor: tr.querySelector('.t12-licensor')?.value || '',
      startup:  tr.querySelector('.t12-startup')?.value  || '',
      spinoff:  tr.querySelector('.t12-spinoff')?.value  || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t12_rows');
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
  function isRowTouched(r) {
    return ['tech', 'owner', 'precomm', 'licensor', 'startup', 'spinoff'].some(k => (r[k] || '').trim() !== '');
  }
  function isRowComplete(r) {
    return (r.tech || '').trim() !== '' && (r.owner || '').trim() !== '';
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(status) {
    if (status === 'done')  return 'Complete';
    if (status === 'draft') return 'In Progress';
    return 'Not Started';
  }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t12_status_badge');
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
    const fields = ['tech', 'owner', 'precomm', 'licensor', 'startup', 'spinoff'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);
    const msgs = { 'done': 'Table 12 saved — all rows complete!', 'draft': 'Table 12 saved — some rows still incomplete.', 'not-started': 'Table 12 saved.' };

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
    const b = document.getElementById('t12_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t12_status_msg'); if (e) e.textContent = m; }

  window.T12 = {
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

