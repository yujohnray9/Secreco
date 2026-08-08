@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-institutions">
  <div class="page-hdr">
    <div>
      <div class="page-title">Member Institutions (View Only)</div>
      <div class="page-sub">CVAARRD Region 2 Consortium Member Institutions</div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Institutions Status Overview</div>
    </div>
    <div class="tbl-wrap">
      <table class="dt">
        <thead>
          <tr>
            <th>Institution</th>
            <th>Type</th>
            <th>CMI Representative</th>
            <th>Tables Done</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="vInstTbody">
          <tr><td colspan="5" style="text-align:center;padding:16px">Loading institutions...</td></tr>
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
    const res = await fetch(`/api/pta/institutions?year=${year}`);
    const json = await res.json();
    const tbody = document.getElementById('vInstTbody');

    if (json.institutions) {
      if (json.institutions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:16px;color:#6b7280">No institutions registered.</td></tr>';
      } else {
        tbody.innerHTML = json.institutions.map(inst => `
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <img src="${inst.logo_url}" width="28" height="28" style="border-radius:4px" onerror="this.style.display='none'"/>
                <strong>${inst.name}</strong>
              </div>
            </td>
            <td>${inst.type}</td>
            <td>${inst.encoder}</td>
            <td>${inst.tables_done}/${inst.total_tables}</td>
            <td><span class="badge ${inst.status === 'Submitted' ? 'badge-green' : (inst.status === 'In Progress' ? 'badge-orange' : 'badge-gray')}">${inst.status}</span></td>
          </tr>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Viewer Institutions load error:', e);
  }
});
</script>
@endsection
