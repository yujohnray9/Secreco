/**
 * render-t3.js — Report renderer for Table 3: Projects and Activities Monitored and Evaluated
 * Flat table: Project | Ongoing/Completed | Duration | Source of Fund
 */

(function () {
  'use strict';

  window.renderT3 = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Summary: count per institution ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th style="width:200px">Institution</th>
            <th style="width:80px">Ongoing</th>
            <th style="width:80px">Completed</th>
            <th style="width:80px">Total Projects</th>
          </tr>
        </thead>
        <tbody>`;

    let totOngoing = 0, totCompleted = 0;

    allRows.forEach(inst => {
      const rows     = (inst.rows || []).filter(r => r.project?.trim());
      const ongoing  = rows.filter(r => r.status === 'Ongoing').length;
      const completed= rows.filter(r => r.status === 'Completed').length;
      totOngoing   += ongoing;
      totCompleted += completed;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center">${ongoing || '—'}</td>
          <td style="text-align:center">${completed || '—'}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${rows.length || '—'}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${totOngoing || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totCompleted || '—'}</td>
            <td style="text-align:center;color:var(--green)">${(totOngoing + totCompleted) || '—'}</td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.project?.trim());
      if (!rows.length) return;
      html += `<details class="rpt-detail-block">
        <summary><strong>${esc(inst.institution || '—')}</strong> — ${rows.length} project(s)</summary>
        <table class="rpt-table merged" style="margin-top:8px">
          <thead><tr>
            <th style="width:36px">#</th>
            <th>Projects and Activities</th>
            <th style="width:130px">Status</th>
            <th style="width:140px">Duration</th>
            <th style="width:150px">Source of Fund</th>
          </tr></thead>
          <tbody>`;
      rows.forEach((r, i) => {
        const statusColor = r.status === 'Completed' ? 'var(--green)' : '#b06b00';
        html += `<tr>
          <td style="text-align:center">${i + 1}.</td>
          <td>${esc(r.project || '—')}</td>
          <td style="color:${statusColor};font-weight:600">${esc(r.status || '—')}</td>
          <td>${esc(r.duration || '—')}</td>
          <td>${esc(r.fund || '—')}</td>
        </tr>`;
      });
      html += '</tbody></table></details>';
    });
    html += '</div>';

    container.innerHTML = html;
  };

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
