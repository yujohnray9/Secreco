/**
 * render-t16.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 16 — Awards Received.
 */
'use strict';

function renderT16(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      ['award', 'recipient', 'sponsor', 'event', 'venue', 'date'].some(k => (r[k] || '').trim() !== '')
    );

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          ${badge}
          <span style="font-size:11px;color:var(--text-muted)">${items.length} award(s) · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
        </div>
        ${docsHtml || ''}
        ${items.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:110px">Category</th>
                <th>Title of Award</th>
                <th>Recipient / Agency</th>
                <th>Sponsor</th>
                <th>Event / Activity</th>
                <th>Venue</th>
                <th>Date Awarded</th>
              </tr>
            </thead>
            <tbody>
              ${items.map(r => `
                <tr>
                  <td>${esc(r.category || '—')}</td>
                  <td>${esc(r.award || '—')}</td>
                  <td>${esc(r.recipient || '—')}</td>
                  <td>${esc(r.sponsor || '—')}</td>
                  <td>${esc(r.event || '—')}</td>
                  <td>${esc(r.venue || '—')}</td>
                  <td>${esc(r.date || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
