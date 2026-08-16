/**
 * t8b_collaborative_rd.js — Table 8b: Collaborative R&D Programs/Projects implemented
 * by the Consortium and member-agencies in support of regional priorities.
 * Flat addable table with auto-summed TOTAL row for Budget column.
 * Columns: Program Title | Project Title | Implementing Agency/Institution |
 *          Duration* | Budget | Source(s) of Fund | Role of Consortium
 */

(function () {
  'use strict';

  const TABLE_NO = 'T8b';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t8b_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 8b. Collaborative R&amp;D Programs/ Projects implemented by the Consortium and member-agencies in support of regional priorities.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:860px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Program Title</th>
              <th class="group">Project Title</th>
              <th class="group" style="width:170px">Implementing Agency / Institution</th>
              <th class="group" style="width:130px">Duration <sup style="color:var(--green)">*</sup></th>
              <th class="group" style="width:120px">Budget</th>
              <th class="group" style="width:140px">Source(s) of Fund</th>
              <th class="group" style="width:160px">Role of Consortium</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t8b_rows"></tbody>
          <tfoot>
            <tr style="font-weight:700;background:var(--bg-soft,#f3f6f4)">
              <td colspan="5" style="text-align:right;padding-right:12px">TOTAL</td>
              <td id="t8b_total" style="text-align:center;color:var(--green)">—</td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div style="font-size:11px;color:var(--text-muted);font-style:italic;margin-bottom:10px">
        * Indicate start and end dates
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T8b.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T8b.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T8b.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t8b_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t8b_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t8b_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS ─── */
  function isRowComplete(r) {
    return (r.program||'').trim() !== '' && (r.project||'').trim() !== '' && (r.agency||'').trim() !== '';
  }
  function isRowTouched(r) {
    return ['program','project','agency','duration','budget','source','role'].some(f => (r[f]||'').toString().trim() !== '');
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
    const badge = document.getElementById('t8b_status_badge');
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
      <td class="t8b-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text"   class="t8b-program"  placeholder="Program Title"               value="${esc(data.program||'')}"/></td>
      <td><input type="text"   class="t8b-project"  placeholder="Project Title"               value="${esc(data.project||'')}"/></td>
      <td><input type="text"   class="t8b-agency"   placeholder="Implementing Agency"         value="${esc(data.agency||'')}"/></td>
      <td><input type="text"   class="t8b-duration" placeholder="e.g. Jan 2025 – Dec 2026"   value="${esc(data.duration||'')}"/></td>
      <td><input type="number" class="t8b-budget"   placeholder="0" min="0" step="0.01"
            style="text-align:right;width:100%" value="${esc(data.budget||'')}"/></td>
      <td><input type="text"   class="t8b-source"   placeholder="Source of Fund"              value="${esc(data.source||'')}"/></td>
      <td><input type="text"   class="t8b-role"     placeholder="Role of Consortium"          value="${esc(data.role||'')}"/></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T8b._renumber();T8b._recalc()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    tr.querySelector('.t8b-budget').addEventListener('input', recalcTotal);
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t8b_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t8b-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#t8b_rows .t8b-budget').forEach(inp => {
      total += parseFloat(inp.value) || 0;
    });
    const el = document.getElementById('t8b_total');
    if (el) el.textContent = total > 0 ? total.toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '—';
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t8b_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
    recalcTotal();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t8b_rows tr')].map(tr => ({
      program:  tr.querySelector('.t8b-program')?.value  || '',
      project:  tr.querySelector('.t8b-project')?.value  || '',
      agency:   tr.querySelector('.t8b-agency')?.value   || '',
      duration: tr.querySelector('.t8b-duration')?.value || '',
      budget:   tr.querySelector('.t8b-budget')?.value   || '',
      source:   tr.querySelector('.t8b-source')?.value   || '',
      role:     tr.querySelector('.t8b-role')?.value     || '',
    }));
  }

  function loadData() {
    const tbody = document.getElementById('t8b_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}];
        rows.forEach((row, i) => tbody.appendChild(makeRow(row, i > 0)));
        renumber();
        recalcTotal();
        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        const status = computeStatus(data.rows && data.rows.length ? data.rows : []);
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
    const fields = ['program', 'project', 'agency', 'duration', 'budget', 'source', 'role'];
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
          'done':        'Table 8b saved — all rows complete!',
          'draft':       'Table 8b saved — some rows still incomplete.',
          'not-started': 'Table 8b saved.',
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
    const b = document.getElementById('t8b_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t8b_status_msg'); if (e) e.textContent = m; }

  window.T8b = {
    addRow()       { addRow({}, true); },
    save,
    openDocs,
    _renumber:     renumber,
    _recalc:       recalcTotal,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();

