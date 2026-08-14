/**
 * submissions.js — CMI My Submissions  (v6)
 * Entry point — wires all modules together on DOMContentLoaded.
 * No logic lives here; everything is delegated to sibling files.
 *
 * Load order in submissions.php:
 *   1. submissions-helpers.js   — SubHelpers
 *   2. submissions-state.js     — SubState
 *   3. submissions-load.js      — SubLoad
 *   4. submissions-render.js    — SubRender
 *   5. submissions-view.js      — SubView
 *   6. submissions-lightbox.js  — SubLightbox
 *   7. submissions-edit.js      — SubEdit
 *   8. submissions.js           ← this file
 *
 * Depends on: sections-data.js (CMI_SECTIONS), tables-config.js (CMI_TABLES)
 * Loaded by:  dashboards/cmi/submissions.php
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {

    /* ── Bootstrap & Year filter ── */
    window.SubHelpers.populateSectionFilter();

    fetch('/api/formats')
      .then(r => r.json())
      .then(data => {
        if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
          const activeYr = data.active_year || new Date().getFullYear();
          const yearSel  = document.getElementById('subYearFilter');
          if (yearSel) {
            const uniqueYears = [...new Set(data.years.map(Number))].sort((a,b) => b - a);
            yearSel.innerHTML = uniqueYears.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
            window.SubState.selectedYear = activeYr;
          }
        }
      }).catch(() => {}).finally(() => {
        window.SubLoad.loadData();
      });

    /* ── View modal ── */
    document.getElementById('btnCloseViewModal')
      ?.addEventListener('click', () => window.SubView.closeViewModal());
    document.getElementById('modalViewTable')
      ?.addEventListener('click', e => { if (e.target === e.currentTarget) window.SubView.closeViewModal(); });

    /* ── Lightbox ── */
    // Delegated on the view modal body so it works after each re-render
    document.getElementById('viewModalBody')?.addEventListener('click', e => {
      const link = e.target.closest('.attachment-thumb');
      if (!link) return;
      e.preventDefault();
      window.SubLightbox.openLightbox(link.dataset.lightboxSrc, link.dataset.lightboxCap);
    });
    document.getElementById('btnCloseLightbox')
      ?.addEventListener('click', () => window.SubLightbox.closeLightbox());
    document.getElementById('modalLightbox')
      ?.addEventListener('click', e => { if (e.target === e.currentTarget) window.SubLightbox.closeLightbox(); });
    document.addEventListener('keydown', e => {
      const lb = document.getElementById('modalLightbox');
      if (e.key === 'Escape' && lb?.style.display === 'flex') window.SubLightbox.closeLightbox();
    });

    /* ── Edit modal ── */
    document.getElementById('btnCloseEditModal')
      ?.addEventListener('click', () => window.closeEditModal());
    document.getElementById('editModalSaveBtn')
      ?.addEventListener('click', () => window.SubEdit.saveEdit());
    document.getElementById('modalEditTable')
      ?.addEventListener('click', e => { if (e.target === e.currentTarget) window.closeEditModal(); });

    // FIX: Replaced MutationObserver with a single call inside openEditModal.
    // MutationObserver fired on every DOM change inside editModalBody
    // (including renderEditAttachments()), causing attachEditListeners() to
    // be called multiple times per open — stacking 'change' listeners on the
    // file input and uploading the same file N times.
    // attachEditListeners() is now called ONCE by openEditModal() after the
    // modal body HTML is stamped. The _listenersAttached guard in SubEdit
    // handles any extra calls safely.

  });

})();
