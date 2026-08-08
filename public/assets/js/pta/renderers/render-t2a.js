/**
 * render-t2a.js  —  SecReCo · PTA Portal
 * Renderer for Table 2a: Summary of Regional Symposium on R&D Highlights (RSRDH)
 *
 * Meta:    Date | Venue (per CMI)
 * Columns: # | Title | Implementing Agency(ies) | Researcher(s) | Major Recommendations | Winners
 * Groups:  Research Category | Development Category
 * + Documentation section below the table
 */
'use strict';

function renderT2a(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  const CAT_LABELS = {
    research:    'Research Category',
    development: 'Development Category',
  };

  let allRows = '';
  let docRows = '';

  data.forEach(cmi => {
    const s = cmi.table_status;

    if (s === 'done' || s === 'submitted') {
      const meta  = cmi.meta || {};
      const date  = meta.date  || '—';
      const venue = meta.venue || '—';
      const rows  = cmi.rows  ?? [];

      // Group rows by category, preserving order
      const byCategory = {};
      rows.forEach(r => {
        const cat = r.category || 'research';
        if (!byCategory[cat]) byCategory[cat] = [];
        byCategory[cat].push(r);
      });

      // CMI info row (date + venue)
      allRows += `
        <tr>
          <td colspan="6" style="background:var(--bg-soft,#f3f6f4);font-size:12px;padding:6px 10px;border-bottom:1px solid #ddd">
            <strong>${esc(cmi.institution)}</strong>
            &nbsp;·&nbsp; Date: <strong>${esc(date)}</strong>
            &nbsp;·&nbsp; Venue: <strong>${esc(venue)}</strong>
          </td>
        </tr>`;

      // Rows per category
      Object.entries(byCategory).forEach(([catKey, catRows]) => {
        allRows += `
          <tr>
            <td colspan="6" style="font-weight:700;background:#eaf3ea;font-size:12px;padding:5px 10px;color:var(--green-dark,#1b4d2e)">
              ${esc(CAT_LABELS[catKey] || catKey)}
            </td>
          </tr>`;

        catRows.forEach((r, i) => {
          allRows += `
            <tr>
              <td style="text-align:center;font-weight:600;width:36px">${i + 1}.</td>
              <td>${esc(r.title           || '—')}</td>
              <td>${esc(r.agency          || '—')}</td>
              <td>${esc(r.researcher      || '—')}</td>
              <td>${esc(r.recommendations || '—')}</td>
              <td>${esc(r.winners         || '—')}</td>
            </tr>`;
        });
      });

      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);

    } else {
      const label = s === 'not-started' ? 'not yet submitted' : esc(s);
      allRows += `
        <tr>
          <td class="not-submitted" colspan="6">${esc(cmi.institution)} — ${label}</td>
        </tr>`;
    }
  });

  const docsSection = docRows ? `
    <div class="doc-card">
      <div class="doc-hdr">
        <i class="ti ti-paperclip" aria-hidden="true"></i>
        <div class="doc-hdr-title">Documentation</div>
      </div>
      <div class="doc-body">${docRows}</div>
    </div>` : '';

  return `
    <table class="merged" style="width:100%">
      <thead>
        <tr>
          <th class="group" style="width:36px">#</th>
          <th class="group">Title</th>
          <th class="group">Implementing Agency(ies)</th>
          <th class="group">Researcher(s)</th>
          <th class="group">Major Recommendations</th>
          <th class="group">Winners</th>
        </tr>
      </thead>
      <tbody>
        ${allRows || '<tr><td colspan="6" class="not-submitted">No submissions yet.</td></tr>'}
      </tbody>
    </table>
    ${docsSection}`;
}
