/**
 * t1_aihrs.js — Table 1: Summary of Agency In-House Reviews (AIHRs)
 * Registers with CMI core engine.
 * Handles: render, add/remove row, auto-total, save draft (status auto-derived),
 *          optional documentation text, optional image uploads.
 */

(function () {
  'use strict';

  const TABLE_NO   = 'T1';
  const API_SAVE   = '/api/cmi/tables/save';
  const API_LOAD   = '/api/cmi/tables/load';
  const API_UPLOAD = '/api/cmi/tables/upload-doc';
  const API_IMG_DEL= '/api/cmi/tables/delete-doc';

  /* ─── State ─── */
  let _images = []; // [{ id, file_path, caption }] loaded from server

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
        <div class="t-title">Table 1. Summary of Agency In-House Reviews (AIHRs) conducted by consortium member-agencies, CY 2025 (January – December).</div>
      </div>

      <!-- Table -->
      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:580px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:110px">Date</th>
              <th class="group" rowspan="2" style="width:150px">Agency</th>
              <th class="group" colspan="4">Number of Projects Presented</th>
              <th class="group" rowspan="2" style="width:100px">Total Projects Reviewed</th>
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

      <!-- Add Row + Note -->
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px">
        <button class="btn btn-sm" onclick="T1.addRow()">+ Add Row</button>
        <span style="font-size:11px;color:var(--text-muted);font-style:italic">
          Note: The Regional Consortium may prepare other tables for ease in data presentation.
        </span>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-primary" onclick="T1.save()">Save</button>
        <button class="btn t-docs-btn" onclick="T1.openDocs()">
          📎 Documentation <span id="t1_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t1_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t1_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>

    </div>`;
  }

  /* ─────────────────────────────────────────
     ROW BUILDERS
  ───────────────────────────────────────── */
  function makeRow(data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" class="t1-date" placeholder="e.g. Jan 15" value="${esc(data.date||'')}"/></td>
      <td><input type="text" class="t1-agency" placeholder="Agency name" value="${esc(data.agency || window.CMI_AGENCY_NAME || '')}" readonly/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.new_||'')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.ongoing||'')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.completed||'')}"/></td>
      <td><input type="number" class="t1-num t1-calc" min="0" placeholder="0" value="${esc(data.terminated||'')}"/></td>
      <td class="t1-total-cell" style="text-align:center;font-weight:600;color:var(--green)">
        ${ computeTotal(data) || '—' }
      </td>
    `;
    if (removable) {
      tr.querySelector('.t1-total-cell').insertAdjacentHTML('afterend',
        `<td style="text-align:center"><button class="row-remove-btn" onclick="this.closest('tr').remove();T1._recalcAll()">🗑</button></td>`);
      // add extra th if needed (done via CSS: hide last th for non-removable rows)
    }
    // auto-calc on input
    tr.querySelectorAll('.t1-calc').forEach(inp =>
      inp.addEventListener('input', () => recalcRow(tr))
    );
    return tr;
  }

  function computeTotal(d) {
    const n = [d.new_||0, d.ongoing||0, d.completed||0, d.terminated||0]
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

  /* ─────────────────────────────────────────
     COLLECT ROWS
  ───────────────────────────────────────── */
  function collectRows() {
    return [...document.querySelectorAll('#t1_rows tr')].map(tr => {
      const inp = [...tr.querySelectorAll('input[type=text],input[type=number]')];
      return {
        date:       inp[0]?.value || '',
        agency:     inp[1]?.value || '',
        new_:       inp[2]?.value || '',
        ongoing:    inp[3]?.value || '',
        completed:  inp[4]?.value || '',
        terminated: inp[5]?.value || '',
      };
    });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived from row completeness)
     A row counts as "touched" if it has a date OR any count.
     The table is:
       - 'not-started' if no row is touched
       - 'done'        if every touched row has a date AND at least one count > 0
       - 'draft'        otherwise (partially filled)
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
        // rows
        tbody.innerHTML = '';
        const rows = (data.rows && data.rows.length) ? data.rows : [{}];
        rows.forEach((row, i) => {
          tbody.appendChild(makeRow(row, i > 0));
        });
        // ensure at least one row has delete btn logic
        if (rows.length === 1) {
          // first row never removable, but add row adds removable ones
        }

        // documentation
        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();

        // status (auto-derived from the rows just loaded)
        const status = computeStatus(rows);
        updateStatusBadge(status);
        if (data.updated_at) {
          setMsg(`Last saved: ${data.updated_at}`);
        }
      })
      .catch(() => {
        // no saved data — show blank row
        tbody.innerHTML = '';
        tbody.appendChild(makeRow({}, false));
        updateStatusBadge('not-started');
      });
  }

  /* ─────────────────────────────────────────
     SAVE
  ───────────────────────────────────────── */
  function save() {
    const rows = collectRows();

    // Block save if the table is completely empty (no rows, no meta content)
    const fields = ['date', 'agency', 'new_', 'ongoing', 'completed', 'terminated'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);

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
          'done':        '✅ Table 1 saved — all rows complete!',
          'draft':       '💾 Table 1 saved — some rows still need a date and at least one count.',
          'not-started': '💾 Table 1 saved.',
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
    return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function setMsg(msg) {
    const el = document.getElementById('t1_status_msg');
    if (el) el.textContent = msg;
  }

  function bindEvents(container) {
    // nothing extra needed — events bound inline / per-row
  }

  /* ─────────────────────────────────────────
     PUBLIC (window.T1)
  ───────────────────────────────────────── */
  window.T1 = {
    addRow() {
      const tbody = document.getElementById('t1_rows');
      if (!tbody) return;
      tbody.appendChild(makeRow({}, true));
    },
    save,
    openDocs,
    _recalcAll() {
      document.querySelectorAll('#t1_rows tr').forEach(recalcRow);
    },
  };

  /* ─────────────────────────────────────────
     REGISTER WITH CORE
  ───────────────────────────────────────── */
  (window.CMI = window.CMI || {});
  // wait for CMI core to be ready
  function register() {
    if (window.CMI && CMI.registerTable) {
      CMI.registerTable({ no: TABLE_NO, render });
    } else {
      setTimeout(register, 50);
    }
  }
  register();

})();

