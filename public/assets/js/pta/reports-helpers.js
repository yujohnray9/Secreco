/**
 * reports-helpers.js  —  SecReCo · PTA Portal
 * Small shared utility functions used across the other reports-*.js files.
 */
'use strict';

// ── SHARED STATE ─────────────────────────────────────────────
// Declared here so all reports-*.js files share the same references.
var _curView = 'table';   // 'table' | 'cmi'
var _cmiList = [];        // last-fetched consolidated data (used by export)

// ── HELPERS ──────────────────────────────────────────────────
// NOTE: esc() is intentionally NOT defined here.
// The authoritative copy lives in reports-renderers.js which loads after this file.

function el(id) { return document.getElementById(id); }

function getYear() {
  return el('rptYearFilter')?.value ?? new Date().getFullYear();
}

function getTableKey() {
  return el('rptTableFilter')?.value ?? 'T1';
}
