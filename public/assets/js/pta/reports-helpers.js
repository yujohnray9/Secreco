/**
 * reports-helpers.js  —  SecReCo · PTA Portal
 * Small shared utility functions used across the other reports-*.js files.
 */
'use strict';

// ── SHARED STATE ─────────────────────────────────────────────
// Declared here so all reports-*.js files share the same references.
var _curView = 'table';   // 'table' | 'cmi'
var _cmiList = [];        // last-fetched consolidated data (used by export)

var STATUS_BADGE = {
  'accepted'   : '<span class="badge badge-teal" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#ecfdf5;color:#0d9488"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> Accepted</span>',
  'submitted'  : '<span class="badge badge-green" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#ecfdf5;color:#059669"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> Submitted</span>',
  'done'       : '<span class="badge badge-green" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#ecfdf5;color:#059669"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> Done</span>',
  'returned'   : '<span class="badge badge-purple" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#f5f3ff;color:#7c3aed"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Returned</span>',
  'draft'      : '<span class="badge badge-orange" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#fff7ed;color:#d97706"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Draft</span>',
  'error'      : '<span class="badge badge-red" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#fef2f2;color:#dc2626"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> For Correction</span>',
  'not-started': '<span class="badge badge-gray" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#f3f4f6;color:#6b7280"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg> Not Started</span>'
};

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
