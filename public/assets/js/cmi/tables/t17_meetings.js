/**
 * t17_meetings.js — Table 17: Schedule, Venue, Host Agencies of Regular Meetings,
 * CY 2025 (January – December).
 * Columns: Type of Meeting/Activity | Venue and Date | Host Agency
 */

(function () {
  'use strict';

  const TABLE_NO = 'T17';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t17_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 17. Schedule, Venue, Host Agencies of Regular Meetings, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:600px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:36px">#</th>
              <th class="group">Type of Meeting / Activity</th>
              <th class="group" style="width:200px">Venue and Date</th>
              <th class="group" style="width:180px">Host Agency</th>
              <th class="group" rowspan="2" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t17_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T17.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T17.openDocs()">
          📎 Documentation <span id="t17_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t17_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t17_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t17-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t17-type"  placeholder="Type of Meeting / Activity" value="${esc(data.type||'')}"/></td>
      <td><textarea class="t17-venue" rows="2" style="width:100%;resize:vertical"
            placeholder="Venue and Date">${esc(data.venue||'')}</textarea></td>
      <td><input type="text" class="t17-host"  placeholder="Host Agency"                value="${esc(data.host||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T17._renumber()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t17_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t17-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t17_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t17_rows tr')].map(tr => ({
      type:  tr.querySelector('.t17-type')?.value  || '',
      venue: tr.querySelector('.t17-venue')?.value || '',
      host:  tr.querySelector('.t17-host')?.value  || '',
    }));
  }

  /* ─── STATUS (auto-derived) ───
     A row is "touched" if type/venue/host has any value.
     "complete" if all three fields are filled. */
  function isRowTouched(r) {
    return [r.type, r.venue, r.host].some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.type, r.venue, r.host].every(v => (v || '').trim() !== '');
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
    const badge = document.getElementById('t17_status_badge');
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
    const tbody = document.getElementById('t17_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}, {}, {}, {}];
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
        [{}, {}, {}, {}].forEach((r, i) => tbody.appendChild(makeRow(r, i > 0)));
        renumber();
        updateStatusBadge('not-started');
      });
  }

  function save() {
    const rows = collectRows();
    const fields = ['type', 'venue', 'host'];
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
          'done':        '✅ Table 17 saved — all rows complete!',
          'draft':       '💾 Table 17 saved — some rows still need all fields filled.',
          'not-started': '💾 Table 17 saved.',
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
    const b = document.getElementById('t17_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t17_status_msg'); if (e) e.textContent = m; }

  window.T17 = {
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

