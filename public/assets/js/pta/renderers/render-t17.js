/**
 * render-t17.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 17 — Schedule, Venue, Host Agencies of Regular Meetings.
 */
'use strict';

function renderT17(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      ['type', 'venue', 'host'].some(k => (r[k] || '').trim() !== '')
    );

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          <span style="font-size:11px;color:var(--text-muted)">${items.length} item(s) · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
        </div>
        ${docsHtml || ''}
        ${items.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:32px">#</th>
                <th>Type of Meeting / Activity</th>
                <th>Venue and Date</th>
                <th>Host Agency</th>
              </tr>
            </thead>
            <tbody>
              ${items.map((r, i) => `
                <tr>
                  <td style="text-align:center">${i + 1}</td>
                  <td>${esc(r.type || '—')}</td>
                  <td>${esc(r.venue || '—')}</td>
                  <td>${esc(r.host || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
