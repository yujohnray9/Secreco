/**
 * t16_awards.js — Table 16: Awards Received.
 * Columns: Title of Award | Recipient/Agency | Sponsor | Event/Activity |
 *          Venue (Place of Award) | Date Awarded
 * Fixed category rows: Local | Regional | National | International
 * Each category can have multiple sub-rows added.
 */

(function () {
  'use strict';

  const TABLE_NO = 'T16';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const CATEGORIES = ['Local', 'Regional', 'National', 'International'];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t16_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 16. Awards Received.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:1050px">
          <thead>
            <tr>
              <th class="group" style="width:40px">#</th>
              <th class="group">Title of Award</th>
              <th class="group" style="width:160px">Recipient / Agency</th>
              <th class="group" style="width:140px">Sponsor</th>
              <th class="group" style="width:160px">Event / Activity</th>
              <th class="group" style="width:160px">Place of Award</th>
              <th class="group" style="width:180px">Date of Award</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t16_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        ${CATEGORIES.map(cat => `<button class="btn btn-sm" onclick="T16.addRow('${cat}')">+ Add ${cat} award</button>`).join('')}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T16.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T16.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t16_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t16_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t16_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 't16-data-row';
    tr.dataset.cat = data.category || 'Local';
    tr.innerHTML = `
      <td class="t16-num"></td>
      <td><textarea rows="2" class="t16-award"     placeholder="Title of Award"       >${esc(data.award||'')}</textarea></td>
      <td><textarea rows="2" class="t16-recipient" placeholder="Recipient / Agency"   >${esc(data.recipient||'')}</textarea></td>
      <td><textarea rows="2" class="t16-sponsor"   placeholder="Sponsor"              >${esc(data.sponsor||'')}</textarea></td>
      <td><textarea rows="2" class="t16-event"     placeholder="Event / Activity"     >${esc(data.event||'')}</textarea></td>
      <td><textarea rows="2" class="t16-venue"     placeholder="Place of Award"        >${esc(data.venue||'')}</textarea></td>
      <td><textarea rows="2" class="t16-date"      placeholder="Date of Award"        >${esc(data.date||'')}</textarea></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T16._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function addRow(category = 'Local', data = {}, removable = true) {
    if (typeof category === 'object') { data = category; category = data.category || 'Local'; }
    const tbody = document.getElementById('t16_rows');
    if (!tbody) return;
    const rows = [...tbody.querySelectorAll('tr[data-cat]')].filter(row => row.dataset.cat === category);
    const row = makeRow({ ...data, category }, removable);
    if (rows.length) rows[rows.length - 1].after(row);
    else tbody.appendChild(row);
    renumber();
  }

  function renumber() {
    CATEGORIES.forEach(cat => {
      [...document.querySelectorAll('#t16_rows tr.t16-data-row')].filter(row => row.dataset.cat === cat)
        .forEach((row, i) => { row.querySelector('.t16-num').textContent = i + 1; });
    });
  }

  function collectRows() {
    return [...document.querySelectorAll('#t16_rows tr.t16-data-row')].map(tr => ({
      category:  tr.dataset.cat || 'Local',
      award:     tr.querySelector('.t16-award')?.value     || '',
      recipient: tr.querySelector('.t16-recipient')?.value || '',
      sponsor:   tr.querySelector('.t16-sponsor')?.value   || '',
      event:     tr.querySelector('.t16-event')?.value     || '',
      venue:     tr.querySelector('.t16-venue')?.value     || '',
      date:      tr.querySelector('.t16-date')?.value      || '',
    }));
  }

  /* ─── STATUS (auto-derived) ───
     A row is "touched" if any non-category field is filled.
     "complete" if all of award/recipient/sponsor/event/venue/date are filled. */
  function isRowTouched(r) {
    return [r.award, r.recipient, r.sponsor, r.event, r.venue, r.date]
      .some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.award, r.recipient, r.sponsor, r.event, r.venue, r.date]
      .every(v => (v || '').trim() !== '');
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
    const badge = document.getElementById('t16_status_badge');
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

  function setRows(rows) {
    const tbody = document.getElementById('t16_rows');
    if (!tbody) return;
    tbody.innerHTML = '';
    const defaultRows = CATEGORIES.map(c => ({ category: c }));
    const list = (rows && rows.length) ? rows : defaultRows;
    CATEGORIES.forEach(cat => {
      const header = document.createElement('tr');
      header.className = 'cat-row';
      header.dataset.cat = cat;
      header.innerHTML = '<td colspan="8" class="category-heading">' + esc(cat) + '</td>';
      tbody.appendChild(header);
      const categoryRows = list.filter(row => (row.category || 'Local').toLowerCase() === cat.toLowerCase());
      (categoryRows.length ? categoryRows : [{ category: cat }]).forEach(row => tbody.appendChild(makeRow({ ...row, category: cat }, true)));
    });
    renumber();
    const status = computeStatus(list);
    updateStatusBadge(status);
  }

  function loadData() {
    const tbody = document.getElementById('t16_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        const defaultRows = CATEGORIES.map(c => ({ category: c }));
        const rows = (data.rows && data.rows.length) ? data.rows : defaultRows;
        setRows(rows);
        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        const status = data.status || computeStatus(rows);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        setRows([]);
        updateStatusBadge('not-started');
      });
  }

  function save(requestedStatus) {
    const rows = collectRows();
    const fields = ['award', 'recipient', 'sponsor', 'event', 'venue', 'date'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = (requestedStatus === 'draft') ? 'draft' : 'done';

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
          'done':        'Table 16 saved — all rows complete!',
          'draft':       'Table 16 saved — some rows still need all fields filled.',
          'not-started': 'Table 16 saved.',
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
    const b = document.getElementById('t16_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t16_status_msg'); if (e) e.textContent = m; }

  window.T16 = {
    addRow,
    _renumber: renumber,
    setRows,
    save,
    openDocs,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

