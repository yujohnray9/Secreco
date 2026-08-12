/**
 * t10_tt_projects.js — Table 10: List of Technology Transfer Program/Projects
 * Packaged, Approved, and Implemented, CY 2025 (January – December).
 *
 * Complex header — two logical row states per entry:
 *   Row A: Program/Project Title | Agency Proponent | Regional Priority Addressed |
 *          Status → [Approved | For Revision | Disapproved]
 *   Row B (continuation of same project when Approved):
 *          [same title/agency/priority cols merged] |
 *          Approved Duration | Approved Budget | Funding Source
 *
 * Implementation approach: single row per project with all fields;
 * the "Approved sub-fields" are only visible/editable when Status = Approved.
 * Grouped: Proposals Packaged | Projects Approved and Implemented
 */

(function () {
  'use strict';

  const TABLE_NO = 'T10';
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
    <div class="t-page" id="t10_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 10. List of Technology Transfer Program/ Projects Packaged, Approved, and Implemented, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:900px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:36px">#</th>
              <th class="group" rowspan="2">Program / Project Title</th>
              <th class="group" rowspan="2" style="width:160px">Agency Proponent</th>
              <th class="group" rowspan="2" style="width:160px">Regional Priority Addressed</th>
              <th class="group" colspan="3" style="width:180px">Status</th>
              <th class="group" colspan="3">If Approved</th>
              <th class="group" rowspan="2" style="width:36px"></th>
            </tr>
            <tr>
              <th class="sub" style="width:80px">Approved</th>
              <th class="sub" style="width:80px">For Revision</th>
              <th class="sub" style="width:90px">Disapproved</th>
              <th class="sub">Approved Duration</th>
              <th class="sub">Approved Budget</th>
              <th class="sub">Funding Source</th>
            </tr>
          </thead>
          <tbody id="t10_rows"></tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        ${CATEGORIES.map(c =>
          `<button class="btn btn-sm" onclick="T10.addRow('${c.key}')">+ Add (${c.label})</button>`
        ).join('')}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T10.openDocs()">
          📎 Documentation <span id="t10_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t10_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t10_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS ─── */
  function isRowComplete(r) {
    const hasTitle    = (r.title||'').trim() !== '';
    const hasAgency   = (r.agency||'').trim() !== '';
    const hasStatus   = r.approved || r.for_revision || r.disapproved;
    return hasTitle && hasAgency && hasStatus;
  }
  function isRowTouched(r) {
    const hasText = ['title','agency','priority','duration','budget','fund'].some(f => (r[f]||'').trim() !== '');
    return hasText || r.approved || r.for_revision || r.disapproved;
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
    const badge = document.getElementById('t10_status_badge');
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
    tr.innerHTML = `<td colspan="11" style="font-weight:700;background:var(--bg-soft,#f3f6f4)">${esc(cat.label)}</td>`;
    return tr;
  }

  function makeRow(catKey, data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.dataset.cat = catKey;

    const mkChk = (field) =>
      `<input type="checkbox" class="t10-${field}" ${data[field] ? 'checked' : ''}
        onchange="T10._syncStatus(this,'${field}')"/>`;

    tr.innerHTML = `
      <td class="t10-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t10-title"    placeholder="Program / Project Title"   value="${esc(data.title||'')}"/></td>
      <td><input type="text" class="t10-agency"   placeholder="Agency Proponent"          value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t10-priority" placeholder="Regional Priority"         value="${esc(data.priority||'')}"/></td>
      <td style="text-align:center">${mkChk('approved')}</td>
      <td style="text-align:center">${mkChk('for_revision')}</td>
      <td style="text-align:center">${mkChk('disapproved')}</td>
      <td><input type="text" class="t10-duration" placeholder="Duration (if approved)"    value="${esc(data.duration||'')}"/></td>
      <td><input type="text" class="t10-budget"   placeholder="Budget (if approved)"      value="${esc(data.budget||'')}"/></td>
      <td><input type="text" class="t10-fund"     placeholder="Funding Source"            value="${esc(data.fund||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T10._renumber('${catKey}')">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  /* Ensure checkboxes are mutually exclusive within a row */
  function syncStatus(checkbox, field) {
    const tr = checkbox.closest('tr');
    if (!tr || !checkbox.checked) return;
    ['approved', 'for_revision', 'disapproved'].forEach(f => {
      if (f !== field) {
        const cb = tr.querySelector(`.t10-${f}`);
        if (cb) cb.checked = false;
      }
    });
  }

  function renumber(catKey) {
    document.querySelectorAll(`#t10_rows tr.data-row[data-cat="${catKey}"]`).forEach((tr, i) => {
      const c = tr.querySelector('.t10-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(catKey, data = {}, removable = true) {
    const tbody = document.getElementById('t10_rows');
    if (!tbody) return;
    const rows = tbody.querySelectorAll(`tr[data-cat="${catKey}"]`);
    const last = rows[rows.length - 1];
    const newRow = makeRow(catKey, data, removable);
    if (last) last.insertAdjacentElement('afterend', newRow);
    else tbody.appendChild(newRow);
    renumber(catKey);
  }

  function collectRows() {
    return [...document.querySelectorAll('#t10_rows tr.data-row')].map(tr => ({
      category:     tr.dataset.cat,
      title:        tr.querySelector('.t10-title')?.value          || '',
      agency:       tr.querySelector('.t10-agency')?.value         || '',
      priority:     tr.querySelector('.t10-priority')?.value       || '',
      approved:     tr.querySelector('.t10-approved')?.checked     || false,
      for_revision: tr.querySelector('.t10-for_revision')?.checked || false,
      disapproved:  tr.querySelector('.t10-disapproved')?.checked  || false,
      duration:     tr.querySelector('.t10-duration')?.value       || '',
      budget:       tr.querySelector('.t10-budget')?.value         || '',
      fund:         tr.querySelector('.t10-fund')?.value           || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t10_rows');
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

    const textFields = ['title', 'agency', 'priority', 'duration', 'budget', 'fund'];
    const hasTextContent = CMIUtils.filterEmptyRows(rows, textFields).length > 0;
    const hasCheckedBox  = rows.some(r => r.approved || r.for_revision || r.disapproved);
    if (!hasTextContent && !hasCheckedBox) {
      toast('⚠️ Wala kang nilagay. Hindi maisasave kung walang data.');
      return;
    }

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
          'done':        '✅ Table 10 saved — all rows complete!',
          'draft':       '💾 Table 10 saved — some rows still incomplete.',
          'not-started': '💾 Table 10 saved.',
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
    const b = document.getElementById('t10_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t10_status_msg'); if (e) e.textContent = m; }

  window.T10 = {
    addRow(catKey)         { addRow(catKey, {}, true); },
    save,
    openDocs,
    _renumber(catKey)      { renumber(catKey); },
    _syncStatus(cb, field) { syncStatus(cb, field); },
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

