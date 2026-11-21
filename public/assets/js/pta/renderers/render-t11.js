/**
 * render-t11.js  —  SecReCo · PTA Portal
 * Renderer for Table 11:
 * List of Technologies Extended/Deployed through Various Technology Transfer Extension Modalities
 * Exactly mirrors the official Word format (Picture 2):
 * - Header:
 *   Row 1: No. (rowspan 2) | Name of Technology (rowspan 2) | Project Title (rowspan 2) | Implementing Agency (rowspan 2) | Technology Transfer Modality (colspan 5)
 *   Row 2: STCBF | STC4iD | SAFE | Food Value Chain | Others
 * - Data rows:
 *   Continuous numbering (1, 2, 3...)
 *   Name of Technology, Project Title, Implementing Agency
 *   Modalities marked with '/' when checked; 'Others' displays specific initiative text (e.g. CRADLE Project, Spin-off, STBF) or '/'
 * - Footnote: "Note: The Regional Consortium may prepare other tables for ease in data presentation."
 * - Submitted Documentation & Attachments block
 */
'use strict';

function renderT11(arg1, arg2) {
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
    const emptyHtml = '<div class="loading-state" style="padding:40px;text-align:center;color:#6b7280">No accepted reports for Table 11 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
    if (container) container.innerHTML = emptyHtml;
    return emptyHtml;
  }

  let docRows = '';
  const allDetailRows = [];

  function isCheckedMod(v) {
    if (v === true || v === 1) return true;
    if (typeof v === 'string') {
      const s = v.trim().toLowerCase();
      return ['1', 'true', 'yes', '✓', '✔', '/', 'x'].includes(s);
    }
    return false;
  }

  acceptedData.forEach(cmi => {
    (cmi.rows || []).forEach(r => {
      const tech = String(r.tech || r.title || '').trim();
      const project = String(r.project || r.source || '').trim();
      const agency = String(r.agency || cmi.institution || '').trim();
      const mods = r.modalities || {};

      const stcbf = isCheckedMod(mods.stcbf ?? r.stcbf);
      const stc4id = isCheckedMod(mods.stc4id ?? r.stc4id);
      const safe = isCheckedMod(mods.safe ?? r.safe);
      const food_value_chain = isCheckedMod(mods.food_value_chain ?? mods.fvc ?? r.food_value_chain ?? r.fvc);

      // Extract others text if provided
      let otherText = '';
      const rawOthers = mods.others ?? mods.other_extension_initiatives ?? r.others ?? r.other ?? r.other_extension_initiatives ?? '';
      if (typeof rawOthers === 'string' && rawOthers.trim()) {
        otherText = rawOthers.trim();
      } else if (rawOthers === true) {
        otherText = '/';
      }

      const hasMod = stcbf || stc4id || safe || food_value_chain || Boolean(otherText);

      // Skip completely empty rows
      if (!tech && !project && !hasMod) return;

      allDetailRows.push({
        tech: tech || '—',
        project: project || '—',
        agency: agency || cmi.institution || '—',
        stcbf,
        stc4id,
        safe,
        food_value_chain,
        others: otherText,
        institution: cmi.institution
      });
    });

    if (cmi.docs) {
      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
    }
  });

  const yearSel = document.getElementById('rptYearFilter');
  const yearText = yearSel?.value || (window._rptCurrentYear || new Date().getFullYear());

  let rowsHtml = '';
  if (!allDetailRows.length) {
    rowsHtml = `
      <tr>
        <td colspan="9" style="text-align:center;padding:16px;color:#6b7280;font-style:italic;border:1px solid #7ea87e;background:#fff;">
          No technologies extended/deployed recorded.
        </td>
      </tr>`;
  } else {
    rowsHtml = allDetailRows.map((r, i) => `
      <tr>
        <td style="text-align:center;padding:8px 6px;font-size:11.5px;font-weight:600;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${i + 1}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.45;word-break:normal;overflow-wrap:break-word;">${esc(r.tech)}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.45;word-break:normal;overflow-wrap:break-word;">${esc(r.project)}</td>
        <td style="padding:8px 10px;font-size:11.5px;border:1px solid #7ea87e;text-align:left;vertical-align:middle;background:#fff;line-height:1.4;word-break:normal;overflow-wrap:break-word;">${esc(r.agency)}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.stcbf ? '/' : ''}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.stc4id ? '/' : ''}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.safe ? '/' : ''}</td>
        <td style="text-align:center;padding:8px 6px;font-size:12px;font-weight:700;border:1px solid #7ea87e;vertical-align:middle;background:#fff;">${r.food_value_chain ? '/' : ''}</td>
        <td style="text-align:center;padding:8px 8px;font-size:11px;font-weight:600;border:1px solid #7ea87e;vertical-align:middle;background:#fff;line-height:1.35;word-break:normal;overflow-wrap:break-word;">${esc(r.others)}</td>
      </tr>
    `).join('');
  }

  const tableHtml = `
    <div style="text-align:center;margin-bottom:16px;">
      <div style="font-weight:800;font-size:15px;color:#111827;letter-spacing:-.2px;">Table 11</div>
      <div style="font-weight:600;font-size:12.5px;color:#27272a;margin-top:3px;line-height:1.4;">
        List of Technologies Extended/Deployed through Various Technology Transfer Extension Modalities,<br>
        CY ${esc(yearText)}
      </div>
    </div>

    <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:18px;">
      <table class="merged tbl-fixed-cols tbl-t11" style="width:100%;table-layout:fixed;border-collapse:collapse;border:1px solid #7ea87e;">
        <colgroup>
          <col style="width:4%">
          <col style="width:19%">
          <col style="width:19%">
          <col style="width:14%">
          <col style="width:7%">
          <col style="width:7%">
          <col style="width:6.5%">
          <col style="width:10.5%">
          <col style="width:13%">
        </colgroup>
        <thead>
          <tr style="background:#c9e2ca;color:#111827;">
            <th rowspan="2" style="text-align:center;padding:8px 4px;font-size:11px;font-weight:700;border:1px solid #7ea87e;background:#c9e2ca;color:#111827;">No.</th>
            <th rowspan="2" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Name of Technology</th>
            <th rowspan="2" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Project Title</th>
            <th rowspan="2" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:left;background:#c9e2ca;color:#111827;">Implementing Agency</th>
            <th colspan="5" style="padding:8px 10px;font-size:11px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Technology Transfer Modality</th>
          </tr>
          <tr style="background:#c9e2ca;color:#111827;">
            <th style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">STCBF</th>
            <th style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">STC4iD</th>
            <th style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">SAFE</th>
            <th style="padding:6px 4px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Food Value<br>Chain</th>
            <th style="padding:6px 6px;font-size:10.5px;font-weight:700;border:1px solid #7ea87e;text-align:center;background:#c9e2ca;color:#111827;">Others</th>
          </tr>
        </thead>
        <tbody>
          ${rowsHtml}
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
