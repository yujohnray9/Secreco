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
    const acceptedRows = (allRows || []).filter(inst => inst.table_status === 'accepted' && (inst.rows || []).length > 0);
    if (!acceptedRows || acceptedRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No accepted reports for Table 8a yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
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
            <th style="width:110px">Proposals Packaged</th>
            <th style="width:130px">Approved &amp; Implemented</th>
            <th style="width:80px">Total</th>
          </tr>
        </thead>
        <tbody>`;

    const colTotals = { packaged: 0, implemented: 0 };
    let grandAll = 0;

    acceptedRows.forEach(inst => {
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
          <td style="text-align:center">${byCat.packaged || '—'}</td>
          <td style="text-align:center">${byCat.implemented || '—'}</td>
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            <td style="text-align:center;color:var(--green)">${colTotals.packaged || '—'}</td>
            <td style="text-align:center;color:var(--green)">${colTotals.implemented || '—'}</td>
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
          </tr>
        </tfoot>
      </table>`;

    // ── Consolidated Detail Tables by Category ──
    CATEGORIES.forEach(cat => {
      const catRows = [];
      acceptedRows.forEach(inst => {
        (inst.rows || []).filter(r => (r.title || '').trim() && r.category === cat.key).forEach(r => {
          catRows.push({
            title: r.title || '—',
            researcher: r.researcher || '—',
            agency: r.agency || inst.institution || '—',
            duration: r.duration || '—',
            funds: r.funds || '—',
            commodity: r.commodity || '—',
            institution: inst.institution
          });
        });
      });

      if (catRows.length > 0) {
        html += `
          <div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">${esc(cat.label)}</div>
          <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
            <table class="merged" style="width:100%;border-collapse:collapse;">
              <thead>
                <tr style="background:#00875a;color:#ffffff;">
                  <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
                  <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Program / Project Title</th>
                  <th style="width:150px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Researcher</th>
                  <th style="width:160px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Implementing Agency</th>
                  <th style="width:110px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Duration</th>
                  <th style="width:120px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Source of Funds</th>
                  <th style="width:140px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Priority Commodity</th>
                </tr>
              </thead>
              <tbody>
                ${catRows.map((r, i) => `
                  <tr>
                    <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                    <td style="padding:7px 12px;">${esc(r.title)}</td>
                    <td style="padding:7px 12px;">${esc(r.researcher)}</td>
                    <td style="padding:7px 12px;">${esc(r.agency)}</td>
                    <td style="text-align:center;padding:7px 12px;">${esc(r.duration)}</td>
                    <td style="padding:7px 12px;">${esc(r.funds)}</td>
                    <td style="padding:7px 12px;">${esc(r.commodity)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>`;
      }
    });

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

  function statusBadge(s) {
    const map = { done:['✅','#e6f4ea','var(--green,#1e7e34)'], draft:['✏️','#fff4e5','#b06b00'], 'not-started':['—','#f1f1f1','#777'] };
    const [icon,bg,fg] = map[s] || map['not-started'];
    return `<span style="background:${bg};color:${fg};padding:1px 7px;border-radius:8px;font-size:11px;font-weight:600">${icon} ${s==='done'?'Done':s==='draft'?'Draft':'Not Started'}</span>`;
  }
  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
