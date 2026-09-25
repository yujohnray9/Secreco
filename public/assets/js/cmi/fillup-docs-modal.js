/**
 * fillup-docs-modal.js — Shared Documentation Modal
 * Reusable across all CMI tables.
 *
 * Usage:
 *   DocsModal.open(tableNo, currentImages, onSave)
 *   DocsModal.open(tableNo, currentImages, notes, onSave)  ← notes ignored (removed)
 *     - tableNo       : e.g. 'T1'
 *     - currentImages : [{ id, file_path, caption }]
 *     - onSave(images): callback with updated images array
 *
 * Caption model (group caption):
 *   One shared caption for ALL attachments in this table — same pattern
 *   as the edit modal in My Submissions. The caption is written to
 *   report_table_docs.caption for every image via update_doc_caption.php
 *   when the user clicks Done.
 *
 * Requires: fillup.css (modal styles)
 */

(function () {
  'use strict';

  const API_UPLOAD         = '/api/cmi/tables/upload-doc';
  const API_IMG_DEL        = '/api/cmi/tables/delete-doc';
  const API_UPDATE_CAPTION = '/api/cmi/tables/upload-doc';

  let _tableNo    = '';
  let _images     = [];   // [{ id, file_path, caption }]
  let _onSave     = null;
  let _isReadOnly = false;

  /* ─────────────────────────────────────────
     INJECT STYLES (once)
  ───────────────────────────────────────── */
  function ensureStyles() {
    if (document.getElementById('dm-extra-styles')) return;
    const style = document.createElement('style');
    style.id = 'dm-extra-styles';
    style.textContent = `
      .dm-group-caption-wrap {
        margin-top: 14px;
      }
      .dm-group-caption-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted, #666);
        margin-bottom: 5px;
      }
      .dm-group-caption-input {
        width: 100%;
        box-sizing: border-box;
        padding: 7px 10px;
        font-family: inherit;
        font-size: 13px;
        border: 1px solid var(--border, #ccc);
        border-radius: 6px;
        background: #fff;
        color: inherit;
      }
      .dm-group-caption-input:focus {
        outline: none;
        border-color: var(--green, #2e7d32);
        box-shadow: 0 0 0 2px rgba(46,125,50,.15);
      }
    `;
    document.head.appendChild(style);
  }

  /* ─────────────────────────────────────────
     INJECT MODAL HTML (once)
  ───────────────────────────────────────── */
  function ensureModal() {
    ensureStyles();
    if (document.getElementById('docsModal')) return;

    const el = document.createElement('div');
    el.id = 'docsModal';
    el.className = 'dm-overlay';
    el.setAttribute('role', 'dialog');
    el.setAttribute('aria-modal', 'true');
    el.setAttribute('aria-labelledby', 'dm-title');
    el.innerHTML = `
      <div class="dm-box">
        <div class="dm-header">
          <span id="dm-title" class="dm-title">Documentation</span>
          <button class="dm-close" onclick="DocsModal.close()" aria-label="Close">✕</button>
        </div>

        <div class="dm-body">
          <!-- Upload trigger -->
          <label class="dm-upload-btn" id="dm-upload-label">
            <span><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Add Photo(s) / PDF</span>
            <input type="file" id="dm-file-input" accept="image/*,.pdf,application/pdf" multiple style="display:none"
              onchange="DocsModal._upload(this)"/>
          </label>
          <p class="dm-hint" id="dm-upload-hint">Supported: JPG, PNG, GIF, WEBP, PDF · Max 5MB each</p>

          <!-- Thumbnail gallery — no per-image caption inputs -->
          <div id="dm-gallery" class="dm-gallery"></div>

          <!-- Shared group caption — one caption for all attachments -->
          <div class="dm-group-caption-wrap">
            <label class="dm-group-caption-label" for="dm-group-caption">
              Caption (optional)
            </label>
            <input type="text" id="dm-group-caption" class="dm-group-caption-input"
              placeholder="Add a caption for these attachments…"
              maxlength="255"
              oninput="DocsModal._updateGroupCaption(this.value)"/>
          </div>
        </div>

        <div class="dm-footer">
          <span id="dm-status" class="dm-status"></span>
          <div style="display:flex;gap:8px">
            <button class="btn" id="dm-cancel-btn" onclick="DocsModal.close()">Cancel</button>
            <button class="btn btn-primary" id="dm-done-btn" onclick="DocsModal._save()">Done</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(el);

    el.addEventListener('click', e => { if (e.target === el) DocsModal.close(); });
  }

  /* ─────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────── */
  function setStatus(msg) {
    const el = document.getElementById('dm-status');
    if (el) el.textContent = msg;
  }

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /** Returns the current shared caption from the input field. */
  function getGroupCaption() {
    const el = document.getElementById('dm-group-caption');
    return el ? el.value : (_images[0]?.caption || '');
  }

  /* ─────────────────────────────────────────
     RENDER GALLERY (thumbnails only, no per-image caption)
  ───────────────────────────────────────── */
  function renderGallery() {
    const gallery = document.getElementById('dm-gallery');
    if (!gallery) return;

    if (!_images.length) {
      gallery.innerHTML = `<p class="dm-empty">No attachments uploaded yet.</p>`;
      return;
    }

    gallery.innerHTML = _images.map((img, idx) => {
      const isPdf = /\.pdf$/i.test(img.file_path || '');
      const delBtn = _isReadOnly ? '' : `<button class="dm-del" onclick="event.stopPropagation();DocsModal._delete(${idx})" title="Remove file" style="position:absolute;top:4px;right:4px">✕</button>`;
      const imgDelBtn = _isReadOnly ? '' : `<button class="dm-del" onclick="DocsModal._delete(${idx})" title="Remove image">✕</button>`;

      if (isPdf) {
        return `
          <div class="dm-item" data-idx="${idx}">
            <div class="dm-thumb-wrap" style="background:#fef2f2;border:1px solid #fecaca;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;cursor:pointer;padding:6px;width:100px;height:100px;box-sizing:border-box"
              onclick="window.open('${esc(img.file_path)}','_blank')" title="Click to open PDF">
              <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
              <span style="font-size:10px;font-weight:700;color:#b91c1c;margin-top:4px">PDF FILE</span>
              <span style="font-size:9.5px;color:#6b7280;max-width:88px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${esc(img.caption || 'Document')}</span>
              ${delBtn}
            </div>
          </div>`;
      }
      return `
        <div class="dm-item" data-idx="${idx}">
          <div class="dm-thumb-wrap">
            <img src="${esc(img.file_path)}" class="dm-thumb"
              onclick="window.open('${esc(img.file_path)}','_blank')"
              title="Click to view full size"/>
            ${imgDelBtn}
          </div>
        </div>
      `;
    }).join('');
  }

  /** Populate the group caption field when opening the modal. */
  function renderGroupCaption() {
    const el = document.getElementById('dm-group-caption');
    if (!el) return;
    // Use the first image's caption as the representative value —
    // all images in a group share the same caption.
    el.value = _images[0]?.caption || '';
    el.disabled = _isReadOnly;
    el.readOnly = _isReadOnly;
    if (_isReadOnly) {
      el.style.background = '#f9fafb';
      el.style.color = '#6b7280';
      el.style.cursor = 'not-allowed';
    } else {
      el.style.background = '';
      el.style.color = '';
      el.style.cursor = '';
    }
  }

  /* ─────────────────────────────────────────
     UPLOAD
  ───────────────────────────────────────── */
  function upload(input) {
    if (_isReadOnly) return;
    const files = [...input.files];
    if (!files.length) return;

    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    for (let f of files) {
      if (f.size > MAX_FILE_SIZE) {
        setStatus(`File "${f.name}" exceeds the 5MB limit. Please upload a file smaller than 5MB.`);
        input.value = '';
        return;
      }
    }

    setStatus('Uploading…');
    const fd = new FormData();
    files.forEach(f => fd.append('images[]', f));
    fd.append('table_no', _tableNo);

    const yearVal = (document.getElementById('fillupYearSel')?.value) || window.CMI_REPORTING_YEAR || new Date().getFullYear();
    fd.append('year', yearVal);
    if (window.CMI_TARGET_USER_ID) {
      fd.append('cmi_user_id', window.CMI_TARGET_USER_ID);
    }

    fetch(API_UPLOAD, { method: 'POST', body: fd })
      .then(async r => {
        const text = await r.text();
        let res;
        try {
          res = JSON.parse(text);
        } catch (parseErr) {
          console.error('Upload response was not valid JSON:', text);
          throw new Error(
            r.ok
              ? 'Server returned an unexpected response (see console for details).'
              : `Server error (HTTP ${r.status}). See console for details.`
          );
        }
        return res;
      })
      .then(res => {
        if (res.success && res.files) {
          // New uploads inherit the current shared caption so they're
          // immediately in sync — no blur or extra step needed.
          const currentCaption = getGroupCaption();
          _images = _images.concat(
            res.files.map(f => ({ ...f, caption: currentCaption }))
          );
          renderGallery();

          // If caption is already set, persist it to the newly uploaded docs right away.
          if (currentCaption) {
            res.files
              .filter(f => f.id)
              .forEach(f => {
                fetch(API_UPDATE_CAPTION, {
                  method  : 'POST',
                  headers : { 'Content-Type': 'application/json' },
                  body    : JSON.stringify({ doc_id: f.id, caption: currentCaption }),
                }).catch(() => {});
              });
          }

          let msg = `${res.files.length} file(s) uploaded.`;
          if (res.warnings?.length) msg += ' Some files were skipped: ' + res.warnings.join('; ');
          setStatus(msg);
        } else {
          setStatus('Upload failed: ' + (res.error || 'Unknown error'));
        }
      })
      .catch(err => {
        setStatus('Upload error: ' + (err?.message || 'please try again.'));
      });

    input.value = '';
  }

  /* ─────────────────────────────────────────
     DELETE
  ───────────────────────────────────────── */
  function deleteImg(idx) {
    if (_isReadOnly) return;
    const img = _images[idx];
    if (!img) return;

    const docId = img.id || img.doc_id;
    if (docId) {
      fetch(API_IMG_DEL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: docId, doc_id: docId }),
      })
      .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); }
        catch { throw new Error('Server returned an unexpected response (see console for details).'); }
      })
      .then(res => {
        if (res.success) { _images.splice(idx, 1); renderGallery(); }
        else setStatus('Delete failed: ' + (res.error || 'Unknown error'));
      })
      .catch(err => {
        setStatus('Delete error: ' + (err?.message || 'please try again.'));
      });
    } else {
      _images.splice(idx, 1);
      renderGallery();
    }
  }

  /* ─────────────────────────────────────────
     PUBLIC API
  ───────────────────────────────────────── */
  window.DocsModal = {

    /**
     * open(tableNo, images, onSave)
     * open(tableNo, images, notes, onSave)  ← notes arg ignored (removed feature)
     */
    open(tableNo, images, notesOrOnSave, maybeOnSave) {
      _tableNo = tableNo;
      _images  = JSON.parse(JSON.stringify(images || []));

      _onSave = typeof notesOrOnSave === 'function' ? notesOrOnSave
              : (maybeOnSave || null);

      _isReadOnly = Boolean(typeof CMI !== 'undefined' && typeof CMI.isLocked === 'function' && CMI.isLocked(tableNo));

      ensureModal();

      const title = document.getElementById('dm-title');
      if (title) {
        title.innerHTML = `Documentation — ${esc(tableNo)}` + (_isReadOnly ? ' <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;background:#fef3c7;color:#92400e;margin-left:6px;vertical-align:middle">Read-Only</span>' : '');
      }

      const uploadLabel = document.getElementById('dm-upload-label');
      const hint = document.getElementById('dm-upload-hint') || document.querySelector('.dm-hint');
      if (uploadLabel) uploadLabel.style.display = _isReadOnly ? 'none' : '';
      if (hint) hint.style.display = _isReadOnly ? 'none' : '';

      const doneBtn = document.getElementById('dm-done-btn');
      if (doneBtn) doneBtn.style.display = _isReadOnly ? 'none' : '';
      const cancelBtn = document.getElementById('dm-cancel-btn');
      if (cancelBtn) cancelBtn.textContent = _isReadOnly ? 'Close' : 'Cancel';

      setStatus('');
      renderGallery();
      renderGroupCaption();

      const overlay = document.getElementById('docsModal');
      if (overlay) overlay.classList.add('dm-open');
      document.body.style.overflow = 'hidden';
    },

    close() {
      const overlay = document.getElementById('docsModal');
      if (overlay) overlay.classList.remove('dm-open');
      document.body.style.overflow = '';
    },

    _save() {
      if (_isReadOnly) {
        DocsModal.close();
        return;
      }

      // Capture the latest caption value (handles paste-then-click-Done
      // without a preceding input event in some browsers).
      const caption = getGroupCaption();

      // Apply the shared caption to every image in memory.
      _images.forEach(img => { img.caption = caption; });

      // Persist to report_table_docs so edit modal (My Submissions) can
      // show the correct caption — not a blank field.
      const captionSaves = _images
        .filter(img => img.id)
        .map(img =>
          fetch(API_UPDATE_CAPTION, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ doc_id: img.id, caption }),
          }).catch(() => {})
        );

      Promise.all(captionSaves).then(() => {
        if (typeof _onSave === 'function') _onSave([..._images]);
        DocsModal.close();
      });
    },

    _upload: upload,
    _delete: deleteImg,

    /** Called by the group caption input's oninput event. */
    _updateGroupCaption(val) {
      // Keep all images in sync in memory as the user types —
      // so that newly-uploaded files added mid-session inherit the
      // current value without needing to re-type.
      _images.forEach(img => { img.caption = val; });
    },
  };

})();
