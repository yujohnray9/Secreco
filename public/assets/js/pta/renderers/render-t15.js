/**
 * render-t15.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 15 — Equipment/Facilities Funded.
 * Consolidated view matching Word/CVAARRD format.
 */
'use strict';

function renderT15(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 15 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let docRows = '';

  const CATEGORIES = [
    'Endorsed',
    'Approved',
    'Facilities Upgraded',
    'Facilities Established',
    'Equipment Purchased'
  ];

  const catMap = {};
  CATEGORIES.forEach(c => { catMap[c] = []; });

  let grandExpenditures = 0;

  const summaryList = acceptedData.map(cmi => {
    let instCount = 0;
    let instExp = 0;

    const rows = (cmi.rows || []).filter(r =>
      ['item', 'location', 'expense', 'funds', 'equipment', 'expenditures'].some(k => (r[k] || '').trim() !== '')
    );

    rows.forEach(r => {
      const expAmt = parseFloat(String(r.expense || r.expenditures || '0').replace(/[^0-9.]/g, '')) || 0;
      instCount++;
      instExp += expAmt;

      const rawCat = (r.category || 'Endorsed').trim();
      const matched = CATEGORIES.find(c => c.toLowerCase() === rawCat.toLowerCase()) || 'Endorsed';

      catMap[matched].push({
        item: r.item || r.equipment || '—',
        location: r.location || cmi.institution || '—',
        expense: expAmt,
        funds: r.funds || r.fundsource || '—',
        institution: cmi.institution
      });
    });

    grandExpenditures += instExp;

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      count: instCount,
      expenditures: instExp
    };
  });

  const sumTotalCount = summaryList.reduce((acc, s) => acc + s.count, 0);

  // 1. Summary Table
  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Institution</th>
            <th class="group" style="width:180px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Facilities / Equipment</th>
            <th class="group" style="width:200px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:right;">Total Expenditures</th>
          </tr>
        </thead>
        <tbody>
          ${summaryList.map(s => `
            <tr>
              <td style="padding:9px 12px;font-weight:500;">${esc(s.institution)}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:600;">${s.count > 0 ? s.count : '—'}</td>
              <td style="padding:9px 12px;text-align:right;font-weight:600;">${s.expenditures > 0 ? fmtAmt(s.expenditures) : '—'}</td>
            </tr>
          `).join('')}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumTotalCount > 0 ? sumTotalCount : '—'}</td>
            <td style="padding:9px 12px;text-align:right;font-weight:800;">${grandExpenditures > 0 ? fmtAmt(grandExpenditures) : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // 2. Detail Table
  let detailRowsHtml = '';
  CATEGORIES.forEach(cat => {
    const rows = catMap[cat] || [];
    if (rows.length === 0) return;

    detailRowsHtml += `
      <tr style="background:#f3f6f4;font-weight:700;">
        <td colspan="5" style="padding:8px 12px;color:#00875a;font-weight:800;border:1px solid #e5e7eb;">${esc(cat)}</td>
      </tr>
    `;

    rows.forEach((r, i) => {
      detailRowsHtml += `
        <tr>
          <td style="width:40px;text-align:center;padding:7px 10px;">${i + 1}</td>
          <td style="padding:7px 12px;">${esc(r.item)}</td>
          <td style="width:200px;padding:7px 12px;">${esc(r.location)}</td>
          <td style="width:140px;text-align:right;padding:7px 12px;">${r.expense > 0 ? fmtAmt(r.expense) : '—'}</td>
          <td style="width:180px;padding:7px 12px;">${esc(r.funds)}</td>
        </tr>
      `;
    });
  });

  const detailTableHtml = detailRowsHtml ? `
    <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Equipment / Facilities Funded</div>
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
            <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Equipment / Facilities Established / Upgraded / Approved</th>
            <th style="width:200px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Location / Agency</th>
            <th style="width:140px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:right;">Expenditures</th>
            <th style="width:180px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Source(s) of Funds</th>
          </tr>
        </thead>
        <tbody>
          ${detailRowsHtml}
        </tbody>
      </table>
    </div>
  ` : '';

  // 3. Footnote
  const footnoteHtml = `
    <div class="rpt-footnote" style="margin-top:12px;font-size:11px;color:#6b7280;font-style:italic;">
      Note: The Regional Consortium may prepare other tables for ease in data presentation.
    </div>
  `;

  let out = summaryTableHtml + detailTableHtml + footnoteHtml;
  if (docRows) {
    out += `
      <div class="rpt-docs-section" style="margin-top:30px;border-top:1px dashed #e5e7eb;padding-top:20px;">
        <h4 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:12px;">Submitted Documentation &amp; Attachments</h4>
        ${docRows}
      </div>
    `;
  }

  return out;
}

function fmtAmt(n) {
  return '₱ ' + (n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
