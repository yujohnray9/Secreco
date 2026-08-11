/**
 * t9_technologies_generated.js — Table 9: List of Technologies/Information Generated from R&D,
 * CY 2025 (January – December).
 * Flat addable table.
 * Columns: Title of Technology/Brief Description | Project/Program Source |
 *          Agency | Researcher(s) | Potential impact or contribution
 */

(function () {
  'use strict';

  const TABLE_NO = 'T9';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t9_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 9. List of Technologies/ Information Generated from R&amp;D, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:780px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Title of Technology / Brief Description</th>
              <th class="group" style="width:170px">Project / Program Source</th>
              <th class="group" style="width:150px">Agency</th>
              <th class="group" style="width:160px">Researcher(s)</th>
              <th class="group">Potential Impact or Contribution</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t9_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T9.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T9.openDocs()">
          📎 Documentation <span id="t9_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t9_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t9_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS ─── */
  function isRowComplete(r) {
    return (r.title||'').trim() !== '' && (r.agency||'').trim() !== '';
  }
  function isRowTouched(r) {
    return ['title','source','agency','researcher','impact'].some(f => (r[f]||'').trim() !== '');
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
    const badge = document.getElementById('t9_status_badge');
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

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t9-num" style="text-align:center;font-weight:600"></td>
      <td><textarea class="t9-title"  placeholder="Title / Brief Description"
            rows="2" style="width:100%;resize:vertical">${esc(data.title||'')}</textarea></td>
      <td><input type="text" class="t9-source"     placeholder="Project / Program Source" value="${esc(data.source||'')}"/></td>
      <td><input type="text" class="t9-agency"     placeholder="Agency"                   value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t9-researcher" placeholder="Researcher(s)"            value="${esc(data.researcher||'')}"/></td>
      <td><textarea class="t9-impact" placeholder="Potential Impact or Contribution"
            rows="2" style="width:100%;resize:vertical">${esc(data.impact||'')}</textarea></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T9._renumber()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t9_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t9-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t9_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t9_rows tr')].map(tr => ({
      title:      tr.querySelector('.t9-title')?.value      || '',
      source:     tr.querySelector('.t9-source')?.value     || '',
      agency:     tr.querySelector('.t9-agency')?.value     || '',
      researcher: tr.querySelector('.t9-researcher')?.value || '',
      impact:     tr.querySelector('.t9-impact')?.value     || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t9_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}, {}];
        rows.forEach((row, i) => tbody.appendChild(makeRow(row, i > 0)));
        renumber();
        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(data.rows && data.rows.length ? data.rows : []);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        tbody.innerHTML = '';
        [{}, {}].forEach((r, i) => tbody.appendChild(makeRow(r, i > 0)));
        renumber();
        updateStatusBadge('not-started');
      });
  }

  function save() {
    const rows = collectRows();
    const fields = ['title', 'source', 'agency', 'researcher', 'impact'];
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
          'done':        '✅ Table 9 saved — all rows complete!',
          'draft':       '💾 Table 9 saved — some rows still incomplete.',
          'not-started': '💾 Table 9 saved.',
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
    const b = document.getElementById('t9_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t9_status_msg'); if (e) e.textContent = m; }

  window.T9 = {
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

