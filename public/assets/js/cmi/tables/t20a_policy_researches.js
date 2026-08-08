/**
 * t20a_policy_researches.js — Table 20a: Policy Researches Conducted,
 * CY 2025 (January – December).
 * Columns: Policy Analysis/Advocacy Project | Agency | Author |
 *          Description of the Project | Findings
 */

(function () {
  'use strict';

  const TABLE_NO = 'T20a';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t20a_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 20a. Policy Researches conducted, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:820px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Policy Analysis / Advocacy Project</th>
              <th class="group" style="width:140px">Agency</th>
              <th class="group" style="width:140px">Author</th>
              <th class="group" style="width:200px">Description of the Project</th>
              <th class="group" style="width:200px">Findings</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t20a_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T20A.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-primary" onclick="T20A.save()">Save</button>
        <button class="btn t-docs-btn" onclick="T20A.openDocs()">
          📎 Documentation <span id="t20a_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t20a_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t20a_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t20a-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t20a-project"     placeholder="Policy Analysis / Advocacy Project" value="${esc(data.project||'')}"/></td>
      <td><input type="text" class="t20a-agency"      placeholder="Agency"                              value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t20a-author"      placeholder="Author"                              value="${esc(data.author||'')}"/></td>
      <td><textarea class="t20a-description" rows="2" style="width:100%;resize:vertical"
            placeholder="Description of the Project">${esc(data.description||'')}</textarea></td>
      <td><textarea class="t20a-findings" rows="2" style="width:100%;resize:vertical"
            placeholder="Findings">${esc(data.findings||'')}</textarea></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T20A._renumber()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t20a_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t20a-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t20a_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t20a_rows tr')].map(tr => ({
      project:     tr.querySelector('.t20a-project')?.value     || '',
      agency:      tr.querySelector('.t20a-agency')?.value      || '',
      author:      tr.querySelector('.t20a-author')?.value      || '',
      description: tr.querySelector('.t20a-description')?.value || '',
      findings:    tr.querySelector('.t20a-findings')?.value    || '',
    }));
  }

  /* ─── STATUS (auto-derived) ───
     A row is "touched" if any field has a value.
     "complete" if all five fields are filled. */
  function isRowTouched(r) {
    return [r.project, r.agency, r.author, r.description, r.findings]
      .some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.project, r.agency, r.author, r.description, r.findings]
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
    const badge = document.getElementById('t20a_status_badge');
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
    const tbody = document.getElementById('t20a_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}, {}, {}];
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
        [{}, {}, {}].forEach((r, i) => tbody.appendChild(makeRow(r, i > 0)));
        renumber();
        updateStatusBadge('not-started');
      });
  }

  function save() {
    const rows = collectRows();
    const fields = ['project', 'agency', 'author', 'description', 'findings'];
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
          'done':        '✅ Table 20a saved — all rows complete!',
          'draft':       '💾 Table 20a saved — some rows still need all fields filled.',
          'not-started': '💾 Table 20a saved.',
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
    const b = document.getElementById('t20a_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t20a_status_msg'); if (e) e.textContent = m; }

  window.T20A = {
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

