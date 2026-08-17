/**
 * render-t13.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 13 — List of Technology Promotion Approaches.
 * Rows are fixed IEC/IMC approaches; only "remarks" varies per institution.
 */
'use strict';

function renderT13(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const rows = cmi.rows || [];

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          <span style="font-size:11px;color:var(--text-muted)">${rows.length} item(s)</span>
        </div>
        ${docsHtml || ''}
        ${rows.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:60%">IEC / IMC Approach</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              ${rows.map(r => `
                <tr>
                  <td>${esc(r.approach || '—')}</td>
                  <td>${esc(r.remarks || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
