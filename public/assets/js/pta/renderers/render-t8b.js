/**
 * render-t8b.js — Report renderer for Table 8b: Collaborative R&D Programs/Projects
 * Flat table with auto-summed Budget column.
 * Columns: Program Title | Project Title | Implementing Agency | Duration | Budget | Source of Fund | Role of Consortium
 */

(function () {
  'use strict';

  window.renderT8b = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Summary ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th style="width:200px">Institution</th>
            <th style="width:80px">Status</th>
            <th style="width:80px">No. of Projects</th>
            <th style="width:150px">Total Budget</th>
            <th style="width:110px">Last Updated</th>
          </tr>
        </thead>
        <tbody>`;

    let grandBudget = 0, grandProjects = 0;

    allRows.forEach(inst => {
      const rows   = (inst.rows || []).filter(r => r.program?.trim() || r.project?.trim());
      const budget = rows.reduce((sum, r) => sum + (parseFloat(r.budget) || 0), 0);
      grandBudget   += budget;
      grandProjects += rows.length;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td>${statusBadge(inst.status)}</td>
          <td style="text-align:center">${rows.length || '—'}</td>
          <td style="text-align:right;font-weight:700;color:var(--green)">${budget > 0 ? fmtAmt(budget) : '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td colspan="2" style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${grandProjects || '—'}</td>
            <td style="text-align:right;color:var(--green)">${grandBudget > 0 ? fmtAmt(grandBudget) : '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.program?.trim() || r.project?.trim());
      if (!rows.length) return;
      let instBudget = rows.reduce((sum, r) => sum + (parseFloat(r.budget) || 0), 0);
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} project(s), budget: ${fmtAmt(instBudget)}</summary>
        <table class="rpt-table merged" style="margin-top:8px">
          <thead><tr>
            <th style="width:36px">#</th>
            <th>Program Title</th>
            <th>Project Title</th>
            <th style="width:160px">Implementing Agency</th>
            <th style="width:120px">Duration</th>
            <th style="width:120px">Budget</th>
            <th style="width:130px">Source of Fund</th>
            <th style="width:150px">Role of Consortium</th>
          </tr></thead>
          <tbody>`;
      rows.forEach((r, i) => {
        html += `<tr>
          <td style="text-align:center">${i + 1}.</td>
          <td>${esc(r.program || '—')}</td>
          <td>${esc(r.project || '—')}</td>
          <td>${esc(r.agency || '—')}</td>
          <td>${esc(r.duration || '—')}</td>
          <td style="text-align:right">${r.budget ? fmtAmt(parseFloat(r.budget)) : '—'}</td>
          <td>${esc(r.source || '—')}</td>
          <td>${esc(r.role || '—')}</td>
        </tr>`;
      });
      html += `<tr style="font-weight:700;background:var(--bg-soft)">
        <td colspan="5" style="text-align:right;padding-right:12px">TOTAL</td>
        <td style="text-align:right;color:var(--green)">${fmtAmt(instBudget)}</td>
        <td colspan="2"></td>
      </tr>`;
      html += '</tbody></table></details>';
    });
    html += '</div>';

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
