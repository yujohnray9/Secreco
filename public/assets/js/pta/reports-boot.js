/**
 * reports-boot.js  —  SecReCo · PTA Portal
 * Wires up filter event listeners and kicks off the initial view.
 * Must load LAST — depends on everything in the other reports-*.js files.
 */
'use strict';

// ── BOOT ──────────────────────────────────────────────────────
// Script is loaded at bottom of body — DOM is ready, no need for DOMContentLoaded
function _reportsInit() {
  el('rptTableFilter')?.addEventListener('change', loadTableView);
  el('rptYearFilter')?.addEventListener('change', () => {
    _cmiList = [];
    if (_curView === 'table') loadTableView();
    else loadCMIView();
  });
  el('rptCMIFilter')?.addEventListener('change', e => {
    if (e.target.value) renderCMIAllTables(e.target.value);
  });

  fetch('/api/formats')
    .then(r => r.json())
    .then(data => {
      if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
        const activeYr = data.active_year || new Date().getFullYear();
        const yearSel  = el('rptYearFilter');
        if (yearSel) {
          yearSel.innerHTML = data.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
        }
      }
    }).catch(() => {}).finally(() => {
      switchReportView('table');
    });
}

// Fire immediately if DOM ready, else wait
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _reportsInit);
} else {
  _reportsInit();
}
