/**
 * sidebar-collapse.js — SecReCo (CMI · PTA · Viewer · Executive)
 * Adds an arrow button that collapses the main sidebar to logo + icons only.
 * State is remembered in localStorage. Desktop/laptop only (> 768px);
 * on phones the existing slide-in menu is used.
 *
 * Load in <head> so the saved state is applied before first paint (no flicker).
 */
(function () {
  'use strict';

  var KEY = 'secreco.sidebarCollapsed';
  var root = document.documentElement;

  // Fill Out page: always start collapsed to give the tables more room.
  // Toggling there is temporary and doesn't change the saved preference.
  var IS_FILLUP = /\/fillup(\/|$|\?)/i.test(location.pathname);

  function isSaved() {
    try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; }
  }

  // Apply immediately (runs in <head>)
  if (IS_FILLUP || isSaved()) root.classList.add('sb-collapsed');

  var ARROW =
    '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" ' +
    'stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<polyline points="15 18 9 12 15 6"></polyline></svg>';

  function updateButton(btn) {
    var collapsed = root.classList.contains('sb-collapsed');
    var label = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
    btn.title = label;
    btn.setAttribute('aria-label', label);
    btn.setAttribute('aria-expanded', String(!collapsed));
  }

  function init() {
    var sidebar = document.querySelector('.sidebar');
    if (!sidebar || sidebar.querySelector('.sb-collapse-btn')) return;

    // Tooltips: show the page name on hover when only icons are visible
    sidebar.querySelectorAll('.nav-item').forEach(function (a) {
      if (!a.title) {
        var text = (a.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) a.title = text;
      }
    });

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'sb-collapse-btn';
    btn.innerHTML = ARROW;
    updateButton(btn);

    btn.addEventListener('click', function () {
      var next = !root.classList.contains('sb-collapsed');
      root.classList.toggle('sb-collapsed', next);
      if (!IS_FILLUP) {
        try { localStorage.setItem(KEY, next ? '1' : '0'); } catch (e) {}
      }
      updateButton(btn);
    });

    // Placed outside the sidebar (fixed on its right edge) so it isn't clipped
    document.body.appendChild(btn);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
