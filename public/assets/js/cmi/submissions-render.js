/**
 * submissions-render.js — CMI My Submissions
 * Builds the main submissions table: section headers, data rows,
 * search/filter, empty state, and the highlight-on-arrival pulse.
 *
 * Depends on: submissions-helpers.js (SubHelpers)
 *             submissions-state.js   (SubState)
 * Exposed as: window.SubRender
 */

(function () {
  'use strict';

  const SECTIONS = window.CMI_SECTIONS || [];
  const { formatDate, escapeHtml, updateSummary, highlightFromQuery } = window.SubHelpers;

  // Read autoStatus from URL on first load
  const _urlParams    = new URLSearchParams(window.location.search);
  const _autoStatus   = _urlParams.get('autoStatus') || '';

  /* ── Populate year filter ── */
  function populateYearFilter() {
    // Year filter is populated dynamically from /api/formats in submissions.js
  }

  /* ── Main table render ── */
  function renderTable() {
    // Sync the page sub-title with the selected year
    const subEl = document.querySelector('.page-sub');
    if (subEl) {
      if (_autoStatus === 'returned') {
        subEl.textContent = 'Tables returned for correction — CY ' + window.SubState.selectedYear;
      } else {
        subEl.textContent = 'Your submitted tables for CY ' + window.SubState.selectedYear;
      }
    }

    populateYearFilter();

    const tbody = document.querySelector('#submissionsTable tbody');
    if (!tbody) return;

    const searchTerm    = (document.getElementById('subSearch')?.value || '').toLowerCase().trim();
    const sectionFilter = document.getElementById('subSectionFilter')?.value || '';

    // Determine which statuses to show
    const targetStatuses = _autoStatus === 'returned' ? ['returned'] : ['accepted'];

    let totalSubmitted = 0;
    const rows = [];

    SECTIONS.forEach(section => {
      if (sectionFilter && sectionFilter !== section.label) return;

      const sectionRows = [];
      section.tables.forEach(t => {
        const canon = t.no.charAt(0).toUpperCase() + t.no.slice(1).toLowerCase();
        const st = window.SubState.statuses[t.no] || window.SubState.statuses[t.no.toUpperCase()] || window.SubState.statuses[t.no.toLowerCase()] || window.SubState.statuses[canon];
        if (!targetStatuses.includes(st)) return;
        if (searchTerm && !(
          t.no.toLowerCase().includes(searchTerm) ||
          t.title.toLowerCase().includes(searchTerm)
        )) return;

        totalSubmitted++;
        sectionRows.push(renderRow(t, st));
      });

      if (sectionRows.length) {
        rows.push(`
          <tr class="section-header-row">
            <td colspan="5" style="
              font-weight:700;font-size:12px;text-transform:uppercase;
              letter-spacing:.06em;color:var(--green,#2e7d32);
              background:var(--bg-soft,#f3f6f4);padding:8px 14px;
              border-left:3px solid var(--green,#2e7d32);
            ">${section.label}</td>
          </tr>`);
        rows.push(...sectionRows);
      }
    });

    updateSummary(totalSubmitted);

    const emptyLabel = _autoStatus === 'returned' ? 'No tables returned for correction' : 'No accepted tables yet';

    if (!rows.length) {
      tbody.innerHTML = `
        <tr><td colspan="5" style="text-align:center;padding:44px 20px;color:var(--text-muted)">
          <div style="font-size:28px;margin-bottom:10px">${_autoStatus === 'returned' ? '✅' : '📭'}</div>
          <div style="font-weight:600;margin-bottom:6px">${emptyLabel}${searchTerm || sectionFilter ? ' matching your filters' : ''}</div>
          <div style="font-size:12px">${_autoStatus === 'returned' ? 'All tables are currently accepted or pending review.' : 'Tables accepted by PTA will appear here.'}</div>
        </td></tr>`;
      return;
    }

    tbody.innerHTML = rows.join('');

    // View button — handled by submissions-view.js
    tbody.querySelectorAll('[data-view-table]').forEach(btn => {
      btn.addEventListener('click', () => window.SubView.openViewModal(btn.dataset.viewTable));
    });

    // Edit button — handled by submissions-edit.js
    tbody.querySelectorAll('[data-edit-table]').forEach(btn => {
      btn.addEventListener('click', () => window.SubEdit?.openEditModal(btn.dataset.editTable));
    });

    highlightFromQuery();
  }

  /* ── Single data row ── */
  function renderRow(t, st) {
    const canon = t.no.charAt(0).toUpperCase() + t.no.slice(1).toLowerCase();
    const meta  = window.SubState.meta[t.no] || window.SubState.meta[t.no.toUpperCase()] || window.SubState.meta[t.no.toLowerCase()] || window.SubState.meta[canon] || {};
    const upd   = (meta.submitted_at || meta.updated_at) ? formatDate(meta.submitted_at || meta.updated_at) : '—';
    const statusBadgeMap = {
      accepted: '<span class="badge" style="background:#ecfdf5;color:#0d9488;font-weight:600;font-size:11.5px">Accepted</span>',
      returned: '<span class="badge" style="background:#fef2f2;color:#dc2626;font-weight:600;font-size:11.5px">Returned for Revision</span>',
      submitted: '<span class="badge badge-green" style="font-size:11.5px">Submitted</span>',
      done: '<span class="badge badge-green" style="font-size:11.5px">Submitted</span>',
    };
    const badgeHtml = statusBadgeMap[st] || '<span class="badge badge-green" style="font-size:11.5px">Submitted</span>';
    const remark = meta.remarks || window.SubState.remarks?.[t.no] || window.SubState.remarks?.[t.no.toUpperCase()] || window.SubState.remarks?.[canon] || '';

    return `
      <tr id="sub-row-${t.no}">
        <td style="font-weight:600;white-space:nowrap">${t.no}</td>
        <td style="font-size:13px">
          <div>${escapeHtml(t.title)}</div>
          ${(st === 'returned' && remark) ? `
            <div style="margin-top:6px;padding:6px 10px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;font-size:12px;color:#991b1b;line-height:1.4">
              <strong>PTA Remarks:</strong> ${escapeHtml(remark)}
            </div>` : ''}
        </td>
        <td>${badgeHtml}</td>
        <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">${upd}</td>
        <td>
          <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
            <button class="btn btn-sm btn-outline" data-view-table="${t.no}">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px;margin-right:4px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> View
            </button>
            ${st === 'returned' ? `
              <a href="/dashboard/cmi/fillup?table=${encodeURIComponent(t.no)}&year=${window.SubState.selectedYear || 2026}" class="btn btn-sm" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;padding:4px 10px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:3px">
                ✏️ Revise
              </a>` : ''}
          </div>
        </td>
      </tr>`;
  }

  window.SubRender = { renderTable, populateYearFilter };

})();
