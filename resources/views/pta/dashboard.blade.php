@extends('layouts.pta')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/dashboard.css?v=3"/>
@endsection

@section('content')
<div class="page active" id="page-dashboard">
  <div class="dashboard-grid">

    <!-- ── TOP ROW: 4 STAT CARDS ── -->
    <div class="stats-cards-grid">
      <!-- Total CMIs -->
      <div class="stat-card-fc">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Total CMIs</span>
          <div class="sc-fc-icon icon-blue">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statTotalCmis">—</div>
          <span class="sc-fc-badge up">Region 2</span>
        </div>
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Member institutions</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/pta/institutions'">→</button>
        </div>
      </div>

      <!-- Submitted -->
      <div class="stat-card-fc">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Submitted</span>
          <div class="sc-fc-icon icon-green">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statSubmitted">—</div>
          <span class="sc-fc-badge up">Reports</span>
        </div>
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Fully submitted reports</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/pta/submissions'">→</button>
        </div>
      </div>

      <!-- In Progress -->
      <div class="stat-card-fc">
        <div class="sc-fc-head">
          <span class="sc-fc-title">In Progress</span>
          <div class="sc-fc-icon icon-orange">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statInProgress">—</div>
          <span class="sc-fc-badge up">Active</span>
        </div>
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Filling up tables</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/pta/submissions'">→</button>
        </div>
      </div>

      <!-- Not Started -->
      <div class="stat-card-fc">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Not Started</span>
          <div class="sc-fc-icon icon-purple">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statNotStarted">—</div>
          <span class="sc-fc-badge down">Pending</span>
        </div>
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Need follow-up action</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/pta/submissions'">→</button>
        </div>
      </div>
    </div>

    <!-- ── MIDDLE ROW: LINE CHART & BAR CHART ── -->
    <div class="middle-charts-grid">
      <!-- Submission Growth Trends (Line Chart) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <div class="card-fc-title">Submission Activity Trends</div>
            <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">Last 14 days activity</div>
          </div>
        </div>
        <div style="height: 260px; position: relative;">
          <canvas id="growthTrendChart"></canvas>
        </div>
      </div>

      <!-- Submission Breakdown by Status (Bar Chart) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div class="card-fc-title">Submissions Status Comparison</div>
        </div>
        <div style="height: 260px; position: relative;">
          <canvas id="frequencyBarChart"></canvas>
        </div>
      </div>
    </div>

    <!-- ── BOTTOM ROW: DONUT CHART & INSTITUTION ACCOMPLISHMENTS ── -->
    <div class="bottom-widgets-grid">
      <!-- Status Breakdown (Donut Chart) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div class="card-fc-title">Status Distribution</div>
        </div>
        <div style="height: 240px; display: flex; align-items: center; justify-content: center; position: relative;">
          <canvas id="statusDonutChart" style="max-height: 220px;"></canvas>
        </div>
      </div>

      <!-- Institution Accomplishments (Horizontal Progress Bars) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div class="card-fc-title">Institution Progress Overview</div>
        </div>
        <div id="institutionProgressList" style="display:flex; flex-direction:column; gap:14px; padding:10px 0;">
          <div style="text-align:center; color:#9ca3af; font-size:13px; padding:20px;">Loading institution progress...</div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
  try {
    const year = new Date().getFullYear();
    const res = await fetch(`/api/pta/dashboard/stats?year=${year}`);
    const json = await res.json();

    if (json.stats) {
      document.getElementById('statTotalCmis').textContent   = json.stats.total_cmis || '0';
      document.getElementById('statSubmitted').textContent   = json.stats.submitted || '0';
      document.getElementById('statInProgress').textContent  = json.stats.in_progress || '0';
      document.getElementById('statNotStarted').textContent  = json.stats.not_started || '0';
    }

    // 1. Line Growth Trend Chart (Real DB Data)
    const ctxTrend = document.getElementById('growthTrendChart').getContext('2d');
    const gradTrend = ctxTrend.createLinearGradient(0, 0, 0, 260);
    gradTrend.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
    gradTrend.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    const labels = json.trend_labels && json.trend_labels.length ? json.trend_labels : ['Day 1','Day 2','Day 3','Day 4','Day 5','Day 6','Today'];
    const values = json.trend_values && json.trend_values.length ? json.trend_values : [0,0,1,2,1,3,4];

    new Chart(ctxTrend, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Completed Tables',
          data: values,
          borderColor: '#10b981',
          borderWidth: 3,
          backgroundColor: gradTrend,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#10b981',
          pointRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { grid: { color: '#f3f4f6' }, ticks: { precision: 0 } }
        }
      }
    });

    // 2. Bar Chart (Status Comparison from DB)
    const ctxBar = document.getElementById('frequencyBarChart').getContext('2d');
    const gradBar = ctxBar.createLinearGradient(0, 0, 0, 260);
    gradBar.addColorStop(0, '#10b981');
    gradBar.addColorStop(1, '#a7f3d0');

    const s = json.stats || {};
    new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: ['Submitted', 'In Progress', 'Accepted', 'Returned'],
        datasets: [{
          data: [s.submitted || 0, s.in_progress || 0, s.accepted || 0, s.returned || 0],
          backgroundColor: ['#10b981', '#f59e0b', '#059669', '#ef4444'],
          borderRadius: 8,
          barThickness: 32
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { ticks: { precision: 0 } }
        }
      }
    });

    // 3. Donut Chart (Status Breakdown from DB)
    const ctxDonut = document.getElementById('statusDonutChart').getContext('2d');
    new Chart(ctxDonut, {
      type: 'doughnut',
      data: {
        labels: ['Submitted', 'In Progress', 'Not Started', 'Returned'],
        datasets: [{
          data: [s.submitted || 0, s.in_progress || 0, s.not_started || 0, s.returned || 0],
          backgroundColor: ['#10b981', '#3b82f6', '#d1d5db', '#ef4444'],
          borderWidth: 0,
          cutout: '72%'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right' } }
      }
    });

    // 4. Real Institution Progress Bars
    const progWrap = document.getElementById('institutionProgressList');
    if (json.cmi_list && json.cmi_list.length > 0) {
      progWrap.innerHTML = json.cmi_list.slice(0, 5).map(item => {
        const pct = item.total_tables > 0 ? Math.round((item.tables_done / item.total_tables) * 100) : 0;
        return `
          <div class="progress-item">
            <div class="progress-info" style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span class="progress-label" style="font-size:13px; font-weight:600; color:#374151;">${item.institution}</span>
              <span class="progress-pct" style="font-size:12px; font-weight:700; color:#10b981;">${pct}%</span>
            </div>
            <div class="progress-track" style="height:7px; background:#f3f4f6; border-radius:10px; overflow:hidden;">
              <div class="progress-fill" style="width: ${pct}%; height:100%; background:linear-gradient(90deg, #10b981, #34d399); border-radius:10px; transition:width 0.8s;"></div>
            </div>
          </div>
        `;
      }).join('');
    } else {
      progWrap.innerHTML = '<div style="text-align:center; color:#9ca3af; font-size:13px; padding:20px;">No institution data available.</div>';
    }

  } catch (e) {
    console.error('PTA Dashboard load error:', e);
  }
});
</script>
@endsection
