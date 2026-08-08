/**
 * submissions-state.js — CMI My Submissions
 * Shared state object. Written by submissions-load.js on fetch,
 * and by submissions-edit.js after a successful save.
 * Read by submissions-render.js, submissions-view.js, submissions-edit.js.
 *
 * Exposed as: window.SubState
 */

(function () {
  'use strict';

  window.SubState = {
    statuses     : {},   // { table_no: 'done'|'pending'|… }
    meta         : {},   // { table_no: { updated_at, rows, metaData } }
    selectedYear : new Date().getFullYear(),  // default to current year
  };

})();
