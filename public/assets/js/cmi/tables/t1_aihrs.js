/**
 * t1_aihrs.js — Table 1: Summary of Agency In-House Reviews (AIHRs)
 * Registers with CMI core engine.
 * Supports categories: Pre In-House & AIHR's with designated Add buttons.
 */

(function () {
  'use strict';

  const TABLE_NO   = 'T1';
  const API_SAVE   = '/api/cmi/tables/save';
  const API_LOAD   = '/api/cmi/tables/load';

  /* ─── State ─── */
  let _images = []; // [{ id, file_path, caption }] loaded from server

  const CATEGORIES = [
    'Pre In-House',
    "AIHR's"
  ];

  /* ─────────────────────────────────────────
     RENDER
  ───────────────────────────────────────── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
    bindEvents(container);
  }

  function buildShell() {
    return `
    <div class="t-page" id="t1_wrap">

      <!-- Header -->
      <div class="t-hdr">
        <div class="t-title">Table 1. Summary of Agency In-House Reviews (AIHRs) conducted by consortium member-agencies.</div>
      </div>

      <!-- Table -->
      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:640px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:120px">Date</th>
              <th class="group" rowspan="2">Agency</th>
              <th class="group" colspan="4">Number of Projects Presented</th>
              <th class="group" rowspan="2" style="width:110px">Total Projects Reviewed</th>
              <th class="group" rowspan="2" style="width:40px;text-align:center"></th>
            </tr>
            <tr>
              <th class="sub">New</th>
              <th class="sub">Ongoing</th>
              <th class="sub">Completed</th>
              <th class="sub">Terminated</th>
            </tr>
          </thead>
          <tbody id="t1_rows"></tbody>
        </table>
      </div>

      <!-- Add Buttons + Note -->
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
        <button class="btn btn-sm" type="button" onclick="T1.addRow('Pre In-House')" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600;padding:6px 16px">+ Add (Pre In-House)</button>
        <button class="btn btn-sm" type="button" onclick="T1.addRow('AIHR\\'s')" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600;padding:6px 16px">+ Add (AIHR's)</button>
      </div>
      <div style="font-size:11px;color:var(--text-muted);font-style:italic;margin-bottom:14px">
        Note: The Regional Consortium may prepare other tables for ease in data presentation.
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" type="button" onclick="T1.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" type="button" onclick="T1.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t1_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t1_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t1_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>

    </div>`;
  }

  /* ─────────────────────────────────────────
     ROW BUILDERS
  ───────────────────────────────────────── */
  function makeCatHeader(cat) {
    return `<tr class="t1-cat-row" data-cat="${esc(cat)}">
      <td colspan="8" style="font-weight:700;background:var(--bg-soft,#f3f6f4);padding:9px 12px;color:var(--forest,#075b42);border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb">${esc(cat)}</td>
    </tr>`;
  }

  function makeRow(cat, data = {}, removable = true) {
    const tr = document.createElement('tr');
    tr.className = 't1-data-row';
    tr.dataset.cat = cat;
    const newCountVal = (data.new_ !== undefined && data.new_ !== '') ? data.new_ : ((data.new !== undefined && data.new !== '') ? data.new : '');
    tr.innerHTML = `
      <td><input type="date" class="t1-date" value="${esc(data.date||'')}"/></td>
      <td><input type="text" class="t1-agency" placeholder="Agency name" value="${esc(data.agency||'')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(newCountVal)}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.ongoing ?? '')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.completed ?? '')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.terminated ?? '')}"/></td>
      <td class="t1-total-cell" style="text-align:center;font-weight:600;color:var(--green)">
        ${ computeTotal(data) || '—' }
      </td>
      <td style="text-align:center">
        ${removable ? `<button type="button" class="row-remove-btn" onclick="this.closest('tr').remove();T1._recalcAll()"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}
      </td>
    `;
    // auto-calc on input
    tr.querySelectorAll('.t1-calc').forEach(inp =>
      inp.addEventListener('input', () => recalcRow(tr))
    );
    return tr;
  }

  function computeTotal(d) {
    const newVal = (d.new_ !== undefined && d.new_ !== '') ? d.new_ : (d.new || 0);
    const n = [newVal, d.ongoing||0, d.completed||0, d.terminated||0]
                .map(v => parseInt(v)||0);
    const t = n.reduce((a,b)=>a+b,0);
    return t > 0 ? t : '';
  }

  function recalcRow(tr) {
    const nums = [...tr.querySelectorAll('.t1-calc')].map(i => parseInt(i.value)||0);
    const total = nums.reduce((a,b)=>a+b,0);
    const cell = tr.querySelector('.t1-total-cell');
    if (cell) cell.textContent = total > 0 ? total : '—';
  }

  function addRow(catKey = "AIHR's", data = {}, removable = true) {
    const tbody = document.getElementById('t1_rows');
    if (!tbody) return;
    const catRows = tbody.querySelectorAll(`tr.t1-data-row[data-cat="${catKey}"]`);
    const newRow = makeRow(catKey, data, removable);
    if (catRows.length > 0) {
      catRows[catRows.length - 1].insertAdjacentElement('afterend', newRow);
    } else {
      const header = tbody.querySelector(`tr.t1-cat-row[data-cat="${catKey}"]`);
      if (header) header.insertAdjacentElement('afterend', newRow);
      else tbody.appendChild(newRow);
    }
  }

  /* ─────────────────────────────────────────
     COLLECT ROWS
  ───────────────────────────────────────── */
  function collectRows() {
    return [...document.querySelectorAll('#t1_rows tr.t1-data-row')].map(tr => {
      const nums = tr.querySelectorAll('.t1-num');
      const valNew = nums[0]?.value || '';
      return {
        category:   tr.dataset.cat || "AIHR's",
        date:       tr.querySelector('.t1-date')?.value || '',
        agency:     tr.querySelector('.t1-agency')?.value || '',
        new_:       valNew,
        new:        valNew,
        ongoing:    nums[1]?.value || '',
        completed:  nums[2]?.value || '',
        terminated: nums[3]?.value || '',
      };
    });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived from row completeness)
  ───────────────────────────────────────── */
  function isRowComplete(r) {
    const hasDate = (r.date || '').trim() !== '';
    const hasCount = [r.new_, r.ongoing, r.completed, r.terminated]
                        .some(v => v !== '' && v !== null && v !== undefined && (parseInt(v) || 0) > 0);
    return hasDate && hasCount;
  }

  function isRowTouched(r) {
    const hasDate = (r.date || '').trim() !== '';
    const hasCount = [r.new_, r.ongoing, r.completed, r.terminated]
                        .some(v => v !== '' && v !== null && v !== undefined && (parseInt(v) || 0) > 0);
    return hasDate || hasCount;
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
    const badge = document.getElementById('t1_status_badge');
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

  /* ─────────────────────────────────────────
     LOAD DATA
  ───────────────────────────────────────── */
  function loadData() {
    const tbody = document.getElementById('t1_rows');
    if (!tbody) return;

    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        const savedRows = (data.rows && data.rows.length) ? data.rows : [];

        CATEGORIES.forEach(cat => {
          tbody.insertAdjacentHTML('beforeend', makeCatHeader(cat));
          const catRows = savedRows.filter(r => (r.category === cat) || (cat === "AIHR's" && (!r.category || r.category === 'aihrs')));
          if (catRows.length > 0) {
            catRows.forEach(r => tbody.appendChild(makeRow(cat, r, true)));
          } else {
            tbody.appendChild(makeRow(cat, {}, true));
          }
        });

        // documentation
        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();

        const status = data.status || computeStatus(collectRows());
        updateStatusBadge(status);
        if (data.updated_at) {
          setMsg(`Last saved: ${data.updated_at}`);
        }
      })
      .catch(() => {
        tbody.innerHTML = '';
        CATEGORIES.forEach(cat => {
          tbody.insertAdjacentHTML('beforeend', makeCatHeader(cat));
          tbody.appendChild(makeRow(cat, {}, true));
        });
        updateStatusBadge('not-started');
      });
  }

  /* ─────────────────────────────────────────
     SAVE
  ───────────────────────────────────────── */
  function save(requestedStatus) {
    const rows = collectRows();

    // Block save if the table is completely empty (no rows, no meta content)
    const fields = ['date', 'agency', 'new_', 'ongoing', 'completed', 'terminated'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = (requestedStatus === 'draft') ? 'draft' : 'done';

    const payload = {
      table_no: TABLE_NO,
      status:   status,
      meta:     { images: _images },
      rows:     rows,
    };

    setMsg('Saving…');
    fetch(API_SAVE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const msgs = {
          'done':        'Table 1 saved — all rows complete!',
          'draft':       'Table 1 saved — draft saved.',
          'not-started': 'Table 1 saved.',
        };
        toast(msgs[status] || msgs['not-started']);
        setMsg(`Saved · ${new Date().toLocaleTimeString()}`);
        updateStatusBadge(status);
        CMI.updateStatus(TABLE_NO, status);
      } else {
        toast('❌ Save failed: ' + (res.error || 'Unknown error'));
        setMsg('Save failed');
      }
    })
    .catch(() => {
      toast('❌ Network error — please try again.');
      setMsg('');
    });
  }

  /* ─────────────────────────────────────────
     BADGE + MODAL
  ───────────────────────────────────────── */
  function updateBadge() {
    const badge = document.getElementById('t1_docs_count');
    if (!badge) return;
    if (_images.length > 0) {
      badge.textContent = _images.length;
      badge.style.display = 'inline';
    } else {
      badge.style.display = 'none';
    }
  }

  function openDocs() {
    DocsModal.open(TABLE_NO, _images, function (saved) {
      _images = saved;
      updateBadge();
    });
  }

  /* ─────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────── */
  function esc(s) {
    return String(s ?? '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function setMsg(msg) {
    const el = document.getElementById('t1_status_msg');
    if (el) el.textContent = msg;
  }

  function bindEvents(container) {}

  /* ─────────────────────────────────────────
     PUBLIC (window.T1)
  ───────────────────────────────────────── */
  window.T1 = {
    addRow(cat = "AIHR's") {
      addRow(cat);
    },
    save,
    openDocs,
    _recalcAll() {
      document.querySelectorAll('#t1_rows tr.t1-data-row').forEach(recalcRow);
    },
  };

  /* ─────────────────────────────────────────
     REGISTER WITH CORE
  ───────────────────────────────────────── */
  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) {
      CMI.registerTable({ no: TABLE_NO, render });
    } else {
      setTimeout(register, 50);
    }
  }
  register();

})();
