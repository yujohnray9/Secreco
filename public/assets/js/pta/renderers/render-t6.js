/**
 * render-t6.js — Report renderer for Table 6: Linkages Forged and Maintained
 * Grouped: Developed/New → Local/National/International
 *          Maintained/Sustained → Local/National/International
 * Columns: Agency/Institution | Address | Year | Nature of Assistance
 */

(function () {
  'use strict';

  const GROUPS = [
    {
      key: 'developed', label: 'Developed / New',
      scopes: [
        { key: 'developed_local',         label: 'Local' },
        { key: 'developed_national',      label: 'National' },
        { key: 'developed_international', label: 'International' },
      ],
    },
    {
      key: 'maintained', label: 'Maintained / Sustained',
      scopes: [
        { key: 'maintained_local',         label: 'Local' },
        { key: 'maintained_national',      label: 'National' },
        { key: 'maintained_international', label: 'International' },
      ],
    },
  ];

  window.renderT6 = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Summary: count developed vs maintained per institution ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th rowspan="2" style="width:200px">Institution</th>
            <th colspan="3">Developed / New</th>
            <th colspan="3">Maintained / Sustained</th>
            <th rowspan="2" style="width:80px">Total</th>
            <th rowspan="2" style="width:110px">Last Updated</th>
          </tr>
          <tr>
            <th style="font-size:10px">Local</th>
            <th style="font-size:10px">National</th>
            <th style="font-size:10px">Intl</th>
            <th style="font-size:10px">Local</th>
            <th style="font-size:10px">National</th>
            <th style="font-size:10px">Intl</th>
          </tr>
        </thead>
        <tbody>`;

    const scopeKeys = GROUPS.flatMap(g => g.scopes.map(s => s.key));
    const colTotals = Object.fromEntries(scopeKeys.map(k => [k, 0]));
    let grandAll = 0;

    allRows.forEach(inst => {
      const rows  = (inst.rows || []).filter(r => r.agency?.trim());
      const byCat = Object.fromEntries(scopeKeys.map(k => [k, 0]));
      rows.forEach(r => { if (byCat[r.scope] !== undefined) byCat[r.scope]++; });
      const total = Object.values(byCat).reduce((a, b) => a + b, 0);
      grandAll += total;
      scopeKeys.forEach(k => { colTotals[k] += byCat[k]; });

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          ${scopeKeys.map(k => `<td style="text-align:center">${byCat[k] || '—'}</td>`).join('')}
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            ${scopeKeys.map(k => `<td style="text-align:center;color:var(--green)">${colTotals[k] || '—'}</td>`).join('')}
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.agency?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} linkage(s)</summary>`;

      GROUPS.forEach(group => {
        const groupRows = rows.filter(r => group.scopes.some(s => s.key === r.scope));
        if (!groupRows.length) return;
        html += `<div style="font-weight:700;margin:10px 0 4px;color:var(--text)">${esc(group.label)}</div>`;

        group.scopes.forEach(scope => {
          const scopeRows = groupRows.filter(r => r.scope === scope.key);
          if (!scopeRows.length) return;
          html += `<div style="font-style:italic;font-size:12px;margin:6px 0 2px 12px;color:var(--text-muted)">${esc(scope.label)}</div>
            <table class="rpt-table merged" style="margin-left:12px;margin-bottom:6px">
              <thead><tr>
                <th>Agency / Institution</th>
                <th style="width:150px">Address</th>
                <th style="width:70px">Year</th>
                <th>Nature of Assistance / Linkages / Project</th>
              </tr></thead>
              <tbody>`;
          scopeRows.forEach(r => {
            html += `<tr>
              <td>${esc(r.agency || '—')}</td>
              <td>${esc(r.address || '—')}</td>
              <td style="text-align:center">${esc(r.year || '—')}</td>
              <td>${esc(r.nature || '—')}</td>
            </tr>`;
          });
          html += '</tbody></table>';
        });
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
