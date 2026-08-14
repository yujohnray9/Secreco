/**
 * render-generic.js  —  SecReCo · PTA Portal
 * Fallback renderer for tables without a custom renderer.
 * Shows: Institution | Status badge | Row count | Last Updated | View Rows button
 */
'use strict';

function renderGeneric(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let rows = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const count    = cmi.rows?.length ?? 0;
    const docsHtml = renderDocsSection(cmi.docs);

    rows += `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
            <strong>${esc(cmi.institution)}</strong>
            <span style="font-size:11px;color:var(--text-muted)">${count} item(s) · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
          </div>
          ${docsHtml ? `<br>${docsHtml}` : ''}
          ${count > 0
            ? `<button class="btn btn-xs" onclick='viewCMIRows(${JSON.stringify(cmi)})'>View Rows</button>`
            : '—'}
        </td>
      </tr>`;
  });

  return `
    <table class="dt" style="width:100%">
      <thead>
        <tr>
          <th>Institution</th>
          <th>Status</th>
          <th>Rows</th>
          <th>Last Updated</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>`;
}

function viewCMIRows(cmi) {
  console.table(cmi.rows);
  toast(`📋 ${cmi.institution}: ${cmi.rows.length} row(s) logged to console`);
}
