/**
 * t20b_policies.js — Table 20b: Policies Formulated, Advocated, Implemented, and Institutionalized
 * Subtitle categories:
 *   1. Policy Formulated
 *   2. Policy Advocated
 *   3. Policy Implemented or Institutionalized
 *
 * Each category starts row numbering at 1 (1, 2, 3...)
 * Columns: No. | List of Policies | Agency | Description
 */

(function () {
  'use strict';

  const TABLE_NO = 'T20b';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const CATEGORIES = [
    { key: 'formulated',  label: 'Policy Formulated' },
    { key: 'advocated',   label: 'Policy Advocated' },
    { key: 'implemented', label: 'Policy Implemented or Institutionalized' },
  ];

  function normalizeCat(catStr) {
    const s = String(catStr || '').toLowerCase().trim();
    if (s.includes('advo')) return 'advocated';
    if (s.includes('impl') || s.includes('inst')) return 'implemented';
    return 'formulated';
  }

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t20b_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 20b. Policies Formulated, Advocated, Implemented, and Institutionalized.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:820px">
          <thead>
            <tr>
              <th class="group" style="width:45px">No.</th>
              <th class="group" style="width:280px">List of Policies</th>
              <th class="group" style="width:200px">Agency</th>
              <th class="group">Description</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t20b_rows"></tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
        <button type="button" class="btn btn-sm" onclick="T20B.addRow('formulated')">+ Add Policy Formulated</button>
        <button type="button" class="btn btn-sm" onclick="T20B.addRow('advocated')">+ Add Policy Advocated</button>
        <button type="button" class="btn btn-sm" onclick="T20B.addRow('implemented')">+ Add Policy Implemented / Institutionalized</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T20B.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T20B.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t20b_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t20b_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t20b_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeCatHeader(cat) {
    const tr = document.createElement('tr');
    tr.className = 't20b-cat-row';
    tr.dataset.cat = cat.key;
    tr.innerHTML = `
      <td colspan="5" style="background:var(--bg-soft,#f3f6f4);font-weight:700;color:var(--forest,#075b42);padding:9px 12px;font-size:13px;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span>${esc(cat.label)}</span>
        </div>
      </td>
    `;
    return tr;
  }

  function makeRow(catKey, data = {}, removable = true) {
    const tr = document.createElement('tr');
    tr.className = 't20b-data-row';
    tr.dataset.cat = catKey;
    const policyVal = data.policy || data.title || data['List of Policies'] || data['list of policies'] || '';
    const agencyVal = data.agency || data['Agency'] || '';
    const descVal   = data.description || data['Description'] || '';

    tr.innerHTML = `
      <td class="t20b-num" style="text-align:center;font-weight:600"></td>
      <td>
        <textarea rows="2" class="t20b-policy" placeholder="e.g. Ordinance / Resolution No.">${esc(policyVal)}</textarea>
      </td>
      <td>
        <textarea rows="2" class="t20b-agency" placeholder="e.g. LGU-San Mateo">${esc(agencyVal)}</textarea>
      </td>
      <td>
        <textarea class="t20b-description" rows="2" style="width:100%;resize:vertical" placeholder="Description of policy">${esc(descVal)}</textarea>
      </td>
      <td style="text-align:center">
        ${removable ? `<button type="button" class="row-remove-btn" onclick="this.closest('tr').remove();T20B._renumber('${catKey}')"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber(catKey) {
    const rows = document.querySelectorAll(`#t20b_rows tr.t20b-data-row[data-cat="${catKey}"]`);
    rows.forEach((tr, i) => {
      const c = tr.querySelector('.t20b-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function renumberAll() {
    CATEGORIES.forEach(cat => renumber(cat.key));
  }

  function addRow(catArg, data = {}, removable = true) {
    const tbody = document.getElementById('t20b_rows');
    if (!tbody) return;

    let catKey = 'formulated';
    if (typeof catArg === 'string' && catArg.trim()) {
      catKey = normalizeCat(catArg);
    } else if (catArg && typeof catArg === 'object') {
      data = catArg;
      catKey = normalizeCat(data.category);
    }

    const rowsInCat = tbody.querySelectorAll(`tr[data-cat="${catKey}"]`);
    const lastInCat = rowsInCat[rowsInCat.length - 1];
    const newRow = makeRow(catKey, data, removable);

    if (lastInCat) {
      lastInCat.insertAdjacentElement('afterend', newRow);
    } else {
      tbody.appendChild(newRow);
    }
    renumber(catKey);
  }

  function setRows(rows) {
    const tbody = document.getElementById('t20b_rows');
    if (!tbody) return;
    tbody.innerHTML = '';

    const saved = (rows && rows.length) ? rows : [];
    const byCat = {
      formulated: [],
      advocated: [],
      implemented: []
    };

    saved.forEach(row => {
      const hasContent = ['policy', 'agency', 'description'].some(f => (row[f] || '').trim() !== '');
      if (!hasContent) return;

      const rawCat = row.category || row.cat || '';
      const key = normalizeCat(rawCat);
      byCat[key].push(row);
    });

    CATEGORIES.forEach(cat => {
      tbody.appendChild(makeCatHeader(cat));
      const catRows = byCat[cat.key].length ? byCat[cat.key] : [{}];
      catRows.forEach(row => {
        tbody.appendChild(makeRow(cat.key, row, true));
      });
    });

    renumberAll();
    const status = computeStatus(saved);
    updateStatusBadge(status);
  }

  function collectRows() {
    return [...document.querySelectorAll('#t20b_rows tr.t20b-data-row')].map(tr => {
      const catKey = tr.dataset.cat;
      const catObj = CATEGORIES.find(c => c.key === catKey) || CATEGORIES[0];
      const policyVal = tr.querySelector('.t20b-policy')?.value?.trim() || '';
      const agencyVal = tr.querySelector('.t20b-agency')?.value?.trim() || '';
      const descVal   = tr.querySelector('.t20b-description')?.value?.trim() || '';
      return {
        category:    catObj.label,
        policy:      policyVal,
        title:       policyVal,
        agency:      agencyVal,
        description: descVal,
      };
    });
  }

  /* ─── STATUS (auto-derived) ─── */
  function isRowTouched(r) {
    return [r.policy, r.title, r.agency, r.description].some(v => (v || '').trim() !== '');
  }
  function isRowComplete(r) {
    return [r.policy || r.title, r.agency, r.description].every(v => (v || '').trim() !== '');
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
    const badge = document.getElementById('t20b_status_badge');
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
    const tbody = document.getElementById('t20b_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        const rows = (data.rows && data.rows.length) ? data.rows : [];
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
    const fields = ['policy', 'agency', 'description'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = (requestedStatus === 'draft') ? 'draft' : computeStatus(rows);

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
          'done':        'Table 20b saved — all rows complete!',
          'draft':       'Table 20b saved — some rows still need all fields filled.',
          'not-started': 'Table 20b saved.',
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
    const b = document.getElementById('t20b_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s ?? '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t20b_status_msg'); if (e) e.textContent = m; }

  window.T20B = {
    addRow(catKey, data = {}, removable = true) { addRow(catKey, data, removable); },
    setRows,
    save,
    openDocs,
    _renumber: renumber,
  };
  window.T20b = window.T20B;

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();
