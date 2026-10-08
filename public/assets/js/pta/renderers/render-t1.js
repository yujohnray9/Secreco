/**
 * render-t1.js  —  SecReCo · PTA Portal
 * Renderer for Table 1: Summary of Agency In-House Reviews (AIHRs)
 * Exactly mirrors the official Word/CVAARRD layout:
 * 1. Top Summary Table: Institution × (Pre In-House, AIHR's) → Grand Total
 * 2. Pre In-House Table: Date | Agency (CMI) | Projects (New, Ongoing, Completed, Terminated) | Total Reviewed
 * 3. AIHR's Table: Date | Agency (CMI) | Projects (New, Ongoing, Completed, Terminated) | Total Reviewed
 * 4. Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * 5. Documentation section below the table
 */
'use strict';

function renderT1(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) {
    return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 1 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
  }

  let docRows = '';

  // Classify rows into Pre In-House vs AIHR's
  const preInHouseRows = [];
  const aihrsRows = [];

  const instSummary = acceptedData.map(cmi => {
    let preTotal = 0;
    let aihrsTotal = 0;
    const rows = cmi.rows ?? [];

    rows.forEach(r => {
      const cat = (r.category || '').trim().toLowerCase();
      const newCount = (+r.new_ || +r.new || +r.new_projects || 0);
      const ongoingCount = (+r.ongoing || 0);
      const completedCount = (+r.completed || 0);
      const terminatedCount = (+r.terminated || 0);
      const rowTotal = newCount + ongoingCount + completedCount + terminatedCount;

      const rowObj = {
        date: r.date || '—',
        agency: r.agency || cmi.institution || '—',
        new: newCount,
        ongoing: ongoingCount,
        completed: completedCount,
        terminated: terminatedCount,
        total: rowTotal,
        institution: cmi.institution
      };

      if (cat.includes('pre') || cat === 'pre in-house' || cat === 'pre-in-house') {
        preTotal += rowTotal;
        preInHouseRows.push(rowObj);
      } else {
        aihrsTotal += rowTotal;
        aihrsRows.push(rowObj);
      }
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      preTotal,
      aihrsTotal,
      grandTotal: preTotal + aihrsTotal
    };
  });

  // Calculate Summary Totals
  const sumPre = instSummary.reduce((acc, i) => acc + i.preTotal, 0);
  const sumAihrs = instSummary.reduce((acc, i) => acc + i.aihrsTotal, 0);
  const sumGrand = sumPre + sumAihrs;

  // 1. Institution Summary Table HTML
  let summaryRowsHtml = instSummary.map(inst => `
    <tr>
      <td style="padding:9px 12px;font-weight:500;">${esc(inst.institution)}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.preTotal > 0 ? inst.preTotal : '—'}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.aihrsTotal > 0 ? inst.aihrsTotal : '—'}</td>
      <td style="padding:9px 12px;text-align:center;font-weight:700;">${inst.grandTotal > 0 ? inst.grandTotal : '—'}</td>
    </tr>
  `).join('');

  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:34%;">Institution</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Pre In-House</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">AIHR's</th>
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:15%;">Grand Total</th>
          </tr>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Total Reviewed</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Total Reviewed</th>
          </tr>
        </thead>
        <tbody>
          ${summaryRowsHtml}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumPre > 0 ? sumPre : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumAihrs > 0 ? sumAihrs : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumGrand > 0 ? sumGrand : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // Helper for generating category table
  function buildCategoryTable(title, rows) {
    let sumN = 0, sumO = 0, sumC = 0, sumT = 0, sumTot = 0;
    rows.forEach(r => {
      sumN += r.new;
      sumO += r.ongoing;
      sumC += r.completed;
      sumT += r.terminated;
      sumTot += r.total;
    });

    const rowsHtml = rows.length ? rows.map(r => `
      <tr>
        <td style="padding:8px 12px;text-align:center;">${esc(r.date)}</td>
        <td style="padding:8px 12px;">${esc(r.agency)}</td>
        <td style="padding:8px 12px;text-align:center;">${r.new}</td>
        <td style="padding:8px 12px;text-align:center;">${r.ongoing}</td>
        <td style="padding:8px 12px;text-align:center;">${r.completed}</td>
        <td style="padding:8px 12px;text-align:center;">${r.terminated}</td>
        <td style="padding:8px 12px;text-align:center;font-weight:700;">${r.total}</td>
      </tr>
    `).join('') : `
      <tr><td colspan="7" style="padding:14px;text-align:center;color:#9ca3af;">No records in this category.</td></tr>
    `;

    return `
      <div class="rpt-sec-title" style="font-size:14px;font-weight:800;color:#111827;margin:24px 0 8px 0;">${title}</div>
      <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
        <table class="merged" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#00875a;color:#ffffff;">
              <th class="group" rowspan="2" style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:14%;">Date</th>
              <th class="group" rowspan="2" style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Agency (CMI)</th>
              <th class="group" colspan="4" style="padding:7px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Number of Projects Presented</th>
              <th class="group" rowspan="2" style="padding:9px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:14%;">Total Reviewed</th>
            </tr>
            <tr style="background:#00875a;color:#ffffff;">
              <th class="sub" style="padding:6px 10px;font-size:10.5px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;width:11%;">NEW</th>
              <th class="sub" style="padding:6px 10px;font-size:10.5px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;width:11%;">ONGOING</th>
              <th class="sub" style="padding:6px 10px;font-size:10.5px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;width:11%;">COMPLETED</th>
              <th class="sub" style="padding:6px 10px;font-size:10.5px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;width:11%;">TERMINATED</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
          <tfoot>
            <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
              <td colspan="2" style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
              <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumN}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumO}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumC}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumT}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumTot}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    `;
  }

  const preTableHtml = buildCategoryTable('Pre In-House', preInHouseRows);
  const aihrsTableHtml = buildCategoryTable("AIHR's", aihrsRows);

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
    <div class="rpt-t1-container">
      ${summaryTableHtml}
      ${preTableHtml}
      ${aihrsTableHtml}
      ${footnoteHtml}
      ${docsSection}
    </div>
  `;
}
