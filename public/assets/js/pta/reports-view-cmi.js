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
    // 1 single API call for all tables:
    let tableData = {};
    let lockedTables = [];
    try {
      const allRes = await fetchConsolidated('all');
      tableData = allRes.tables || allRes.data || {};
      lockedTables = (allRes.locked_tables || []).map(t => String(t).toUpperCase());
    } catch (allErr) {
      // Fallback: batch individual tables if 'all' fails
      const results = await Promise.allSettled(keys.map(k => fetchConsolidated(k)));
      keys.forEach((k, i) => {
        if (results[i].status === 'fulfilled' && results[i].value?.data) {
          tableData[k] = results[i].value.data;
          if (results[i].value.locked_tables) {
            lockedTables = results[i].value.locked_tables.map(t => String(t).toUpperCase());
          }
        }
      });
    }

    const topBtn = el('btnPtaCmiFillOut');
    if (topBtn) {
      if (lockedTables.length > 0) {
        topBtn.href = `/dashboard/cmi/fillup?year=${getYear()}&inst=${encodeURIComponent(institution)}&table=${lockedTables[0]}`;
        topBtn.style.display = 'inline-flex';
      } else {
        topBtn.style.display = 'none';
      }
    }

    let rows = '';
    keys.forEach((key) => {
      const list     = tableData[key] || [];
      const cmi      = Array.isArray(list) ? list.find(c => c.institution === institution) : null;
      const s        = cmi?.table_status ?? 'not-started';
      const badge    = STATUS_BADGE[s] ?? STATUS_BADGE['not-started'];
      const rowCount = cmi?.rows?.length ?? 0;
      const title    = TABLE_DEFS[key]?.label?.split('—')[1]?.trim() ?? '';
      const isLocked = lockedTables.includes(key.toUpperCase());

      const actionCell = isLocked
        ? `<td style="text-align:center">
             <a href="/dashboard/cmi/fillup?year=${getYear()}&inst=${encodeURIComponent(institution)}&table=${key}" class="btn btn-sm" style="background:#075b42;color:#fff;border-radius:6px;font-size:11.5px;font-weight:700;padding:4px 10px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Fill out locked Table ${key} for this institution">
               <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>Fill Out
             </a>
           </td>`
        : `<td style="text-align:center;color:#9ca3af;font-size:13px">—</td>`;

      rows += `<tr>
        <td><strong>${key}</strong> ${isLocked ? '<span style="font-size:10px;color:#d97706;" title="Locked: PTA only">🔒</span>' : ''}</td>
        <td>${esc(title)}</td>
        <td>${badge}</td>
        <td style="text-align:center">${rowCount || '—'}</td>
        <td style="font-size:11px;color:var(--text-muted)">${esc(cmi?.updated_at ?? '—')}</td>
        ${actionCell}
      </tr>`;
    });

    el('cmiContainer').innerHTML = `
      <table class="dt" style="width:100%">
        <thead>
          <tr><th>Table</th><th>Title</th><th>Status</th><th>Rows</th><th>Updated</th><th style="text-align:center">Action</th></tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  } catch (e) {
    el('cmiContainer').innerHTML =
      `<div class="loading-state" style="color:var(--danger,red)">⚠️ ${esc(e.message)}</div>`;
    console.error('[reports] renderCMIAllTables error:', e);
  }
}
