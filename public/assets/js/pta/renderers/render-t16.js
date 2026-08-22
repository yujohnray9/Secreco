/**
 * render-t16.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 16 — Awards Received.
 */
'use strict';

function renderT16(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  const CATEGORIES = ['Local', 'Regional', 'National', 'International'];
  let out = '';

  data.forEach(cmi => {
    const docsHtml = renderDocsSection(cmi.docs);
    const allRows = (cmi.rows || []).filter(r =>
      ['award', 'recipient', 'sponsor', 'event', 'venue', 'date'].some(k => (r[k] || '').trim() !== '')
    );

    let tbodyHtml = '';
    if (allRows.length > 0) {
      // Group items by category preserving standard order
      const groups = {};
      CATEGORIES.forEach(c => { groups[c] = []; });

      allRows.forEach(r => {
        const rawCat = (r.category || 'Local').trim();
        const matched = CATEGORIES.find(c => c.toLowerCase() === rawCat.toLowerCase()) || rawCat;
        if (!groups[matched]) groups[matched] = [];
        groups[matched].push(r);
      });

      // Render standard categories in order + any extra custom categories
      const allCategoryKeys = Object.keys(groups);
      allCategoryKeys.forEach(cat => {
        const catRows = groups[cat];
        if (!catRows || catRows.length === 0) return;

        catRows.forEach((r, idx) => {
          tbodyHtml += '<tr>';
          if (idx === 0) {
            const rowspanAttr = catRows.length > 1 ? ` rowspan="${catRows.length}"` : '';
            tbodyHtml += `<td${rowspanAttr} style="background:#edf5ee;font-weight:600;color:#14241b;vertical-align:middle;">${esc(cat)}</td>`;
          }
          tbodyHtml += `
            <td>${esc(r.award || '—')}</td>
            <td>${esc(r.recipient || '—')}</td>
            <td>${esc(r.sponsor || '—')}</td>
            <td>${esc(r.event || '—')}</td>
            <td>${esc(r.venue || '—')}</td>
            <td>${esc(r.date || '—')}</td>
          </tr>`;
        });
      });
    }

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:24px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
          <strong style="font-size:13.5px;color:#14241b">${esc(cmi.institution)}</strong>
          <span style="font-size:11.5px;color:var(--text-muted)">${allRows.length} item(s)</span>
        </div>
        ${docsHtml || ''}
        ${allRows.length ? `
          <div class="tbl-wrap" style="overflow-x:auto">
            <table class="merged" style="width:100%;margin-top:6px;border-collapse:collapse">
              <thead>
                <tr>
                  <th style="width:130px">CATEGORY</th>
                  <th>TITLE OF AWARD</th>
                  <th>RECIPIENT / AGENCY</th>
                  <th>SPONSOR</th>
                  <th>EVENT / ACTIVITY</th>
                  <th>VENUE</th>
                  <th>DATE AWARDED</th>
                </tr>
              </thead>
              <tbody>
                ${tbodyHtml}
              </tbody>
            </table>
          </div>
        ` : `<div style="font-size:12px;color:var(--text-muted);font-style:italic;margin-bottom:12px">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
