/**
 * render-generic.js  —  SecReCo · PTA Portal
 * Universal dynamic renderer for custom and blank format tables.
 * Accurately extracts column definitions from FormatTemplate.columns_json
 * or dynamically detects columns from CMI row keys.
 * Renders full consolidated table (Accomplishments + Pending), documentation thumbnails,
 * summary totals for numeric columns, and status badges.
 */
'use strict';

function resolveGenericColumns(template, data) {
  let cols = [];
  let parsed = template?.columns_json;
  if (typeof parsed === 'string') {
    try { parsed = JSON.parse(parsed); } catch (e) { parsed = null; }
  }
  if (parsed) {
    if (Array.isArray(parsed)) {
      cols = parsed;
    } else if (typeof parsed === 'object') {
      if (Array.isArray(parsed.titles) && parsed.titles.length > 0) {
        if (Array.isArray(parsed.titles[0]?.columns) && parsed.titles[0].columns.length > 0) {
          cols = parsed.titles[0].columns;
        }
      }
      if ((!cols || !cols.length) && Array.isArray(parsed.columns) && parsed.columns.length > 0) {
        cols = parsed.columns;
      }
    }
  }

  // Format cols into normalized { key, label, type }
  let normalizedCols = [];
  if (Array.isArray(cols) && cols.length > 0) {
    normalizedCols = cols.map((c, i) => {
      if (typeof c === 'string') {
        return { key: c, label: c, type: 'text' };
      }
      const key = c.key || c.name || `col_${i + 1}`;
      const label = c.label || c.name || c.title || key;
      const type = c.type || 'text';
      return { key, label, type, options: c.options || [] };
    });
  }

  // If no columns defined in template, auto-detect from non-empty row keys in data
  if (!normalizedCols.length && Array.isArray(data)) {
    const keySet = new Set();
    data.forEach(cmi => {
      (cmi.rows || []).forEach(r => {
        if (r && typeof r === 'object') {
          Object.keys(r).forEach(k => {
            if (!['_cat', '_group', '_fixed', '_label', 'id', 'user_id', 'created_at', 'updated_at', 'status'].includes(k)) {
              keySet.add(k);
            }
          });
        }
      });
    });
    if (keySet.size > 0) {
      normalizedCols = Array.from(keySet).map(k => ({
        key: k,
        label: k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' '),
        type: 'text'
      }));
    }
  }

  // Default fallback if table has no columns configured
  if (!normalizedCols.length) {
    normalizedCols = [
      { key: 'title', label: 'Title / Description', type: 'text' },
      { key: 'agency', label: 'Agency / Institution', type: 'text' },
      { key: 'remarks', label: 'Remarks', type: 'text' }
    ];
  }

  return normalizedCols;
}

function renderGeneric(data, template) {
  if (!data || !data.length) {
    return '<div class="loading-state">No CMI data found for this year.</div>';
  }

  const columns = resolveGenericColumns(template, data);
  const hasCategories = (data || []).some(cmi => (cmi.rows || []).some(r => r && r._cat));

  let submitted = '';
  let pending   = '';
  let docBlocks = '';
  let overallIdx = 0;

  // Numeric totals map
  const numericTotals = {};
  let hasNumericCol = false;
  columns.forEach(c => {
    if (c.type === 'number' || c.type === 'amount') {
      numericTotals[c.key] = 0;
      hasNumericCol = true;
    }
  });

  data.forEach(cmi => {
    const s = cmi.table_status;
    const badge = STATUS_BADGE[s] ?? STATUS_BADGE['not-started'];
    const docs = cmi.docs || [];
    if (docs.length) {
      docBlocks += renderCMIDocsBlock(cmi.institution, docs);
    }
    const docsInline = renderDocsSection(docs);

    const validRows = (cmi.rows || []).filter(r => {
      if (!r || typeof r !== 'object') return false;
      return Object.entries(r).some(([k, v]) => k !== '_cat' && String(v || '').trim() !== '');
    });

    if ((s === 'done' || s === 'submitted' || s === 'accepted') && validRows.length > 0) {
      validRows.forEach((r, rIdx) => {
        overallIdx++;
        let cells = '';
        columns.forEach(c => {
          let val = r[c.key] ?? r[c.label] ?? r[c.key.toLowerCase()] ?? r[c.key.toUpperCase()] ?? '';
          if (c.type === 'number' || c.type === 'amount') {
            const num = parseFloat(String(val).replace(/,/g, ''));
            if (!isNaN(num)) {
              numericTotals[c.key] = (numericTotals[c.key] || 0) + num;
              val = (c.type === 'amount')
                ? num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : num.toLocaleString('en-US');
            }
            cells += `<td style="text-align:right">${esc(val || '—')}</td>`;
          } else if (c.type === 'date') {
            cells += `<td style="text-align:center">${esc(val || '—')}</td>`;
          } else {
            cells += `<td>${esc(val || '—')}</td>`;
          }
        });

        const instCell = (rIdx === 0)
          ? `<td rowspan="${validRows.length}" style="vertical-align:top;font-weight:600;border-right:1px solid #e5e7eb;">
               <div style="font-weight:700;color:#1b4d2e;margin-bottom:4px">${esc(cmi.institution)}</div>
               ${docsInline ? `<div style="margin-top:4px">${docsInline}</div>` : ''}
             </td>`
          : '';

        const catCell = hasCategories ? `<td><span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;background:#f3f4f6;color:#374151">${esc(r._cat || '—')}</span></td>` : '';

        submitted += `
          <tr>
            <td style="text-align:center;color:#6b7280;font-size:12px">${overallIdx}</td>
            ${instCell}
            ${catCell}
            ${cells}
          </tr>`;
      });
    } else {
      pending += `
        <tr>
          <td><strong>${esc(cmi.institution)}</strong></td>
          <td>${badge}</td>
          <td style="font-size:12px;color:#9ca3af">${esc(cmi.updated_at || '—')}</td>
        </tr>`;
    }
  });

  // Calculate colspans
  const totalCols = 2 + (hasCategories ? 1 : 0) + columns.length;

  // Build footer totals if numeric columns exist
  let tfootHtml = '';
  if (hasNumericCol && overallIdx > 0) {
    let tfootCells = '';
    columns.forEach(c => {
      if (c.type === 'number' || c.type === 'amount') {
        const tot = numericTotals[c.key] || 0;
        const formatted = (c.type === 'amount')
          ? tot.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
          : tot.toLocaleString('en-US');
        tfootCells += `<td style="text-align:right;font-weight:700;color:#1b4d2e">${formatted}</td>`;
      } else {
        tfootCells += `<td></td>`;
      }
    });
    const labelColspan = 2 + (hasCategories ? 1 : 0);
    tfootHtml = `
      <tfoot>
        <tr style="font-weight:700;background:var(--bg-soft,#f7faf7);border-top:2px solid #cbd5e1">
          <td colspan="${labelColspan}" style="text-align:right;padding-right:12px;color:#1b4d2e">TOTAL</td>
          ${tfootCells}
        </tr>
      </tfoot>`;
  }

  const ths = columns.map(c => {
    const align = (c.type === 'number' || c.type === 'amount') ? 'text-align:right' : (c.type === 'date' ? 'text-align:center' : 'text-align:left');
    return `<th style="${align};font-size:12px">${esc(c.label)}</th>`;
  }).join('');

  return `
    <table class="rpt-table merged" style="width:100%">
      <thead>
        <tr>
          <th style="width:40px;text-align:center">#</th>
          <th style="width:200px">Institution</th>
          ${hasCategories ? '<th style="width:140px">Category</th>' : ''}
          ${ths}
        </tr>
      </thead>
      <tbody>
        ${submitted || `<tr><td colspan="${totalCols}" style="text-align:center;padding:36px 20px;color:#9ca3af;font-style:italic">No entries submitted yet for this table.</td></tr>`}
      </tbody>
      ${tfootHtml}
    </table>

    ${pending ? `
      <div style="margin-top:28px">
        <h4 style="font-size:13px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">
          Pending / Not Yet Submitted
        </h4>
        <table class="rpt-table" style="width:100%">
          <thead>
            <tr>
              <th>Institution</th>
              <th style="width:140px">Status</th>
              <th style="width:180px">Last Activity</th>
            </tr>
          </thead>
          <tbody>${pending}</tbody>
        </table>
      </div>` : ''}

    ${docBlocks ? `
      <div class="doc-card" style="margin-top:28px">
        <div class="doc-card-title">Attached Documentation</div>
        ${docBlocks}
      </div>` : ''}
  `;
}
