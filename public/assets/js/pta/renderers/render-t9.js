/**
 * render-t9.js — Report renderer for Table 9: Technologies/Information Generated from R&D
 * Flat table.
 * Columns: Title/Brief Description | Project/Program Source | Agency | Researcher(s) | Potential Impact
 */

(function () {
  'use strict';

  window.renderT9 = function (container, allRows) {
    const acceptedRows = (allRows || []).filter(inst => inst.table_status === 'accepted' && (inst.rows || []).length > 0);
    if (!acceptedRows || acceptedRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No accepted reports for Table 9 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
      return;
    }

    let docRows = '';
    acceptedRows.forEach(inst => {
      if (inst.docs) docRows += renderCMIDocsBlock(inst.institution, inst.docs);
    });

    // ── Summary ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th style="width:200px">Institution</th>
            <th style="width:120px">No. of Technologies</th>
          </tr>
        </thead>
        <tbody>`;

    let grandTotal = 0;

    acceptedRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.title?.trim());
      grandTotal += rows.length;
      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${rows.length || '—'}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${grandTotal || '—'}</td>
          </tr>
        </tfoot>
      </table>`;

    // ── Consolidated Detail Table ──
    const allDetailRows = [];
    acceptedRows.forEach(inst => {
      (inst.rows || []).filter(r => (r.title || '').trim()).forEach(r => {
        allDetailRows.push({
          title: r.title || '—',
          source: r.source || '—',
          agency: r.agency || inst.institution || '—',
          researcher: r.researcher || '—',
          impact: r.impact || '—',
          institution: inst.institution
        });
      });
    });

    if (allDetailRows.length > 0) {
      html += `
        <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Technologies Generated from R&amp;D Activities</div>
        <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
          <table class="merged" style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="background:#00875a;color:#ffffff;">
                <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Title / Brief Description</th>
                <th style="width:180px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Project / Program Source</th>
                <th style="width:150px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Agency</th>
                <th style="width:160px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Researcher(s)</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Potential Impact or Contribution</th>
              </tr>
            </thead>
            <tbody>
              ${allDetailRows.map((r, i) => `
                <tr>
                  <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                  <td style="padding:7px 12px;">${esc(r.title)}</td>
                  <td style="padding:7px 12px;">${esc(r.source)}</td>
                  <td style="padding:7px 12px;">${esc(r.agency)}</td>
                  <td style="padding:7px 12px;">${esc(r.researcher)}</td>
                  <td style="padding:7px 12px;">${esc(r.impact)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>`;
    }

    html += `
      <div class="rpt-footnote" style="margin-top:12px;font-size:11px;color:#6b7280;font-style:italic;">
        Note: The Regional Consortium may prepare other tables for ease in data presentation.
      </div>
    `;

    if (docRows) {
      html += `
        <div class="rpt-docs-section" style="margin-top:30px;border-top:1px dashed #e5e7eb;padding-top:20px;">
          <h4 style="font-size:14px;font-weight:700;color:#1e3a5f;margin-bottom:12px;">📁 Supporting Documents</h4>
          <div style="display:flex;flex-direction:column;gap:12px;">
            ${docRows}
          </div>
        </div>`;
    }

    container.innerHTML = html;
  };

  function statusBadge(s) {
    const map = { done:['✅','#e6f4ea','var(--green,#1e7e34)'], draft:['✏️','#fff4e5','#b06b00'], 'not-started':['—','#f1f1f1','#777'] };
    const [icon,bg,fg] = map[s] || map['not-started'];
    return `<span style="background:${bg};color:${fg};padding:1px 7px;border-radius:8px;font-size:11px;font-weight:600">${icon} ${s==='done'?'Done':s==='draft'?'Draft':'Not Started'}</span>`;
  }
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
