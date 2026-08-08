/**
 * submissions-edit.js — CMI My Submissions
 * Entry point for the Edit modal — open/close only.
 * All logic is delegated to sibling modules:
 *
 *   submissions-edit-state.js    — shared mutable state (SubEditState)
 *   submissions-edit-builders.js — HTML construction    (SubEditBuilders)
 *   submissions-edit-events.js   — event listeners      (SubEditEvents)
 *   submissions-edit-save.js     — save to server       (SubEditSave)
 *
 * Load order in submissions.php (add BEFORE submissions.js):
 *   <script src="submissions-edit-state.js">
 *   <script src="submissions-edit-builders.js">
 *   <script src="submissions-edit-events.js">
 *   <script src="submissions-edit-save.js">
 *   <script src="submissions-edit.js">
 *
 * Exposed as: window.SubEdit  (openEditModal, saveEdit)
 *             window.closeEditModal  (used by PHP inline onclick)
 */

(function () {
  'use strict';

  /* ─────────────────────────────────────────
     OPEN
  ───────────────────────────────────────── */
  function openEditModal(tableNo) {
    const state = window.SubEditState;
    state.tableNo           = tableNo;
    state.listenersAttached = false;   // reset — fresh listeners each open

    const meta = window.SubState.meta[tableNo] || {};

    // Deep-copy so edits don't bleed into the cache until a successful save
    state.rows = JSON.parse(JSON.stringify(meta.rows     || []));
    state.meta = JSON.parse(JSON.stringify(meta.metaData || {}));

    // Normalise docs → images so previously-saved attachments appear
    if (!state.meta.images?.length) {
      const docs = meta.docs || state.meta.docs || [];
      if (docs.length) {
        state.meta.images = docs.map(d => ({
          doc_id   : d.id        || null,
          file_path: d.file_path || '',
          caption  : d.caption   || '',
        }));
      }
    }
    if (!Array.isArray(state.meta.images)) state.meta.images = [];

    const tableInfo = window.CMI_TABLE_INDEX?.[tableNo] || { title: tableNo };
    document.getElementById('editModalTableNo').textContent    = tableNo;
    document.getElementById('editModalTableTitle').textContent = tableInfo.title || tableNo;

    // Stamp HTML then attach listeners ONCE — no MutationObserver needed
    document.getElementById('editModalBody').innerHTML =
      window.SubEditBuilders.buildEditContent(tableNo);
    window.SubEditEvents.attachEditListeners();

    const overlay = document.getElementById('modalEditTable');
    if (overlay) {
      overlay.style.display = 'flex';
      requestAnimationFrame(() => overlay.classList.add('modal-visible'));
    }
  }

  /* ─────────────────────────────────────────
     CLOSE
  ───────────────────────────────────────── */
  function closeEditModal() {
    const overlay = document.getElementById('modalEditTable');
    if (!overlay) return;
    overlay.classList.remove('modal-visible');
    setTimeout(() => { overlay.style.display = 'none'; }, 200);

    const state = window.SubEditState;
    state.tableNo           = null;
    state.rows              = [];
    state.meta              = {};
    state.listenersAttached = false;
  }

  /* ─────────────────────────────────────────
     EXPORT
  ───────────────────────────────────────── */
  window.SubEdit        = { openEditModal, saveEdit: window.SubEditSave.saveEdit };
  window.closeEditModal = closeEditModal;

})();
