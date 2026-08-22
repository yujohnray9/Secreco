/**
 * reports-view-cmi.js  —  SecReCo · PTA Portal
 * Per CMI consolidated view: dropdown + all-tables-for-one-institution rendering.
 */
'use strict';

// ── PER CMI ───────────────────────────────────────────────────
async function loadCMIView() {
  // Populate dropdown — use cache or fetch T1 to get CMI list
  if (_cmiList.length === 0) {
    try {
      const json = await fetchConsolidated('T1');
      _cmiList = json.data;
    } catch (e) {
      console.error('[reports] loadCMIView error:', e);
      return;
    }
  }
  populateCMIDropdown();

  const sel = el('rptCMIFilter').value;
  if (sel) renderCMIAllTables(sel);
}

function populateCMIDropdown() {
  const sel = el('rptCMIFilter');
  const cur = sel.value;
  sel.innerHTML = '<option value="">Select Institution...</option>';
  _cmiList.forEach(c => {
    const opt = document.createElement('option');
    opt.value       = c.institution;
    opt.textContent = c.institution;
    if (c.institution === cur) opt.selected = true;
    sel.appendChild(opt);
  });
}

async function renderCMIAllTables(institution) {
  el('cmiCardTitle').textContent  = institution + ' — All Tables';
  el('cmiContainer').innerHTML    = '<div class="loading-state">⏳ Loading all tables...</div>';

  const keys = Object.keys(TABLE_DEFS);
  try {
    const results = await Promise.all(keys.map(k => fetchConsolidated(k)));
    let rows = '';
    keys.forEach((key, i) => {
      const cmi      = results[i].data.find(c => c.institution === institution);
      const s        = cmi?.table_status ?? 'not-started';
      const badge    = STATUS_BADGE[s] ?? STATUS_BADGE['not-started'];
      const rowCount = cmi?.rows?.length ?? 0;
      const title    = TABLE_DEFS[key].label.split('—')[1]?.trim() ?? '';

      rows += `<tr>
        <td><strong>${key}</strong></td>
        <td>${esc(title)}</td>
        <td style="text-align:center">${rowCount || '—'}</td>
        <td style="font-size:11px;color:var(--text-muted)">${esc(cmi?.updated_at ?? '—')}</td>
      </tr>`;
    });

    el('cmiContainer').innerHTML = `
      <table class="dt" style="width:100%">
        <thead>
          <tr><th>Table</th><th>Title</th><th>Rows</th><th>Updated</th></tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  } catch (e) {
    el('cmiContainer').innerHTML =
      `<div class="loading-state" style="color:var(--danger,red)">⚠️ ${esc(e.message)}</div>`;
    console.error('[reports] renderCMIAllTables error:', e);
  }
}
