/**
 * render-t7b.js — Report renderer for Table 7b: Information Systems Developed/Enhanced and Maintained
 * Identical structure to T7a but for Information Systems.
 */

(function () {
  'use strict';

  const CATEGORIES = [
    { key: 'developed',  label: 'Developed / Enhanced' },
    { key: 'maintained', label: 'Maintained' },
  ];

  window.renderT7b = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th style="width:200px">Institution</th>
            <th style="width:110px">Developed / Enhanced</th>
            <th style="width:110px">Maintained</th>
            <th style="width:80px">Total</th>
            <th style="width:110px">Last Updated</th>
          </tr>
        </thead>
        <tbody>`;

    const colTotals = { developed: 0, maintained: 0 };
    let grandAll = 0;

    allRows.forEach(inst => {
      const rows  = (inst.rows || []).filter(r => r.type?.trim());
      const byCat = { developed: 0, maintained: 0 };
      rows.forEach(r => { if (byCat[r.category] !== undefined) byCat[r.category]++; });
      const total = byCat.developed + byCat.maintained;
      grandAll += total;
      colTotals.developed  += byCat.developed;
      colTotals.maintained += byCat.maintained;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center">${byCat.developed || '—'}</td>
          <td style="text-align:center">${byCat.maintained || '—'}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${colTotals.developed || '—'}</td>
            <td style="text-align:center;color:var(--green)">${colTotals.maintained || '—'}</td>
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.type?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} information system(s)</summary>`;

      CATEGORIES.forEach(cat => {
        const catRows = rows.filter(r => r.category === cat.key);
        if (!catRows.length) return;
        html += `<div style="font-weight:700;margin:10px 0 4px;color:var(--text)">${esc(cat.label)}</div>
          <table class="rpt-table merged" style="margin-bottom:6px">
            <thead><tr>
              <th style="width:36px">#</th>
              <th>Type of Information System</th>
              <th style="width:140px">Date Created</th>
              <th>Purpose / Use</th>
            </tr></thead>
            <tbody>`;
        catRows.forEach((r, i) => {
          html += `<tr>
            <td style="text-align:center">${i + 1}.</td>
            <td>${esc(r.type || '—')}</td>
            <td>${esc(r.date || '—')}</td>
            <td>${esc(r.purpose || '—')}</td>
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
