/**
 * t11_technologies_extended.js — Table 11: List of Technologies Extended/Deployed
 * through Various Technology Transfer Extension Modalities.
 * Columns: Name of Technology | Project Title |
 *          TT Modality → [STCBF | STC4iD | SAFE | Food Value Chain | Other extension initiatives]
 */

(function () {
  'use strict';

  const TABLE_NO = 'T11';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const MODALITIES = ['STCBF', 'STC4iD', 'SAFE', 'Food Value Chain', 'Others'];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const modalityHeaders = MODALITIES.map(m =>
      `<th class="sub" style="width:${m === 'Others' ? '120px' : '70px'};font-size:10.5px;line-height:1.3">${esc(m)}</th>`
    ).join('');

    return `
    <div class="t-page" id="t11_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 11. List of Technologies Extended/Deployed through Various Technology Transfer Extension Modalities.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:780px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:36px">#</th>
              <th class="group" rowspan="2">Name of Technology</th>
              <th class="group" rowspan="2">Project Title</th>
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
        <button class="btn btn-sm" onclick="T11.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T11.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t11_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t11_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t11_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function isTruthyVal(v) {
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v > 0;
    if (!v) return false;
    const str = String(v).toLowerCase().trim();
    return ['approved', 'yes', 'true', '1', '✓', 'x', 'checked', 'y'].includes(str);
  }

  function makeRow(data = {}, removable = false) {
    const modalities = Object.assign({}, data.modalities || {});
    MODALITIES.forEach(m => {
      const key = m.toLowerCase().replace(/\s+/g, '_');
      if (modalities[key] === undefined) {
        if (data[key] !== undefined) {
          modalities[key] = (key === 'others' || key === 'other_extension_initiatives') ? data[key] : isTruthyVal(data[key]);
        } else if (data[m] !== undefined) {
          modalities[key] = (key === 'others' || key === 'other_extension_initiatives') ? data[m] : isTruthyVal(data[m]);
        }
      }
    });

    if (data.modality) {
      const mStr = String(data.modality).toLowerCase().trim();
      if (mStr.includes('stcbf')) modalities.stcbf = true;
      if (mStr.includes('stc4id')) modalities.stc4id = true;
      if (mStr.includes('safe')) modalities.safe = true;
      if (mStr.includes('food') || mStr.includes('value')) modalities.food_value_chain = true;
      if (mStr.includes('other')) {
        modalities.others = data.modality_other || data.others || data.modality;
      }
    }

    const modalityCells = MODALITIES.map(m => {
      const key = m.toLowerCase().replace(/\s+/g, '_');
      if (key === 'others' || key === 'other_extension_initiatives') {
        const val = typeof modalities.others === 'string' ? modalities.others : (typeof modalities.other_extension_initiatives === 'string' ? modalities.other_extension_initiatives : (modalities.others || modalities.other_extension_initiatives ? '/' : ''));
        return `<td style="text-align:center">
          <textarea rows="2" class="t11-mod-other" data-mod="others" placeholder="Specify /" style="width:100%;box-sizing:border-box;padding:4px 6px;font-size:12px;">${esc(val)}</textarea>
        </td>`;
      }
      return `<td style="text-align:center">
        <input type="checkbox" class="t11-mod" data-mod="${key}" ${modalities[key] ? 'checked' : ''}/>
      </td>`;
    }).join('');

    const tr = document.createElement('tr');
    tr.dataset.legacyAgency = data.agency || '';
    tr.innerHTML = `
      <td class="t11-num" style="text-align:center;font-weight:600"></td>
      <td><textarea rows="2" class="t11-tech"    placeholder="Name of Technology"   >${esc(data.tech||'')}</textarea></td>
      <td><textarea rows="2" class="t11-project" placeholder="Project Title"        >${esc(data.project||'')}</textarea></td>
      ${modalityCells}
      <td style="text-align:center">
        ${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T11._renumber()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
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
      const activeMods = [];
      tr.querySelectorAll('.t11-mod').forEach(cb => {
        const isChecked = cb.checked;
        mods[cb.dataset.mod] = isChecked;
        if (isChecked) {
          activeMods.push(cb.dataset.mod.toUpperCase().replace(/_/g, ' '));
        }
      });
      const otherInp = tr.querySelector('.t11-mod-other');
      if (otherInp && otherInp.value.trim()) {
        mods['others'] = otherInp.value.trim();
        mods['other_extension_initiatives'] = otherInp.value.trim();
        activeMods.push(`Others: ${otherInp.value.trim()}`);
      }
      return {
        tech:       tr.querySelector('.t11-tech')?.value    || '',
        project:    tr.querySelector('.t11-project')?.value || '',
        agency:     tr.dataset.legacyAgency || '',
        modalities: mods,
        modality:   activeMods.join(', '),
        stcbf:      Boolean(mods.stcbf),
        stc4id:     Boolean(mods.stc4id),
        safe:       Boolean(mods.safe),
        food_value_chain: Boolean(mods.food_value_chain),
        others:     mods.others || '',
      };
    });
  }

  function setRows(rows) {
    const tbody = document.getElementById('t11_rows');
    if (!tbody) return;
    tbody.innerHTML = '';
    const validRows = (rows && rows.length) ? rows : [{}];
    validRows.forEach((r, i) => tbody.appendChild(makeRow(r, i > 0)));
    renumber();
    const status = computeStatus(validRows);
    updateStatusBadge(status);
  }

  function loadData() {
    const tbody = document.getElementById('t11_rows');
    if (!tbody) return;
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        const rows = (data.rows && data.rows.length) ? data.rows : [{}];
        setRows(rows);
        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        setRows([{}]);
      });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function isRowTouched(r) {
    const hasText = ['tech', 'project'].some(k => (r[k] || '').trim() !== '');
    const hasMod  = r.modalities && Object.values(r.modalities).some(Boolean);
    return hasText || hasMod;
  }

  function isRowComplete(r) {
    const hasText = ['tech', 'project'].every(k => (r[k] || '').trim() !== '');
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

  function save(requestedStatus) {
    const rows = collectRows();

    const textFields = ['tech', 'project'];
    const hasTextContent = CMIUtils.filterEmptyRows(rows, textFields).length > 0;
    const hasCheckedMod  = rows.some(r => r.modalities && Object.values(r.modalities).some(Boolean));
    if (!hasTextContent && !hasCheckedMod) {
      toast('No Data has been entered. Please provide the required information before saving.', 4000, 'error');
      return;
    }

    const status = (requestedStatus === 'draft') ? 'draft' : 'done';
    const msgs = {
      'done':        'Table 11 saved — all rows complete!',
      'draft':       'Table 11 saved — some rows still incomplete.',
      'not-started': 'Table 11 saved.',
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
    setRows,
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

