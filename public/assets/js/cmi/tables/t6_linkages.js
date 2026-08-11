/**
 * t6_linkages.js — Table 6: Linkages Forged and Maintained, CY 2025 (January – December).
 * Grouped structure:
 *   Developed/New → Local | National | International  (addable rows per scope)
 *   Maintained/Sustained → Local | National | International (addable rows per scope)
 * Columns: Agency/Institution | Address | Year | Nature of Assistance/Linkages/Project
 */

(function () {
  'use strict';

  const TABLE_NO = 'T6';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const GROUPS = [
    {
      key: 'developed',
      label: 'Developed / New',
      scopes: [
        { key: 'developed_local',         label: 'Local' },
        { key: 'developed_national',      label: 'National' },
        { key: 'developed_international', label: 'International' },
      ],
    },
    {
      key: 'maintained',
      label: 'Maintained / Sustained',
      scopes: [
        { key: 'maintained_local',         label: 'Local' },
        { key: 'maintained_national',      label: 'National' },
        { key: 'maintained_international', label: 'International' },
      ],
    },
  ];

  /* ─── RENDER ─── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const addBtns = GROUPS.flatMap(g =>
      g.scopes.map(s =>
        `<button class="btn btn-sm" onclick="T6.addRow('${s.key}')">+ Add ${g.label} / ${s.label}</button>`
      )
    ).join('');

    return `
    <div class="t-page" id="t6_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 6. Linkages Forged and Maintained, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:680px">
          <thead>
            <tr>
              <th class="group" style="width:180px">Agency / Institution</th>
              <th class="group" style="width:160px">Address</th>
              <th class="group" style="width:80px">Year</th>
              <th class="group">Nature of Assistance / Linkages / Project</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t6_rows"></tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        ${addBtns}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T6.openDocs()">
          📎 Documentation <span id="t6_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t6_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t6_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  /* ─── STATUS ─── */
  function isRowComplete(r) {
    return (r.agency||'').trim() !== '' && (r.nature||'').trim() !== '';
  }
  function isRowTouched(r) {
    return ['agency','address','year','nature'].some(f => (r[f]||'').trim() !== '');
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
    const badge = document.getElementById('t6_status_badge');
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

  /* ─── GROUP HEADER ROW ─── */
  function makeGroupHeader(group) {
    const tr = document.createElement('tr');
    tr.className = 'cat-row';
    tr.dataset.group = group.key;
    tr.innerHTML = `<td colspan="5" style="font-weight:700;background:var(--bg-soft,#f3f6f4)">${esc(group.label)}</td>`;
    return tr;
  }

  /* ─── SCOPE HEADER ROW ─── */
  function makeScopeHeader(scope) {
    const tr = document.createElement('tr');
    tr.className = 'scope-row';
    tr.dataset.scope = scope.key;
    tr.innerHTML = `
      <td colspan="5" style="font-style:italic;padding-left:20px;
        background:color-mix(in srgb,var(--bg-soft,#f3f6f4) 60%,white)">
        ${esc(scope.label)}
      </td>`;
    return tr;
  }

  /* ─── DATA ROW ─── */
  function makeRow(scopeKey, data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.dataset.scope = scopeKey;
    tr.innerHTML = `
      <td><input type="text" class="t6-agency"  placeholder="Agency / Institution" value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t6-address" placeholder="Address"              value="${esc(data.address||'')}"/></td>
      <td><input type="text" class="t6-year"    placeholder="Year"    style="text-align:center" value="${esc(data.year||'')}"/></td>
      <td><textarea class="t6-nature" placeholder="Nature of Assistance / Linkages / Project"
            rows="2" style="width:100%;resize:vertical">${esc(data.nature||'')}</textarea></td>
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  /* ─── ADD ROW (public) ─── */
  function addRow(scopeKey, data = {}, removable = true) {
    const tbody = document.getElementById('t6_rows');
    if (!tbody) return;
    const rowsInScope = tbody.querySelectorAll(`tr[data-scope="${scopeKey}"]`);
    const last = rowsInScope[rowsInScope.length - 1];
    const newRow = makeRow(scopeKey, data, removable);
    if (last) last.insertAdjacentElement('afterend', newRow);
    else tbody.appendChild(newRow);
  }

  /* ─── COLLECT ─── */
  function collectRows() {
    return [...document.querySelectorAll('#t6_rows tr.data-row')].map(tr => ({
      scope:   tr.dataset.scope,
      agency:  tr.querySelector('.t6-agency')?.value  || '',
      address: tr.querySelector('.t6-address')?.value || '',
      year:    tr.querySelector('.t6-year')?.value    || '',
      nature:  tr.querySelector('.t6-nature')?.value  || '',
    }));
  }

  /* ─── LOAD ─── */
  function loadData() {
    const tbody = document.getElementById('t6_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const saved = (data.rows && data.rows.length) ? data.rows : [];
        const byScopeKey = {};
        GROUPS.forEach(g => g.scopes.forEach(s => { byScopeKey[s.key] = []; }));
        saved.forEach(row => {
          if (byScopeKey[row.scope]) byScopeKey[row.scope].push(row);
        });

        GROUPS.forEach(group => {
          tbody.appendChild(makeGroupHeader(group));
          group.scopes.forEach(scope => {
            tbody.appendChild(makeScopeHeader(scope));
            const rows = byScopeKey[scope.key].length ? byScopeKey[scope.key] : [{}];
            rows.forEach((row, i) => tbody.appendChild(makeRow(scope.key, row, i > 0)));
          });
        });

        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(saved.length ? saved : []);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        tbody.innerHTML = '';
        GROUPS.forEach(group => {
          tbody.appendChild(makeGroupHeader(group));
          group.scopes.forEach(scope => {
            tbody.appendChild(makeScopeHeader(scope));
            tbody.appendChild(makeRow(scope.key, {}, false));
          });
        });
        updateStatusBadge('not-started');
      });
  }

  /* ─── SAVE ─── */
  function save() {
    const rows = collectRows();
    const fields = ['agency', 'address', 'year', 'nature'];
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
          'done':        '✅ Table 6 saved — all rows complete!',
          'draft':       '💾 Table 6 saved — some rows still incomplete.',
          'not-started': '💾 Table 6 saved.',
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
    const b = document.getElementById('t6_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t6_status_msg'); if (e) e.textContent = m; }

  window.T6 = {
    addRow(scopeKey) { addRow(scopeKey, {}, true); },
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

