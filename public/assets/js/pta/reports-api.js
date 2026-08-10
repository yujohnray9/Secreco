/**
 * reports-api.js  —  SecReCo · PTA Portal
 * Fetches consolidated table data from the backend.
 */
'use strict';

// ── API ───────────────────────────────────────────────────────
async function fetchConsolidated(tableKey) {
  const year = getYear();
  const url  = `/api/pta/reports/consolidated?year=${encodeURIComponent(year)}&table=${encodeURIComponent(tableKey)}`;
  const res  = await fetch(url);
  if (!res.ok) throw new Error('HTTP ' + res.status);
  const json = await res.json();
  if (!json.ok) throw new Error(json.error ?? 'Unknown API error');
  return json;
}
