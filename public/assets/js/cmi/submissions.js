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

    const urlParams = new URLSearchParams(window.location.search);
    const urlSection = urlParams.get('section');
    const secFilter = document.getElementById('subSectionFilter');
    if (urlSection && secFilter) {
      secFilter.value = urlSection;
    }

    fetch('/api/formats?include_drafts=1')
      .then(r => r.json())
      .then(data => {
        const urlYear   = urlParams.get('year') ? parseInt(urlParams.get('year'), 10) : null;
        let storedYear  = null;
        try {
          const rawStored = localStorage.getItem('secreco_selected_year');
          if (rawStored) storedYear = parseInt(rawStored, 10);
        } catch(e){}

        const backendYears = [
          ...(Array.isArray(data.all_years) ? data.all_years : []),
          ...(Array.isArray(data.years) ? data.years : []),
          data.active_year
        ].map(y => parseInt(y, 10)).filter(y => !isNaN(y) && y >= 2000);

        let targetYear = null;
        if (urlYear && !isNaN(urlYear) && urlYear >= 2000) {
          targetYear = urlYear;
        } else if (storedYear && !isNaN(storedYear) && storedYear >= 2000) {
          targetYear = storedYear;
        } else if (data.active_year && !isNaN(parseInt(data.active_year, 10))) {
          targetYear = parseInt(data.active_year, 10);
        } else if (backendYears.length > 0) {
          targetYear = Math.max(...backendYears);
        } else {
          targetYear = new Date().getFullYear();
        }

        const uniqueYears = [...new Set([...backendYears, targetYear])].sort((a,b) => b - a);

        const yearSel = document.getElementById('subYearFilter');
        if (yearSel) {
          yearSel.innerHTML = uniqueYears.map(y => `<option value="${y}" ${y === targetYear ? 'selected' : ''}>CY ${y}</option>`).join('');
          yearSel.value = String(targetYear);
        }

        try {
          localStorage.setItem('secreco_selected_year', String(targetYear));
          const u = new URL(window.location);
          u.searchParams.set('year', targetYear);
          window.history.replaceState({}, '', u);
        } catch(e){}
        window.SubState.selectedYear = targetYear;
      }).catch(() => {
        window.SubState.selectedYear = 2026;
      }).finally(() => {
        window.SubLoad.loadData();
      });

    // Year filter change event
    document.getElementById('subYearFilter')?.addEventListener('change', (e) => {
      const yr = parseInt(e.target.value) || new Date().getFullYear();
      try {
        localStorage.setItem('secreco_selected_year', String(yr));
        const u = new URL(window.location);
        u.searchParams.set('year', yr);
        window.history.replaceState({}, '', u);
      } catch(err){}
      window.SubState.selectedYear = yr;
      window.SubLoad.loadData();
    });

    // Search and Section filter events
    document.getElementById('subSearch')?.addEventListener('input', () => window.SubRender.renderTable());
    document.getElementById('subSectionFilter')?.addEventListener('change', (e) => {
      try {
        const u = new URL(window.location);
        if (e.target.value) u.searchParams.set('section', e.target.value);
        else u.searchParams.delete('section');
        window.history.replaceState({}, '', u);
      } catch(err){}
      window.SubRender.renderTable();
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
