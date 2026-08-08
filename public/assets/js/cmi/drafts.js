/**
 * drafts.js — CMI My Drafts
 * Entry point — boots the page on DOMContentLoaded.
 * No logic lives here; everything is delegated to sibling files.
 *
 * Load order in drafts.php:
 *   1. sections-data.js   — CMI_SECTIONS (no cache-bust; static)
 *   2. drafts-config.js   — DraftsConfig
 *   3. drafts-state.js    — DraftsState
 *   4. drafts-render.js   — DraftsRender
 *   5. drafts-load.js     — DraftsLoad
 *   6. drafts.js          ← this file
 *
 * Loaded by: dashboards/cmi/drafts.php
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => window.DraftsLoad.loadData());

})();
