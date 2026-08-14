/**
 * render-t11.js  —  SecReCo · PTA Portal
 * Custom renderer for Table 11 — Technologies Extended/Deployed.
 * Groups submissions by institution; each institution shows its
 * status badge, doc attachments, and a detail table of technologies
 * with the TT modalities that were checked.
 */
'use strict';

const T11_MOD_LABELS = {
  stcbf: 'STCBF',
  stc4id: 'STC4iD',
  safe: 'SAFE',
  food_value_chain: 'Food Value Chain',
  other_extension_initiatives: 'Other extension initiatives',
};

function renderT11(data) {
  if (!data?.length) return '<div class="loading-state">No CMI data found for this year.</div>';

  let out = '';

  data.forEach(cmi => {
    const badge    = STATUS_BADGE[cmi.table_status] ?? STATUS_BADGE['not-started'];
    const docsHtml = renderDocsSection(cmi.docs);
    const items = (cmi.rows || []).filter(r =>
      (r.tech || '').trim()    !== '' ||
      (r.project || '').trim() !== '' ||
      (r.agency || '').trim()  !== '' ||
      (r.modalities && Object.values(r.modalities).some(Boolean))
    );

    out += `
      <div class="rpt-cmi-block" style="margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
          <strong>${esc(cmi.institution)}</strong>
          <span style="font-size:11px;color:var(--text-muted)">${items.length} item(s) · Last updated: ${esc(cmi.updated_at ?? '—')}</span>
        </div>
        ${docsHtml || ''}
        ${items.length ? `
          <table class="dt" style="width:100%;margin-top:6px">
            <thead>
              <tr>
                <th style="width:32px">#</th>
                <th>Name of Technology</th>
                <th>Project Title</th>
                <th>Implementing Agency</th>
                <th>TT Modality</th>
              </tr>
            </thead>
            <tbody>
              ${items.map((r, i) => `
                <tr>
                  <td style="text-align:center">${i + 1}</td>
                  <td>${esc(r.tech || '—')}</td>
                  <td>${esc(r.project || '—')}</td>
                  <td>${esc(r.agency || '—')}</td>
                  <td>${esc(t11ModalityList(r.modalities))}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        ` : `<div style="font-size:12px;color:var(--text-muted)">No data submitted.</div>`}
      </div>`;
  });

  return out;
}

function t11ModalityList(modalities) {
  if (!modalities) return '—';
  const checked = Object.keys(modalities).filter(k => modalities[k]).map(k => T11_MOD_LABELS[k] || k);
  return checked.length ? checked.join(', ') : '—';
}
