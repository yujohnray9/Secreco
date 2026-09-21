/**
 * submissions-helpers.js — CMI My Submissions
 * Shared utilities: formatting, DOM helpers, skeleton/error states,
 * section-filter population, and the summary badge.
 *
 * Loaded before submissions-readonly.js and submissions.js.
 * Exposed on window.SubHelpers so both sibling modules can import cleanly.
 */

(function () {
  'use strict';

  /* ─────────────────────────────────────────
     FORMATTING
  ───────────────────────────────────────── */
  function formatDate(dt) {
    if (!dt) return '—';
    let str = String(dt).trim();
    if (/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/.test(str)) {
      str = str.replace(' ', 'T') + '+08:00';
    } else {
      const hasTimezone = /Z$|[+-]\d{2}:?\d{2}$/.test(str);
      if (!hasTimezone) str = str.replace('T', ' ') + ' GMT+0800';
    }
    const d = new Date(str);
    if (isNaN(d.getTime())) return dt;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' })
         + ' ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Manila' });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ─────────────────────────────────────────
     SUMMARY BADGE
  ───────────────────────────────────────── */
  function updateSummary(accepted) {
    const el = document.getElementById('submissions-summary');
    if (!el) return;
    el.innerHTML = `<span class="sub-count-badge"><strong>${accepted}</strong><span>Accepted</span></span>`;
  }

  /* ─────────────────────────────────────────
     SECTION FILTER
  ───────────────────────────────────────── */
  function populateSectionFilter() {
    const sel      = document.getElementById('subSectionFilter');
    const SECTIONS = window.CMI_SECTIONS || [];
    if (!sel) return;
    SECTIONS.forEach(s => {
      const opt       = document.createElement('option');
      opt.value       = s.label;
      opt.textContent = s.label;
      sel.appendChild(opt);
    });
  }

  /* ─────────────────────────────────────────
     SKELETON / ERROR STATES
  ───────────────────────────────────────── */
  function showSkeleton() {
    const tbody = document.querySelector('#submissionsTable tbody');
    if (!tbody) return;
    tbody.innerHTML = Array(4).fill(`
      <tr>${Array(4).fill('<td><div class="skel-line"></div></td>').join('')}<td></td></tr>`
    ).join('');
  }

  function showError() {
    const tbody = document.querySelector('#submissionsTable tbody');
    if (tbody) tbody.innerHTML = `
      <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted)">
        ⚠️ Could not load submission data. Please refresh the page.
      </td></tr>`;
  }

  /* ─────────────────────────────────────────
     HIGHLIGHT-ON-ARRIVAL
     Pulse-highlights the row whose table_no matches ?t= in the URL.
     Call once after tbody rows have been stamped into the DOM.
  ───────────────────────────────────────── */
  let _highlightDone = false;

  function highlightFromQuery() {
    if (_highlightDone) return;
    const target = new URLSearchParams(window.location.search).get('t');
    if (!target) return;
    const row = document.getElementById(`sub-row-${target}`);
    if (!row) return;
    _highlightDone = true;
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    row.classList.add('row-highlight');
    setTimeout(() => row.classList.remove('row-highlight'), 2500);
  }

  /* ─────────────────────────────────────────
     EXPORT
  ───────────────────────────────────────── */
  window.SubHelpers = {
    formatDate,
    escapeHtml,
    updateSummary,
    populateSectionFilter,
    showSkeleton,
    showError,
    highlightFromQuery,
  };

})();
