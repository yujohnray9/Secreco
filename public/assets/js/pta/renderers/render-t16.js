/**
 * render-t16.js  —  SecReCo · PTA Portal
 * Renderer for Table 16: Awards Received
 * Exactly mirrors official Word/CVAARRD layout:
 * 1. Top Summary Table: Institution × (Local, Regional, National, International) → Grand Total
 * 2. Consolidated Detail Table: Category | Title of Award | Recipient / Agency | Sponsor | Event / Activity | Date of Award | Place of Award
 * 3. Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * 4. Documentation section below the table
 */
'use strict';

function renderT16(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) {
    return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 16 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
  }

  let docRows = '';
  const CATEGORIES = ['Local', 'Regional', 'National', 'International'];

  // Map of category -> rows across all institutions
  const categoryRows = {};
  CATEGORIES.forEach(c => { categoryRows[c] = []; });

  // Summary per institution
  const instSummary = acceptedData.map(cmi => {
    const counts = { Local: 0, Regional: 0, National: 0, International: 0 };
    const rows = cmi.rows ?? [];

    rows.forEach(r => {
      const award = (r.award || r.title || '').trim();
      const recipient = (r.recipient || r.agency || '').trim();
      const sponsor = (r.sponsor || '').trim();
      const event = (r.event || r.activity || '').trim();
      const venue = (r.venue || r.place || r.location || '').trim();
      const date = (r.date || r.date_awarded || '').trim();

      // Only count non-blank entries
      if (!award && !recipient && !sponsor && !event && !venue && !date) return;

      const rawCat = (r.category || 'Local').trim();
      const matched = CATEGORIES.find(c => c.toLowerCase() === rawCat.toLowerCase()) || 'Local';

      counts[matched] = (counts[matched] || 0) + 1;

      if (!categoryRows[matched]) categoryRows[matched] = [];
      categoryRows[matched].push({
        award: award || '—',
        recipient: recipient || cmi.institution || '—',
        sponsor: sponsor || '—',
        event: event || '—',
        date: date || '—',
        venue: venue || '—',
        institution: cmi.institution
      });
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    const grandTotal = Object.values(counts).reduce((a, b) => a + b, 0);

    return {
      institution: cmi.institution,
      counts,
      grandTotal
    };
  });

  // Calculate totals across institutions
  const sumCounts = { Local: 0, Regional: 0, National: 0, International: 0 };
  let sumGrand = 0;

  instSummary.forEach(inst => {
    CATEGORIES.forEach(c => {
      sumCounts[c] += (inst.counts[c] || 0);
    });
    sumGrand += inst.grandTotal;
  });

  // 1. Institution Summary Table HTML
  const summaryRowsHtml = instSummary.map(inst => `
    <tr>
      <td style="padding:9px 12px;font-weight:500;">${esc(inst.institution)}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.counts.Local > 0 ? inst.counts.Local : '—'}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.counts.Regional > 0 ? inst.counts.Regional : '—'}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.counts.National > 0 ? inst.counts.National : '—'}</td>
      <td style="padding:9px 12px;text-align:center;">${inst.counts.International > 0 ? inst.counts.International : '—'}</td>
      <td style="padding:9px 12px;text-align:center;font-weight:700;">${inst.grandTotal > 0 ? inst.grandTotal : '—'}</td>
    </tr>
  `).join('');

  const summaryTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:30%;">Institution</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Local</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Regional</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">National</th>
            <th class="group" colspan="1" style="padding:8px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">International</th>
            <th class="group" rowspan="2" style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;width:14%;">Grand Total</th>
          </tr>
          <tr style="background:#00875a;color:#ffffff;">
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Awards</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Awards</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Awards</th>
            <th class="sub" style="padding:6px 10px;font-size:11px;font-weight:600;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Awards</th>
          </tr>
        </thead>
        <tbody>
          ${summaryRowsHtml}
        </tbody>
        <tfoot>
          <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
            <td style="padding:9px 12px;text-align:right;font-weight:800;">TOTAL</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumCounts.Local > 0 ? sumCounts.Local : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumCounts.Regional > 0 ? sumCounts.Regional : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumCounts.National > 0 ? sumCounts.National : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumCounts.International > 0 ? sumCounts.International : '—'}</td>
            <td style="padding:9px 12px;text-align:center;font-weight:800;">${sumGrand > 0 ? sumGrand : '—'}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;

  // 2. Consolidated Detail Table HTML
  let detailRowsHtml = '';

  CATEGORIES.forEach(cat => {
    const rows = categoryRows[cat] || [];
    if (rows.length === 0) {
      detailRowsHtml += `
        <tr>
          <td style="padding:8px 12px;font-weight:700;vertical-align:middle;text-align:center;background:#fff;">${esc(cat)}</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
          <td style="padding:8px 12px;text-align:center;">—</td>
        </tr>
        <tr style="background:#f9fafb;font-weight:600;border-bottom:1px solid #e5e7eb;">
          <td colspan="5" style="padding:6px 12px;text-align:right;font-weight:700;">${esc(cat)} Subtotal</td>
          <td colspan="2" style="padding:6px 12px;text-align:center;font-weight:700;">—</td>
        </tr>
      `;
    } else {
      rows.forEach((r, idx) => {
        detailRowsHtml += '<tr>';
        if (idx === 0) {
          detailRowsHtml += `
            <td rowspan="${rows.length}" style="padding:8px 12px;font-weight:700;vertical-align:middle;text-align:center;background:#fff;border-right:1px solid #e5e7eb;">
              ${esc(cat)}
            </td>
          `;
        }
        detailRowsHtml += `
          <td style="padding:8px 12px;text-align:left;">${esc(r.award)}</td>
          <td style="padding:8px 12px;text-align:left;">${esc(r.recipient)}</td>
          <td style="padding:8px 12px;text-align:left;">${esc(r.sponsor)}</td>
          <td style="padding:8px 12px;text-align:left;">${esc(r.event)}</td>
          <td style="padding:8px 12px;text-align:left;">${esc(r.venue)}</td>
          <td style="padding:8px 12px;text-align:center;white-space:nowrap;">${esc(r.date)}</td>
        </tr>`;
      });
      // Subtotal row
      detailRowsHtml += `
        <tr style="background:#f9fafb;font-weight:600;border-bottom:1px solid #e5e7eb;">
          <td colspan="5" style="padding:6px 12px;text-align:right;font-weight:700;">${esc(cat)} Subtotal</td>
          <td colspan="2" style="padding:6px 12px;text-align:center;font-weight:700;">${rows.length}</td>
        </tr>
      `;
    }
  });

  const detailTableHtml = `
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:20px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:13%;text-align:center;">Category</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Title of Award</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:16%;text-align:left;">Recipient / Agency</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:14%;text-align:left;">Sponsor</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:15%;text-align:left;">Event / Activity</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:15%;text-align:left;">Place of Award</th>
            <th style="padding:10px 12px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;width:12%;text-align:center;">Date of Award</th>
          </tr>
        </thead>
        <tbody>
          ${detailRowsHtml}
        </tbody>
      </table>
    </div>
  `;

  // 3. Footnote
  const footnoteHtml = `
    <div class="rpt-footnote" style="margin-top:12px;font-size:11px;color:#6b7280;font-style:italic;">
      Note: The Regional Consortium may prepare other tables for ease in data presentation.
    </div>
  `;

  let out = summaryTableHtml + detailTableHtml + footnoteHtml;

  // 4. Docs block if available
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
