/**
 * render-t13.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 13 — List of Technology Promotion Approaches.
 * Supports the 6 categories with specific approaches and remarks per institution.
 */
'use strict';

function renderT13(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 13 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let out = '';

  const CATEGORIES = [
    'Regional FIESTA',
    'Print (press releases, publications, newspapers, magazines, brochures, flyers, leaflets, posters, comics, and others)',
    'Radio/TV broadcast (news features, plugs, school on the air, interviews, guesting, vlogs, podcasts, and others)',
    'ICT-based IEC materials (e-publications, static web-based pages, CDs) and online promotion (web and social releases such as social media cards, reels, stories, and short videos)',
    'IEC-related trainings',
    'Other Technology Promotion (conduct of communication campaigns/advocacy initiatives, info caravan, and others; participation in NSTW, RSTW, AgriLink, DATE)'
  ];

  acceptedData.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const rows = (cmi.rows || []).filter(r => (r.approach || '').trim() !== '' || (r.remarks || '').trim() !== '');

    let tbodyHtml = '';
    const hasCategoryInRows = rows.some(r => r.category);

    if (hasCategoryInRows) {
      CATEGORIES.forEach(cat => {
        const catRows = rows.filter(r => r.category === cat || r.approach === cat);
        if (catRows.length > 0) {
          tbodyHtml += `
            <tr style="background:#f3f6f4;font-weight:700;color:#075b42">
              <td colspan="2">${esc(cat)}</td>
            </tr>`;
          catRows.forEach(r => {
            const appr = (r.approach && r.approach !== cat) ? r.approach : '—';
            tbodyHtml += `
              <tr>
                <td style="padding-left:18px">${esc(appr)}</td>
                <td>${esc(r.remarks || '—')}</td>
              </tr>`;
          });
        }
      });
    } else {
      tbodyHtml = rows.map(r => `
        <tr>
          <td>${esc(r.approach || '—')}</td>
          <td>${esc(r.remarks || '—')}</td>
        </tr>`).join('');
    }

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
                <th style="width:65%">Information, Education, and Communication (IEC) and Integrated Marketing Communication (IMC) Approaches</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              ${tbodyHtml}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}
