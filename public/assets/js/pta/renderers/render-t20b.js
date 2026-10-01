/**
 * render-t20b.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 20b — Policies formulated, advocated, implemented,
 * and institutionalized. Rows are grouped under the three fixed categories
 * (Policy formulated / advocated / implemented or institutionalized), though
 * an institution may have added extra rows under any category.
 */
'use strict';

function renderT20b(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 20b yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let out = '';

  acceptedData.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      ['agency', 'description'].some(k => (r[k] || '').trim() !== '')
    );

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          <span style="font-size:11px;color:var(--text-muted)">${items.length} item(s)</span>
        </div>
        ${docsHtml || ''}
        ${items.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:200px">List of Policies (Category)</th>
                <th>Agency</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              ${items.map(r => `
                <tr>
                  <td style="font-weight:600">${esc(r.category || '—')}</td>
                  <td>${esc(r.agency || '—')}</td>
                  <td>${esc(r.description || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
