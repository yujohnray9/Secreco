@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-reports">
  <div class="page-hdr">
    <div>
      <div class="page-title">Consolidated Reports (View Only)</div>
      <div class="page-sub">View consolidated accomplishment data by table</div>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px">
    <div class="card-hdr">
      <div class="card-title">Select Table</div>
    </div>
    <div class="card-body">
      <select id="vReportTableSel" class="form-select" style="max-width:300px">
        <option value="T1">Table 1 - R&D Projects</option>
        <option value="T2a">Table 2a - Completed R&D Projects</option>
        <option value="T2b">Table 2b - Ongoing R&D Projects</option>
        <option value="T3">Table 3 - Technologies Commercialized</option>
        <option value="T4">Table 4 - Intellectual Property Applications</option>
        <option value="T5">Table 5 - Publications</option>
      </select>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title" id="vRepTableHdr">Consolidated Data — Table T1</div>
    </div>
    <div class="tbl-wrap">
      <table class="dt">
        <thead>
          <tr>
            <th>Institution</th>
            <th>Submission Status</th>
            <th>Table Status</th>
            <th>Submitted At</th>
          </tr>
        </thead>
        <tbody id="vRepTbody">
          <tr><td colspan="4" style="text-align:center;padding:16px">Loading report data...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
  async function loadConsolidated(table) {
    const year = new Date().getFullYear();
    document.getElementById('vRepTableHdr').textContent = `Consolidated Data — Table ${table}`;
    const tbody = document.getElementById('vRepTbody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px">Loading report data...</td></tr>';

    try {
      const res = await fetch(`/api/pta/reports/consolidated?year=${year}&table=${table}`);
      const json = await res.json();

      if (json.data) {
        if (json.data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;color:#6b7280">No data submitted for this table yet.</td></tr>';
        } else {
          tbody.innerHTML = json.data.map(d => `
            <tr>
              <td><strong>${d.institution}</strong></td>
              <td><span class="badge badge-blue">${d.sub_status || 'Submitted'}</span></td>
              <td><span class="badge ${d.table_status === 'done' ? 'badge-green' : 'badge-gray'}">${d.table_status}</span></td>
              <td>${d.submitted_at ? d.submitted_at.substring(0, 16).replace('T', ' ') : '—'}</td>
            </tr>
          `).join('');
        }
      }
    } catch (e) {
      console.error('Report load error:', e);
    }
  }

  document.getElementById('vReportTableSel').addEventListener('change', function(e) {
    loadConsolidated(e.target.value);
  });

  loadConsolidated('T1');
});
</script>
@endsection
