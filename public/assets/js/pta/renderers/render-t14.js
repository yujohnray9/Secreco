/**
 * render-t14.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 14 — Non-degree Trainings Conducted/Facilitated.
 * Consolidated view matching Word/CVAARRD format.
 */
'use strict';

function renderT14(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 14 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let docRows = '';
  const allDetailRows = [];

  let grandParticipants = 0;
  let grandExpenditures = 0;

  const summaryList = acceptedData.map(cmi => {
    const items = (cmi.rows || []).filter(r =>
      ['title', 'venue', 'participants', 'expenditures', 'expenditure', 'amount', 'expense', 'funds', 'source'].some(k => (r[k] || '').toString().trim() !== '')
    );

    let instParticipants = 0;
    let instExpenditures = 0;

    items.forEach(r => {
      const pCount = parseInt(String(r.participants || '0').replace(/[^0-9]/g, '')) || 0;
      const rawExp = r.expenditures ?? r.expenditure ?? r.amount ?? r.expense ?? r.cost ?? '0';
      const expAmt = parseFloat(String(rawExp).replace(/[^0-9.]/g, '')) || 0;

      instParticipants += pCount;
      instExpenditures += expAmt;

      allDetailRows.push({
        title: r.title || '—',
        venue: r.venue || '—',
        participants: pCount,
        expenditures: expAmt,
        funds: r.funds || r.source || r.fundsource || r.fund || '—',
        institution: cmi.institution
      });
    });

    grandParticipants += instParticipants;
    grandExpenditures += instExpenditures;

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      count: items.length,
      participants: instParticipants,
      expenditures: instExpenditures
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
            <th class="group" style="width:140px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Activities</th>
            <th class="group" style="width:160px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Total Participants</th>
            <th class="group" style="width:180px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:right;">Total Expenditures</th>
          </tr>
        </thead>
        <tbody>
          ${summaryList.map(s => `
            <tr>
              <td style="padding:9px 12px;font-weight:500;">${esc(s.institution)}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:600;">${s.count > 0 ? s.count : '—'}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:600;">${s.participants > 0 ? s.participants : '—'}</td>
              <td style="padding:9px 12px;text-align:right;font-weight:600;">${s.expenditures > 0 ? fmtAmt(s.expenditures) : '—'}</td>
            </tr>
          `).join('')}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumTotalCount > 0 ? sumTotalCount : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${grandParticipants > 0 ? grandParticipants : '—'}</td>
            <td style="padding:9px 12px;text-align:right;font-weight:800;">${grandExpenditures > 0 ? fmtAmt(grandExpenditures) : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // 2. Detail Table
  let detailTableHtml = '';
  if (allDetailRows.length > 0) {
    detailTableHtml = `
      <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Non-degree Trainings Conducted / Facilitated</div>
      <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
        <table class="merged" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#00875a;color:#ffffff;">
              <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
              <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Title of Activity</th>
              <th style="width:180px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Implementing Agency</th>
              <th style="width:160px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Date / Venue</th>
              <th style="width:110px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Participants</th>
              <th style="width:130px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:right;">Expenditures</th>
              <th style="width:150px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Source of Funds</th>
            </tr>
          </thead>
          <tbody>
            ${allDetailRows.map((r, i) => `
              <tr>
                <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                <td style="padding:7px 12px;">${esc(r.title)}</td>
                <td style="padding:7px 12px;">${esc(r.institution)}</td>
                <td style="padding:7px 12px;">${esc(r.venue)}</td>
                <td style="text-align:center;padding:7px 12px;">${r.participants > 0 ? r.participants : '—'}</td>
                <td style="text-align:right;padding:7px 12px;">${r.expenditures > 0 ? fmtAmt(r.expenditures) : '—'}</td>
                <td style="padding:7px 12px;">${esc(r.funds)}</td>
              </tr>
            `).join('')}
          </tbody>
          <tfoot>
            <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
              <td colspan="4" style="text-align:right;padding:8px 12px;font-weight:800;">TOTAL</td>
              <td style="text-align:center;padding:8px 12px;font-weight:800;">${grandParticipants > 0 ? grandParticipants : '—'}</td>
              <td style="text-align:right;padding:8px 12px;font-weight:800;">${grandExpenditures > 0 ? fmtAmt(grandExpenditures) : '—'}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    `;
  }

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
