/**
 * reports-docs-lightbox.js  —  SecReCo · PTA Portal
 * Documentation thumbnails display + image lightbox viewer.
 */
'use strict';

// ── DOCS / IMAGES DISPLAY ────────────────────────────────────
function renderDocsSection(docs) {
  if (!docs?.length) return '';
  const imgs = docs.map(d => {
    const src = d.file_path ? ('/' + d.file_path.replace(/^\//, '')) : '';
    if (!src) return '';
    const isPdf = /\.pdf$/i.test(src);
    if (isPdf) {
      return `<a href="${src}" target="_blank" rel="noopener noreferrer" title="${esc(d.caption || 'View PDF')}" style="height:72px;width:72px;border-radius:6px;border:1px solid #fca5a5;background:#fef2f2;cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;padding:4px;box-sizing:border-box;">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
        <span style="font-size:10px;font-weight:700;color:#dc2626;margin-top:2px">PDF</span>
      </a>`;
    }
    return `<img src="${src}" onerror="this.style.display='none'" onclick="openLightbox('${src}', '${esc(d.caption || '')}')" title="${esc(d.caption || 'View image')}" style="height:72px;width:72px;object-fit:cover;border-radius:6px;border:1px solid var(--border);cursor:pointer;display:block;"/>`;
  }).join('');
  return `<div class="docs-thumbs" style="display:flex;flex-wrap:wrap;gap:4px;padding:4px 0">${imgs}</div>`;
}

// ── DOCUMENTATION SECTION (per-CMI block, used by renderT1) ──
function renderCMIDocsBlock(institution, docs) {
  if (!docs?.length) {
    return '';
  }
  const thumbsHtml = `<div style="display:flex;flex-wrap:wrap;gap:4px;padding:4px 0;">
        ${docs.map(d => {
          const src = d.file_path ? ('/' + d.file_path.replace(/^\//, '')) : '';
          if (!src) return '';
          const isPdf = /\.pdf$/i.test(src);
          if (isPdf) {
            return `<a href="${src}" target="_blank" rel="noopener noreferrer" title="${esc(d.caption || 'View PDF')}" style="width:72px;height:72px;border-radius:6px;border:1px solid #fca5a5;background:#fef2f2;cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;padding:4px;box-sizing:border-box;">
              <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
              <span style="font-size:10px;font-weight:700;color:#dc2626;margin-top:2px">PDF</span>
            </a>`;
          }
          return `<img src="${src}" width="72" height="72" onerror="this.style.display='none'" onclick="openLightbox('${src}', '${esc(d.caption || '')}')" title="${esc(d.caption || 'View image')}" style="width:72px;height:72px;object-fit:cover;border-radius:6px;border:1px solid #ddd;cursor:pointer;display:block;"/>`;
        }).join('')}
      </div>
      ${docs[0]?.caption ? `<p style="margin:5px 0 0;font-size:8.5pt;font-style:italic;color:#555;font-family:Calibri,Arial,sans-serif;">"${esc(docs[0].caption)}"</p>` : ''}`;

  return `<div style="margin-bottom:14pt;">
    <p style="margin:0 0 5px;font-size:9pt;font-weight:600;color:#1b4d2e;font-family:Calibri,Arial,sans-serif;">
      ${esc(institution)}
    </p>
    ${thumbsHtml}
  </div>`;
}

// ── LIGHTBOX ─────────────────────────────────────────────────
function openLightbox(src, caption) {
  if (/\.pdf$/i.test(src)) {
    window.open(src, '_blank');
    return;
  }
  let overlay = el('imgLightboxOverlay');
  if (!overlay) {
    document.body.insertAdjacentHTML('beforeend', `
      <div id="imgLightboxOverlay" onclick="closeLightbox()" style="
          display:none;position:fixed;inset:0;background:rgba(0,0,0,0.88);
          z-index:9999;justify-content:center;align-items:center;flex-direction:column;gap:10px">
        <button onclick="closeLightbox()" title="Close" style="
            position:fixed;top:18px;right:24px;background:rgba(255,255,255,0.15);
            border:none;color:#fff;font-size:26px;width:44px;height:44px;
            border-radius:50%;cursor:pointer;line-height:44px;text-align:center;z-index:10000;
            transition:background .2s"
          onmouseover="this.style.background='rgba(255,255,255,0.3)'"
          onmouseout="this.style.background='rgba(255,255,255,0.15)'">&#x2715;</button>
        <img id="imgLightboxImg" src="" onclick="event.stopPropagation()" style="
            max-width:90vw;max-height:82vh;border-radius:8px;
            box-shadow:0 4px 32px rgba(0,0,0,0.6)"/>
        <div id="imgLightboxCaption" style="
            color:#ddd;font-size:13px;max-width:80vw;text-align:center;line-height:1.4"></div>
      </div>`);
    overlay = el('imgLightboxOverlay');
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
  }
  el('imgLightboxImg').src             = src;
  el('imgLightboxCaption').textContent = caption || '';
  overlay.style.display                = 'flex';
}

function closeLightbox() {
  const overlay = el('imgLightboxOverlay');
  if (!overlay) return;
  overlay.style.display    = 'none';
  el('imgLightboxImg').src = '';
}
