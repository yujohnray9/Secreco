/**
 * render-t4.js — Report renderer for Table 4: Resources Shared
 * Columns: Donor/Source | Activity/Project | Amount Shared | Remarks
 * Shows per-institution totals + grand total of Amount Shared.
 */

(function () {
  'use strict';

  window.renderT4 = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Summary table ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th style="width:200px">Institution</th>
            <th style="width:80px">No. of Entries</th>
            <th style="width:140px">Total Amount Shared</th>
            <th style="width:110px">Last Updated</th>
          </tr>
        </thead>
        <tbody>`;

    let grandTotal = 0;

    allRows.forEach(inst => {
      const rows  = (inst.rows || []).filter(r => r.donor?.trim() || r.activity?.trim());
      const total = rows.reduce((sum, r) => sum + (parseFloat(String(r.amount).replace(/[^0-9.]/g,'')) || 0), 0);
      grandTotal += total;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center">${rows.length || '—'}</td>
          <td style="text-align:right;font-weight:700;color:var(--green)">${total > 0 ? fmtAmt(total) : '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td colspan="2" style="text-align:right;padding-right:12px">GRAND TOTAL</td>
            <td style="text-align:right;color:var(--green)">${grandTotal > 0 ? fmtAmt(grandTotal) : '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.donor?.trim() || r.activity?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} entry(ies)</summary>
        <table class="rpt-table merged" style="margin-top:8px">
          <thead><tr>
            <th style="width:36px">#</th>
            <th style="width:180px">Donor / Source</th>
            <th>Activity / Project</th>
            <th style="width:140px">Amount Shared</th>
            <th style="width:160px">Remarks</th>
          </tr></thead>
          <tbody>`;
      let instTotal = 0;
      rows.forEach((r, i) => {
        const amt = parseFloat(String(r.amount).replace(/[^0-9.]/g,'')) || 0;
        instTotal += amt;
        html += `<tr>
          <td style="text-align:center">${i + 1}.</td>
          <td>${esc(r.donor || '—')}</td>
          <td>${esc(r.activity || '—')}</td>
          <td style="text-align:right">${esc(r.amount || '—')}</td>
          <td>${esc(r.remarks || '')}</td>
        </tr>`;
      });
      html += `<tr style="font-weight:700;background:var(--bg-soft)">
        <td colspan="3" style="text-align:right;padding-right:12px">Total</td>
        <td style="text-align:right;color:var(--green)">${instTotal > 0 ? fmtAmt(instTotal) : '—'}</td>
        <td></td>
      </tr>`;
      html += '</tbody></table></details>';
    });
    html += '</div>';

    container.innerHTML = html;
  };

  function fmtAmt(n) {
    return '₱ ' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function statusBadge(s) {
    const map = {
      accepted:      ['✅', '#ecfdf5', '#0d9488', 'Accepted'],
      submitted:     ['📤', '#e6f4ea', 'var(--green,#1e7e34)', 'Submitted'],
      done:          ['✅', '#e6f4ea', 'var(--green,#1e7e34)', 'Done'],
      returned:      ['↩️', '#f5f3ff', '#7c3aed', 'Returned'],
      draft:         ['✏️', '#fff4e5', '#b06b00', 'Draft'],
      'not-started': ['—',  '#f1f1f1', '#777', 'Not Started'],
    };
    const [icon, bg, fg, label] = map[s] || map['not-started'];
    return `<span style="background:${bg};color:${fg};padding:1px 7px;border-radius:8px;font-size:11px;font-weight:600">${icon} ${label}</span>`;
  }
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
