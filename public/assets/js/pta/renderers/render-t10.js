/**
 * render-t10.js  —  SecReCo · PTA Portal
 * Renderer for Table 10:
 * List of technology transfer program/projects packaged, approved and implemented
 * Exactly mirrors the official Word format (Picture 1):
 * - Title block: Table 10 / List of technology transfer program/projects packaged, approved and implemented, January-December [Year]
 * - 3-tier header:
 *   Row 1: No (rowspan 3) | Program/ Project Title (rowspan 3) | Agency Proponent (rowspan 3) | Regional Priority Addressed (rowspan 3) | Status (colspan 5)
 *   Row 2: Approved (colspan 3) | For Revision (rowspan 2) | Disapproved (rowspan 2)
 *   Row 3: Approved Duration | Approved Budget (Php) | Funding Source
 * - Full-width category banners: "Proposals Packaged" and "Projects Approved and Implemented"
 * - Clean columns matching Picture 1
 * - Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * - Submitted Documentation & Attachments block
 */
'use strict';

function renderT10(arg1, arg2) {
  let container = null;
  let data = null;
  if (arg1 && typeof arg1 === 'object' && arg1.nodeType) {
    container = arg1;
    data = arg2;
  } else {
    data = arg1;
  }

  const acceptedData = (data || []).filter(cmi => cmi.table_status === 'accepted' && (cmi.rows || []).length > 0);
  if (!acceptedData.length) {
    const emptyHtml = '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 10 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
    if (container) container.innerHTML = emptyHtml;
    return emptyHtml;
  }

  let docRows = '';
  const packagedRows = [];
  const implementedRows = [];

  acceptedData.forEach(cmi => {
    (cmi.rows || []).forEach(r => {
      const title = String(r.title || '').trim();
      const agency = String(r.agency || cmi.institution || '').trim();
      const priority = String(r.priority || '').trim();
      const duration = String(r.duration || '').trim();
      const budget = String(r.budget ?? '').trim();
      const fund = String(r.fund || r.source || r.fundsource || '').trim();
      const statusNorm = String(r.status || '').trim().toLowerCase();
      const approved = !!r.approved || statusNorm === 'approved';
      const for_revision = !!r.for_revision || statusNorm === 'for revision' || statusNorm === 'revision';
      const disapproved = !!r.disapproved || statusNorm === 'disapproved';

      // Only include non-empty rows
      if (!title && !agency && !priority && !duration && !budget && !fund && !approved && !for_revision && !disapproved) {
        return;
      }

      const rowObj = {
        title: title || '—',
        agency: agency || cmi.institution || '—',
        priority: priority || '—',
        approved,
        for_revision,
        disapproved,
        duration: (approved || duration) ? (duration || '—') : '',
        budget: (approved || budget) ? formatT10Budget(budget) : '',
        fund: (approved || fund) ? (fund || '—') : '',
        institution: cmi.institution
      };

      const cat = (r.category || '').toLowerCase();
      if (cat.includes('pack')) {
        packagedRows.push(rowObj);
      } else {
        implementedRows.push(rowObj);
      }
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }
  });

  const yearSel = document.getElementById('rptYearFilter');
  const yearText = yearSel?.value || (window._rptCurrentYear || new Date().getFullYear());

  // Helper to render section rows
  function renderSectionRows(rows) {
    if (!rows.length) {
      return `
        <tr>
          <td colspan="9" style="text-align:center;padding:14px;color:#6b7280;font-style:italic;border:1px solid #7ea87e;background:#fff;">
            No entries recorded.
          </td>
        </tr>`;
    }

    return rows.map((r, i) => `
      <tr>
        <td style="text-align:center;padding:8px 6px;font-size:11.5px;font-weight:600;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${i + 1}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.45;word-break:normal;overflow-wrap:break-word;">${esc(r.title)}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.4;word-break:normal;overflow-wrap:break-word;">${esc(r.agency)}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.4;word-break:normal;overflow-wrap:break-word;">${esc(r.priority)}</td>
        <td style="padding:8px 8px;font-size:11px;border:1px solid #7ea87e;text-align:center;vertical-align:middle;background:#fff;line-height:1.35;word-break:normal;overflow-wrap:break-word;">${esc(r.duration)}</td>
        <td style="padding:8px 8px;font-size:11px;border:1px solid #7ea87e;text-align:right;vertical-align:middle;background:#fff;font-variant-numeric:tabular-nums;word-break:normal;overflow-wrap:break-word;">${esc(r.budget)}</td>
        <td style="padding:8px 8px;font-size:11px;border:1px solid #7ea87e;text-align:center;vertical-align:middle;background:#fff;line-height:1.35;word-break:normal;overflow-wrap:break-word;">${esc(r.fund)}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.for_revision ? '/' : ''}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.disapproved ? '/' : ''}</td>
      </tr>
    `).join('');
  }

  const tableHtml = `
    <div style="text-align:center;margin-bottom:16px;">
      <div style="font-weight:800;font-size:15px;color:#111827;letter-spacing:-.2px;">Table 10</div>
      <div style="font-weight:600;font-size:12.5px;color:#27272a;margin-top:3px;line-height:1.4;">
        List of technology transfer program/projects packaged, approved and implemented,<br>
        January-December ${esc(yearText)}
      </div>
    </div>

    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:18px;">
      <table class="merged tbl-fixed-cols tbl-t10" style="width:100%;table-layout:fixed;border-collapse:collapse;border:1px solid #7ea87e;">
        <colgroup>
          <col style="width:4%">
          <col style="width:22%">
          <col style="width:13%">
          <col style="width:12%">
          <col style="width:12%">
          <col style="width:12%">
          <col style="width:8%">
          <col style="width:8%">
          <col style="width:9%">
        </colgroup>
        <thead>
          <tr style="background:#c9e2ca;color:#111827;">
            <th rowspan="3" style="text-align:center;padding:8px 4px;font-size:11px;font-weight:700;border:1px solid #7ea87e;background:#c9e2ca;color:#111827;">No</th>
            <th rowspan="3" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Program / Project Title</th>
            <th rowspan="3" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Agency Proponent</th>
            <th rowspan="3" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Regional Priority Addressed</th>
            <th colspan="5" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Status</th>
          </tr>
          <tr style="background:#c9e2ca;color:#111827;">
            <th colspan="3" style="padding:6px 8px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Approved</th>
            <th rowspan="2" style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">For<br>Revision</th>
            <th rowspan="2" style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Disapproved</th>
          </tr>
          <tr style="background:#c9e2ca;color:#111827;">
            <th style="padding:6px 6px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Approved<br>Duration</th>
            <th style="padding:6px 6px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Approved Budget<br>(Php)</th>
            <th style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Funding<br>Source</th>
          </tr>
        </thead>
        <tbody>
          <!-- Category 1: Proposals Packaged -->
          <tr style="background:#d8ead9;font-weight:700;">
            <td colspan="9" style="padding:8px 12px;font-weight:800;color:#111827;background:#d8ead9;border:1px solid #7ea87e;text-align:left;">
              Proposals Packaged
            </td>
          </tr>
          ${renderSectionRows(packagedRows)}

          <!-- Category 2: Projects Approved and Implemented -->
          <tr style="background:#d8ead9;font-weight:700;">
            <td colspan="9" style="padding:8px 12px;font-weight:800;color:#111827;background:#d8ead9;border:1px solid #7ea87e;text-align:left;">
              Projects Approved and Implemented
            </td>
          </tr>
          ${renderSectionRows(implementedRows)}
        </tbody>
      </table>
    </div>
  `;

  const footnoteHtml = `
    <div class="rpt-footnote" style="margin-top:12px;font-size:11px;color:#6b7280;font-style:italic;">
      Note: The Regional Consortium may prepare other tables for ease in data presentation.
    </div>
  `;

  let out = tableHtml + footnoteHtml;
  if (docRows) {
    out += `
      <div class="rpt-docs-section" style="margin-top:30px;border-top:1px dashed #e5e7eb;padding-top:20px;">
        <h4 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:12px;">Submitted Documentation &amp; Attachments</h4>
        ${docRows}
      </div>
    `;
  }

  if (container) {
    container.innerHTML = out;
  }
  return out;
}

function formatT10Budget(val) {
  if (!val && val !== 0) return '';
  const str = String(val).trim();
  if (!str) return '';
  const match = str.match(/-?\d[\d,.]*/);
  if (!match) return str;
  let cleaned = match[0].replace(/,/g, '');
  const parts = cleaned.split('.');
  if (parts.length > 2) {
    const last = parts.pop();
    cleaned = parts.join('') + (last.length <= 2 ? '.' + last : last);
  }
  const cleanNum = parseFloat(cleaned);
  if (isNaN(cleanNum) || cleanNum === 0) return str;
  return cleanNum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
