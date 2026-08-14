/**
 * reports-renderers.js  —  SecReCo · PTA Portal
 * Shared renderer utilities only.
 * Individual table renderers are in assets/js/pta/renderers/render-tXX.js
 *
 * Load order in reports.php (renderers must load BEFORE reports-tabledefs.js):
 *   render-t1.js
 *   render-t2a.js
 *   render-t8b.js
 *   render-generic.js   ← must be last (fallback)
 *   reports-renderers.js
 *   reports-tabledefs.js
 */
'use strict';

// ── SHARED RENDERER UTILITIES ─────────────────────────────────
// These are called by individual render-tXX.js files.

/**
 * renderCMIDocsBlock
 * Builds the per-CMI documentation block (images + captions).
 * Used inside doc-card sections in custom renderers (e.g. T1, T2a).
 */
function renderCMIDocsBlock(institution, docs) {
  if (!docs?.length) {
    return '';
  }

  const imgs = docs.map(d => `
    <div class="doc-thumb-wrap" onclick="openDocLightbox('${esc(d.file_path)}','${esc(d.caption||'')}')">
      <img src="${esc(d.file_path)}" class="doc-thumb" alt="${esc(d.caption || 'documentation')}" loading="lazy"/>
      ${d.caption ? `<div class="doc-caption">"${esc(d.caption)}"</div>` : ''}
    </div>`).join('');

  return `
    <div class="doc-cmi-block">
      <div class="doc-cmi-name">${esc(institution)}</div>
      <div class="doc-thumbs">${imgs}</div>
    </div>`;
}

/**
 * renderDocsSection
 * Inline docs (used inside table cells — e.g. T8b).
 */
function renderDocsSection(docs) {
  if (!docs?.length) return '';
  return docs.map(d => `
    <span class="doc-inline-link" onclick="openDocLightbox('${esc(d.file_path)}','${esc(d.caption||'')}')">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> ${esc(d.caption || 'attachment')}
    </span>`).join(' ');
}

/** HTML escape helper — used by all renderers */
function esc(s) {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
