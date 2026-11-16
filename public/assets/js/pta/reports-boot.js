/**
 * reports-boot.js  —  SecReCo · PTA Portal
 * Wires up filter event listeners and kicks off the initial view.
 * Must load LAST — depends on everything in the other reports-*.js files.
 */
'use strict';

// ── BOOT ──────────────────────────────────────────────────────
// Script is loaded at bottom of body — DOM is ready, no need for DOMContentLoaded
function _reportsInit() {
  el('rptTableFilter')?.addEventListener('change', e => {
    const tbl = e.target.value;
    try {
      localStorage.setItem('secreco_selected_table', String(tbl));
      const u = new URL(window.location);
      u.searchParams.set('table', tbl);
      window.history.replaceState({}, '', u);
    } catch(err){}
    loadTableView();
  });
  el('rptYearFilter')?.addEventListener('change', async e => {
    const yr = e.target.value;
    try {
      localStorage.setItem('secreco_selected_year', String(yr));
      const u = new URL(window.location);
      u.searchParams.set('year', yr);
      window.history.replaceState({}, '', u);
    } catch(err){}
    _cmiList = [];
    await syncTableOptionsForYear(yr);
    if (_curView === 'table') loadTableView();
    else loadCMIView();
  });
  el('rptCMIFilter')?.addEventListener('change', e => {
    const cmiVal = e.target.value;
    try {
      localStorage.setItem('secreco_reports_cmi', cmiVal);
      const u = new URL(window.location);
      u.searchParams.set('cmi', cmiVal);
      window.history.replaceState({}, '', u);
    } catch(err){}
    if (cmiVal) renderCMIAllTables(cmiVal);
  });

  const urlParams = new URLSearchParams(window.location.search);
  const rawUrlYear = urlParams.get('year');
  const urlYear = rawUrlYear ? parseInt(rawUrlYear, 10) : null;
  let storedYear = null;
  try {
    const rawStored = localStorage.getItem('secreco_selected_year');
    if (rawStored) storedYear = parseInt(rawStored, 10);
  } catch(e){}

  fetch('/api/formats?include_drafts=1')
    .then(r => r.json())
    .then(data => {
      // Gather all possible years from backend and user intent
      const backendYears = [
        ...(Array.isArray(data.all_years) ? data.all_years : []),
        ...(Array.isArray(data.years) ? data.years : []),
        data.active_year
      ].map(y => parseInt(y, 10)).filter(y => !isNaN(y) && y >= 2000);

      // Preferred year: URL param > localStorage > active_year > latest year
      let targetYear = null;
      if (urlYear && !isNaN(urlYear) && urlYear >= 2000) {
        targetYear = urlYear;
      } else if (storedYear && !isNaN(storedYear) && storedYear >= 2000) {
        targetYear = storedYear;
      } else if (data.active_year && !isNaN(parseInt(data.active_year, 10))) {
        targetYear = parseInt(data.active_year, 10);
      } else if (backendYears.length > 0) {
        targetYear = Math.max(...backendYears);
      } else {
        targetYear = new Date().getFullYear();
      }

      // Ensure targetYear is always in the options list so it cannot be omitted
      const yearList = [...new Set([...backendYears, targetYear])].sort((a, b) => b - a);

      const yearSel = el('rptYearFilter');
      if (yearSel) {
        yearSel.innerHTML = yearList.map(y => `<option value="${y}" ${parseInt(y, 10) === targetYear ? 'selected' : ''}>CY ${y}</option>`).join('');
        yearSel.value = String(targetYear);
      }
      try {
        localStorage.setItem('secreco_selected_year', String(targetYear));
        const u = new URL(window.location);
        u.searchParams.set('year', targetYear);
        window.history.replaceState({}, '', u);
      } catch(e){}
    }).catch(() => {
      const fallbackYear = urlYear || storedYear || new Date().getFullYear();
      const yearSel = el('rptYearFilter');
      if (yearSel && !yearSel.value) {
        yearSel.innerHTML = `<option value="${fallbackYear}" selected>CY ${fallbackYear}</option>`;
        yearSel.value = String(fallbackYear);
      }
    }).finally(async () => {
      const curYr = el('rptYearFilter')?.value || new Date().getFullYear();
      await syncTableOptionsForYear(curYr);
      const targetView = urlParams.get('view') || localStorage.getItem('secreco_reports_view') || 'table';
      switchReportView(targetView);
    });
}

async function syncTableOptionsForYear(year) {
  try {
    const res = await fetch(`/api/formats?year=${year}&include_drafts=1`);
    const json = await res.json();
    const templates = json.templates || [];
    const tblSel = el('rptTableFilter');
    if (!tblSel) return;

    if (!window.TABLE_DEFS) window.TABLE_DEFS = typeof TABLE_DEFS !== 'undefined' ? TABLE_DEFS : {};

    if (templates.length === 0) {
      tblSel.innerHTML = '<option value="">(No tables configured for this year)</option>';
      tblSel.value = '';
      return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const urlTable = urlParams.get('table');
    let storedTable = null;
    try { storedTable = localStorage.getItem('secreco_selected_table'); } catch(e){}
    const targetTable = urlTable || storedTable || tblSel.value || 'T1';

    const norm = str => String(str || '').trim().toLowerCase().replace(/^table\s*/i, '').replace(/^t/i, '');
    const cleanTarget = norm(targetTable);

    let targetMatched = null;
    tblSel.innerHTML = templates.map(t => {
      const no = t.table_no;
      const cleanNo = String(no).replace(/^Table\s*/i, '');
      const label = t.title ? `Table ${cleanNo} — ${t.title}` : `Table ${cleanNo}`;

      const existingDef = window.TABLE_DEFS[no] || window.TABLE_DEFS[cleanNo] || window.TABLE_DEFS['T' + cleanNo] || {};
      // Check if template has actual custom columns (from Manage Formats)
      let hasCustomCols = false;
      if (t.columns_json) {
        let parsedCols = t.columns_json;
        if (typeof parsedCols === 'string') {
          try { parsedCols = JSON.parse(parsedCols); } catch (e) { parsedCols = null; }
        }
        if (parsedCols && typeof parsedCols === 'object') {
          if (Array.isArray(parsedCols.titles) && parsedCols.titles.some(tit => Array.isArray(tit.columns) && tit.columns.length > 0)) {
            hasCustomCols = true;
          } else if (Array.isArray(parsedCols.columns) && parsedCols.columns.length > 0) {
            hasCustomCols = true;
          } else if (Array.isArray(parsedCols) && parsedCols.length > 0) {
            const isLegacyPlaceholder = parsedCols.length === 3 &&
              parsedCols[0] === 'Item Description' &&
              parsedCols[1] === 'Agency' &&
              parsedCols[2] === 'Remarks';
            if (!isLegacyPlaceholder) {
              hasCustomCols = true;
            }
          }
        }
      }
      let chosenRenderer = existingDef.renderer || (typeof renderGeneric !== 'undefined' ? renderGeneric : null);
      if (hasCustomCols || !chosenRenderer) {
        chosenRenderer = (typeof renderGeneric !== 'undefined' ? renderGeneric : existingDef.renderer);
      }

      const defObj = {
        label: label,
        template: t,
        renderer: chosenRenderer
      };

      window.TABLE_DEFS[no] = defObj;
      window.TABLE_DEFS[cleanNo] = defObj;
      window.TABLE_DEFS['T' + cleanNo] = defObj;

      if (!targetMatched && norm(no) === cleanTarget) {
        targetMatched = no;
      }
      return `<option value="${no}">${label}</option>`;
    }).join('');

    const finalTable = targetMatched || (templates.length > 0 ? templates[0].table_no : 'T1');
    tblSel.value = finalTable;
    try {
      localStorage.setItem('secreco_selected_table', String(finalTable));
      const u = new URL(window.location);
      u.searchParams.set('table', finalTable);
      window.history.replaceState({}, '', u);
    } catch(e){}
  } catch (e) {
    console.warn('[reports] syncTableOptionsForYear warning:', e);
  }
}

// Fire immediately if DOM ready, else wait
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _reportsInit);
} else {
  _reportsInit();
}
