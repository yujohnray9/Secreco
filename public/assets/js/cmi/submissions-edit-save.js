/**
 * submissions-edit-save.js — CMI My Submissions
 * Handles saving the edited table data to the server.
 * Attachments are excluded from meta_json — they live in report_table_docs.
 *
 * Depends on: submissions-edit-state.js (SubEditState)
 * Exposed as: window.SubEditSave
 */

(function () {
  'use strict';

  const API_SAVE = '/api/cmi/tables/save';

  function saveEdit() {
    const state = window.SubEditState;
    if (!state.tableNo) return;

    const saveBtn = document.getElementById('editModalSaveBtn');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving…'; }

    // Strip images + docs before sending — attachments live in report_table_docs
    const metaToSave = Object.assign({}, state.meta);
    delete metaToSave.images;
    delete metaToSave.docs;

    fetch(API_SAVE, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ table_no: state.tableNo, status: 'done', meta: metaToSave, rows: state.rows }),
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          const tNo   = state.tableNo;
          const canon = tNo.charAt(0).toUpperCase() + tNo.slice(1).toLowerCase();
          const metaObj = {
            updated_at: res.updated_at || new Date().toISOString(),
            rows      : JSON.parse(JSON.stringify(state.rows)),
            metaData  : Object.assign({}, metaToSave, { images: JSON.parse(JSON.stringify(state.meta.images || [])) }),
            docs      : JSON.parse(JSON.stringify(state.meta.images || [])),
            remarks   : null,
          };
          window.SubState.meta[tNo] = metaObj;
          window.SubState.meta[tNo.toUpperCase()] = metaObj;
          window.SubState.meta[tNo.toLowerCase()] = metaObj;
          window.SubState.meta[canon] = metaObj;

          // Update status in local cache
          window.SubState.statuses[tNo] = 'done';
          window.SubState.statuses[tNo.toUpperCase()] = 'done';
          window.SubState.statuses[tNo.toLowerCase()] = 'done';
          window.SubState.statuses[canon] = 'done';

          if (window.SubState.remarks) {
            delete window.SubState.remarks[tNo];
            delete window.SubState.remarks[tNo.toUpperCase()];
            delete window.SubState.remarks[tNo.toLowerCase()];
            delete window.SubState.remarks[canon];
          }

          toast('Table saved successfully!');
          window.closeEditModal();
          window.SubRender.renderTable();
        } else {
          toast('❌ Save failed: ' + (res.error || 'Unknown error'));
        }
      })
      .catch(() => toast('❌ Network error — please try again.'))
      .finally(() => {
        if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Save Changes'; }
      });
  }

  window.SubEditSave = { saveEdit };

})();
