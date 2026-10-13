/**
 * reports-view-cmi.js  —  SecReCo · PTA Portal
 * Per CMI consolidated view: dropdown + all-tables-for-one-institution rendering.
 */
'use strict';

// ── PER CMI ───────────────────────────────────────────────────
async function loadCMIView() {
  // The master institution list includes CMIs without accounts or submissions.
  try {
    const response = await fetch('/api/pta/institutions?year=' + encodeURIComponent(getYear()));
    if (!response.ok) throw new Error('Could not load institutions.');
    const json = await response.json();
    const names = (json.institutions || json.data || []).map(i => typeof i === 'string' ? i : (i.institution || i.name || '')).filter(Boolean);
    _cmiList = [...new Set(names)].sort((a, b) => a.localeCompare(b)).map(institution => ({ institution }));
  } catch (e) {
    console.error('[reports] loadCMIView error:', e);
  }
  populateCMIDropdown();

  const sel = el('rptCMIFilter').value;
  if (sel) renderCMIAllTables(sel);
}

function populateCMIDropdown() {
  const sel = el('rptCMIFilter');
  const cur = sel.value;
  sel.innerHTML = '<option value="">Select Institution...</option>';
  (_cmiList || []).forEach(c => {
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

  try {
    let tableData = {};
    let lockedTables = [];
    let templatesMap = {};

    try {
      const allRes = await fetchConsolidated('all');
      tableData = allRes.tables || allRes.data || {};
      lockedTables = (allRes.locked_tables || []).map(t => String(t).toUpperCase());
      templatesMap = allRes.templates || {};
    } catch (allErr) {
      console.warn('[reports] fetch all failed, trying fallback:', allErr);
    }

    // Determine the list of keys to display for this year
    let keys = Object.keys(tableData);
    if (!keys.length) {
      keys = Object.keys(window.TABLE_DEFS || {});
    }

    const topBtn = el('btnPtaCmiFillOut');
    if (topBtn) {
      if (lockedTables.length > 0) {
        topBtn.href = `/dashboard/cmi/fillup?year=${getYear()}&inst=${encodeURIComponent(institution)}&table=${encodeURIComponent(lockedTables[0])}`;
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

      const cleanKey = String(key).replace(/^Table\s*/i, '');
      const def      = (window.TABLE_DEFS && (window.TABLE_DEFS[key] || window.TABLE_DEFS[cleanKey] || window.TABLE_DEFS['T' + cleanKey])) || {};
      const tmpl     = templatesMap[key] || templatesMap[cleanKey] || def?.template || null;
      const title    = tmpl?.title || (def?.label ? def.label.split('—')[1]?.trim() : key);
      const isLocked = lockedTables.includes(key.toUpperCase()) || (tmpl && tmpl.is_locked);

      const actionCell = isLocked
        ? `<td style="text-align:center">
             <a href="/dashboard/cmi/fillup?year=${getYear()}&inst=${encodeURIComponent(institution)}&table=${encodeURIComponent(key)}" class="btn btn-sm" style="background:#075b42;color:#fff;border-radius:6px;font-size:11.5px;font-weight:700;padding:4px 10px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Fill out locked Table ${key} for this institution">
               <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>Fill Out
             </a>
           </td>`
        : `<td style="text-align:center;color:#9ca3af;font-size:13px">—</td>`;

      rows += `<tr>
        <td><strong>${esc(key)}</strong> ${isLocked ? '<span style="display:inline-flex;vertical-align:middle;color:#d97706;margin-left:3px;" title="Locked: PTA only"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>' : ''}</td>
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
        <tbody>${rows || '<tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af">No tables available.</td></tr>'}</tbody>
      </table>`;
  } catch (e) {
    el('cmiContainer').innerHTML =
      `<div class="loading-state" style="color:var(--danger,red)">⚠️ ${esc(e.message)}</div>`;
    console.error('[reports] renderCMIAllTables error:', e);
  }
}
