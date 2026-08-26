/**
 * submissions-load.js — CMI My Submissions
 * Fetches submission statuses then loads meta/rows for every "done" table.
 * Writes results into window.SubState, then triggers renderTable.
 *
 * Depends on: submissions-helpers.js (SubHelpers)
 *             submissions-state.js   (SubState)
 *             submissions-render.js  (SubRender.renderTable)
 * Exposed as: window.SubLoad
 */

(function () {
  'use strict';

  const API_STATUSES = '/api/cmi/tables/statuses';
  const API_LOAD     = '/api/cmi/tables/load';

  const { showSkeleton, showError } = window.SubHelpers;

  function loadData() {
    showSkeleton();

    // Reset state on each load so stale data from a previous year is cleared
    window.SubState.statuses = {};
    window.SubState.meta     = {};

    const year = window.SubState.selectedYear;

    fetch(`${API_STATUSES}?year=${year}`)
      .then(r => r.json())
      .then(data => {
        window.SubState.statuses = data?.statuses ?? {};

        const relevant = [...new Set(Object.keys(window.SubState.statuses)
          .filter(no => window.SubState.statuses[no] === 'accepted')
          .map(no => no.toUpperCase()))];

        if (!relevant.length) { window.SubRender.renderTable(); return; }

        const fetches = relevant.map(no =>
          fetch(`${API_LOAD}?table_no=${no}&year=${year}`)
            .then(r => r.json())
            .then(d => {
              const metaObj = {
                updated_at : d?.updated_at ?? null,
                rows       : Array.isArray(d?.rows) ? d.rows : [],
                metaData   : (d?.meta && typeof d.meta === 'object') ? d.meta : {},
                docs       : Array.isArray(d?.docs) ? d.docs : [],
              };
              window.SubState.meta[no] = metaObj;
              window.SubState.meta[no.toUpperCase()] = metaObj;
              window.SubState.meta[no.toLowerCase()] = metaObj;
            })
            .catch(() => {
              const emptyObj = { updated_at: null, rows: [], metaData: {}, docs: [] };
              window.SubState.meta[no] = emptyObj;
              window.SubState.meta[no.toUpperCase()] = emptyObj;
              window.SubState.meta[no.toLowerCase()] = emptyObj;
            })
        );

        Promise.all(fetches).then(() => window.SubRender.renderTable());
      })
      .catch(() => showError());
  }

  window.SubLoad = { loadData };

})();
