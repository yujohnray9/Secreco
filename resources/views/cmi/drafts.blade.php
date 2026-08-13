@extends('layouts.cmi')

@section('styles')
<style>
.pg-filter-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.filter-select { border:1px solid #d1d5db; border-radius:8px; padding:7px 14px; font-size:13px; color:#374151; background:#fff; cursor:pointer; outline:none; }
.filter-select:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
</style>
@endsection

@section('content')
<div class="page active" id="page-drafts">
  <div class="page-hdr">
    <div>
      <div class="page-title">My Drafts</div>
      <div class="page-sub">Saved report tables that are not yet marked complete</div>
    </div>
  </div>

  <div class="pg-filter-row">
    <label style="font-size:13px;font-weight:600;color:#374151">Year:</label>
    <select class="filter-select" id="draftsYearSel">
      <option value="">Loading...</option>
    </select>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Draft Tables — <span id="draftsYearLabel">...</span></div>
    </div>
    <div class="tbl-wrap">
      <table class="dt" id="draftsTable">
        <thead>
          <tr>
            <th>Table</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="draftsTbody">
          <tr><td colspan="3" style="text-align:center;padding:16px">Loading drafts...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const TABLE_TITLES = {
  T1: 'AIHRs', T2a: 'RSRDH Summary', T2b: 'RSRDH Participants', T3: 'Projects Monitored',
  T4: 'Resources Shared', T5: 'Resources Generated', T6: 'Linkages', T7a: 'Databases',
  T7b: 'Info Systems', T8a: 'R&D Programs', T8b: 'Collaborative R&D', T9: 'Technologies from R&D',
  T10: 'TT Programs', T11: 'Technologies Extended', T12: 'Commercialized', T13: 'Promotion Approaches',
  T14: 'Non-degree Trainings', T15: 'Equipment/Facilities', T16: 'Awards', T17: 'Regular Meetings',
  T18: 'CMI Contributions', T19: 'New Initiatives', T20a: 'Policy Research', T20b: 'Policies'
};

async function loadDrafts() {
  const year  = document.getElementById('draftsYearSel').value;
  const tbody = document.getElementById('draftsTbody');
  const label = document.getElementById('draftsYearLabel');
  if (label) label.textContent = 'CY ' + year;
  tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:24px;color:#9ca3af">Loading drafts...</td></tr>';

  try {
    const res  = await fetch('/api/cmi/tables/statuses?year=' + year);
    const json = await res.json();

    if (json.statuses) {
      const drafts = Object.entries(json.statuses).filter(([t, st]) => st === 'draft');
      if (drafts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:32px;color:#6b7280">No draft tables found for CY ' + year + '.<br><span style="font-size:12px;color:#9ca3af">Saved draft tables will appear here.</span></td></tr>';
      } else {
        tbody.innerHTML = drafts.map(([tableNo, st]) => `
          <tr>
            <td><strong>Table ${tableNo}</strong> — ${TABLE_TITLES[tableNo] || ''}</td>
            <td><span class="badge badge-orange" style="background:#fff7ed;color:#d97706;padding:4px 10px;border-radius:12px;font-weight:600;font-size:11.5px">⏳ DRAFT</span></td>
            <td><a href="/dashboard/cmi/fillup?t=${tableNo}" class="btn btn-sm" style="background:#10b981;color:#fff;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;text-decoration:none">Edit Draft</a></td>
          </tr>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Drafts load error:', e);
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:16px;color:#dc2626">Error loading drafts.</td></tr>';
  }
}

document.getElementById('draftsYearSel').addEventListener('change', loadDrafts);
document.addEventListener('DOMContentLoaded', function() {
  // Populate year dropdown from Manage Format activated years
  fetch('/api/formats')
    .then(r => r.json())
    .then(data => {
      const sel = document.getElementById('draftsYearSel');
      if (sel && data && data.years && data.years.length > 0) {
        const activeYr = data.active_year || data.years[0];
        sel.innerHTML = data.years.map(y =>
          `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`
        ).join('');
      }
    })
    .catch(() => {})
    .finally(() => loadDrafts());
});
</script>
@endsection
