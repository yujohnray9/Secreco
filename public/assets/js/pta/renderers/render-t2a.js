/**
 * render-t2a.js  —  SecReCo · PTA Portal
 * Renderer for Table 2a: Summary of Regional Symposium on R&D Highlights (RSRDH)
 * Exactly mirrors the official Word/CVAARRD layout:
 * 1. Top Summary Table: Institution × (Research Category, Development Category) → Grand Total
 * 2. Research Category Table: # | Implementing Agency(ies) | Researcher(s) | Major Recommendations | Winners
 * 3. Development Category Table: # | Implementing Agency(ies) | Researcher(s) | Major Recommendations | Winners
 * 4. Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * 5. Documentation section below the table
 */
'use strict';

function renderT2a(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) {
    return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 2a yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
  }

  let docRows = '';

  const researchRows = [];
  const developmentRows = [];

  const instSummary = acceptedData.map(cmi => {
    let resCount = 0;
    let devCount = 0;
    const rows = cmi.rows ?? [];

    rows.forEach(r => {
      const cat = (r.category || 'research').trim().toLowerCase();
      const rowObj = {
        agency: r.agency || cmi.institution || '—',
        researcher: r.researcher || '—',
        recommendations: r.recommendations || '—',
        winners: r.winners || '—',
        institution: cmi.institution
      };

      if (cat.includes('dev') || cat === 'development') {
        devCount++;
        developmentRows.push(rowObj);
      } else {
        resCount++;
        researchRows.push(rowObj);
      }
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      resCount,
      devCount,
      grandTotal: resCount + devCount
    };
  });

  // Calculate Summary Totals
  const sumRes = instSummary.reduce((acc, i) => acc + i.resCount, 0);
  const sumDev = instSummary.reduce((acc, i) => acc + i.devCount, 0);
  const sumGrand = sumRes + sumDev;

  // 1. Institution Summary Table HTML
  let summaryRowsHtml = instSummary.map(inst => `
    <tr>
      <td style="padding:9px 12px;font-weight:500;">${esc(inst.institution)}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.resCount > 0 ? inst.resCount : '—'}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.devCount > 0 ? inst.devCount : '—'}</td>
      <td style="padding:9px 12px;text-align:center;font-weight:700;">${inst.grandTotal > 0 ? inst.grandTotal : '—'}</td>
    </tr>
  `).join('');

  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:34%;">Institution</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Research Category</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Development Category</th>
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:15%;">Grand Total</th>
          </tr>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of RSRDH</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of RSRDH</th>
          </tr>
        </thead>
        <tbody>
          ${summaryRowsHtml}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumRes > 0 ? sumRes : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumDev > 0 ? sumDev : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumGrand > 0 ? sumGrand : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // Helper for generating category table
  function buildCategoryTable(title, rows) {
    const rowsHtml = rows.length ? rows.map((r, idx) => `
      <tr>
        <td style="padding:8px 10px;text-align:center;font-weight:600;width:38px;">${idx + 1}</td>
        <td style="padding:8px 12px;">${esc(r.agency)}</td>
        <td style="padding:8px 12px;">${esc(r.researcher)}</td>
        <td style="padding:8px 12px;">${esc(r.recommendations)}</td>
        <td style="padding:8px 12px;">${esc(r.winners)}</td>
      </tr>
    `).join('') : `
      <tr><td colspan="5" style="padding:14px;text-align:center;color:#9ca3af;">No records in this category.</td></tr>
    `;

    return `
      <div class="rpt-sec-title" style="font-size:14px;font-weight:800;color:#111827;margin:24px 0 8px 0;">${title}</div>
      <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
        <table class="merged" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#00875a;color:#ffffff;">
              <th style="padding:9px 10px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:40px;">#</th>
              <th style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;width:26%;">Implementing Agency(ies)</th>
              <th style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;width:20%;">Researcher(s)</th>
              <th style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;width:34%;">Major Recommendations</th>
              <th style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;width:16%;">Winners</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
        </table>
      </div>
    `;
  }

  const resTableHtml = buildCategoryTable('Research Category', researchRows);
  const devTableHtml = buildCategoryTable('Development Category', developmentRows);

  const footnoteHtml = `
    <div class="rpt-footnote" style="font-size:11.5px;font-style:italic;color:#6b7280;margin:12px 0 24px 0;">
      Note: The Regional Consortium may prepare other tables for ease in data presentation.
    </div>
  `;

  const docsSection = docRows ? `
    <div class="doc-card" style="margin-top:24px;">
      <div class="doc-hdr">
        <i class="ti ti-paperclip" aria-hidden="true"></i>
        <div class="doc-hdr-title">Documentation</div>
      </div>
      <div class="doc-body">${docRows}</div>
    </div>` : '';

  return `
    <div class="rpt-t2a-container">
      ${summaryTableHtml}
      ${resTableHtml}
      ${devTableHtml}
      ${footnoteHtml}
      ${docsSection}
    </div>
  `;
}
