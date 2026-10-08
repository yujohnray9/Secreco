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
 * Builds the per-CMI documentation block (images + PDF badges + captions).
 * Used inside doc-card sections in custom renderers (e.g. T1, T2a).
 */
function renderCMIDocsBlock(institution, docs) {
  if (!docs?.length) {
    return '';
  }

  const imgs = docs.map(d => {
    const rawPath = d.file_path || '';
    const src = rawPath ? ('/' + rawPath.replace(/^\//, '')) : '';
    if (!src) return '';
    const isPdf = /\.pdf$/i.test(src);
    const cap = d.caption || '';

    if (isPdf) {
      return `
        <div class="doc-thumb-wrap doc-pdf-wrap" onclick="openDocLightbox('${esc(src)}','${esc(cap)}')" title="${esc(cap || 'View PDF')}" style="cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;width:76px;height:76px;border-radius:8px;border:1px solid #fecaca;background:#fff5f5;padding:6px;box-sizing:border-box;text-align:center;">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
          <span style="font-size:10px;font-weight:700;color:#dc2626;margin-top:2px;">PDF</span>
          ${cap ? `<div class="doc-caption" style="max-width:68px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:9px;color:#6b7280;margin-top:1px;">"${esc(cap)}"</div>` : ''}
        </div>`;
    }

    return `
      <div class="doc-thumb-wrap" onclick="openDocLightbox('${esc(src)}','${esc(cap)}')" title="${esc(cap || 'View image')}" style="cursor:pointer;position:relative;">
        <img src="${esc(src)}" class="doc-thumb" alt="${esc(cap || 'documentation')}" loading="lazy" style="width:76px;height:76px;object-fit:cover;border-radius:8px;border:1px solid #ddd;display:block;"/>
        ${cap ? `<div class="doc-caption" style="max-width:76px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:9px;color:#6b7280;margin-top:2px;">"${esc(cap)}"</div>` : ''}
      </div>`;
  }).join('');

  return `
    <div class="doc-cmi-block" style="margin-bottom:14px;">
      <div class="doc-cmi-name" style="font-size:12px;font-weight:600;color:#1b4d2e;margin-bottom:6px;">${esc(institution)}</div>
      <div class="doc-thumbs" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">${imgs}</div>
    </div>`;
}

/**
 * renderDocsSection
 * Inline docs (used inside table cells — e.g. T8b).
 */
function renderDocsSection(docs) {
  if (!docs?.length) return '';
  return docs.map(d => {
    const rawPath = d.file_path || '';
    const src = rawPath ? ('/' + rawPath.replace(/^\//, '')) : '';
    if (!src) return '';
    const isPdf = /\.pdf$/i.test(src);
    const cap = esc(d.caption || (isPdf ? 'PDF Document' : 'attachment'));
    return `
      <span class="doc-inline-link" onclick="openDocLightbox('${esc(src)}','${cap}')" style="cursor:pointer;display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;background:#f3f4f6;border:1px solid #e5e7eb;font-size:11px;font-weight:500;color:#374151;margin:2px 4px 2px 0;">
        ${isPdf
          ? `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#dc2626" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`
          : `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>`
        }
        ${cap}
      </span>`;
  }).join(' ');
}

/** HTML escape helper — used by all renderers */
function esc(s) {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/**
 * openDocLightbox / openLightbox
 * Handles both images (via modal overlay) and PDFs (opens directly in new tab)
 */
function openDocLightbox(src, caption) {
  if (!src) return;
  const cleanSrc = '/' + src.replace(/^\//, '');
  if (/\.pdf$/i.test(cleanSrc)) {
    window.open(cleanSrc, '_blank');
    return;
  }

  let overlay = document.getElementById('imgLightboxOverlay');
  if (!overlay) {
    document.body.insertAdjacentHTML('beforeend', `
      <div id="imgLightboxOverlay" onclick="closeDocLightbox()" style="
          display:none;position:fixed;inset:0;background:rgba(0,0,0,0.88);
          z-index:99999;justify-content:center;align-items:center;flex-direction:column;gap:10px">
        <button onclick="closeDocLightbox()" title="Close" style="
            position:fixed;top:18px;right:24px;background:rgba(255,255,255,0.15);
            border:none;color:#fff;font-size:26px;width:44px;height:44px;
            border-radius:50%;cursor:pointer;line-height:44px;text-align:center;z-index:100000;
            transition:background .2s"
          onmouseover="this.style.background='rgba(255,255,255,0.3)'"
          onmouseout="this.style.background='rgba(255,255,255,0.15)'">&#x2715;</button>
        <img id="imgLightboxImg" src="" onclick="event.stopPropagation()" style="
            max-width:90vw;max-height:82vh;border-radius:8px;
            box-shadow:0 4px 32px rgba(0,0,0,0.6)"/>
        <div id="imgLightboxCaption" style="
            color:#ddd;font-size:13px;max-width:80vw;text-align:center;line-height:1.4"></div>
      </div>`);
    overlay = document.getElementById('imgLightboxOverlay');
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDocLightbox(); });
  }

  const imgEl = document.getElementById('imgLightboxImg');
  const capEl = document.getElementById('imgLightboxCaption');
  if (imgEl) imgEl.src = cleanSrc;
  if (capEl) capEl.textContent = caption || '';
  if (overlay) overlay.style.display = 'flex';
}

function closeDocLightbox() {
  const overlay = document.getElementById('imgLightboxOverlay');
  if (!overlay) return;
  overlay.style.display = 'none';
  const imgEl = document.getElementById('imgLightboxImg');
  if (imgEl) imgEl.src = '';
}

window.openDocLightbox  = openDocLightbox;
window.closeDocLightbox = closeDocLightbox;
window.openLightbox     = openDocLightbox;
window.closeLightbox    = closeDocLightbox;

