/**
 * t15_equipment_facilities.js — Table 15: Equipment/Facilities Funded.
 * 5 Categories with section headers and designated Add buttons.
 * Columns: Item Description | Location / Agency | Expenditures | Source(s) of Funds
 */

(function () {
  'use strict';

  const TABLE_NO = 'T15';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const CATEGORIES = [
    'Endorsed',
    'Approved',
    'Facilities Upgraded',
    'Facilities Established',
    'Equipment Purchased'
  ];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const addBtns = CATEGORIES.map(cat =>
      `<button class="btn btn-sm" type="button" onclick="T15.addRow('${esc(cat)}')" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600;padding:7px 12px;text-align:center;font-size:12px">+ Add (${esc(cat)})</button>`
    ).join('');

    return `
    <div class="t-page" id="t15_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 15. Equipment/ Facilities Funded.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:680px">
          <thead>
            <tr>
              <th class="group" style="width:30%">Item Description</th>
              <th class="group" style="width:25%">Location / Agency</th>
              <th class="group" style="width:20%">Expenditures</th>
              <th class="group" style="width:25%">Source(s) of Funds</th>
              <th class="group" style="width:40px;text-align:center"></th>
            </tr>
          </thead>
          <tbody id="t15_rows"></tbody>
        </table>
      </div>

      <!-- Add Buttons Grid matching Image 3 -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:8px;margin-bottom:14px">
        ${addBtns}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" type="button" onclick="T15.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" type="button" onclick="T15.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t15_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t15_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t15_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeCatHeader(cat) {
    return `<tr class="t15-cat-row" data-cat="${esc(cat)}">
      <td colspan="5" style="font-weight:700;background:var(--bg-soft,#f3f6f4);padding:9px 12px;color:var(--forest,#075b42);border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb">${esc(cat)}</td>
    </tr>`;
  }

  function createRowHTML(cat, data = {}) {
    const tr = document.createElement('tr');
    tr.className = 't15-data-row';
    tr.dataset.cat = cat;
    tr.innerHTML = `
      <td><textarea rows="2" class="t15-item" placeholder="Item description" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:4px">${esc(data.item || '')}</textarea></td>
      <td><textarea rows="2" class="t15-location" placeholder="Location / Agency" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:4px">${esc(data.location || '')}</textarea></td>
      <td><input type="number" step="0.01" min="0" class="t15-expense" placeholder="0.00" value="${esc(data.expense || data.expenditures || '')}" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:4px"/></td>
      <td><textarea rows="2" class="t15-funds" placeholder="Source(s) of Funds" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:4px">${esc(data.funds || data.source || '')}</textarea></td>
      <td style="text-align:center">
        <button type="button" class="row-remove-btn" onclick="this.closest('tr').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;font-weight:700;padding:0 6px;line-height:1" title="Remove row">&times;</button>
      </td>
    `;
    return tr;
  }

  function addRow(cat = CATEGORIES[0], data = {}) {
    const tbody = document.getElementById('t15_rows');
    if (!tbody) return;
    const catRows = tbody.querySelectorAll(`tr.t15-data-row[data-cat="${cat}"]`);
    const tr = createRowHTML(cat, data);
    if (catRows.length > 0) {
      catRows[catRows.length - 1].insertAdjacentElement('afterend', tr);
    } else {
      const header = tbody.querySelector(`tr.t15-cat-row[data-cat="${cat}"]`);
      if (header) header.insertAdjacentElement('afterend', tr);
      else tbody.appendChild(tr);
    }
    const input = tr.querySelector('.t15-item');
    if (input && !data.item) input.focus();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t15_rows tr.t15-data-row')].map(tr => ({
      category: tr.dataset.cat || CATEGORIES[0],
      item:     tr.querySelector('.t15-item')?.value.trim()     || '',
      location: tr.querySelector('.t15-location')?.value.trim() || '',
      expense:  tr.querySelector('.t15-expense')?.value.trim()  || '',
      funds:    tr.querySelector('.t15-funds')?.value.trim()    || '',
    })).filter(r => r.item !== '' || r.location !== '' || r.expense !== '' || r.funds !== '');
  }

  function matchCategory(catStr) {
    const s = String(catStr || '').toLowerCase().trim();
    if (s.includes('endors')) return 'Endorsed';
    if (s.includes('approv')) return 'Approved';
    if (s.includes('upgrad')) return 'Facilities Upgraded';
    if (s.includes('estab')) return 'Facilities Established';
    if (s.includes('purch') || s.includes('equip')) return 'Equipment Purchased';
    return CATEGORIES[0];
  }

  function setRows(rows) {
    const tbody = document.getElementById('t15_rows');
    if (!tbody) return;
    tbody.innerHTML = '';

    const savedRows = (rows && Array.isArray(rows)) ? rows : [];
    const byCat = {};
    CATEGORIES.forEach(c => { byCat[c] = []; });

    savedRows.forEach(r => {
      const rowItem = (r.item || '').trim();
      const rowLoc  = (r.location || '').trim();
      const rowExp  = (r.expense || r.expenditures || '').toString().trim();
      const rowFunds= (r.funds || r.source || '').trim();
      if (!rowItem && !rowLoc && !rowExp && !rowFunds) return;

      const normRow = {
        item: rowItem,
        location: rowLoc,
        expense: rowExp,
        funds: rowFunds,
      };
      const cat = matchCategory(r.category);
      byCat[cat].push(normRow);
    });

    CATEGORIES.forEach(cat => {
      tbody.insertAdjacentHTML('beforeend', makeCatHeader(cat));
      const list = byCat[cat];
      if (list && list.length > 0) {
        list.forEach(r => tbody.appendChild(createRowHTML(cat, r)));
      } else {
        tbody.appendChild(createRowHTML(cat, {}));
      }
    });

    const status = computeStatus(collectRows());
    updateStatusBadge(status);
  }

  function loadData() {
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        const savedRows = (data.rows && Array.isArray(data.rows)) ? data.rows : [];
        setRows(savedRows);

        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        const status = data.status || computeStatus(collectRows());
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        setRows([]);
      });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function computeStatus(rows) {
    if (rows.length === 0) return 'not-started';
    return 'done';
  }
  function statusLabel(status) { if (status === 'done') return 'Complete'; if (status === 'draft') return 'In Progress'; return 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t15_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { 'done': { bg: '#e6f4ea', fg: 'var(--green, #1e7e34)' }, 'draft': { bg: '#fff4e5', fg: '#b06b00' }, 'not-started': { bg: '#f1f1f1', fg: '#777' } };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg;
    badge.style.color = c.fg;
  }

  function save(requestedStatus) {
    const rows = collectRows();
    const fields = ['item', 'location', 'expense', 'funds'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = (requestedStatus === 'draft') ? 'draft' : 'done';
    const msgs = { 'done': 'Table 15 saved — all rows complete!', 'draft': 'Table 15 saved — draft saved.', 'not-started': 'Table 15 saved.' };

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
    const b = document.getElementById('t15_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s ?? '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t15_status_msg'); if (e) e.textContent = m; }

  window.T15 = {
    save,
    addRow,
    openDocs,
    setRows,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();
