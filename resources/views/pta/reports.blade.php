@extends('layouts.pta')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:24px; }
.fc-table-wrap { overflow-x:auto; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:last-child { border-bottom:none; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#eff6ff; color:#2563eb; }

/* ── Select Filter ── */
.filter-select { border:1px solid #e5e7eb; border-radius:8px; padding:7px 14px; font-size:13px; color:#374151; background:#f9fafb; cursor:pointer; outline:none; min-width:180px; }
.filter-select:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.12); }

/* ── Report Grid (two charts side by side) ── */
.report-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
@media (max-width:900px) { .report-grid { grid-template-columns:1fr; } }
.chart-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.chart-title { font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.chart-title svg { color:#10b981; }

/* ── Completion Progress Bars ── */
.inst-prog-list { display:flex; flex-direction:column; gap:16px; padding:8px 0; }
.inst-prog-item { display:flex; flex-direction:column; gap:6px; }
.inst-prog-head { display:flex; justify-content:space-between; align-items:center; }
.inst-prog-name { font-size:13px; font-weight:600; color:#374151; }
.inst-prog-pct  { font-size:12px; font-weight:700; color:#10b981; }
.prog-track { height:8px; background:#f3f4f6; border-radius:20px; overflow:hidden; }
.prog-fill  { height:100%; border-radius:20px; background:linear-gradient(90deg,#10b981,#34d399); transition:width .8s ease; }
</style>
@endsection

@section('content')
<div class="page active" id="page-reports">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Consolidated Reports</div>
      <div class="pg-banner-sub">View consolidated accomplishment data by table</div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
      <select id="reportYearSel" class="filter-select">
        @for($y = date('Y'); $y >= 2020; $y--)
          <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>CY {{ $y }}</option>
        @endfor
      </select>
      <select id="reportTableSel" class="filter-select">
        <option value="T1">Table 1 — R&D Projects</option>
        <option value="T2a">Table 2a — Completed R&D</option>
        <option value="T2b">Table 2b — Ongoing R&D</option>
        <option value="T3">Table 3 — Technologies Commercialized</option>
        <option value="T4">Table 4 — Intellectual Property</option>
        <option value="T5">Table 5 — Publications</option>
      </select>
    </div>
  </div>

  <!-- Main Table Card -->
  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <span id="repTableHdr">Consolidated Data — Table T1</span>
      </div>
      <span class="badge badge-blue" id="repCount">—</span>
    </div>
    <div class="fc-card-body" style="padding:0 24px 24px">
      <div class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr>
              <th>Institution</th>
              <th>Submission Status</th>
              <th>Table Status</th>
              <th>Submitted At</th>
            </tr>
          </thead>
          <tbody id="repTbody">
            <tr><td colspan="4" style="text-align:center;padding:32px;color:#9ca3af">Loading report data...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Summary Charts + Progress Bars -->
  <div class="report-grid">
    <!-- Submission Progress by Institution -->
    <div class="chart-card">
      <div class="chart-title">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
        Institution Completion
      </div>
      <div class="inst-prog-list" id="instProgList">
        <div style="color:#9ca3af;font-size:13px">Loading...</div>
      </div>
    </div>

    <!-- Doughnut Summary -->
    <div class="chart-card">
      <div class="chart-title">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 7.07 17.07"/></svg>
        Status Overview
      </div>
      <canvas id="statusDonut" height="200"></canvas>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let donutChart = null;

async function loadConsolidated() {
  const table = document.getElementById('reportTableSel').value;
  const year  = document.getElementById('reportYearSel').value;
  document.getElementById('repTableHdr').textContent = `Consolidated Data — Table ${table}`;
  const tbody = document.getElementById('repTbody');
  tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:32px;color:#9ca3af">Loading report data...</td></tr>';

  try {
    const res  = await fetch(`/api/pta/reports/consolidated?year=${year}&table=${table}`);
    const json = await res.json();
    const data = json.data || [];
    document.getElementById('repCount').textContent = `${data.length} institutions`;

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:40px;color:#9ca3af">No data submitted for this table yet.</td></tr>`;
    } else {
      tbody.innerHTML = data.map(d => `
        <tr>
          <td><strong>${d.institution}</strong></td>
          <td><span class="badge badge-blue">${d.sub_status || 'Submitted'}</span></td>
          <td><span class="badge ${d.table_status === 'done' ? 'badge-green' : 'badge-gray'}">${d.table_status}</span></td>
          <td style="color:#6b7280;font-size:12.5px">${d.submitted_at ? d.submitted_at.substring(0,16).replace('T',' ') : '—'}</td>
        </tr>
      `).join('');
    }

    // Institution progress bars
    const progList = document.getElementById('instProgList');
    if (data.length > 0) {
      progList.innerHTML = data.slice(0,6).map(d => {
        const pct = d.table_status === 'done' ? 100 : (d.table_status === 'draft' ? 50 : 0);
        return `
          <div class="inst-prog-item">
            <div class="inst-prog-head">
              <span class="inst-prog-name">${d.institution}</span>
              <span class="inst-prog-pct">${pct}%</span>
            </div>
            <div class="prog-track"><div class="prog-fill" style="width:${pct}%"></div></div>
          </div>
        `;
      }).join('');
    } else {
      progList.innerHTML = '<div style="color:#9ca3af;font-size:13px">No institution data available.</div>';
    }

    // Donut chart
    const done       = data.filter(d => d.table_status === 'done').length;
    const draft      = data.filter(d => d.table_status === 'draft').length;
    const notStarted = data.filter(d => !d.table_status || d.table_status === 'not-started').length;
    const error      = data.filter(d => d.table_status === 'error').length;
    const ctx = document.getElementById('statusDonut').getContext('2d');
    if (donutChart) donutChart.destroy();
    donutChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Done','Draft','Not Started','Error'],
        datasets: [{ data:[done,draft,notStarted,error], backgroundColor:['#10b981','#f59e0b','#d1d5db','#ef4444'], borderWidth:0, hoverOffset:6 }]
      },
      options: { cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{ padding:16, font:{size:12} } } } }
    });

  } catch(e) { console.error('Report load error:', e); }
}

document.getElementById('reportTableSel').addEventListener('change', loadConsolidated);
document.getElementById('reportYearSel').addEventListener('change', loadConsolidated);
document.addEventListener('DOMContentLoaded', loadConsolidated);
</script>
@endsection
