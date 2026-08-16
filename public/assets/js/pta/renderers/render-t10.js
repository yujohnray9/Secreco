/**
 * render-t10.js — Report renderer for Table 10: Technology Transfer Programs/Projects
 * Grouped: Proposals Packaged | Projects Approved and Implemented
 * Columns: Program/Project Title | Agency Proponent | Regional Priority |
 *          Status (Approved/For Revision/Disapproved) | Duration | Budget | Funding Source
 */

(function () {
  'use strict';

  const CATEGORIES = [
    { key: 'packaged',    label: 'Proposals Packaged' },
    { key: 'implemented', label: 'Projects Approved and Implemented' },
  ];

  window.renderT10 = function (container, allRows) {
    if (!allRows || allRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No data submitted yet.</div>';
      return;
    }

    // ── Summary ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th rowspan="2" style="width:200px">Institution</th>
            <th colspan="2">Proposals Packaged</th>
            <th colspan="3">Projects Approved &amp; Implemented</th>
            <th rowspan="2" style="width:80px">Total</th>
            <th rowspan="2" style="width:110px">Last Updated</th>
          </tr>
          <tr>
            <th style="font-size:10px;width:80px">Total</th>
            <th style="font-size:10px;width:80px">Approved</th>
            <th style="font-size:10px;width:90px">For Revision</th>
            <th style="font-size:10px;width:90px">Disapproved</th>
            <th style="font-size:10px;width:80px">Total</th>
          </tr>
        </thead>
        <tbody>`;

    let totals = { packaged: 0, impl_approved: 0, impl_revision: 0, impl_disapproved: 0, impl_total: 0, grand: 0 };

    allRows.forEach(inst => {
      const rows    = (inst.rows || []).filter(r => r.title?.trim() || r.approved || r.for_revision || r.disapproved);
      const packed  = rows.filter(r => r.category === 'packaged');
      const impl    = rows.filter(r => r.category === 'implemented');
      const iApproved   = impl.filter(r => r.approved).length;
      const iRevision   = impl.filter(r => r.for_revision).length;
      const iDisapproved= impl.filter(r => r.disapproved).length;
      const total   = packed.length + impl.length;

      totals.packaged       += packed.length;
      totals.impl_approved  += iApproved;
      totals.impl_revision  += iRevision;
      totals.impl_disapproved += iDisapproved;
      totals.impl_total     += impl.length;
      totals.grand          += total;

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          <td style="text-align:center">${packed.length || '—'}</td>
          <td style="text-align:center">${iApproved || '—'}</td>
          <td style="text-align:center">${iRevision || '—'}</td>
          <td style="text-align:center">${iDisapproved || '—'}</td>
          <td style="text-align:center">${impl.length || '—'}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
          <td style="font-size:11px;color:var(--text-muted)">${esc(inst.updated_at || '—')}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${totals.packaged || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totals.impl_approved || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totals.impl_revision || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totals.impl_disapproved || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totals.impl_total || '—'}</td>
            <td style="text-align:center;color:var(--green)">${totals.grand || '—'}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;

    // ── Drill-down ──
    html += '<div class="rpt-drilldown">';
    allRows.forEach(inst => {
      const rows = (inst.rows || []).filter(r => r.title?.trim() || r.approved || r.for_revision || r.disapproved);
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
              <th style="width:150px">Agency Proponent</th>
              <th style="width:150px">Regional Priority</th>
              <th style="width:100px">Status</th>
              <th style="width:120px">Approved Duration</th>
              <th style="width:110px">Approved Budget</th>
              <th style="width:130px">Funding Source</th>
            </tr></thead>
            <tbody>`;
        catRows.forEach((r, i) => {
          const statusStr = r.approved ? 'Approved' : r.for_revision ? '✏️ For Revision' : r.disapproved ? '❌ Disapproved' : '—';
          const statusColor = r.approved ? 'var(--green)' : r.for_revision ? '#b06b00' : r.disapproved ? '#c0392b' : '#777';
          html += `<tr>
            <td style="text-align:center">${i + 1}.</td>
            <td>${esc(r.title || '—')}</td>
            <td>${esc(r.agency || '—')}</td>
            <td>${esc(r.priority || '—')}</td>
            <td style="color:${statusColor};font-weight:600;font-size:11px">${statusStr}</td>
            <td>${esc(r.duration || '—')}</td>
            <td>${esc(r.budget || '—')}</td>
            <td>${esc(r.fund || '—')}</td>
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
