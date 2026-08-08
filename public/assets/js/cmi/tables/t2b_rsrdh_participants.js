/**
 * t2b_rsrdh_participants.js — Table 2b: Number of Participants in the RSRDH
 * Registers with CMI core engine.
 * Handles: render, add/remove row per participant category (GO, NGO, Private Sector, LGU),
 *          auto-total per category + grand total, save draft (status auto-derived),
 *          optional documentation (notes + images).
 */

(function () {
  'use strict';

  const TABLE_NO = 'T2b';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  const CATEGORIES = [
    { key: 'go',      label: 'GO' },
    { key: 'ngo',     label: 'NGO' },
    { key: 'private', label: 'Private Sector' },
    { key: 'lgu',     label: 'LGU' },
  ];

  /* ─── State ─── */
  let _images = [];

  /* ─────────────────────────────────────────
     STATUS (auto-derived from completeness)
     "remarks" is optional.
  ───────────────────────────────────────── */
  function isRowTouched(r) {
    return ['agency','count','remarks'].some(f => (r[f] || '').toString().trim() !== '');
  }
  function isRowComplete(r) {
    return (r.agency || '').trim() !== '' && (r.count || '').toString().trim() !== '';
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(s) { return s === 'done' ? 'Complete' : s === 'draft' ? 'In Progress' : 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t2b_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { done:{bg:'#e6f4ea',fg:'var(--green,#1e7e34)'}, draft:{bg:'#fff4e5',fg:'#b06b00'}, 'not-started':{bg:'#f1f1f1',fg:'#777'} };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg; badge.style.color = c.fg;
  }

  /* ─────────────────────────────────────────
     RENDER
  ───────────────────────────────────────── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t2b_wrap">

      <!-- Header -->
      <div class="t-hdr">
        <div class="t-title">Table 2b. Number of Participants in the RSRDH, CY 2025.</div>
      </div>

      <!-- Table -->
      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:600px">
          <thead>
            <tr>
              <th class="group" style="width:150px">Participants</th>
              <th class="group">Agency / Association</th>
              <th class="group" style="width:140px">No. of Participants</th>
              <th class="group">Remarks</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t2b_rows"></tbody>
          <tfoot>
            <tr id="t2b_grand_total_row" style="font-weight:700;background:var(--bg-soft,#f3f6f4)">
              <td colspan="2" style="text-align:right;padding-right:12px">Grand Total</td>
              <td id="t2b_grand_total" style="text-align:center;color:var(--green)">—</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Add Row buttons -->
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;flex-wrap:wrap">
        ${CATEGORIES.map(c =>
          `<button class="btn btn-sm" onclick="T2b.addRow('${c.key}')">+ Add ${c.label} Row</button>`
        ).join('')}
      </div>

      <!-- Note -->
      <div style="font-size:11px;color:var(--text-muted);font-style:italic;margin-bottom:14px">
        Note: Participants may be researchers, farmers, policy makers, or extension workers.
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-primary" onclick="T2b.save()">Save</button>
        <button class="btn t-docs-btn" onclick="T2b.openDocs()">
          📎 Documentation <span id="t2b_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t2b_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t2b_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>

    </div>`;
  }

  /* ─────────────────────────────────────────
     CATEGORY HEADER ROW
  ───────────────────────────────────────── */
  function makeCatHeader(cat) {
    const tr = document.createElement('tr');
    tr.className = 'cat-row';
    tr.dataset.cat = cat.key;
    tr.innerHTML = `
      <td style="font-weight:700;background:var(--bg-soft,#f3f6f4)">${esc(cat.label)}</td>
      <td style="background:var(--bg-soft,#f3f6f4)"></td>
      <td id="t2b_subtotal_${cat.key}" style="text-align:center;font-weight:700;background:var(--bg-soft,#f3f6f4);color:var(--green)">—</td>
      <td style="background:var(--bg-soft,#f3f6f4);font-size:11px;color:var(--text-muted);font-style:italic">subtotal</td>
      <td style="background:var(--bg-soft,#f3f6f4)"></td>
    `;
    return tr;
  }

  /* ─────────────────────────────────────────
     DATA ROW
  ───────────────────────────────────────── */
  function makeRow(catKey, data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.dataset.cat = catKey;
    tr.innerHTML = `
      <td></td>
      <td><input type="text" class="t2b-agency" placeholder="Agency / Association" value="${esc(data.agency||'')}"/></td>
      <td>
        <input type="number" class="t2b-count" min="0" placeholder="0" value="${esc(data.count||'')}"
          style="text-align:center;width:100%"/>
      </td>
      <td><input type="text" class="t2b-remarks" placeholder="Remarks" value="${esc(data.remarks||'')}"/></td>
      <td style="text-align:center">
        ${removable
          ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T2b._recalc('${catKey}')">🗑</button>`
          : ''}
      </td>
    `;
    tr.querySelector('.t2b-count').addEventListener('input', () => recalcCategory(catKey));
    return tr;
  }

  /* ─────────────────────────────────────────
     ADD ROW (public + internal)
  ───────────────────────────────────────── */
  function addRow(catKey, data = {}, removable = true) {
    const tbody = document.getElementById('t2b_rows');
    if (!tbody) return;

    // Insert after the last row that belongs to this category
    const rowsInCat = tbody.querySelectorAll(`tr[data-cat="${catKey}"]`);
    const lastInCat = rowsInCat[rowsInCat.length - 1];
    const newRow = makeRow(catKey, data, removable);

    if (lastInCat) {
      lastInCat.insertAdjacentElement('afterend', newRow);
    } else {
      tbody.appendChild(newRow);
    }
    recalcCategory(catKey);
  }

  /* ─────────────────────────────────────────
     RECALC — subtotal per category + grand total
  ───────────────────────────────────────── */
  function recalcCategory(catKey) {
    const rows = document.querySelectorAll(`#t2b_rows tr.data-row[data-cat="${catKey}"]`);
    let sub = 0;
    rows.forEach(tr => {
      sub += parseInt(tr.querySelector('.t2b-count')?.value || 0) || 0;
    });

    const subtotalEl = document.getElementById(`t2b_subtotal_${catKey}`);
    if (subtotalEl) subtotalEl.textContent = sub > 0 ? sub : '—';

    recalcGrand();
  }

  function recalcAll() {
    CATEGORIES.forEach(cat => recalcCategory(cat.key));
  }

  function recalcGrand() {
    let grand = 0;
    CATEGORIES.forEach(cat => {
      const el = document.getElementById(`t2b_subtotal_${cat.key}`);
      const val = parseInt(el?.textContent) || 0;
      grand += val;
    });
    const grandEl = document.getElementById('t2b_grand_total');
    if (grandEl) grandEl.textContent = grand > 0 ? grand : '—';
  }

  /* ─────────────────────────────────────────
     COLLECT ROWS
  ───────────────────────────────────────── */
  function collectRows() {
    return [...document.querySelectorAll('#t2b_rows tr.data-row')].map(tr => ({
      category: tr.dataset.cat,
      agency:   tr.querySelector('.t2b-agency')?.value  || '',
      count:    tr.querySelector('.t2b-count')?.value   || '',
      remarks:  tr.querySelector('.t2b-remarks')?.value || '',
    }));
  }

  /* ─────────────────────────────────────────
     LOAD DATA
  ───────────────────────────────────────── */
  function loadData() {
    const tbody = document.getElementById('t2b_rows');
    if (!tbody) return;

    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';

        // Group saved rows by category
        const saved = (data.rows && data.rows.length) ? data.rows : [];
        const byCat = {};
        CATEGORIES.forEach(cat => { byCat[cat.key] = []; });
        saved.forEach(row => {
          const key = row.category || CATEGORIES[0].key;
          if (!byCat[key]) byCat[key] = [];
          byCat[key].push(row);
        });

        // Build: header + rows for each category (min 1 row per category)
        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          const rows = byCat[cat.key].length ? byCat[cat.key] : [{}];
          rows.forEach((row, i) => {
            tbody.appendChild(makeRow(cat.key, row, i > 0));
          });
        });
        recalcAll();

        // documentation
        const meta = data.meta || {};
        _images = meta.images || [];
        updateBadge();

        // status (auto-derived)
        const status = computeStatus(saved);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        // No saved data — show blank rows
        tbody.innerHTML = '';
        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          tbody.appendChild(makeRow(cat.key, {}, false));
        });
        recalcAll();
        updateStatusBadge('not-started');
      });
  }

  /* ─────────────────────────────────────────
     SAVE
  ───────────────────────────────────────── */
  function save() {
    const rows = collectRows();
    const fields = ['agency', 'count', 'remarks'];
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
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const msgs = {
          done:        '✅ Table 2b saved — all rows complete!',
          draft:       '💾 Table 2b saved — some rows still need an agency and a count.',
          'not-started':'💾 Table 2b saved.',
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
     BADGE + DOCS MODAL
  ───────────────────────────────────────── */
  function updateBadge() {
    const badge = document.getElementById('t2b_docs_count');
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
    return String(s).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function setMsg(msg) {
    const el = document.getElementById('t2b_status_msg');
    if (el) el.textContent = msg;
  }

  /* ─────────────────────────────────────────
     PUBLIC (window.T2b)
  ───────────────────────────────────────── */
  window.T2b = {
    addRow(catKey)   { addRow(catKey, {}, true); },
    save,
    openDocs,
    _recalc(catKey)  { recalcCategory(catKey); },
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

