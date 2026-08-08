/**
 * drafts-render.js — CMI My Drafts
 * Builds the status overview table (grouped by section),
 * the progress summary card, and skeleton/error states.
 *
 * Sections (in order):
 *   1. RENDER TABLE   — renderTable, renderRow
 *   2. SUMMARY CARD   — updateSummary
 *   3. SKELETON/ERROR — showSkeleton, showError
 *   4. HELPER         — formatDate
 *   5. EXPORT         — window.DraftsRender
 *
 * Depends on: drafts-config.js (DraftsConfig)
 *             drafts-state.js  (DraftsState)
 *             sections-data.js (CMI_SECTIONS)
 * Exposed as: window.DraftsRender
 */

(function () {
  'use strict';

  const SECTIONS = window.CMI_SECTIONS || [];
  const { STATUS_CFG, FILLUP_URL, SUBMISSIONS_URL } = window.DraftsConfig;

  /* ══════════════════════════════════════════
     1. RENDER TABLE
  ══════════════════════════════════════════ */

  function renderTable() {
    const tbody = document.querySelector('#draftsTable tbody');
    if (!tbody) return;

    const { statuses, updatedAt } = window.DraftsState;

    // Compute summary counts before building rows
    const total         = SECTIONS.reduce((n, s) => n + s.tables.length, 0);
    const done          = Object.values(statuses).filter(s => s === 'done').length;
    const draft         = Object.values(statuses).filter(s => s === 'draft').length;
    const errors        = Object.values(statuses).filter(s => s === 'error').length;
    // "Not Started" = tables with no row yet + rows saved as 'not-started'
    const blankWithRow  = Object.values(statuses).filter(s => s === 'not-started').length;
    const blank         = (total - Object.keys(statuses).length) + blankWithRow;

    updateSummary({ total, done, draft, errors, blank });

    // Build rows grouped by section
    const rows = [];
    SECTIONS.forEach(section => {
      rows.push(renderSectionHeader(section.label));
      section.tables.forEach(t => rows.push(renderRow(t, statuses, updatedAt)));
    });

    tbody.innerHTML = rows.join('');
  }

  function renderSectionHeader(label) {
    return `
      <tr class="section-header-row">
        <td colspan="6" style="
          font-weight:700;font-size:12px;text-transform:uppercase;
          letter-spacing:.06em;color:var(--green,#2e7d32);
          background:var(--bg-soft,#f3f6f4);padding:8px 14px;
          border-left:3px solid var(--green,#2e7d32);
        ">${label}</td>
      </tr>`;
  }

  function renderRow(t, statuses, updatedAt) {
    const st  = statuses[t.no] || 'not-started';
    const cfg = STATUS_CFG[st] || STATUS_CFG['not-started'];
    const upd = updatedAt[t.no] ? formatDate(updatedAt[t.no]) : '—';

    // Completed tables live in My Submissions — send user there (highlighted)
    // instead of back into the fillup form.
    const href = (st === 'done')
      ? `${SUBMISSIONS_URL}?t=${t.no}`
      : `${FILLUP_URL}?t=${t.no}`;

    return `
      <tr>
        <td style="font-weight:600;white-space:nowrap">${t.no}</td>
        <td style="font-size:13px">${t.title}</td>
        <td>
          <span class="badge ${cfg.cls}" style="font-size:11.5px">${cfg.icon} ${cfg.label}</span>
        </td>
        <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">${upd}</td>
        <td>
          <a href="${href}" class="btn ${cfg.btnCls}">${cfg.action}</a>
        </td>
      </tr>`;
  }

  /* ══════════════════════════════════════════
     2. SUMMARY CARD
  ══════════════════════════════════════════ */

  function updateSummary({ total, done }) {
    const el = document.getElementById('drafts-summary');
    if (!el) return;
    const pct = Math.round((done / total) * 100);
    el.innerHTML = `
      <div class="progress-wrap">
        <div class="progress-label">
          <span>Overall Progress</span>
          <strong>${pct}% (${done} / ${total} tables complete)</strong>
        </div>
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" style="width:${pct}%"></div>
        </div>
      </div>`;
  }

  /* ══════════════════════════════════════════
     3. SKELETON / ERROR
  ══════════════════════════════════════════ */

  function showSkeleton() {
    const tbody = document.querySelector('#draftsTable tbody');
    if (!tbody) return;
    tbody.innerHTML = Array(5).fill(`
      <tr>
        ${Array(5).fill('<td><div class="skel-line"></div></td>').join('')}
        <td></td>
      </tr>`).join('');
  }

  function showError() {
    const tbody = document.querySelector('#draftsTable tbody');
    if (tbody) tbody.innerHTML = `
      <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted)">
        ⚠️ Could not load draft data. Please refresh the page.
      </td></tr>`;
  }

  /* ══════════════════════════════════════════
     4. HELPER
  ══════════════════════════════════════════ */

  function formatDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
         + ' ' + d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
  }

  /* ══════════════════════════════════════════
     5. EXPORT
  ══════════════════════════════════════════ */

  window.DraftsRender = { renderTable, showSkeleton, showError };

})();
