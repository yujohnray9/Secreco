/**
 * render-t17.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 17 — Schedule, Venue, Host Agencies of Regular Meetings.
 * Consolidated view matching Word/CVAARRD format.
 */
'use strict';

function renderT17(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 17 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let docRows = '';
  const allDetailRows = [];

  const summaryList = acceptedData.map(cmi => {
    const items = (cmi.rows || []).filter(r =>
      ['type', 'activity', 'venue', 'venue_date', 'host'].some(k => (r[k] || '').trim() !== '')
    );

    items.forEach(r => {
      allDetailRows.push({
        type: r.type || r.activity || '—',
        venue: r.venue || r.venue_date || '—',
        host: r.host || cmi.institution || '—',
        institution: cmi.institution
      });
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      count: items.length
    };
  });

  const sumTotal = summaryList.reduce((acc, s) => acc + s.count, 0);

  // 1. Summary Table
  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Institution</th>
            <th class="group" style="width:200px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Meetings / Activities</th>
          </tr>
        </thead>
        <tbody>
          ${summaryList.map(s => `
            <tr>
              <td style="padding:9px 12px;font-weight:500;">${esc(s.institution)}</td>
              <td style="padding:9px 12px;text-align:center;font-weight:600;">${s.count > 0 ? s.count : '—'}</td>
            </tr>
          `).join('')}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumTotal > 0 ? sumTotal : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // 2. Detail Table
  let detailTableHtml = '';
  if (allDetailRows.length > 0) {
    detailTableHtml = `
      <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Regular Meetings / Activities</div>
      <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
        <table class="merged" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#00875a;color:#ffffff;">
              <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
              <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Type of Meeting / Activity</th>
              <th style="width:220px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Venue and Date</th>
              <th style="width:200px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Host Agency</th>
            </tr>
          </thead>
          <tbody>
            ${allDetailRows.map((r, i) => `
              <tr>
                <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                <td style="padding:7px 12px;">${esc(r.type)}</td>
                <td style="padding:7px 12px;">${esc(r.venue)}</td>
                <td style="padding:7px 12px;">${esc(r.host)}</td>
              </tr>
            `).join('')}
          </tbody>
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
