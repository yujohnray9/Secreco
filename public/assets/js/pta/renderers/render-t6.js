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
        { key: 'developed_regional',      label: 'Regional' },
        { key: 'developed_national',      label: 'National' },
        { key: 'developed_international', label: 'International' },
      ],
    },
    {
      key: 'maintained', label: 'Maintained / Sustained',
      scopes: [
        { key: 'maintained_local',         label: 'Local' },
        { key: 'maintained_regional',      label: 'Regional' },
        { key: 'maintained_national',      label: 'National' },
        { key: 'maintained_international', label: 'International' },
      ],
    },
  ];

  window.renderT6 = function (container, allRows) {
    const acceptedRows = (allRows || []).filter(inst => inst.table_status === 'accepted' && (inst.rows || []).length > 0);
    if (!acceptedRows || acceptedRows.length === 0) {
      container.innerHTML = '<div class="empty-state">No accepted reports for Table 6 yet. Reports must be accepted by PTA before appearing in consolidated reports.</div>';
      return;
    }

    let docRows = '';
    acceptedRows.forEach(inst => {
      if (inst.docs) docRows += renderCMIDocsBlock(inst.institution, inst.docs);
    });

    // ── Summary: count developed vs maintained per institution ──
    let html = `
      <table class="rpt-table merged">
        <thead>
          <tr>
            <th rowspan="2" style="width:200px">Institution</th>
            <th colspan="4">Developed / New</th>
            <th colspan="4">Maintained / Sustained</th>
            <th rowspan="2" style="width:80px">Total</th>
          </tr>
          <tr>
            <th style="font-size:10px">Local</th>
            <th style="font-size:10px">Regional</th>
            <th style="font-size:10px">National</th>
            <th style="font-size:10px">Intl</th>
            <th style="font-size:10px">Local</th>
            <th style="font-size:10px">Regional</th>
            <th style="font-size:10px">National</th>
            <th style="font-size:10px">Intl</th>
          </tr>
        </thead>
        <tbody>`;

    const scopeKeys = GROUPS.flatMap(g => g.scopes.map(s => s.key));
    const colTotals = Object.fromEntries(scopeKeys.map(k => [k, 0]));
    let grandAll = 0;

    acceptedRows.forEach(inst => {
      const rows  = (inst.rows || []).filter(r => r.agency?.trim());
      const byCat = Object.fromEntries(scopeKeys.map(k => [k, 0]));
      rows.forEach(r => {
        let sc = r.scope;
        if (byCat[sc] !== undefined) {
          byCat[sc]++;
        } else if (sc) {
          const norm = String(sc).toLowerCase();
          const matched = scopeKeys.find(k => k === norm || k.endsWith('_' + norm));
          if (matched) byCat[matched]++;
        }
      });
      const total = Object.values(byCat).reduce((a, b) => a + b, 0);
      grandAll += total;
      scopeKeys.forEach(k => { colTotals[k] += byCat[k]; });

      html += `
        <tr>
          <td>${esc(inst.institution || '—')}</td>
          ${scopeKeys.map(k => `<td style="text-align:center">${byCat[k] || '—'}</td>`).join('')}
          <td style="text-align:center;font-weight:700;color:var(--green)">${total || '—'}</td>
        </tr>`;
    });

    html += `
        </tbody>
        <tfoot>
          <tr style="font-weight:700;background:var(--bg-soft)">
            <td style="text-align:right;padding-right:12px">TOTAL</td>
            ${scopeKeys.map(k => `<td style="text-align:center;color:var(--green)">${colTotals[k] || '—'}</td>`).join('')}
            <td style="text-align:center;color:var(--green)">${grandAll || '—'}</td>
          </tr>
        </tfoot>
      </table>`;

    // ── Consolidated Detail Tables by Category & Scope ──
    GROUPS.forEach(group => {
      let groupHasRows = false;
      let groupHtml = `<div class="rpt-sec-title" style="font-size:13px;font-weight:700;color:#111827;margin:22px 0 8px 0;">${esc(group.label)}</div>`;

      group.scopes.forEach(scope => {
        const matchingRows = [];
        acceptedRows.forEach(inst => {
          (inst.rows || []).filter(r => (r.agency || '').trim() && (r.scope === scope.key || r.scope === scope.label.toLowerCase())).forEach(r => {
            matchingRows.push({
              agency: r.agency || '—',
              address: r.address || '—',
              year: r.year || '—',
              nature: r.nature || '—',
              institution: inst.institution
            });
          });
        });

        if (matchingRows.length > 0) {
          groupHasRows = true;
          groupHtml += `
            <div style="font-size:12px;font-weight:600;color:#374151;margin:10px 0 6px 0;">${esc(scope.label)}</div>
            <div class="tbl-wrap" style="overflow-x:auto;margin-bottom:14px;">
              <table class="merged" style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr style="background:#00875a;color:#ffffff;">
                    <th style="width:40px;text-align:center;padding:8px 10px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;">#</th>
                    <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Agency / Institution</th>
                    <th style="width:180px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Address</th>
                    <th style="width:80px;padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:center;">Year</th>
                    <th style="padding:8px 12px;font-size:11px;font-weight:700;color:#ffffff;border:1px solid #00734d;text-align:left;">Nature of Assistance / Linkages / Project</th>
                  </tr>
                </thead>
                <tbody>
                  ${matchingRows.map((r, i) => `
                    <tr>
                      <td style="text-align:center;padding:7px 10px;">${i + 1}</td>
                      <td style="padding:7px 12px;">${esc(r.agency)}</td>
                      <td style="padding:7px 12px;">${esc(r.address)}</td>
                      <td style="text-align:center;padding:7px 12px;">${esc(r.year)}</td>
                      <td style="padding:7px 12px;">${esc(r.nature)}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>`;
        }
      });

      if (groupHasRows) {
        html += groupHtml;
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
