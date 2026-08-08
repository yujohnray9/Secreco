/**
 * render-t12.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 12 — Technologies Commercialized or
 * Pre-Commercialization Initiatives.
 */
'use strict';

function renderT12(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      ['tech', 'owner', 'precomm', 'licensor', 'startup', 'spinoff'].some(k => (r[k] || '').trim() !== '')
    );

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          ${badge}
          <span style="font-size:11px;color:var(--text-muted)">${items.length} item(s) · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
        </div>
        ${docsHtml || ''}
        ${items.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:32px">#</th>
                <th>Name of Technology</th>
                <th>Technology Owner</th>
                <th>Pre-Commercialization Activity</th>
                <th>Licensor</th>
                <th>Start-Up</th>
                <th>Spin-off</th>
              </tr>
            </thead>
            <tbody>
              ${items.map((r, i) => `
                <tr>
                  <td style="text-align:center">${i + 1}</td>
                  <td>${esc(r.tech || '—')}</td>
                  <td>${esc(r.owner || '—')}</td>
                  <td>${esc(r.precomm || '—')}</td>
                  <td>${esc(r.licensor || '—')}</td>
                  <td>${esc(r.startup || '—')}</td>
                  <td>${esc(r.spinoff || '—')}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
