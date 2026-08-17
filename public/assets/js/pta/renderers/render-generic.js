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
          </div>
          ${docsHtml ? `<br>${docsHtml}` : ''}
        </td>
        <td style="text-align:center;font-weight:700;color:var(--green)">${count || '—'}</td>
      </tr>`;
  });

  return `
    <table class="rpt-table merged" style="width:100%">
      <thead>
        <tr>
          <th>Institution</th>
          <th style="width:100px;text-align:center">Rows</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>`;
}

function viewCMIRows(cmi) {
  console.table(cmi.rows);
  toast(`📋 ${cmi.institution}: ${cmi.rows.length} row(s) logged to console`);
}
