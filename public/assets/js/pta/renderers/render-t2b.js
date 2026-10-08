/**
 * render-t2b.js  —  SecReCo · PTA Portal
 * Renderer for Table 2b: Number of Participants in the RSRDH
 * Exactly mirrors the official Word/CVAARRD layout:
 * 1. Top Summary Table: Institution × (GO, NGO, Private Sector, LGU) → Grand Total
 * 2. Consolidated Category Table: Category (rowspan) | Agency / Association | No. of Participants | Remarks
 *    With category subtotals (GO Subtotal, NGO Subtotal, etc.)
 * 3. Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * 4. Documentation section below the table
 */
'use strict';

function renderT2b(arg1, arg2) {
  const allRows = arg2 !== undefined ? arg2 : arg1;
  const container = arg2 !== undefined ? arg1 : null;

  const acceptedRows = (allRows || []).filter(inst => inst.table_status === 'accepted' && (inst.rows || []).length > 0);
  if (!acceptedRows || acceptedRows.length === 0) {
    const emptyHtml = '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 2b yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
    if (container) container.innerHTML = emptyHtml;
    return emptyHtml;
  }

  const CATEGORIES = [
    { key: 'go',      label: 'GO' },
    { key: 'ngo',     label: 'NGO' },
    { key: 'private', label: 'Private Sector' },
    { key: 'lgu',     label: 'LGU' },
  ];

  let docRows = '';

  // 1. Grand summary table data
  let colTotals = Object.fromEntries(CATEGORIES.map(c => [c.key, 0]));
  let grandAll  = 0;

  const instSummary = acceptedRows.map(inst => {
    const rows = inst.rows || [];
    const bycat = Object.fromEntries(CATEGORIES.map(c => [c.key, 0]));

    rows.forEach(r => {
      const key = (r.category || '').toLowerCase();
      const matched = CATEGORIES.find(c => c.key === key || c.label.toLowerCase() === key);
      const k = matched ? matched.key : key;
      if (bycat[k] !== undefined) {
        bycat[k] += parseInt(r.count) || 0;
      }
    });

    const instTotal = Object.values(bycat).reduce((a, b) => a + b, 0);
    grandAll += instTotal;
    CATEGORIES.forEach(c => { colTotals[c.key] += bycat[c.key]; });

    if (inst.docs) {
      docRows += renderCMIDocsBlock(inst.institution, inst.docs);
    }

    return {
      institution: inst.institution || '—',
      bycat,
      total: instTotal
    };
  });

  let summaryRowsHtml = instSummary.map(inst => `
    <tr>
      <td style="padding:9px 12px;font-weight:500;">${esc(inst.institution)}</td>
      ${CATEGORIES.map(c => `<td style="padding:9px 12px;text-align:center;">${inst.bycat[c.key] > 0 ? inst.bycat[c.key] : '—'}</td>`).join('')}
      <td style="padding:9px 12px;text-align:center;font-weight:700;">${inst.total > 0 ? inst.total : '—'}</td>
    </tr>
  `).join('');

  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:28%;">Institution</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">GO</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">NGO</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Private Sector</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">LGU</th>
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:14%;">Grand Total</th>
          </tr>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Participants</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Participants</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Participants</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">Participants</th>
          </tr>
        </thead>
        <tbody>
          ${summaryRowsHtml}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            ${CATEGORIES.map(c => `<td style="padding:9px 12px;text-align:center;font-weight:800;">${colTotals[c.key] > 0 ? colTotals[c.key] : '—'}</td>`).join('')}
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${grandAll > 0 ? grandAll : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // 2. Consolidated Category Breakdown Table
  // Gather all rows by category across all institutions
  const categorizedRows = Object.fromEntries(CATEGORIES.map(c => [c.key, []]));
  acceptedRows.forEach(inst => {
    (inst.rows || []).forEach(r => {
      const key = (r.category || '').toLowerCase();
      const matched = CATEGORIES.find(c => c.key === key || c.label.toLowerCase() === key);
      const k = matched ? matched.key : key;
      if (categorizedRows[k]) {
        categorizedRows[k].push({
          agency: r.agency || '—',
          count: parseInt(r.count) || 0,
          remarks: r.remarks || '',
          institution: inst.institution
        });
      }
    });
  });

  let detailTbodyHtml = '';
  CATEGORIES.forEach(cat => {
    const rows = categorizedRows[cat.key] || [];
    const subtotal = rows.reduce((acc, r) => acc + r.count, 0);

    if (rows.length > 0) {
      rows.forEach((r, idx) => {
        detailTbodyHtml += `
          <tr>
            ${idx === 0 ? `<td rowspan="${rows.length}" style="padding:10px 12px;font-weight:700;text-align:center !important;vertical-align:middle !important;background:#ffffff;border:1px solid #e5e7eb;width:18%;">${esc(cat.label)}</td>` : ''}
            <td style="padding:9px 12px;text-align:center !important;vertical-align:middle !important;border:1px solid #e5e7eb;">${esc(r.agency)}</td>
            <td style="padding:9px 12px;text-align:center !important;vertical-align:middle !important;border:1px solid #e5e7eb;width:20%;">${r.count}</td>
            <td style="padding:9px 12px;text-align:center !important;vertical-align:middle !important;border:1px solid #e5e7eb;width:26%;">${esc(r.remarks)}</td>
          </tr>
        `;
      });
      detailTbodyHtml += `
        <tr style="background:#f9fafb;font-weight:700;border:1px solid #e5e7eb;">
          <td colspan="2" style="padding:9px 12px;text-align:center !important;font-weight:800;border:1px solid #e5e7eb;">${esc(cat.label)} Subtotal</td>
          <td style="padding:9px 12px;text-align:center !important;font-weight:800;border:1px solid #e5e7eb;">${subtotal}</td>
          <td style="padding:9px 12px;border:1px solid #e5e7eb;"></td>
        </tr>
      `;
    } else {
      // Empty category display as in reference screenshot
      detailTbodyHtml += `
        <tr>
          <td style="padding:10px 12px;font-weight:700;text-align:center !important;vertical-align:middle !important;background:#ffffff;border:1px solid #e5e7eb;width:18%;">${esc(cat.label)}</td>
          <td style="padding:9px 12px;text-align:center !important;vertical-align:middle !important;border:1px solid #e5e7eb;">—</td>
          <td style="padding:9px 12px;text-align:center !important;vertical-align:middle !important;border:1px solid #e5e7eb;width:20%;">—</td>
          <td style="padding:9px 12px;border:1px solid #e5e7eb;width:26%;"></td>
        </tr>
        <tr style="background:#f9fafb;font-weight:700;border:1px solid #e5e7eb;">
          <td colspan="2" style="padding:9px 12px;text-align:center !important;font-weight:800;border:1px solid #e5e7eb;">${esc(cat.label)} Subtotal</td>
          <td style="padding:9px 12px;text-align:center !important;font-weight:800;border:1px solid #e5e7eb;">—</td>
          <td style="padding:9px 12px;border:1px solid #e5e7eb;"></td>
        </tr>
      `;
    }
  });

  const detailTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-top:24px;margin-bottom:12px;">
      <table class="merged tbl-t2b-detail" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th style="padding:10px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center !important;width:18%;">Category</th>
            <th style="padding:10px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center !important;">Agency / Association</th>
            <th style="padding:10px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center !important;width:20%;">No. of Participants</th>
            <th style="padding:10px 12px;font-size:11.5px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center !important;width:26%;">Remarks</th>
          </tr>
        </thead>
        <tbody>
          ${detailTbodyHtml}
        </tbody>
      </table>
    </div>
  `;

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

  const finalHtml = `
    <div class="rpt-t2b-container">
      ${summaryTableHtml}
      ${detailTableHtml}
      ${footnoteHtml}
      ${docsSection}
    </div>
  `;

  if (container) {
    container.innerHTML = finalHtml;
  }
  return finalHtml;
}

window.renderT2b = renderT2b;
