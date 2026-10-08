/**
 * render-t13.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 13 — List of Technology Promotion Approaches.
 * Consolidated view matching Word/CVAARRD format.
 */
'use strict';

function renderT13(data) {
  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) return '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 13 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';

  let docRows = '';

  const CATEGORIES = [
    'Regional FIESTA',
    'Print (press releases, publications, newspapers, magazines, brochures, flyers, leaflets, posters, comics, and others)',
    'Radio/TV broadcast (news features, plugs, school on the air, interviews, guesting, vlogs, podcasts, and others)',
    'ICT-based IEC materials (e-publications, static web-based pages, CDs) and online promotion (web and social releases such as social media cards, reels, stories, and short videos)',
    'IEC-related trainings',
    'Other Technology Promotion (conduct of communication campaigns/advocacy initiatives, info caravan, and others; participation in NSTW, RSTW, AgriLink, DATE)'
  ];

  const catMap = {};
  CATEGORIES.forEach(c => { catMap[c] = []; });

  const summaryList = acceptedData.map(cmi => {
    let instCount = 0;
    const rows = (cmi.rows || []).filter(r => (r.approach || '').trim() !== '' || (r.remarks || '').trim() !== '');

    rows.forEach(r => {
      const appr = (r.approach || '').trim();
      const rem = (r.remarks || '').trim();
      const rawCat = (r.category || '').trim();

      let targetCat = CATEGORIES.find(c => c.toLowerCase() === rawCat.toLowerCase() || c.toLowerCase() === appr.toLowerCase());
      if (!targetCat) targetCat = CATEGORIES[5]; // Other

      instCount++;
      catMap[targetCat].push({
        approach: appr && appr !== targetCat ? appr : '—',
        agency: cmi.institution,
        remarks: rem || '—'
      });
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }

    return {
      institution: cmi.institution,
      count: instCount
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
            <th class="group" style="width:200px;padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">No. of Approaches</th>
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
  let detailRowsHtml = '';
  CATEGORIES.forEach(cat => {
    const rows = catMap[cat] || [];
    if (rows.length === 0) return;

    detailRowsHtml += `
      <tr style="background:#f3f6f4;font-weight:700;">
        <td colspan="4" style="padding:8px 12px;color:#00875a;font-weight:800;border:1px solid #e5e7eb;">${esc(cat)}</td>
      </tr>
    `;

    rows.forEach((r, i) => {
      detailRowsHtml += `
        <tr>
          <td style="width:40px;text-align:center;padding:7px 10px;">${i + 1}</td>
          <td style="padding:7px 12px;">${esc(r.approach)}</td>
          <td style="width:200px;padding:7px 12px;">${esc(r.agency)}</td>
          <td style="padding:7px 12px;">${esc(r.remarks)}</td>
        </tr>
      `;
    });
  });

  const detailTableHtml = detailRowsHtml ? `
    <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Technology Promotion Approaches</div>
    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
      <table class="merged" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#00875a;color:#ffffff;">
            <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
            <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Approach</th>
            <th style="width:200px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Implementing Agency</th>
            <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Remarks</th>
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
