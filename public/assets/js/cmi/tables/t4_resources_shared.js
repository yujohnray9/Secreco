/**
 * t4_resources_shared.js — Table 4: Resources Shared, CY 2025 (January – December).
 * Columns: Donor/Source | Activity/Project | Amount Shared | Remarks
 * Includes: Activity/Project note with enumerated options.
 */

(function () {
  'use strict';

  const TABLE_NO = 'T4';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const ACTIVITY_NOTE = `Activity/Project can be:
• Implementation of Consortium-led R&D and Technology Transfer-related programs/activities
• HRD activities
• Improvement of consortium's or member-institutions' facilities
• Planning/consultation activities
• AIHRs/Sectoral Reviews • RSRDH
• Regional Fairs/Exhibits (e.g., FIESTA, etc.)
• Others (specify)`;

  /* ─── RENDER ─── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t4_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 4. Resources Shared, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:580px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group" style="width:180px">Donor / Source</th>
              <th class="group">Activity / Project <sup style="color:var(--green)">*</sup></th>
              <th class="group" style="width:140px">Amount Shared</th>
              <th class="group" style="width:160px">Remarks</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t4_rows"></tbody>
        </table>
      </div>

      <div style="font-size:11px;color:var(--text-muted);font-style:italic;white-space:pre-line;margin-bottom:14px;line-height:1.6">
        ${esc(ACTIVITY_NOTE)}
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T4.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T4.openDocs()">
           Documentation <span id="t4_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t4_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t4_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function isRowTouched(r) {
    return ['donor','activity','amount','remarks'].some(f => (r[f] || '').trim() !== '');
  }
  function isRowComplete(r) {
    return ['donor','activity','amount'].every(f => (r[f] || '').trim() !== '');
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(s) { return s === 'done' ? 'Complete' : s === 'draft' ? 'In Progress' : 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t4_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { done:{bg:'#e6f4ea',fg:'var(--green,#1e7e34)'}, draft:{bg:'#fff4e5',fg:'#b06b00'}, 'not-started':{bg:'#f1f1f1',fg:'#777'} };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg; badge.style.color = c.fg;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t4-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t4-donor" placeholder="Donor / Source" value="${esc(data.donor||'')}"/></td>
      <td><input type="text" class="t4-activity" placeholder="Activity / Project" value="${esc(data.activity||'')}"/></td>
      <td><input type="text" class="t4-amount" placeholder="e.g. ₱ 50,000" value="${esc(data.amount||'')}"/></td>
      <td><input type="text" class="t4-remarks" placeholder="Remarks" value="${esc(data.remarks||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T4._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t4_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t4-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t4_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t4_rows tr')].map(tr => ({
      donor:    tr.querySelector('.t4-donor')?.value    || '',
      activity: tr.querySelector('.t4-activity')?.value || '',
      amount:   tr.querySelector('.t4-amount')?.value   || '',
      remarks:  tr.querySelector('.t4-remarks')?.value  || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t4_rows');
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

  function save(requestedStatus) {
    const rows = collectRows();
    const fields = ['donor', 'activity', 'amount', 'remarks'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    let status = 'draft';
    if (requestedStatus === 'done') {
      status = 'done';
    } else if (requestedStatus === 'draft' || window._cmiSavingDraft) {
      status = 'draft';
    } else {
      status = computeStatus(rows);
    }

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
          done:        'Table 4 saved — all rows complete!',
          draft:       'Table 4 saved — some rows still need a donor, activity, and amount.',
          'not-started':'Table 4 saved.',
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
    const b = document.getElementById('t4_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t4_status_msg'); if (e) e.textContent = m; }

  window.T4 = {
    addRow()       { addRow({}, true); },
    save,
    openDocs,
    _renumber:     renumber,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

