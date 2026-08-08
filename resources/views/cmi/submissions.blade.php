@extends('layouts.cmi')

@section('styles')
<link rel="stylesheet" href="/assets/css/cmi/submissions.css"/>
@endsection

@section('content')
<div class="page active" id="page-submissions">
  <div class="page-hdr">
    <div>
      <div class="page-title">My Submissions</div>
      <div class="page-sub">Your submitted tables for CY {{ date('Y') }}</div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr" style="display:flex;justify-content:space-between;align-items:center">
      <div class="card-title">Completed Tables</div>
      <button type="button" class="btn btn-primary" id="btnSubmitReport">Submit Full Report to PTA</button>
    </div>
    <div class="tbl-wrap">
      <table class="dt" id="submissionsTable">
        <thead>
          <tr>
            <th>Table</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="subsTbody">
          <tr><td colspan="3" style="text-align:center;padding:16px">Loading completed tables...</td></tr>
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
    const tbody = document.getElementById('subsTbody');
    
    if (json.statuses) {
      const completed = Object.entries(json.statuses).filter(([t, st]) => st === 'done');
      if (completed.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:16px;color:#6b7280">No completed tables found yet.</td></tr>';
      } else {
        tbody.innerHTML = completed.map(([tableNo, st]) => `
          <tr>
            <td><strong>Table ${tableNo}</strong></td>
            <td><span class="badge badge-green">COMPLETED</span></td>
            <td><a href="/dashboard/cmi/fillup?table=${tableNo}" class="btn btn-sm">View/Edit</a></td>
          </tr>
        `).join('');
      }
    }

    document.getElementById('btnSubmitReport').addEventListener('click', async function() {
      if (!confirm('Submit all completed tables to PTA?')) return;
      try {
        const subRes = await fetch('/api/cmi/report/submit', { method: 'POST' });
        const subJson = await subRes.json();
        if (subJson.success) {
          alert('Report submitted successfully!');
          location.reload();
        } else {
          alert('Submission failed: ' + (subJson.message || 'Error occurred.'));
        }
      } catch (e) {
        alert('Network error submitting report.');
      }
    });
  } catch (e) {
    console.error('Submissions load error:', e);
  }
});
</script>
@endsection
