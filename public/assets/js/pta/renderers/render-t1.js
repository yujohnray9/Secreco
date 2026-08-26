/**
 * render-t1.js  —  SecReCo · PTA Portal
 * Renderer for Table 1: Summary of Agency In-House Reviews (AIHRs)
 *
 * Columns: Date | Agency (CMI) | New | Ongoing | Completed | Terminated | Total Reviewed
 * + Documentation section below the table
 */
'use strict';

function renderT1(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let submitted = '';
  let pending   = '';
  let docRows   = '';

  data.forEach(cmi => {
    const s = cmi.table_status;

    if (s === 'done' || s === 'submitted' || s === 'accepted') {
      (cmi.rows ?? []).forEach(r => {
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
