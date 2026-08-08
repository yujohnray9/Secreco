/**
 * reports-tabledefs.js
 * Central registry of all 24 CMI tables.
 * Each entry maps a table key → { label, renderer }
 *
 * renderer options:
 *   renderT1, renderT2a, renderT2b … renderT20b — custom renderers (dedicated files)
 *   renderGeneric                                — fallback, kept on standby in case
 *                                                    a future table is added without
 *                                                    its own renderer yet
 *
 * Load order in reports.php (renderers must come before this file):
 *   render-t1.js
 *   render-t2a.js
 *   render-t2b.js
 *   render-t3.js
 *   render-t4.js
 *   render-t5.js
 *   render-t6.js
 *   render-t7a.js
 *   render-t7b.js
 *   render-t8a.js
 *   render-t8b.js
 *   render-t9.js
 *   render-t10.js
 *   render-t11.js
 *   render-t12.js
 *   render-t13.js
 *   render-t14.js
 *   render-t15.js
 *   render-t16.js
 *   render-t17.js
 *   render-t18.js
 *   render-t19.js
 *   render-t20a.js
 *   render-t20b.js
 *   render-generic.js   ← must be last; provides renderGeneric fallback
 *   reports-tabledefs.js
 */

const TABLE_DEFS = {

  /* ── Table 1 ── */
  T1: {
    label:    'Table 1 — AIHRs',
    renderer: renderT1,       // render-t1.js
  },

  /* ── Table 2a ── */
  T2a: {
    label:    'Table 2a — RSRDH Summary',
    renderer: renderT2a,      // render-t2a.js
  },

  /* ── Table 2b ── */
  T2b: {
    label:    'Table 2b — RSRDH Participants',
    renderer: renderT2b,      // render-t2b.js
  },

  /* ── Table 3 ── */
  T3: {
    label:    'Table 3 — Projects Monitored',
    renderer: renderT3,       // render-t3.js
  },

  /* ── Table 4 ── */
  T4: {
    label:    'Table 4 — Resources Shared',
    renderer: renderT4,       // render-t4.js
  },

  /* ── Table 5 ── */
  T5: {
    label:    'Table 5 — Resources Generated',
    renderer: renderT5,       // render-t5.js
  },

  /* ── Table 6 ── */
  T6: {
    label:    'Table 6 — Linkages',
    renderer: renderT6,       // render-t6.js
  },

  /* ── Table 7a ── */
  T7a: {
    label:    'Table 7a — Databases',
    renderer: renderT7a,      // render-t7a.js
  },

  /* ── Table 7b ── */
  T7b: {
    label:    'Table 7b — Info Systems',
    renderer: renderT7b,      // render-t7b.js
  },

  /* ── Table 8a ── */
  T8a: {
    label:    'Table 8a — R&D Programs',
    renderer: renderT8a,      // render-t8a.js
  },

  /* ── Table 8b ── */
  T8b: {
    label:    'Table 8b — Collaborative R&D',
    renderer: renderT8b,      // render-t8b.js
  },

  /* ── Table 9 ── */
  T9: {
    label:    'Table 9 — Technologies from R&D',
    renderer: renderT9,       // render-t9.js
  },

  /* ── Table 10 ── */
  T10: {
    label:    'Table 10 — TT Programs',
    renderer: renderT10,      // render-t10.js
  },

  /* ── Table 11 ── */
  T11: {
    label:    'Table 11 — Technologies Extended',
    renderer: renderT11,      // render-t11.js
  },

  /* ── Table 12 ── */
  T12: {
    label:    'Table 12 — Commercialized',
    renderer: renderT12,      // render-t12.js
  },

  /* ── Table 13 ── */
  T13: {
    label:    'Table 13 — Promotion Approaches',
    renderer: renderT13,      // render-t13.js
  },

  /* ── Table 14 ── */
  T14: {
    label:    'Table 14 — Non-degree Trainings',
    renderer: renderT14,      // render-t14.js
  },

  /* ── Table 15 ── */
  T15: {
    label:    'Table 15 — Equipment / Facilities',
    renderer: renderT15,      // render-t15.js
  },

  /* ── Table 16 ── */
  T16: {
    label:    'Table 16 — Awards',
    renderer: renderT16,      // render-t16.js
  },

  /* ── Table 17 ── */
  T17: {
    label:    'Table 17 — Regular Meetings',
    renderer: renderT17,      // render-t17.js
  },

  /* ── Table 18 ── */
  T18: {
    label:    'Table 18 — CMI Contributions',
    renderer: renderT18,      // render-t18.js
  },

  /* ── Table 19 ── */
  T19: {
    label:    'Table 19 — New Initiatives',
    renderer: renderT19,      // render-t19.js
  },

  /* ── Table 20a ── */
  T20a: {
    label:    'Table 20a — Policy Researches',
    renderer: renderT20a,     // render-t20a.js
  },

  /* ── Table 20b ── */
  T20b: {
    label:    'Table 20b — Policies',
    renderer: renderT20b,     // render-t20b.js
  },
};
