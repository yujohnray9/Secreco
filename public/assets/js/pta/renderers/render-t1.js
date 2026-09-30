/**
 * render-t1.js  —  SecReCo · PTA Portal
 * Renderer for Table 1: Summary of Agency In-House Reviews (AIHRs)
 *
 * Columns: Date | Agency (CMI) | New | Ongoing | Completed | Terminated | Total Reviewed
 * Supports categories: Pre In-House & AIHR's
 * + Documentation section below the table
 */
'use strict';

function renderT1(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let submitted = '';
  let docRows   = '';

  const CATEGORIES = ['Pre In-House', "AIHR's"];

  data.forEach(cmi => {
    const s = cmi.table_status;

    if (s === 'done' || s === 'submitted' || s === 'accepted') {
      const rows = cmi.rows ?? [];
      const hasCategories = rows.some(r => r.category);

      if (hasCategories) {
        CATEGORIES.forEach(cat => {
          const catRows = rows.filter(r => (r.category === cat) || (cat === "AIHR's" && (!r.category || r.category === 'aihrs')));
          if (catRows.length > 0) {
            submitted += `
              <tr style="background:#f3f6f4;font-weight:700;color:#075b42">
                <td colspan="7">${esc(cat)} — ${esc(cmi.institution)}</td>
              </tr>`;
            catRows.forEach(r => {
              const newCount = (+r.new_ || +r.new || +r.new_projects || 0);
              const ongoingCount = (+r.ongoing || 0);
              const completedCount = (+r.completed || 0);
              const terminatedCount = (+r.terminated || 0);
              const total = newCount + ongoingCount + completedCount + terminatedCount;
              submitted += `
                <tr>
                  <td>${esc(r.date ?? '')}</td>
                  <td>${esc(r.agency || cmi.institution)}</td>
                  <td>${newCount}</td>
                  <td>${ongoingCount}</td>
                  <td>${completedCount}</td>
                  <td>${terminatedCount}</td>
                  <td><strong>${total}</strong></td>
                </tr>`;
            });
          }
        });
      } else {
        rows.forEach(r => {
          const newCount = (+r.new_ || +r.new || +r.new_projects || 0);
          const ongoingCount = (+r.ongoing || 0);
          const completedCount = (+r.completed || 0);
          const terminatedCount = (+r.terminated || 0);
          const total = newCount + ongoingCount + completedCount + terminatedCount;
          submitted += `
            <tr>
              <td>${esc(r.date ?? '')}</td>
              <td>${esc(r.agency || cmi.institution)}</td>
              <td>${newCount}</td>
              <td>${ongoingCount}</td>
              <td>${completedCount}</td>
              <td>${terminatedCount}</td>
              <td><strong>${total}</strong></td>
            </tr>`;
        });
      }

      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);
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
          <th class="group" rowspan="2" style="width:90px">Date</th>
          <th class="group" rowspan="2">Agency (CMI)</th>
          <th class="group" colspan="4">Number of Projects Presented</th>
          <th class="group" rowspan="2" style="width:110px">Total Reviewed</th>
        </tr>
        <tr>
          <th class="sub">New</th>
          <th class="sub">Ongoing</th>
          <th class="sub">Completed</th>
          <th class="sub">Terminated</th>
        </tr>
      </thead>
      <tbody>
        ${submitted || '<tr><td colspan="7" class="not-submitted">No submissions yet.</td></tr>'}
      </tbody>
    </table>
    ${docsSection}`;
}
