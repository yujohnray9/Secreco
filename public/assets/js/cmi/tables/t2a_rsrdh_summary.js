/**
 * t2a_rsrdh_summary.js — Table 2a: Summary of Regional Symposium on R&D Highlights (RSRDH)
 * Registers with CMI core engine.
 * Handles: render, date/venue fields, add/remove row per category,
 *          save draft (status auto-derived), documentation (notes + images).
 */

(function () {
  'use strict';

  const TABLE_NO   = 'T2a';
  const API_SAVE   = '/api/cmi/tables/save';
  const API_LOAD   = '/api/cmi/tables/load';

  const CATEGORIES = [
    { key: 'research',     label: 'Research Category' },
    { key: 'development', label: 'Development Category' },
  ];

  /* ─── State ─── */
  let _images = []; // [{ id, file_path, caption }] loaded from server

  /* ─────────────────────────────────────────
     RENDER
  ───────────────────────────────────────── */
  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    return `
    <div class="t-page" id="t2a_wrap">

      <!-- Header -->
      <div class="t-hdr">
        <div class="t-title">Table 2a. Summary of Regional Symposium on R&amp;D Highlights.</div>
      </div>

      <!-- Table -->
      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:780px">
          <thead>
            <tr>
              <th class="group" style="width:36px">#</th>
              <th class="group">Title</th>
              <th class="group">Implementing Agency(ies)</th>
              <th class="group">Researcher(s)</th>
              <th class="group">Major Recommendations</th>
              <th class="group">Winners</th>
              <th class="group" style="width:36px"></th>
            </tr>
          </thead>
          <tbody id="t2a_rows"></tbody>
        </table>
      </div>

      <!-- Add Row buttons -->
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T2a.addRow('research')">+ Add Research Row</button>
        <button class="btn btn-sm" onclick="T2a.addRow('development')">+ Add Development Row</button>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" onclick="T2a.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" onclick="T2a.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t2a_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t2a_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t2a_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>

    </div>`;
  }

  /* ─────────────────────────────────────────
     CATEGORY HEADER + DATA ROW BUILDERS
  ───────────────────────────────────────── */
  function makeCatHeader(cat) {
    const tr = document.createElement('tr');
    tr.className = 'cat-row';
    tr.dataset.cat = cat.key;
    tr.innerHTML = `<td colspan="7" style="font-weight:700;background:var(--bg-soft,#f3f6f4)">${esc(cat.label)}</td>`;
    return tr;
  }

  function makeRow(catKey, data = {}, removable = false) {
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.dataset.cat = catKey;
    tr.innerHTML = `
      <td class="t2a-num" style="text-align:center;font-weight:600"></td>
      <td><input type="text" class="t2a-title" placeholder="Title" value="${esc(data.title||'')}"/></td>
      <td><input type="text" class="t2a-agency" placeholder="Implementing agency(ies)" value="${esc(data.agency||'')}"/></td>
      <td><input type="text" class="t2a-researcher" placeholder="Researcher(s)" value="${esc(data.researcher||'')}"/></td>
      <td><textarea class="t2a-recommendations" placeholder="Major recommendations" rows="2" style="width:100%;resize:vertical">${esc(data.recommendations||'')}</textarea></td>
      <td><input type="text" class="t2a-winners" placeholder="Winners" value="${esc(data.winners||'')}"/></td>
      <td style="text-align:center">${removable ? `<button class="row-remove-btn" onclick="this.closest('tr').remove();T2a._renumber('${catKey}')"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>` : ''}</td>
    `;
    return tr;
  }

  /* ─────────────────────────────────────────
     RENUMBER ROWS WITHIN A CATEGORY
  ───────────────────────────────────────── */
  function renumber(catKey) {
    const rows = document.querySelectorAll(`#t2a_rows tr.data-row[data-cat="${catKey}"]`);
    rows.forEach((tr, i) => {
      const numCell = tr.querySelector('.t2a-num');
      if (numCell) numCell.textContent = (i + 1) + '.';
    });
  }

  function renumberAll() {
    CATEGORIES.forEach(cat => renumber(cat.key));
  }

  /* ─────────────────────────────────────────
     ADD ROW
  ───────────────────────────────────────── */
  function addRow(catKey, data = {}, removable = true) {
    const tbody = document.getElementById('t2a_rows');
    if (!tbody) return;

    // Find the last row belonging to this category (header or data row),
    // and insert the new row right after it.
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

  /* ─────────────────────────────────────────
     COLLECT ROWS
  ───────────────────────────────────────── */
  function collectRows() {
    return [...document.querySelectorAll('#t2a_rows tr.data-row')].map(tr => {
      return {
        category:       tr.dataset.cat,
        title:          tr.querySelector('.t2a-title')?.value || '',
        agency:         tr.querySelector('.t2a-agency')?.value || '',
        researcher:     tr.querySelector('.t2a-researcher')?.value || '',
        recommendations:tr.querySelector('.t2a-recommendations')?.value || '',
        winners:        tr.querySelector('.t2a-winners')?.value || '',
      };
    });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived from completeness)
     "winners" is optional (not every entry wins).
  ───────────────────────────────────────── */
  function isRowTouched(r) {
    return ['title','agency','researcher','recommendations','winners']
      .some(f => (r[f] || '').trim() !== '');
  }
  function isRowComplete(r) {
    return ['title','agency','researcher','recommendations']
      .every(f => (r[f] || '').trim() !== '');
  }
  function computeStatus(rows) {
    const touched = rows.filter(isRowTouched);
    if (touched.length === 0) return 'not-started';
    return touched.every(isRowComplete) ? 'done' : 'draft';
  }
  function statusLabel(s) { return s === 'done' ? 'Complete' : s === 'draft' ? 'In Progress' : 'Not Started'; }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t2a_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { done:{bg:'#e6f4ea',fg:'var(--green,#1e7e34)'}, draft:{bg:'#fff4e5',fg:'#b06b00'}, 'not-started':{bg:'#f1f1f1',fg:'#777'} };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg; badge.style.color = c.fg;
  }

  /* ─────────────────────────────────────────
     LOAD DATA
  ───────────────────────────────────────── */
  function loadData() {
    const tbody = document.getElementById('t2a_rows');
    if (!tbody) return;

    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';

        // group saved rows by category
        const saved = (data.rows && data.rows.length) ? data.rows : [];
        const byCat = {};
        CATEGORIES.forEach(cat => byCat[cat.key] = []);
        saved.forEach(row => {
          const key = row.category || CATEGORIES[0].key;
          if (!byCat[key]) byCat[key] = [];
          byCat[key].push(row);
        });

        // build table: header + rows for each category (min 1 row each)
        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          const rows = byCat[cat.key].length ? byCat[cat.key] : [{}];
          rows.forEach((row, i) => {
            tbody.appendChild(makeRow(cat.key, row, i > 0));
          });
        });
        renumberAll();

        const meta = data.meta || {};

        // documentation
        _images = (data.docs && data.docs.length) ? data.docs : (meta.images || []);
        updateBadge();

        // status (use server status if provided, else auto-derive)
        const status = data.status || computeStatus(saved);
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        // no saved data — show blank rows
        tbody.innerHTML = '';
        CATEGORIES.forEach(cat => {
          tbody.appendChild(makeCatHeader(cat));
          tbody.appendChild(makeRow(cat.key, {}, false));
        });
        renumberAll();
        updateStatusBadge('not-started');
      });
  }

  /* ─────────────────────────────────────────
     SAVE
  ───────────────────────────────────────── */
  function save(requestedStatus) {
    const rows = collectRows();
    const meta = {
      images: _images,
    };

    // Block save if completely empty
    const fields = ['title', 'agency', 'researcher', 'recommendations', 'winners'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status   = (requestedStatus === 'draft') ? 'draft' : 'done';

    const payload = {
      table_no: TABLE_NO,
      status:   status,
      meta:     meta,
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
          done:        'Table 2a saved — all rows complete!',
          draft:       'Table 2a saved — some entries still need title, agency, researcher, and recommendations.',
          'not-started':'Table 2a saved.',
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
    const badge = document.getElementById('t2a_docs_count');
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
    const el = document.getElementById('t2a_status_msg');
    if (el) el.textContent = msg;
  }

  /* ─────────────────────────────────────────
     PUBLIC (window.T2a)
  ───────────────────────────────────────── */
  window.T2a = {
    addRow(catKey) {
      addRow(catKey, {}, true);
    },
    save,
    openDocs,
    _renumber(catKey) { renumber(catKey); },
  };
  window.T2A = window.T2a;

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

