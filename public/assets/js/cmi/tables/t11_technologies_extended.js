/**
 * t11_technologies_extended.js — Table 11: List of Technologies Extended/Deployed
 * through Various Technology Transfer Extension Modalities, CY 2025 (January – December).
 * Columns: Name of Technology | Project Title | Implementing Agency |
 *          TT Modality → [STCBF | STC4iD | SAFE | Food Value Chain | Other extension initiatives]
 */

(function () {
  'use strict';

  const TABLE_NO = 'T11';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const MODALITIES = ['STCBF', 'STC4iD', 'SAFE', 'Food Value Chain', 'Other extension initiatives'];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const modalityHeaders = MODALITIES.map(m =>
      `<th class="sub" style="width:70px;font-size:10px;line-height:1.3">${esc(m)}</th>`
    ).join('');

    return `
    <div class="t-page" id="t11_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 11. List of Technologies Extended/Deployed through Various Technology Transfer Extension Modalities, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:780px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:36px">#</th>
              <th class="group" rowspan="2">Name of Technology</th>
              <th class="group" rowspan="2">Project Title</th>
              <th class="group" rowspan="2" style="width:170px">Implementing Agency</th>
              <th class="group" colspan="${MODALITIES.length}">Technology Transfer Modality</th>
              <th class="group" rowspan="2" style="width:36px"></th>
            </tr>
            <tr>
              ${modalityHeaders}
            </tr>
          </thead>
          <tbody id="t11_rows"></tbody>
        </table>
      </div>

      <div style="margin-bottom:14px">
        <button class="btn btn-sm" onclick="T11.addRow()">+ Add Row</button>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T11.openDocs()">
          📎 Documentation <span id="t11_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t11_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t11_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeRow(data = {}, removable = false) {
    const modalities = data.modalities || {};
    const modalityCells = MODALITIES.map(m => {
      const key = m.toLowerCase().replace(/\s+/g, '_');
      return `<td style="text-align:center">
        <input type="checkbox" class="t11-mod" data-mod="${key}" ${modalities[key] ? 'checked' : ''}/>
      </td>`;
    }).join('');

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="t11-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t11-tech"    placeholder="Name of Technology"    value="${esc(data.tech||'')}"/></td>
      <td><input type="text" class="t11-project" placeholder="Project Title"         value="${esc(data.project||'')}"/></td>
      <td><input type="text" class="t11-agency"  placeholder="Implementing Agency"   value="${esc(data.agency||'')}"/></td>
      ${modalityCells}
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T11._renumber()">🗑</button>` : ''}
      </td>
    `;
    return tr;
  }

  function renumber() {
    document.querySelectorAll('#t11_rows tr').forEach((tr, i) => {
      const c = tr.querySelector('.t11-num');
      if (c) c.textContent = (i + 1) + '.';
    });
  }

  function addRow(data = {}, removable = true) {
    const tbody = document.getElementById('t11_rows');
    if (!tbody) return;
    tbody.appendChild(makeRow(data, removable));
    renumber();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t11_rows tr')].map(tr => {
      const mods = {};
      tr.querySelectorAll('.t11-mod').forEach(cb => {
        mods[cb.dataset.mod] = cb.checked;
      });
      return {
        tech:       tr.querySelector('.t11-tech')?.value    || '',
        project:    tr.querySelector('.t11-project')?.value || '',
        agency:     tr.querySelector('.t11-agency')?.value  || '',
        modalities: mods,
      };
    });
  }

  function loadData() {
    const tbody = document.getElementById('t11_rows');
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
      });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function isRowTouched(r) {
    const hasText = ['tech', 'project', 'agency'].some(k => (r[k] || '').trim() !== '');
    const hasMod  = r.modalities && Object.values(r.modalities).some(Boolean);
    return hasText || hasMod;
  }

  function isRowComplete(r) {
    const hasText = ['tech', 'project', 'agency'].every(k => (r[k] || '').trim() !== '');
    const hasMod  = r.modalities && Object.values(r.modalities).some(Boolean);
    return hasText && hasMod;
  }

  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }

  function statusLabel(status) {
    if (status === 'done')  return 'Complete';
    if (status === 'draft') return 'In Progress';
    return 'Not Started';
  }

  function updateStatusBadge(status) {
    const badge = document.getElementById('t11_status_badge');
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

  function save() {
    const rows = collectRows();

    const textFields = ['tech', 'project', 'agency'];
    const hasTextContent = CMIUtils.filterEmptyRows(rows, textFields).length > 0;
    const hasCheckedMod  = rows.some(r => r.modalities && Object.values(r.modalities).some(Boolean));
    if (!hasTextContent && !hasCheckedMod) {
      toast('⚠️ Wala kang nilagay. Hindi maisasave kung walang data.');
      return;
    }

    const status = computeStatus(rows);
    const msgs = {
      'done':        '✅ Table 11 saved — all rows complete!',
      'draft':       '💾 Table 11 saved — some rows still incomplete.',
      'not-started': '💾 Table 11 saved.',
    };

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
    const b = document.getElementById('t11_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t11_status_msg'); if (e) e.textContent = m; }

  window.T11 = {
    addRow()   { addRow({}, true); },
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

