/**
 * submissions-edit-events.js — CMI My Submissions
 * Event listeners for the Edit modal: input sync, row add/remove,
 * attachment upload, attachment remove, and caption blur-save.
 *
 * FIX: Double-upload bug resolved by attaching the 'change' listener
 * DIRECTLY on the file input element (not delegated on body).
 * Previously, delegation on body + renderEditAttachments() re-stamping
 * the DOM caused duplicate listeners to stack, uploading N times per click.
 *
 * Depends on: submissions-helpers.js       (SubHelpers.escapeHtml)
 *             submissions-edit-state.js    (SubEditState)
 *             submissions-edit-builders.js (SubEditBuilders)
 * Exposed as: window.SubEditEvents
 */

(function () {
  'use strict';

  const API_UPLOAD         = '/api/cmi/tables/upload-doc';
  const API_DELETE         = '/api/cmi/tables/delete-doc';
  const API_UPDATE_CAPTION = '/api/cmi/tables/upload-doc';

  const { renderEditAttachments, resolveColumns, buildEditRow } = window.SubEditBuilders;

  /* ─────────────────────────────────────────
     STATUS HELPER
  ───────────────────────────────────────── */
  function _setAttachStatus(msg, isError) {
    const el = document.getElementById('editAttachmentStatus');
    if (!el) return;
    el.textContent = msg;
    el.style.color = isError ? 'var(--danger,#c62828)' : 'var(--text-muted,#7c8a82)';
  }

  /* ─────────────────────────────────────────
     ATTACH ALL LISTENERS
     Called ONCE per modal open by openEditModal() in submissions-edit.js.
     The _listenersAttached guard in SubEditState prevents stacking.
  ───────────────────────────────────────── */
  function attachEditListeners() {
    const body = document.getElementById('editModalBody');
    if (!body) return;

    if (window.SubEditState.listenersAttached) return;
    window.SubEditState.listenersAttached = true;

    const state    = window.SubEditState;
    const tableDef = window.CMI_TABLES?.getTable(state.tableNo?.toUpperCase()) ?? null;
    const columns  = resolveColumns(tableDef, state.rows);

    /* ── input: sync cell + meta + group caption into state ── */
    body.addEventListener('input', e => {
      const el = e.target;
      if (el.classList.contains('edit-cell-input') && state.rows[+el.dataset.row] !== undefined)
        state.rows[+el.dataset.row][el.dataset.key] = el.value;
      if (el.classList.contains('edit-meta-input'))
        state.meta[el.dataset.metaKey] = el.value;
      if (el.id === 'editAttachmentGroupCaption' && Array.isArray(state.meta.images))
        state.meta.images.forEach(img => { img.caption = el.value; });
    });

    /* ── click: remove row · add row · remove attachment ── */
    body.addEventListener('click', e => {
      // Remove data row
      if (e.target.classList.contains('edit-row-del')) {
        state.rows.splice(+e.target.dataset.row, 1);
        const tbody = document.getElementById('editTableBody');
        if (tbody) tbody.innerHTML = state.rows.map((r, i) => buildEditRow(r, i, columns)).join('');
      }

      // Add data row
      if (e.target.id === 'editAddRowBtn') {
        const blank = {};
        columns.forEach(c => blank[c.key] = '');
        state.rows.push(blank);
        const tbody = document.getElementById('editTableBody');
        if (tbody) {
          const tmp = document.createElement('tbody');
          tmp.innerHTML = buildEditRow(blank, state.rows.length - 1, columns);
          tbody.appendChild(tmp.firstElementChild);
        }
      }

      // Remove attachment
      if (e.target.classList.contains('edit-attachment-remove') && Array.isArray(state.meta.images)) {
        const idx   = +e.target.dataset.idx;
        const docId = e.target.dataset.docId;

        state.meta.images.splice(idx, 1);
        renderEditAttachments();

        if (docId) {
          fetch(API_DELETE, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ doc_id: docId }),
          })
            .then(r => r.json())
            .then(res => { if (!res.success) _setAttachStatus('⚠️ File removed from view but server delete failed.', true); })
            .catch(() => _setAttachStatus('⚠️ Server delete failed — file may still exist on disk.', true));
        }
      }
    });

    /* ── blur (capture): persist group caption to server ── */
    body.addEventListener('blur', e => {
      const el = e.target;
      if (el.id !== 'editAttachmentGroupCaption') return;

      const caption = el.value;
      const docIds  = (state.meta.images || []).map(img => img.doc_id).filter(Boolean);
      if (!docIds.length) return;

      docIds.forEach(docId => {
        fetch(API_UPDATE_CAPTION, {
          method : 'POST',
          headers: { 'Content-Type': 'application/json' },
          body   : JSON.stringify({ doc_id: docId, caption }),
        })
          .then(r => r.json())
          .then(res => {
            if (!res.success) _setAttachStatus('⚠️ Caption not saved: ' + (res.error || 'unknown error'), true);
          })
          .catch(() => _setAttachStatus('⚠️ Network error — caption may not have saved.', true));
      });
    }, true); // capture — blur doesn't bubble

    /* ── change: file upload ──
         FIX: listener attached DIRECTLY on the input element, not delegated
         on body. This means renderEditAttachments() re-stamping the thumbnail
         list has zero effect on this listener — it lives on the input itself,
         which is outside the re-rendered #editAttachmentsList div.
         One listener, one upload, guaranteed.                                  */
    _attachFileInputListener();
  }

  /* ─────────────────────────────────────────
     FILE INPUT LISTENER (direct, not delegated)
  ───────────────────────────────────────── */
  function _attachFileInputListener() {
    const input = document.getElementById('editAttachmentInput');
    if (!input) return;

    input.addEventListener('change', function onFileChange(e) {
      const file = e.target.files?.[0];
      if (!file) return;

      const ALLOWED = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
      const MAX     = 5 * 1024 * 1024;

      if (!ALLOWED.includes(file.type)) {
        _setAttachStatus('❌ Invalid file type. Only JPG, PNG, GIF, WebP, or PDF allowed.', true);
        e.target.value = '';
        return;
      }
      if (file.size > MAX) {
        _setAttachStatus('❌ File too large. Maximum size is 5 MB.', true);
        e.target.value = '';
        return;
      }

      const label = document.getElementById('editAttachmentLabel');
      if (label) label.style.pointerEvents = 'none';
      _setAttachStatus('⏳ Uploading…', false);

      const formData = new FormData();
      formData.append('file',     file);
      formData.append('table_no', window.SubEditState.tableNo);

      fetch(API_UPLOAD, { method: 'POST', body: formData, credentials: 'same-origin' })
        .then(r => {
          const status = r.status;
          return r.text().then(txt => {
            let json = null;
            try { json = JSON.parse(txt); } catch (_) {}

            if (json) {
              if (json.success) {
                if (!Array.isArray(window.SubEditState.meta.images))
                  window.SubEditState.meta.images = [];

                const captionEl = document.getElementById('editAttachmentGroupCaption');
                window.SubEditState.meta.images.push({
                  doc_id   : json.doc_id    || null,
                  file_path: json.file_path || '',
                  caption  : captionEl ? captionEl.value : '',
                });
                renderEditAttachments();
                _setAttachStatus('✅ Uploaded successfully.', false);
                setTimeout(() => _setAttachStatus('Optional — only attach if you have a file to add.', false), 3000);
                return;
              }
              throw new Error(json.error || 'Upload failed (server error)');
            }

            const isHtml = /<html[\s>]/i.test(txt) || /<!DOCTYPE/i.test(txt);
            if (isHtml) {
              if (status === 401 || /sign[\s-]?in|log[\s-]?in|not authenticated/i.test(txt))
                throw new Error('Session expired — please refresh the page and log in again.');
              throw new Error('Upload endpoint returned an HTML page (HTTP ' + status + '). The API route may be misconfigured.');
            }

            throw new Error(txt.trim().slice(0, 200) || 'Upload failed (HTTP ' + status + ')');
          });
        })
        .catch(err => {
          const isNet = (err instanceof TypeError) && /fetch|failed to fetch/i.test(err.message);
          _setAttachStatus(
            isNet ? '❌ Network error — check your connection and try again.'
                  : '❌ ' + (err.message || 'Upload failed — please try again.'),
            true
          );
        })
        .finally(() => {
          if (label) label.style.pointerEvents = '';
          e.target.value = '';
        });
    });
  }

  /* ─────────────────────────────────────────
     EXPORT
  ───────────────────────────────────────── */
  window.SubEditEvents = { attachEditListeners };

})();
