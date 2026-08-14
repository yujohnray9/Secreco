/**
 * t15_equipment_facilities.js — Table 15: Equipment/Facilities Funded,
 * CY 2025 (January – December).
 * Fixed category rows: Endorsed | Approved | Facilities Upgraded |
 *                      Facilities Established | Equipment Purchased
 * Columns: Equipment/Facilities | Location/Agency | Expenditures | Source(s) of Funds
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
    'Equipment Purchased',
  ];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const rows = CATEGORIES.map((cat, i) => `
      <tr>
        <td style="font-weight:600;padding:6px 8px">${esc(cat)}</td>
        <td><input type="text" class="t15-item"     data-idx="${i}" placeholder="Equipment / Facility name"  value=""/></td>
        <td><input type="text" class="t15-location" data-idx="${i}" placeholder="Location / Agency"           value=""/></td>
        <td><input type="text" class="t15-expense"  data-idx="${i}" placeholder="Amount / Expenditures"       value=""/></td>
        <td><input type="text" class="t15-funds"    data-idx="${i}" placeholder="Source(s) of Funds"          value=""/></td>
      </tr>`).join('');

    return `
    <div class="t-page" id="t15_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 15. Equipment/ Facilities Funded, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:700px">
          <thead>
            <tr>
              <th class="group" style="width:180px">Equipment / Facilities Established / Upgraded / Approved</th>
              <th class="group">Item Description</th>
              <th class="group" style="width:160px">Location / Agency</th>
              <th class="group" style="width:140px">Expenditures</th>
              <th class="group" style="width:150px">Source(s) of Funds</th>
            </tr>
          </thead>
          <tbody id="t15_rows">${rows}</tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T15.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t15_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t15_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t15_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function collectRows() {
    return CATEGORIES.map((cat, i) => ({
      category: cat,
      item:     document.querySelector(`.t15-item[data-idx="${i}"]`)?.value     || '',
      location: document.querySelector(`.t15-location[data-idx="${i}"]`)?.value || '',
      expense:  document.querySelector(`.t15-expense[data-idx="${i}"]`)?.value  || '',
      funds:    document.querySelector(`.t15-funds[data-idx="${i}"]`)?.value    || '',
    }));
  }

  function loadData() {
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        if (data.rows && data.rows.length) {
          data.rows.forEach((row, i) => {
            const set = (cls, val) => {
              const el = document.querySelector(`.${cls}[data-idx="${i}"]`);
              if (el) el.value = val || '';
            };
            set('t15-item',     row.item);
            set('t15-location', row.location);
            set('t15-expense',  row.expense);
            set('t15-funds',    row.funds);
          });
        }
        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(collectRows());
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {});
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function isRowTouched(r) { return ['item', 'location', 'expense', 'funds'].some(k => (r[k] || '').trim() !== ''); }
  function isRowComplete(r) { return (r.item || '').trim() !== '' && (r.location || '').trim() !== ''; }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
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

  function save() {
    const rows = collectRows();
    const fields = ['item', 'location', 'expense', 'funds'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);
    const msgs = { 'done': 'Table 15 saved — all rows complete!', 'draft': 'Table 15 saved — some rows still incomplete.', 'not-started': 'Table 15 saved.' };

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

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t15_status_msg'); if (e) e.textContent = m; }

  window.T15 = {
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

