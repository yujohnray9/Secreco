/**
 * render-t9.js — Report renderer for Table 9: Technologies/Information Generated from R&D
 * Flat table.
 * Columns: Title/Brief Description | Project/Program Source | Agency | Researcher(s) | Potential Impact
 */

(function () {
  'use strict';

  window.renderT9 = function (container, allRows) {
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
            <th style="width:120px">No. of Technologies</th>
          </tr>
        </thead>
        <tbody>`;

    let grandTotal = 0;

    allRows.forEach(inst => {
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

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.title?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} technology(ies)</summary>
        <table class="rpt-table merged" style="margin-top:8px">
          <thead><tr>
            <th style="width:36px">#</th>
            <th>Title / Brief Description</th>
            <th style="width:160px">Project / Program Source</th>
            <th style="width:140px">Agency</th>
            <th style="width:150px">Researcher(s)</th>
            <th>Potential Impact or Contribution</th>
          </tr></thead>
          <tbody>`;
      rows.forEach((r, i) => {
        html += `<tr>
          <td style="text-align:center">${i + 1}.</td>
          <td>${esc(r.title || '—')}</td>
          <td>${esc(r.source || '—')}</td>
          <td>${esc(r.agency || '—')}</td>
          <td>${esc(r.researcher || '—')}</td>
          <td>${esc(r.impact || '—')}</td>
        </tr>`;
      });
      html += '</tbody></table></details>';
    });
    html += '</div>';

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
