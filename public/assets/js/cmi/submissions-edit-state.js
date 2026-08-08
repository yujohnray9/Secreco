/**
 * submissions-edit-state.js — CMI My Submissions
 * Shared mutable state for the Edit modal.
 * All sibling edit-* modules read and write through window.SubEditState.
 *
 * Load order: load BEFORE all other submissions-edit-*.js files.
 */

(function () {
  'use strict';

  window.SubEditState = {
    tableNo          : null,   // e.g. "T1"
    rows             : [],     // array of row objects (deep-copied from SubState)
    meta             : {},     // metaData object (deep-copied from SubState)
    listenersAttached: false,  // guard flag — reset by openEditModal each open
  };

})();
