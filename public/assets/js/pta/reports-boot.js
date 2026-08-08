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
  switchReportView('table');
}

// Fire immediately if DOM ready, else wait
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _reportsInit);
} else {
  _reportsInit();
}
