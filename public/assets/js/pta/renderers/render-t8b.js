/**
 * render-t8b.js — Report renderer for Table 8b: Collaborative R&D Programs/Projects
 * Flat table with auto-summed Budget column.
 * Columns: Program Title | Project Title | Implementing Agency | Duration | Budget | Source of Fund | Role of Consortium
 */

(function () {
  'use strict';

  window.renderT8b = function (container, allRows) {
    const acceptedRows = (allRows || []).filter(inst => inst.table_status === 'accepted' && (inst.rows || []).length > 0);
    if (!acceptedRows || acceptedRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No accepted reports for Table 8b yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
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
            <th style="width:80px">No. of Projects</th>
            <th style="width:150px">Total Budget</th>
          </tr>
        </thead>
        <tbody>`;

    let grandBudget = 0, grandProjects = 0;

    acceptedRows.forEach(inst => {
      const rows   = (inst.rows || []).filter(r => r.program?.trim() || r.project?.trim());
      const budget = rows.reduce((sum, r) => sum + (parseFloat(r.budget) || 0), 0);
      grandBudget   += budget;
      grandProjects += rows.length;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center">${rows.length || '—'}</td>
          <td style="text-align:right;font-weight:700;color:var(--green)">${budget > 0 ? fmtAmt(budget) : '—'}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${grandProjects || '—'}</td>
            <td style="text-align:right;color:var(--green)">${grandBudget > 0 ? fmtAmt(grandBudget) : '—'}</td>
          </tr>
        </tfoot>
      </table>`;

    // ── Consolidated Detail Table ──
    const allDetailRows = [];
    acceptedRows.forEach(inst => {
      (inst.rows || []).filter(r => (r.program || '').trim() || (r.project || '').trim()).forEach(r => {
        allDetailRows.push({
          program: r.program || '—',
          project: r.project || '—',
          agency: r.agency || inst.institution || '—',
          duration: r.duration || '—',
          budget: parseFloat(r.budget) || 0,
          source: r.source || '—',
          role: r.role || '—',
          institution: inst.institution
        });
      });
    });

    if (allDetailRows.length > 0) {
      const sumBudget = allDetailRows.reduce((acc, r) => acc + r.budget, 0);
      html += `
        <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">Collaborative R&amp;D Programs / Projects</div>
        <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
          <table class="merged" style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="background:#00875a;color:#ffffff;">
                <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Program Title</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Project Title</th>
                <th style="width:160px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Implementing Agency</th>
                <th style="width:110px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Duration</th>
                <th style="width:120px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:right;">Budget</th>
                <th style="width:130px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Source of Fund</th>
                <th style="width:140px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Role of Consortium</th>
              </tr>
            </thead>
            <tbody>
              ${allDetailRows.map((r, i) => `
                <tr>
                  <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                  <td style="padding:7px 12px;">${esc(r.program)}</td>
                  <td style="padding:7px 12px;">${esc(r.project)}</td>
                  <td style="padding:7px 12px;">${esc(r.agency)}</td>
                  <td style="text-align:center;padding:7px 12px;">${esc(r.duration)}</td>
                  <td style="text-align:right;padding:7px 12px;">${r.budget > 0 ? fmtAmt(r.budget) : '—'}</td>
                  <td style="padding:7px 12px;">${esc(r.source)}</td>
                  <td style="padding:7px 12px;">${esc(r.role)}</td>
                </tr>
              `).join('')}
            </tbody>
            <tfoot>
              <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb;">
                <td colspan="5" style="text-align:right;padding:8px 12px;font-weight:800;">TOTAL</td>
                <td style="text-align:right;padding:8px 12px;font-weight:800;">${sumBudget > 0 ? fmtAmt(sumBudget) : '—'}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
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

  function fmtAmt(n) {
    return '₱ ' + (n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function statusBadge(s) {
    const map = { done:['✅','#e6f4ea','var(--green,#1e7e34)'], draft:['✏️','#fff4e5','#b06b00'], 'not-started':['—','#f1f1f1','#777'] };
    const [icon,bg,fg] = map[s] || map['not-started'];
    return `<span style="background:${bg};color:${fg};padding:1px 7px;border-radius:8px;font-size:11px;font-weight:600">${icon} ${s==='done'?'Done':s==='draft'?'Draft':'Not Started'}</span>`;
  }
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
