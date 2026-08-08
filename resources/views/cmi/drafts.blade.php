@extends('layouts.cmi')

@section('content')
<div class="page active" id="page-drafts">
  <div class="page-hdr">
    <div>
      <div class="page-title">My Drafts</div>
      <div class="page-sub">Saved report tables that are not yet marked complete</div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Draft Tables — CY {{ date('Y') }}</div>
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
document.addEventListener('DOMContentLoaded', async function () {
  try {
    const res = await fetch('/api/cmi/tables/statuses');
    const json = await res.json();
    const tbody = document.getElementById('draftsTbody');
    
    if (json.statuses) {
      const drafts = Object.entries(json.statuses).filter(([t, st]) => st === 'draft');
      if (drafts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:16px;color:#6b7280">No draft tables found. All tables are either not started or completed.</td></tr>';
      } else {
        tbody.innerHTML = drafts.map(([tableNo, st]) => `
          <tr>
            <td><strong>Table ${tableNo}</strong></td>
            <td><span class="badge badge-orange">DRAFT</span></td>
            <td><a href="/dashboard/cmi/fillup?table=${tableNo}" class="btn btn-sm">Edit Draft</a></td>
          </tr>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Drafts load error:', e);
  }
});
</script>
@endsection
