/**
 * submissions-lightbox.js — CMI My Submissions
 * In-page image preview opened from the View modal's attachments.
 * Keeps staff inside the dashboard instead of sending them to a
 * bare browser tab with no way back.
 *
 * Exposed as: window.SubLightbox
 *             window.closeLightbox  (PHP inline onclick)
 */

(function () {
  'use strict';

  function openLightbox(src, caption) {
    const overlay = document.getElementById('modalLightbox');
    const img     = document.getElementById('lightboxImg');
    const capEl   = document.getElementById('lightboxCaption');
    if (!overlay || !img) return;

    img.src = src;
    img.alt = caption || '';
    if (capEl) capEl.textContent = caption || '';

    overlay.style.display = 'flex';
    requestAnimationFrame(() => overlay.classList.add('modal-visible'));
  }

  function closeLightbox() {
    const overlay = document.getElementById('modalLightbox');
    if (!overlay) return;
    overlay.classList.remove('modal-visible');
    setTimeout(() => {
      overlay.style.display = 'none';
      const img = document.getElementById('lightboxImg');
      if (img) img.src = '';
    }, 200);
  }

  /* ══════════════════════════════════════════
     EXPORT
  ══════════════════════════════════════════ */

  window.SubLightbox  = { openLightbox, closeLightbox };
  window.closeLightbox = closeLightbox;   // PHP inline onclick

})();
