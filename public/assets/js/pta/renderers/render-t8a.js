/**
 * render-t8a.js — Report renderer for Table 8a: R&D Programs/Projects Packaged, Approved, Implemented
 * Grouped: Proposals Packaged | Projects Approved and Implemented
 * Columns: Program/Project Title | Researcher | Implementing Agency | Duration | Source of Funds | Commodity
 */

(function () {
  'use strict';

  const CATEGORIES = [
    { key: 'packaged',    label: 'Proposals Packaged' },
    { key: 'implemented', label: 'Projects Approved and Implemented' },
  ];

  window.renderT8a = function (container, allRows) {
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
            <th style="width:110px">Proposals Packaged</th>
            <th style="width:130px">Approved &amp; Implemented</th>
            <th style="width:80px">Total</th>
            <th style="width:110px">Last Updated</th>
          </tr>
        </thead>
        <tbody>`;

    const colTotals = { packaged: 0, implemented: 0 };
    let grandAll = 0;

    allRows.forEach(inst => {
      const rows  = (inst.rows || []).filter(r => r.title?.trim());
      const byCat = { packaged: 0, implemented: 0 };
      rows.forEach(r => { if (byCat[r.category] !== undefined) byCat[r.category]++; });
      const total = byCat.packaged + byCat.implemented;
      grandAll += total;
      colTotals.packaged    += byCat.packaged;
      colTotals.implemented += byCat.implemented;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td>${statusBadge(inst.status)}</td>
          <td style="text-align:center">${byCat.packaged || '—'}</td>
          <td style="text-align:center">${byCat.implemented || '—'}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td colspan="2" style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${colTotals.packaged || '—'}</td>
            <td style="text-align:center;color:var(--green)">${colTotals.implemented || '—'}</td>
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.title?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} project(s)</summary>`;

      CATEGORIES.forEach(cat => {
        const catRows = rows.filter(r => r.category === cat.key);
        if (!catRows.length) return;
        html += `<div style="font-weight:700;margin:10px 0 4px;color:var(--text)">${esc(cat.label)}</div>
          <table class="rpt-table merged" style="margin-bottom:6px">
            <thead><tr>
              <th style="width:36px">#</th>
              <th>Program / Project Title</th>
              <th style="width:140px">Researcher</th>
              <th style="width:150px">Implementing Agency</th>
              <th style="width:130px">Duration</th>
              <th style="width:130px">Source of Funds</th>
              <th style="width:150px">Priority Commodity</th>
            </tr></thead>
            <tbody>`;
        catRows.forEach((r, i) => {
          html += `<tr>
            <td style="text-align:center">${i + 1}.</td>
            <td>${esc(r.title || '—')}</td>
            <td>${esc(r.researcher || '—')}</td>
            <td>${esc(r.agency || '—')}</td>
            <td>${esc(r.duration || '—')}</td>
            <td>${esc(r.funds || '—')}</td>
            <td>${esc(r.commodity || '—')}</td>
          </tr>`;
        });
        html += '</tbody></table>';
      });

      html += '</details>';
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
