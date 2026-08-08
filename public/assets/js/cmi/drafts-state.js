/**
 * drafts-state.js — CMI My Drafts
 * Shared state. Written by drafts-load.js after each fetch.
 * Read by drafts-render.js to build the table.
 *
 * Exposed as: window.DraftsState
 */

(function () {
  'use strict';

  window.DraftsState = {
    statuses  : {},   // { T1: 'done', T2a: 'draft', … }
    updatedAt : {},   // { T1: '2026-06-14 13:26:39', … }
  };

})();
