/**
 * render-t15.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 15 — Equipment/Facilities Funded.
 * Rows are fixed categories (Endorsed, Approved, Facilities Upgraded,
 * Facilities Established, Equipment Purchased) per institution.
 */
'use strict';

function renderT15(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const rows = cmi.rows || [];
    const touched = rows.filter(r =>
      ['item', 'location', 'expense', 'funds'].some(k => (r[k] || '').trim() !== '')
    ).length;

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          ${badge}
          <span style="font-size:11px;color:var(--text-muted)">${touched} of ${rows.length || 5} categories filled · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
        </div>
        ${docsHtml || ''}
        ${rows.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:170px">Category</th>
                <th>Item Description</th>
                <th>Location / Agency</th>
                <th>Expenditures</th>
                <th>Source(s) of Funds</th>
              </tr>
            </thead>
            <tbody>
              ${rows.map(r => `
                <tr>
                  <td style="font-weight:600">${esc(r.category || '—')}</td>
                  <td>${esc(r.item || '—')}</td>
                  <td>${esc(r.location || '—')}</td>
                  <td>${esc(r.expense || '—')}</td>
                  <td>${esc(r.funds || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
