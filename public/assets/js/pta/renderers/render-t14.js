/**
 * render-t14.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 14 — Non-degree Trainings Conducted/Facilitated.
 */
'use strict';

function renderT14(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 14 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let out = '';

  acceptedData.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      ['title', 'venue', 'participants', 'expenditures', 'funds'].some(k => (r[k] || '').trim() !== '')
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
                <th style="width:32px">#</th>
                <th>Title of Activity</th>
                <th>Date / Venue</th>
                <th>No. of Participants</th>
                <th>Expenditures</th>
                <th>Source of Funds</th>
              </tr>
            </thead>
            <tbody>
              ${items.map((r, i) => `
                <tr>
                  <td style="text-align:center">${i + 1}</td>
                  <td>${esc(r.title || '—')}</td>
                  <td>${esc(r.venue || '—')}</td>
                  <td style="text-align:center">${esc(r.participants || '—')}</td>
                  <td>${esc(r.expenditures || '—')}</td>
                  <td>${esc(r.funds || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
