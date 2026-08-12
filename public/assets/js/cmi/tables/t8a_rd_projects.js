/**
 * t8a_rd_projects.js — Table 8a: List of R&D Programs/Projects Packaged, Approved,
 * and Implemented, CY 2025 (January – December).
 * Grouped: Proposals Packaged | Projects Approved and Implemented
 * Columns: Program/Project Title | Researcher | Implementing Agency |
 *          Duration* | Source of Funds/Budget | Regional Priority Commodities Addressed
 * Note: *Indicate start and end dates
 */

(function () {
  'use strict';

  const TABLE_NO = 'T8a';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const CATEGORIES = [
    { key: 'packaged',     label: 'Proposals Packaged' },
    { key: 'implemented',  label: 'Projects Approved and Implemented' },
  ];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t8a_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 8a. List of R&amp;D Programs/ Projects Packaged, Approved, and Implemented, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:820px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Program / Project Title</th>
              <th class="group" style="width:150px">Researcher</th>
              <th class="group" style="width:160px">Implementing Agency</th>
              <th class="group" style="width:130px">Duration <sup style="color:var(--green)">*</sup></th>
              <th class="group" style="width:140px">Source of Funds / Budget</th>
              <th class="group" style="width:160px">Regional Priority Commodities Addressed</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t8a_rows"></tbody>
        </table>
      </div>

      <div style="font-size:11px;color:var(--text-muted);font-style:italic;margin-bottom:10px">
        * Indicate start and end dates
      </div>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        ${CATEGORIES.map(c =>
          `<button class="btn btn-sm" onclick="T8a.addRow('${c.key}')">+ Add (${c.label})</button>`
        ).join('')}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T8a.openDocs()">
          📎 Documentation <span id="t8a_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t8a_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t8a_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS ─── */
  function isRowComplete(r) {
    return (r.title||'').trim() !== '' && (r.agency||'').trim() !== '';
  }
  function isRowTouched(r) {
    return ['title','researcher','agency','duration','funds','commodity'].some(f => (r[f]||'').trim() !== '');
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(s) {
    return s === 'done' ? 'Complete' : s === 'draft' ? 'In Progress' : 'Not Started';
  }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t8a_status_badge');
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

  function makeCatHeader(cat) {
    const tr = document.createElement('tr');
    tr.className = 'cat-row';
    tr.dataset.cat = cat.key;
    tr.innerHTML = `<td colspan="8" style="font-weight:700;background:var(--bg-soft,#f3f6f4)">${esc(cat.label)}</td>`;
    return tr;
  }

  function makeRow(catKey, data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.dataset.cat = catKey;
    tr.innerHTML = `
      <td class="t8a-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t8a-title"      placeholder="Program / Project Title"     value="${esc(data.title||'')}"/></td>
      <td><input type="text" class="t8a-researcher"  placeholder="Researcher"                  value="${esc(data.researcher||'')}"/></td>
      <td><input type="text" class="t8a-agency"      placeholder="Implementing Agency"         value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t8a-duration"    placeholder="e.g. Jan 2025 – Dec 2027"   value="${esc(data.duration||'')}"/></td>
      <td><input type="text" class="t8a-funds"       placeholder="Source / Budget"             value="${esc(data.funds||'')}"/></td>
      <td><input type="text" class="t8a-commodity"   placeholder="Priority Commodity"          value="${esc(data.commodity||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T8a._renumber('${catKey}')">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber(catKey) {
    document.querySelectorAll(`#t8a_rows tr.data-row[data-cat="${catKey}"]`).forEach((tr, i) => {
      const c = tr.querySelector('.t8a-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(catKey, data = {}, removable = true) {
    const tbody = document.getElementById('t8a_rows');
    if (!tbody) return;
    const rows = tbody.querySelectorAll(`tr[data-cat="${catKey}"]`);
    const last = rows[rows.length - 1];
    const newRow = makeRow(catKey, data, removable);
    if (last) last.insertAdjacentElement('afterend', newRow);
    else tbody.appendChild(newRow);
    renumber(catKey);
  }

  function collectRows() {
    return [...document.querySelectorAll('#t8a_rows tr.data-row')].map(tr => ({
      category:   tr.dataset.cat,
      title:      tr.querySelector('.t8a-title')?.value      || '',
      researcher: tr.querySelector('.t8a-researcher')?.value || '',
      agency:     tr.querySelector('.t8a-agency')?.value     || '',
      duration:   tr.querySelector('.t8a-duration')?.value   || '',
      funds:      tr.querySelector('.t8a-funds')?.value      || '',
      commodity:  tr.querySelector('.t8a-commodity')?.value  || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t8a_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const saved = (data.rows && data.rows.length) ? data.rows : [];
        const byCat = {};
        CATEGORIES.forEach(c => { byCat[c.key] = []; });
        saved.forEach(row => { if (byCat[row.category]) byCat[row.category].push(row); });

        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          const rows = byCat[cat.key].length ? byCat[cat.key] : [{}];
          rows.forEach((row, i) => tbody.appendChild(makeRow(cat.key, row, i > 0)));
          renumber(cat.key);
        });

        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(saved.length ? saved : []);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        tbody.innerHTML = '';
        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          [{}].forEach((r, i) => tbody.appendChild(makeRow(cat.key, r, i > 0)));
          renumber(cat.key);
        });
        updateStatusBadge('not-started');
      });
  }

  function save() {
    const rows = collectRows();
    const fields = ['title', 'researcher', 'agency', 'duration', 'funds', 'commodity'];
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
          'done':        '✅ Table 8a saved — all rows complete!',
          'draft':       '💾 Table 8a saved — some rows still incomplete.',
          'not-started': '💾 Table 8a saved.',
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
    const b = document.getElementById('t8a_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t8a_status_msg'); if (e) e.textContent = m; }

  window.T8a = {
    addRow(catKey)    { addRow(catKey, {}, true); },
    save,
    openDocs,
    _renumber(catKey) { renumber(catKey); },
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

