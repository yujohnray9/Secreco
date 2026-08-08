@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-submissions">
  <div class="page-hdr">
    <div>
      <div class="page-title">Submissions (View Only)</div>
      <div class="page-sub">CY {{ date('Y') }} CMI report submissions</div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">All Submissions</div>
    </div>
    <div class="tbl-wrap">
      <table class="dt">
        <thead>
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Table</th>
            <th>Updated At</th>
          </tr>
        </thead>
        <tbody id="vSubsTbody">
          <tr><td colspan="4" style="text-align:center;padding:16px">Loading submissions...</td></tr>
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
    const year = new Date().getFullYear();
    const res = await fetch(`/api/pta/submissions?year=${year}`);
    const json = await res.json();
    const tbody = document.getElementById('vSubsTbody');

    if (json.rows) {
      if (json.rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;color:#6b7280">No submissions found.</td></tr>';
      } else {
        tbody.innerHTML = json.rows.map(r => `
          <tr>
            <td><strong>${r.institution}</strong></td>
            <td>${r.encoder}</td>
            <td>Table ${r.table_no}</td>
            <td>${r.updated_at ? r.updated_at.substring(0, 16).replace('T', ' ') : '—'}</td>
          </tr>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Viewer Submissions load error:', e);
  }
});
</script>
@endsection
