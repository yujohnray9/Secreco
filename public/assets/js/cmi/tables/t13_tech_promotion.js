/**
 * t13_tech_promotion.js — Table 13: List of Technology Promotion Approaches.
 * 6 Categories with section headers and designated Add buttons.
 */

(function () {
  'use strict';

  const TABLE_NO = 'T13';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const CATEGORIES = [
    {
      btnLabel: 'Regional FIESTA',
      title: 'Regional FIESTA'
    },
    {
      btnLabel: 'Print',
      title: 'Print (press releases, publications, newspapers, magazines, brochures, flyers, leaflets, posters, comics, and others)'
    },
    {
      btnLabel: 'Radio/TV Broadcast',
      title: 'Radio/TV broadcast (news features, plugs, school on the air, interviews, guesting, vlogs, podcasts, and others)'
    },
    {
      btnLabel: 'ICT-based IEC Materials',
      title: 'ICT-based IEC materials (e-publications, static web-based pages, CDs) and online promotion (web and social releases such as social media cards, reels, stories, and short videos)'
    },
    {
      btnLabel: 'IEC-related Trainings',
      title: 'IEC-related trainings'
    },
    {
      btnLabel: 'Other Technology Promotion',
      title: 'Other Technology Promotion (conduct of communication campaigns/advocacy initiatives, info caravan, and others; participation in NSTW, RSTW, AgriLink, DATE)'
    }
  ];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const addBtns = CATEGORIES.map(c =>
      `<button class="btn btn-sm" type="button" onclick="T13.addRow('${esc(c.title)}')" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600;padding:7px 10px;text-align:center;font-size:12px">+ Add (${esc(c.btnLabel)})</button>`
    ).join('');

    return `
    <div class="t-page" id="t13_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 13. List of Technology Promotion Approaches.</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:650px">
          <thead>
            <tr>
              <th class="group" style="width:65%">Information, Education, and Communication (IEC) and Integrated Marketing Communication (IMC) Approaches</th>
              <th class="group">Remarks</th>
              <th class="group" style="width:40px;text-align:center"></th>
            </tr>
          </thead>
          <tbody id="t13_rows"></tbody>
        </table>
      </div>

      <!-- Add Buttons Grid matching Image 2 -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:8px;margin-bottom:14px">
        ${addBtns}
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-sm" type="button" onclick="T13.save()" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600">Save</button>
        <button class="btn t-docs-btn" type="button" onclick="T13.openDocs()">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="t13_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t13_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t13_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function makeCatHeader(catTitle) {
    return `<tr class="t13-cat-row" data-cat="${esc(catTitle)}">
      <td colspan="3" style="font-weight:700;background:var(--bg-soft,#f3f6f4);padding:9px 12px;color:var(--forest,#075b42);border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb">${esc(catTitle)}</td>
    </tr>`;
  }

  function createRowHTML(catTitle, approach = '', remarks = '') {
    const tr = document.createElement('tr');
    tr.className = 't13-data-row';
    tr.dataset.cat = catTitle;
    tr.innerHTML = `
      <td style="padding:6px 8px">
        <textarea rows="2" class="t13-approach" placeholder="Enter specific approach / title / activity..." style="width:100%;box-sizing:border-box;padding:6px 10px;border:1px solid #d1d5db;border-radius:4px;font-size:13px" >${esc(approach)}</textarea>
      </td>
      <td style="padding:6px 8px">
        <textarea class="t13-remarks" rows="2" style="width:100%;box-sizing:border-box;resize:vertical;padding:6px 8px;border:1px solid #d1d5db;border-radius:4px" placeholder="Remarks">${esc(remarks)}</textarea>
      </td>
      <td style="text-align:center">
        <button type="button" class="row-remove-btn" onclick="T13.removeRow(this)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;font-weight:700;padding:0 6px;line-height:1" title="Remove row">&times;</button>
      </td>
    `;
    return tr;
  }

  function addRow(catTitle = CATEGORIES[0].title, approach = '', remarks = '') {
    const tbody = document.getElementById('t13_rows');
    if (!tbody) return;
    const catRows = tbody.querySelectorAll(`tr.t13-data-row[data-cat="${catTitle}"]`);
    const tr = createRowHTML(catTitle, approach, remarks);
    if (catRows.length > 0) {
      catRows[catRows.length - 1].insertAdjacentElement('afterend', tr);
    } else {
      const header = tbody.querySelector(`tr.t13-cat-row[data-cat="${catTitle}"]`);
      if (header) header.insertAdjacentElement('afterend', tr);
      else tbody.appendChild(tr);
    }
    const input = tr.querySelector('.t13-approach');
    if (input && !approach) input.focus();
  }

  function removeRow(btn) {
    const tr = btn.closest('tr');
    if (tr) tr.remove();
  }

  function collectRows() {
    return [...document.querySelectorAll('#t13_rows tr.t13-data-row')].map((tr) => {
      const approach = tr.querySelector('.t13-approach')?.value.trim() || '';
      const remarks  = tr.querySelector('.t13-remarks')?.value.trim() || '';
      const category = tr.dataset.cat || CATEGORIES[0].title;
      return { category, approach, remarks };
    }).filter(r => r.approach !== '' || r.remarks !== '');
  }

  function matchCategory(catStr) {
    const s = String(catStr || '').toLowerCase().trim();
    if (s.includes('fiesta')) return CATEGORIES[0].title;
    if (s.includes('print')) return CATEGORIES[1].title;
    if (s.includes('radio') || s.includes('tv') || s.includes('broadcast')) return CATEGORIES[2].title;
    if (s.includes('ict') || s.includes('web') || s.includes('media') || s.includes('online') || s.includes('reels') || s.includes('video')) return CATEGORIES[3].title;
    if (s.includes('train')) return CATEGORIES[4].title;
    if (s.includes('other') || s.includes('caravan') || s.includes('campaign') || s.includes('nstw')) return CATEGORIES[5].title;
    return CATEGORIES[0].title;
  }

  function setRows(rows) {
    const tbody = document.getElementById('t13_rows');
    if (!tbody) return;
    tbody.innerHTML = '';

    const savedRows = (rows && Array.isArray(rows)) ? rows : [];
    const byCat = {};
    CATEGORIES.forEach(c => { byCat[c.title] = []; });

    savedRows.forEach(r => {
      const approach = (r.approach || '').trim();
      const remarks  = (r.remarks  || '').trim();
      if (!approach && !remarks) return;
      const targetCat = matchCategory(r.category);
      byCat[targetCat].push({ approach, remarks });
    });

    CATEGORIES.forEach(c => {
      tbody.insertAdjacentHTML('beforeend', makeCatHeader(c.title));
      const list = byCat[c.title];
      if (list && list.length > 0) {
        list.forEach(r => {
          tbody.appendChild(createRowHTML(c.title, r.approach, r.remarks));
        });
      } else {
        tbody.appendChild(createRowHTML(c.title, '', ''));
      }
    });

    const status = computeStatus(collectRows());
    updateStatusBadge(status);
  }

  function loadData() {
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        const savedRows = (data.rows && Array.isArray(data.rows)) ? data.rows : [];
        setRows(savedRows);

        _images = (data.docs && data.docs.length) ? data.docs : ((data.meta && data.meta.images) ? data.meta.images : []);
        updateBadge();
        const status = data.status || computeStatus(collectRows());
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {
        setRows([]);
      });
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function computeStatus(rows) {
    const touched = rows.filter(r => (r.remarks || '').trim() !== '' || (r.approach || '').trim() !== '');
    if (touched.length === 0) return 'not-started';
    return 'done';
  }
  function statusLabel(status) {
    if (status === 'done')  return 'Complete';
    if (status === 'draft') return 'In Progress';
    return 'Not Started';
  }
  function updateStatusBadge(status) {
    const badge = document.getElementById('t13_status_badge');
    if (!badge) return;
    badge.textContent = statusLabel(status);
    badge.style.display = 'inline-block';
    const colors = { 'done': { bg: '#e6f4ea', fg: 'var(--green, #1e7e34)' }, 'draft': { bg: '#fff4e5', fg: '#b06b00' }, 'not-started': { bg: '#f1f1f1', fg: '#777' } };
    const c = colors[status] || colors['not-started'];
    badge.style.background = c.bg;
    badge.style.color = c.fg;
  }

  function save(requestedStatus) {
    const rows = collectRows();
    const fields = ['remarks', 'approach'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = (requestedStatus === 'draft') ? 'draft' : 'done';
    const msgs = { 'done': 'Table 13 saved — all rows complete!', 'draft': 'Table 13 saved — draft saved.', 'not-started': 'Table 13 saved.' };

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
    const b = document.getElementById('t13_docs_count');
    if (!b) return;
    b.textContent = _images.length;
    b.style.display = _images.length > 0 ? 'inline' : 'none';
  }
  function openDocs() {
    DocsModal.open(TABLE_NO, _images, saved => { _images = saved; updateBadge(); });
  }

  function esc(s) { return String(s ?? '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t13_status_msg'); if (e) e.textContent = m; }

  window.T13 = {
    save,
    addRow,
    removeRow,
    openDocs,
    setRows,
  };

  (window.CMI = window.CMI || {});
  function register() {
    if (window.CMI && CMI.registerTable) CMI.registerTable({ no: TABLE_NO, render });
    else setTimeout(register, 50);
  }
  register();
})();
