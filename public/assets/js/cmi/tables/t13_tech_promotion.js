/**
 * t13_tech_promotion.js — Table 13: List of Technology Promotion Approaches,
 * CY 2025 (January – December).
 * Columns: IEC and IMC Approaches (fixed rows) | Remarks
 */

(function () {
  'use strict';

  const TABLE_NO = 'T13';
  const API_SAVE = '/api/cmi/tables/save';
  const API_LOAD = '/api/cmi/tables/load';

  let _images = [];

  const FIXED_APPROACHES = [
    'Regional FIESTA',
    'Print (press releases, publications, newspapers, magazines, brochures, flyers, leaflets, posters, comics, and others)',
    'Radio/TV broadcast (news features, plugs, school on the air, interviews, guesting, vlogs, podcasts, and others)',
    'ICT-based IEC materials (e-publications, static web-based pages, CDs) and online promotion (web and social releases such as social media cards, reels, stories, and short videos)',
    'IEC-related trainings',
    'Other Technology Promotion (conduct of communication campaigns/advocacy initiatives, info caravan, and others; participation in NSTW, RSTW, Agrilink, DATE)',
  ];

  function render(container) {
    container.innerHTML = buildShell();
    loadData();
  }

  function buildShell() {
    const rows = FIXED_APPROACHES.map((approach, i) => `
      <tr>
        <td style="padding:6px 8px">${esc(approach)}</td>
        <td><textarea class="t13-remarks" data-idx="${i}" rows="2"
              style="width:100%;resize:vertical" placeholder="Remarks"></textarea></td>
      </tr>`).join('');

    return `
    <div class="t-page" id="t13_wrap">
      <div class="t-hdr">
        <div class="t-title">Table 13. List of Technology Promotion Approaches, CY 2025 (January – December).</div>
      </div>

      <div class="tbl-wrap" style="margin:14px 0">
        <table class="merged" style="width:100%;min-width:600px">
          <thead>
            <tr>
              <th class="group" style="width:70%">Information, Education, and Communication (IEC) and Integrated Marketing Communication (IMC) Approaches</th>
              <th class="group">Remarks</th>
            </tr>
          </thead>
          <tbody id="t13_rows">${rows}</tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <button class="btn t-docs-btn" onclick="T13.openDocs()">
          📎 Documentation <span id="t13_docs_count" class="t-docs-badge" style="display:none">0</span>
        </button>
        <span id="t13_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
        <span id="t13_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
      </div>
    </div>`;
  }

  function collectRows() {
    return [...document.querySelectorAll('#t13_rows tr')].map((tr, i) => ({
      approach: FIXED_APPROACHES[i] || '',
      remarks:  tr.querySelector('.t13-remarks')?.value || '',
    }));
  }

  function loadData() {
    fetch(`${API_LOAD}?table_no=${TABLE_NO}`)
      .then(r => r.json())
      .then(data => {
        if (data.rows && data.rows.length) {
          data.rows.forEach((row, i) => {
            const ta = document.querySelector(`.t13-remarks[data-idx="${i}"]`);
            if (ta) ta.value = row.remarks || '';
          });
        }
        _images = (data.meta && data.meta.images) ? data.meta.images : [];
        updateBadge();
        const status = computeStatus(collectRows());
        updateStatusBadge(status);
        if (data.updated_at) setMsg(`Last saved: ${data.updated_at}`);
      })
      .catch(() => {});
  }

  /* ─────────────────────────────────────────
     STATUS (auto-derived)
  ───────────────────────────────────────── */
  function computeStatus(rows) {
    const touched = rows.filter(r => (r.remarks || '').trim() !== '');
    if (touched.length === 0) return 'not-started';
    return touched.length === rows.length ? 'done' : 'draft';
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

  function save() {
    const rows = collectRows();
    const fields = ['remarks'];
    if (!CMIUtils.guardEmptySave(rows, fields)) return;

    const status = computeStatus(rows);
    const msgs = { 'done': '✅ Table 13 saved — all rows complete!', 'draft': '💾 Table 13 saved — some rows still incomplete.', 'not-started': '💾 Table 13 saved.' };

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

  function esc(s) { return String(s).replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function setMsg(m) { const e = document.getElementById('t13_status_msg'); if (e) e.textContent = m; }

  window.T13 = {
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

