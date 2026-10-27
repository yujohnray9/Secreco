/**
 * reports-view-table.js  —  SecReCo · PTA Portal
 * View toggle (Per Table / Per CMI) + Per Table loading logic.
 */
'use strict';

// ── VIEW TOGGLE ───────────────────────────────────────────────
function switchReportView(view) {
  _curView = view;
  const isTable = view === 'table';

  try {
    localStorage.setItem('secreco_reports_view', view);
    const u = new URL(window.location);
    u.searchParams.set('view', view);
    window.history.replaceState({}, '', u);
  } catch(e){}

  el('togPerTable')?.classList.toggle('active',  isTable);
  el('togPerCMI')?.classList.toggle('active',   !isTable);

  // panels
  const pTable = el('viewPerTable');
  const pCMI   = el('viewPerCMI');
  if (pTable) { pTable.style.display = isTable ? 'flex' : 'none'; pTable.style.flexDirection = 'column'; }
  if (pCMI)   pCMI.style.display    = isTable ? 'none' : 'block';

  // conditional filters
  if (el('rptTableFilter')) el('rptTableFilter').style.display  = isTable ? 'block' : 'none';
  if (el('rptCMIFilter'))   el('rptCMIFilter').style.display    = isTable ? 'none'  : 'block';
  if (el('rptExportBtns'))  el('rptExportBtns').style.display   = isTable ? 'flex'  : 'none';

  if (isTable) {
    loadTableView();
  } else {
    loadCMIView();
  }
}
// Expose to window so inline onclick attributes always find it
window.switchReportView = switchReportView;

// ── PER TABLE ─────────────────────────────────────────────────
async function loadTableView() {
  const key  = getTableKey();
  const year = getYear();

  if (!key) {
    el('tableCardTitle').textContent = 'No Tables Configured';
    el('rptSubtitle').textContent    = 'Consolidated Annual Accomplishment Report — CY ' + year;
    el('tableContainer').innerHTML   = '<div class="loading-state" style="padding:40px;text-align:center;color:#9ca3af">No tables are configured for CY ' + year + '. You can add format tables in Manage Formats.</div>';
    closeWordPreview();
    const wrap = el('rptExportBtns');
    if (wrap) wrap.innerHTML = '';
    return;
  }

  const cleanKey = String(key).replace(/^Table\s*/i, '');
  const def = (window.TABLE_DEFS && (window.TABLE_DEFS[key] || window.TABLE_DEFS[cleanKey] || window.TABLE_DEFS['T' + cleanKey])) || null;

  el('tableCardTitle').textContent = def?.label ?? `Table ${key}`;
  el('rptSubtitle').textContent    = 'Consolidated Annual Accomplishment Report — CY ' + year;
  el('tableContainer').innerHTML   = '<div class="loading-state">⏳ Loading...</div>';
  closeWordPreview();
  renderExportBtns();

  try {
    const json = await fetchConsolidated(key);
    _cmiList = json.data || [];

    const templateMeta = def?.template || json.template || null;
    const renderer     = def?.renderer ?? renderGeneric;
    const container    = el('tableContainer');

    if (renderer === renderGeneric) {
      container.innerHTML = renderGeneric(json.data, templateMeta);
    } else if (renderer.length >= 2) {
      renderer(container, json.data);
    } else {
      container.innerHTML = renderer(json.data);
    }

    tagDetailTables(container);
  } catch (e) {
    el('tableContainer').innerHTML =
      `<div class="loading-state" style="color:var(--danger,red)">⚠️ Failed to load: ${esc(e.message)}</div>`;
    console.error('[reports] loadTableView error:', e);
  }
}

/**
 * In multi-table views, marks the detail text tables with 'detail-table' / 'detail-table-wrap'.
 * This hides the long text tables from the on-screen dashboard view (keeping the top summary data/numbers visible),
 * while keeping all tables fully intact for Word preview and export.
 */
function tagDetailTables(container) {
  if (!container) return;
  const tables = [...container.querySelectorAll('table:not(.doc-table)')];
  if (tables.length > 1) {
    // tables[0] is the top summary table (data with numbers) — KEEP VISIBLE on dashboard!
    // tables[1..n] are detail text tables — mark as detail-table to hide on dashboard
    for (let i = 1; i < tables.length; i++) {
      const table = tables[i];
      table.classList.add('detail-table');
      const wrap = table.closest('.tbl-wrap');
      if (wrap && wrap !== container) {
        wrap.classList.add('detail-table-wrap');
      }
    }
  }
}
