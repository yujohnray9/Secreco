/**
 * t18_cmi_contributions.js — Table 18: List of CMI Contributions,
 * CY 2025 (January – December).
 * Columns: Name of CMI | Amount of Contribution (cash/in-kind/services)
 */

(function () {
  'use strict';

  const TABLE_NO = 'T18';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t18_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 18. List of CMI Contributions, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:500px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Name of CMI</th>
              <th class="group" style="width:280px">Amount of Contribution*</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t18_rows"></tbody>
        </table>
        <p style="font-size:11px;color:var(--text-muted);margin:6px 0 0">
          * Indicate whether the contribution is in kind or in the form of services rendered.
        </p>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T18.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T18.openDocs()">
          📎 Documentation <span id="t18_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t18_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t18_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t18-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t18-cmi"    placeholder="Name of CMI"              value="${esc(data.cmi||'')}"/></td>
      <td><input type="text" class="t18-amount" placeholder="Amount / In-kind / Services"
                                                value="${esc(data.amount||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T18._renumber()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t18_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t18-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t18_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t18_rows tr')].map(tr => ({
      cmi:    tr.querySelector('.t18-cmi')?.value    || '',
      amount: tr.querySelector('.t18-amount')?.value || '',
    }));
  }

  /* ─── STATUS (auto-derived) ───
     A row is "touched" if cmi or amount has a value.
     "complete" if both fields are filled. */
  function isRowTouched(r) {
    return [r.cmi, r.amount].some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.cmi, r.amount].every(v => (v || '').trim() !== '');
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
    const badge = document.getElementById('t18_status_badge');
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
    const tbody = document.getElementById('t18_rows');
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
    const fields = ['cmi', 'amount'];
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
          'done':        '✅ Table 18 saved — all rows complete!',
          'draft':       '💾 Table 18 saved — some rows still need all fields filled.',
          'not-started': '💾 Table 18 saved.',
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
    const b = document.getElementById('t18_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t18_status_msg'); if (e) e.textContent = m; }

  window.T18 = {
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

