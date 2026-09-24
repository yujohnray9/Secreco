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
  el('rptYearFilter')?.addEventListener('change', async e => {
    const yr = e.target.value;
    try { localStorage.setItem('secreco_selected_year', String(yr)); } catch(err){}
    _cmiList = [];
    await syncTableOptionsForYear(yr);
    if (_curView === 'table') loadTableView();
    else loadCMIView();
  });
  el('rptCMIFilter')?.addEventListener('change', e => {
    if (e.target.value) renderCMIAllTables(e.target.value);
  });

  const urlYear = parseInt(new URLSearchParams(window.location.search).get('year'));
  let storedYear = null;
  try { storedYear = parseInt(localStorage.getItem('secreco_selected_year')); } catch(e){}

  fetch('/api/formats')
    .then(r => r.json())
    .then(data => {
      if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
        // Preferred year order: URL param -> localStorage -> active_year -> latest available year
        let targetYear = null;
        if (urlYear && data.years.includes(urlYear)) {
          targetYear = urlYear;
        } else if (storedYear && data.years.includes(storedYear)) {
          targetYear = storedYear;
        } else if (data.active_year && data.years.includes(data.active_year)) {
          targetYear = data.active_year;
        } else {
          targetYear = data.years[0];
        }

        const yearSel = el('rptYearFilter');
        if (yearSel) {
          yearSel.innerHTML = data.years.map(y => `<option value="${y}" ${y === targetYear ? 'selected' : ''}>CY ${y}</option>`).join('');
          yearSel.value = String(targetYear);
        }
        try { localStorage.setItem('secreco_selected_year', String(targetYear)); } catch(e){}
      }
    }).catch(() => {}).finally(async () => {
      const curYr = el('rptYearFilter')?.value || new Date().getFullYear();
      await syncTableOptionsForYear(curYr);
      switchReportView('table');
    });
}

async function syncTableOptionsForYear(year) {
  try {
    const res = await fetch(`/api/formats?year=${year}`);
    const json = await res.json();
    const templates = json.templates || [];
    const tblSel = el('rptTableFilter');
    if (!tblSel || templates.length === 0) return;

    const currentVal = tblSel.value;
    let matched = false;
    tblSel.innerHTML = templates.map(t => {
      const no = t.table_no;
      const label = `Table ${no} — ${t.title || no}`;
      if (!window.TABLE_DEFS) window.TABLE_DEFS = typeof TABLE_DEFS !== 'undefined' ? TABLE_DEFS : {};
      if (!window.TABLE_DEFS[no]) {
        window.TABLE_DEFS[no] = {
          label: label,
          renderer: typeof renderGeneric !== 'undefined' ? renderGeneric : (data => `<div style="padding:20px;text-align:center;color:#6b7280">Table ${no}</div>`)
        };
      }
      if (no === currentVal) matched = true;
      return `<option value="${no}">${label}</option>`;
    }).join('');

    if (matched) {
      tblSel.value = currentVal;
    }
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
