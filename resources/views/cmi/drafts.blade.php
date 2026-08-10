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
      @for($y = date('Y'); $y >= 2020; $y--)
        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>CY {{ $y }}</option>
      @endfor
    </select>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Draft Tables — <span id="draftsYearLabel">CY {{ date('Y') }}</span></div>
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
async function loadDrafts() {
  const year  = document.getElementById('draftsYearSel').value;
  const tbody = document.getElementById('draftsTbody');
  const label = document.getElementById('draftsYearLabel');
  if (label) label.textContent = 'CY ' + year;
  tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:16px;color:#9ca3af">Loading...</td></tr>';

  try {
    const res  = await fetch('/api/cmi/tables/statuses?year=' + year);
    const json = await res.json();

    if (json.statuses) {
      const drafts = Object.entries(json.statuses).filter(([t, st]) => st === 'draft');
      if (drafts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:24px;color:#6b7280">No draft tables found for CY ' + year + '.</td></tr>';
      } else {
        tbody.innerHTML = drafts.map(([tableNo, st]) => `
          <tr>
            <td><strong>Table ${tableNo}</strong></td>
            <td><span class="badge badge-orange">DRAFT</span></td>
            <td><a href="/dashboard/cmi/fillup?t=${tableNo}" class="btn btn-sm">Edit Draft</a></td>
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
document.addEventListener('DOMContentLoaded', loadDrafts);
</script>
@endsection
