/**
 * render-t2b.js — Report renderer for Table 2b: Number of Participants in the RSRDH
 * Grouped by category (GO, NGO, Private Sector, LGU) with subtotals + grand total.
 * Used by reports-view-table.js via TABLE_DEFS[T2b].renderer
 */

(function () {
  'use strict';

  const CATEGORIES = [
    { key: 'go',      label: 'GO' },
    { key: 'ngo',     label: 'NGO' },
    { key: 'private', label: 'Private Sector' },
    { key: 'lgu',     label: 'LGU' },
  ];

  /**
   * renderT2b(container, allRows)
   * allRows: array of { institution, status, rows: [...], updated_at }
   */
  window.renderT2b = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Grand summary table: Institution × Category → participant counts ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th rowspan="2" style="width:200px">Institution</th>
            <th rowspan="2" style="width:80px">Status</th>
            ${CATEGORIES.map(c => `<th>${esc(c.label)}</th>`).join('')}
            <th rowspan="2" style="width:90px">Grand Total</th>
            <th rowspan="2" style="width:110px">Last Updated</th>
          </tr>
          <tr>
            ${CATEGORIES.map(() => '<th style="font-size:10px;color:var(--text-muted)">Participants</th>').join('')}
          </tr>
        </thead>
        <tbody>`;

    let colTotals = Object.fromEntries(CATEGORIES.map(c => [c.key, 0]));
    let grandAll  = 0;

    allRows.forEach(inst => {
      const rows  = inst.rows || [];
      const bycat = Object.fromEntries(CATEGORIES.map(c => [c.key, 0]));
      rows.forEach(r => {
        const key = r.category || '';
        if (bycat[key] !== undefined) bycat[key] += parseInt(r.count) || 0;
      });
      const instTotal = Object.values(bycat).reduce((a, b) => a + b, 0);
      grandAll += instTotal;
      CATEGORIES.forEach(c => { colTotals[c.key] += bycat[c.key]; });

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td>${statusBadge(inst.status)}</td>
          ${CATEGORIES.map(c => `<td style="text-align:center">${bycat[c.key] || '—'}</td>`).join('')}
          <td style="text-align:center;font-weight:700;color:var(--green)">${instTotal || '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    // Totals footer
    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td colspan="2" style="text-align:right;padding-right:12px">TOTAL</td>
            ${CATEGORIES.map(c => `<td style="text-align:center;color:var(--green)">${colTotals[c.key] || '—'}</td>`).join('')}
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down: per-institution detail ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      if (!inst.rows || inst.rows.length === 0) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — participant detail</summary>
        <table class="rpt-table merged" style="margin-top:8px">
          <thead><tr>
            <th>Category</th><th>Agency / Association</th>
            <th style="width:120px">No. of Participants</th><th>Remarks</th>
          </tr></thead>
          <tbody>`;

      const byCat = Object.fromEntries(CATEGORIES.map(c => [c.key, []]));
      inst.rows.forEach(r => {
        const k = r.category || '';
        if (byCat[k]) byCat[k].push(r);
      });

      CATEGORIES.forEach(cat => {
        const catRows = byCat[cat.key];
        if (!catRows.length) return;
        let sub = 0;
        catRows.forEach((r, i) => {
          sub += parseInt(r.count) || 0;
          html += `<tr>
            ${i === 0 ? `<td rowspan="${catRows.length}" style="font-weight:700;vertical-align:middle">${esc(cat.label)}</td>` : ''}
            <td>${esc(r.agency || '—')}</td>
            <td style="text-align:center">${esc(r.count || '—')}</td>
            <td>${esc(r.remarks || '')}</td>
          </tr>`;
        });
        html += `<tr style="background:var(--bg-soft);font-weight:600">
          <td colspan="2" style="text-align:right;padding-right:12px">${esc(cat.label)} Subtotal</td>
          <td style="text-align:center;color:var(--green)">${sub || '—'}</td><td></td>
        </tr>`;
      });

      html += '</tbody></table></details>';
    });
    html += '</div>';

    container.innerHTML = html;
  };

  /* ── helpers ── */
  function statusBadge(s) {
    const map = {
      done:          ['✅', '#e6f4ea', 'var(--green,#1e7e34)'],
      draft:         ['✏️', '#fff4e5', '#b06b00'],
      'not-started': ['—',  '#f1f1f1', '#777'],
    };
    const [icon, bg, fg] = map[s] || map['not-started'];
    return `<span style="background:${bg};color:${fg};padding:1px 7px;border-radius:8px;font-size:11px;font-weight:600">${icon} ${s === 'done' ? 'Done' : s === 'draft' ? 'Draft' : 'Not Started'}</span>`;
  }
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

})();
