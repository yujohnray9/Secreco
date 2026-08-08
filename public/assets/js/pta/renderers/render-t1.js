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

    if (s === 'done' || s === 'submitted') {
      (cmi.rows ?? []).forEach(r => {
        const total = (+r.new_ || 0) + (+r.ongoing || 0) + (+r.completed || 0) + (+r.terminated || 0);
        submitted += `
          <tr>
            <td>${esc(r.date ?? '')}</td>
            <td>${esc(cmi.institution)}</td>
            <td>${+r.new_       || 0}</td>
            <td>${+r.ongoing    || 0}</td>
            <td>${+r.completed  || 0}</td>
            <td>${+r.terminated || 0}</td>
            <td><strong>${total}</strong></td>
          </tr>`;
      });

      docRows += renderCMIDocsBlock(cmi.institution, cmi.docs);

    } else {
      const label = s === 'not-started' ? 'not yet submitted' : esc(s);
      pending += `
        <tr>
          <td class="not-submitted" colspan="7">${esc(cmi.institution)} — ${label}</td>
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
        ${pending}
      </tbody>
    </table>
    ${docsSection}`;
}
