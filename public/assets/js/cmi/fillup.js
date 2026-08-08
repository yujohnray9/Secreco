/**
 * fillup.js — CMI Fill Up Report
 * Handles: section/table navigation sidebar,
 * active table form rendering, dynamic row
 * add/remove, save draft / mark complete.
 *
 * Depends on: c_core.js (toast, openModal, etc.)
 * Loaded by:  dashboards/cmi/fillup.php
 */

(function () {
  'use strict';

  /* ── MOCK DATA (replace with fetch('/api/cmi/fillup') later) ── */
  const SECTIONS = [
    { label: 'R&D Mgt. & Coord.', pct: 75, tables: [
      { no: 'T1',  title: 'AIHRs',                status: 'done' },
      { no: 'T2a', title: 'RSRDH Summary',         status: 'done' },
      { no: 'T2b', title: 'RSRDH Participants',    status: 'done' },
      { no: 'T3',  title: 'Projects Monitored',    status: 'draft' },
      { no: 'T4',  title: 'Resources Shared',      status: 'draft' },
      { no: 'T5',  title: 'Resources Generated',   status: 'not-started' },
      { no: 'T6',  title: 'Linkages',              status: 'done' },
      { no: 'T7a', title: 'Databases',             status: 'done' },
      { no: 'T7b', title: 'Info Systems',          status: 'done' },
    ]},
    { label: 'Strategic R&D', pct: 50, tables: [
      { no: 'T8a', title: 'R&D Programs',          status: 'done' },
      { no: 'T8b', title: 'Collaborative R&D',     status: 'error' },
      { no: 'T9',  title: 'Technologies from R&D', status: 'done' },
    ]},
    { label: 'Results Utilization', pct: 100, tables: [
      { no: 'T10', title: 'TTP Programs',           status: 'done' },
      { no: 'T11', title: 'Technologies Extended', status: 'done' },
      { no: 'T12', title: 'Commercialized',        status: 'done' },
      { no: 'T13', title: 'Promotion Approaches',  status: 'done' },
    ]},
    { label: 'Capability & Gov.', pct: 25, tables: [
      { no: 'T14', title: 'Non-degree Trainings',  status: 'done' },
      { no: 'T15', title: 'Equipment/Facilities',  status: 'draft' },
      { no: 'T16', title: 'Awards',                status: 'not-started' },
      { no: 'T17', title: 'Regular Meetings',      status: 'draft' },
      { no: 'T18', title: 'CMI Contributions',     status: 'not-started' },
      { no: 'T19', title: 'New Initiatives',       status: 'not-started' },
    ]},
    { label: 'Policy Analysis', pct: 0, tables: [
      { no: 'T20a', title: 'Policy Research', status: 'not-started' },
      { no: 'T20b', title: 'Policies',        status: 'not-started' },
    ]},
    { label: 'Financial Report', pct: 100, tables: [
      { no: 'FIN', title: 'Financial Summary', status: 'done' },
    ]},
  ];

  const STATUS_ICON = {
    done: '✅',
    draft: '⏳',
    error: '🔴',
    'not-started': '⚪',
  };

  const INSTITUTION_NAME = 'ISU – Echague';

  /* ── ROW TEMPLATE for Table 1 ── */
  function rowHTML() {
    return `
      <td><input type="text" placeholder="Date"/></td>
      <td><input type="text" style="width:130px" value="${INSTITUTION_NAME}" readonly/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td style="text-align:center;font-weight:600;color:var(--green)">—</td>`;
  }

  function removableRowHTML() {
    return `
      <td><input type="text" placeholder="Date"/></td>
      <td><input type="text" style="width:130px" value="${INSTITUTION_NAME}" readonly/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td><input type="number" placeholder="0"/></td>
      <td style="text-align:center"><button class="row-remove-btn" onclick="this.closest('tr').remove()">🗑</button></td>`;
  }

  /* ─────────────────────────────────────────
     1. SECTION / TABLE NAVIGATION
  ───────────────────────────────────────── */
  function renderFillNav() {
    const nav = document.getElementById('fillNav');
    if (!nav) return;

    nav.innerHTML = SECTIONS.map(s => {
      const pctColor = s.pct === 100 ? '#fff' : s.pct === 0 ? 'rgba(255,255,255,.4)' : 'rgba(255,255,255,.8)';
      const header = `<div class="fill-nav-item"><span style="font-size:12px">📁</span>${s.label}<span class="fn-pct" style="color:${pctColor}">${s.pct}%</span></div>`;
      const rows = s.tables.map(t =>
        `<div class="fill-nav-sub ${t.status}" onclick="showTable('${t.no}')">${STATUS_ICON[t.status]} ${t.no} — ${t.title}</div>`
      ).join('');
      return header + rows;
    }).join('');
  }

  /* ─────────────────────────────────────────
     2. ACTIVE TABLE FORM (Table 1 default)
  ───────────────────────────────────────── */
  function renderFillBody() {
    const body = document.getElementById('fillBody');
    if (!body) return;

    body.innerHTML = `
      <div style="margin-bottom:14px">
        <div style="font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;margin-bottom:4px">Table 1. Summary of Agency In-House Reviews (AIHRs)</div>
        <div style="font-size:12px;color:var(--text-muted)">conducted by consortium member-agencies, CY 2025 (January – December)</div>
      </div>
      <div class="tbl-wrap" style="margin-bottom:12px">
        <table class="merged" style="width:100%;min-width:560px">
          <thead>
            <tr>
              <th class="group" rowspan="2" style="width:100px">Date</th>
              <th class="group" rowspan="2" style="width:140px">Agency</th>
              <th class="group" colspan="4">Number of Projects Presented</th>
              <th class="group" rowspan="2" style="width:90px">Total Projects Reviewed</th>
            </tr>
            <tr>
              <th class="sub">New</th>
              <th class="sub">Ongoing</th>
              <th class="sub">Completed</th>
              <th class="sub">Terminated</th>
            </tr>
          </thead>
          <tbody id="t1rows">
            <tr>${rowHTML()}</tr>
          </tbody>
        </table>
      </div>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px">
        <button class="btn btn-sm" onclick="addRow()">+ Add Row</button>
        <span style="font-size:11px;color:var(--text-muted);font-style:italic">Note: The Regional Consortium may prepare other tables for ease in data presentation.</span>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn" onclick="toast('💾 Table 1 draft saved!')">💾 Save Draft</button>
        <button class="btn btn-primary" onclick="toast('✅ Table 1 marked as complete!')">✅ Mark as Complete</button>
      </div>`;
  }

  /* ─────────────────────────────────────────
     3. TABLE SWITCHING (placeholder)
  ───────────────────────────────────────── */
  window.showTable = function (no) {
    toast('Opening ' + no + '...');
    // Future: fetch and render the form for the selected table number.
  };

  /* ─────────────────────────────────────────
     4. DYNAMIC ROW ADD
  ───────────────────────────────────────── */
  window.addRow = function () {
    const tbody = document.getElementById('t1rows');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.innerHTML = removableRowHTML();
    tbody.appendChild(tr);
  };

  /* ─────────────────────────────────────────
     INIT
  ───────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    renderFillNav();
    renderFillBody();
  });

})();
