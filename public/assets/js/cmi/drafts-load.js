/**
 * drafts-load.js — CMI My Drafts
 * Fetches statuses then loads updated_at for every saved table.
 * Writes results into window.DraftsState, then triggers renderTable.
 *
 * Depends on: drafts-config.js (DraftsConfig)
 *             drafts-state.js  (DraftsState)
 *             drafts-render.js (DraftsRender.renderTable, DraftsRender.showSkeleton, DraftsRender.showError)
 * Exposed as: window.DraftsLoad
 */

(function () {
  'use strict';

  const { API_STATUSES, API_LOAD } = window.DraftsConfig;

  function loadData() {
    window.DraftsRender.showSkeleton();

    // 1. Fetch statuses (fast, single query)
    fetch(API_STATUSES)
      .then(r => r.json())
      .then(data => {
        window.DraftsState.statuses = data?.statuses ?? {};

        const savedNos = Object.keys(window.DraftsState.statuses);
        if (!savedNos.length) { window.DraftsRender.renderTable(); return; }

        // 2. Fetch updated_at for each saved table in parallel
        const fetches = savedNos.map(no =>
          fetch(`${API_LOAD}?table_no=${no}`)
            .then(r => r.json())
            .then(d => { if (d?.updated_at) window.DraftsState.updatedAt[no] = d.updated_at; })
            .catch(() => {})
        );

        Promise.all(fetches).then(() => window.DraftsRender.renderTable());
      })
      .catch(() => window.DraftsRender.showError());
  }

  window.DraftsLoad = { loadData };

})();
