@extends('layouts.pta')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/dashboard.css?v=20260903"/>
@endsection

@section('content')
<div class="page active" id="page-dashboard">
  <!-- ── DASHBOARD HEADER & YEAR FILTER ── -->
  <div class="dash-page-heading">
    <div>
      <h1>PTA Dashboard</h1>
      <p id="ptaDashYearSub">Consortium progress and submissions overview for CY {{ date('Y') }}.</p>
    </div>
    <div class="year-control-pill">
      <span>Reporting Year:</span>
      <select id="ptaDashYearFilter">
        <option value="2026">CY 2026</option>
      </select>
    </div>
  </div>

  <div class="dashboard-grid">

    <!-- ── TOP ROW: 4 STAT / KPI CARDS ── -->
    <div class="stats-cards-grid">
      <!-- Total CMIs -->
      <div class="stat-card-fc kpi-green">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Total CMIs</span>
          <div class="sc-fc-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statTotalCmis">—</div>
          <span class="sc-fc-badge badge-mint">Region 2</span>
        </div>
        <span class="sc-fc-sub">Member institutions</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/institutions'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- Submitted -->
      <div class="stat-card-fc kpi-green">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Submitted</span>
          <div class="sc-fc-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <polyline points="9 15 11 17 15 13"/>
            </svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statSubmitted">—</div>
          <span class="sc-fc-badge badge-mint">Reports</span>
        </div>
        <span class="sc-fc-sub">Fully submitted reports</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/submissions'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- In Progress -->
      <div class="stat-card-fc kpi-gold">
        <div class="sc-fc-head">
          <span class="sc-fc-title">In Progress</span>
          <div class="sc-fc-icon icon-gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="21" x2="4" y2="14"/>
              <line x1="4" y1="10" x2="4" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="12"/>
              <line x1="12" y1="8" x2="12" y2="3"/>
              <line x1="20" y1="21" x2="20" y2="16"/>
              <line x1="20" y1="12" x2="20" y2="3"/>
              <line x1="1" y1="14" x2="7" y2="14"/>
              <line x1="9" y1="8" x2="15" y2="8"/>
              <line x1="17" y1="16" x2="23" y2="16"/>
            </svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statInProgress">—</div>
          <span class="sc-fc-badge badge-gold">Active</span>
        </div>
        <span class="sc-fc-sub">Encoding or drafting</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/submissions'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- Unstarted -->
      <div class="stat-card-fc kpi-orange">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Unstarted</span>
          <div class="sc-fc-icon icon-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statNotStarted">—</div>
          <span class="sc-fc-badge badge-orange">Pending</span>
        </div>
        <span class="sc-fc-sub">No reports submitted yet</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/institutions'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- ── MIDDLE ROW: LINE CHART & BAR CHART ── -->
    <div class="middle-charts-grid">
      <!-- Submission Activity Trends (Line Chart) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <h2 class="card-fc-title">Submission Activity Trends</h2>
            <p class="card-fc-sub" id="trendSubtitle">Last 7 days activity</p>
          </div>
          <!-- Monthly / Quarterly / Annually switcher -->
          <div class="period-switch">
            <button id="btnM" class="selected" onclick="filterTrend('monthly')">Monthly</button>
            <button id="btnQ" onclick="filterTrend('quarterly')">Quarterly</button>
            <button id="btnA" onclick="filterTrend('annually')">Annually</button>
          </div>
        </div>
        <div style="height:250px; position:relative;">
          <canvas id="growthTrendChart"></canvas>
        </div>
      </div>

      <!-- Submissions Status Comparison -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <h2 class="card-fc-title">Submissions Status Comparison</h2>
            <p class="card-fc-sub">Current reports by status</p>
          </div>
          <div class="section-badge-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
        </div>
        <div class="submissions-status-bars" id="submissionsStatusBars">
          <div class="status-bar-row">
            <div class="status-bar-meta">
              <span class="status-bar-label">Submitted</span>
              <strong class="status-bar-count" id="barCountSubmitted">0</strong>
            </div>
            <div class="status-bar-track">
              <div class="status-bar-fill bar-fill-submitted" id="barFillSubmitted" style="width: 0%"></div>
            </div>
          </div>
          <div class="status-bar-row">
            <div class="status-bar-meta">
              <span class="status-bar-label">In Progress</span>
              <strong class="status-bar-count" id="barCountInProgress">0</strong>
            </div>
            <div class="status-bar-track">
              <div class="status-bar-fill bar-fill-inprogress" id="barFillInProgress" style="width: 0%"></div>
            </div>
          </div>
          <div class="status-bar-row">
            <div class="status-bar-meta">
              <span class="status-bar-label">Accepted</span>
              <strong class="status-bar-count" id="barCountAccepted">0</strong>
            </div>
            <div class="status-bar-track">
              <div class="status-bar-fill bar-fill-accepted" id="barFillAccepted" style="width: 0%"></div>
            </div>
          </div>
          <div class="status-bar-row">
            <div class="status-bar-meta">
              <span class="status-bar-label">Returned</span>
              <strong class="status-bar-count" id="barCountReturned">0</strong>
            </div>
            <div class="status-bar-track">
              <div class="status-bar-fill bar-fill-returned" id="barFillReturned" style="width: 0%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── BOTTOM ROW: DONUT CHART & INSTITUTION PROGRESS ── -->
    <div class="bottom-widgets-grid">
      <!-- Status Distribution (Donut Chart) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <h2 class="card-fc-title">Status Distribution</h2>
            <p class="card-fc-sub">Proportion of submissions by status</p>
          </div>
          <div class="section-badge-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </div>
        </div>
        <div class="donut-layout">
          <div class="donut-chart-wrap">
            <canvas id="statusDonutChart"></canvas>
          </div>
          <div class="donut-legend" id="donutLegendList">
            <!-- Populated dynamically -->
          </div>
        </div>
      </div>

      <!-- Institution Accomplishments (Horizontal Progress Bars) -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <h2 class="card-fc-title">Institution Progress Overview</h2>
            <p class="card-fc-sub">Submission progress by institution</p>
          </div>
          <div class="section-badge-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>
        <div class="institution-progress-list" id="institutionProgressList">
          <div style="text-align:center; color:#74807c; font-size:12px; padding:30px;">Loading institution progress...</div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPtaDashYear = 2026;
let trendChartInst = null;
let barChartInst = null;
let donutChartInst = null;

function goToPtaYearPage(base) {
  const yr = document.getElementById('ptaDashYearFilter')?.value || currentPtaDashYear;
  window.location.href = `${base}?year=${yr}`;
}

async function loadPtaDashboard(year) {
  currentPtaDashYear = year || document.getElementById('ptaDashYearFilter')?.value || 2026;
  const subEl = document.getElementById('ptaDashYearSub');
  if (subEl) subEl.textContent = `Consortium progress and submissions overview for CY ${currentPtaDashYear}.`;

  try {
    const res = await fetch(`/api/pta/dashboard/stats?year=${currentPtaDashYear}`);
    const json = await res.json();

    const stats = json.stats || {};
    document.getElementById('statTotalCmis').textContent   = stats.total_cmis !== undefined ? stats.total_cmis : '0';
    document.getElementById('statSubmitted').textContent   = stats.submitted !== undefined ? stats.submitted : '0';
    document.getElementById('statInProgress').textContent  = stats.in_progress !== undefined ? stats.in_progress : '0';
    document.getElementById('statNotStarted').textContent  = stats.not_started !== undefined ? stats.not_started : '0';

    // 1. Line Growth Trend Chart
    const monthlyLabels   = json.monthly_labels   || ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const monthlyValues   = json.monthly_values   || [0,0,0,0,0,0,0,0,0,0,0,0];
    const quarterlyLabels = json.quarterly_labels || ['Q1 (Jan–Mar)', 'Q2 (Apr–Jun)', 'Q3 (Jul–Sep)', 'Q4 (Oct–Dec)'];
    const quarterlyValues = json.quarterly_values || [0, 0, 0, 0];
    const annualLabels    = json.annual_labels    || ['2022','2023','2024','2025','2026'];
    const annualValues    = json.annual_values    || [0,0,0,0,0];

    function buildTrendChart(labels, values) {
      const ctx = document.getElementById('growthTrendChart')?.getContext('2d');
      if (!ctx) return;
      const grad = ctx.createLinearGradient(0, 0, 0, 240);
      grad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
      grad.addColorStop(1, 'rgba(16, 185, 129, 0.01)');
      if (trendChartInst) trendChartInst.destroy();
      trendChartInst = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Submissions',
            data: values,
            borderColor: '#10b981',
            borderWidth: 2.5,
            backgroundColor: grad,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#10b981',
            pointBorderWidth: 2.5,
            pointRadius: 4.5,
            pointHoverRadius: 6.5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#16352d',
              titleFont: { family: 'DM Sans', size: 12 },
              bodyFont: { family: 'DM Sans', size: 12 },
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: '#74807c', font: { family: 'DM Sans', size: 10.5 } }
            },
            y: {
              grid: { color: '#edf1ef' },
              ticks: { precision: 0, color: '#74807c', font: { family: 'DM Sans', size: 10.5 } },
              beginAtZero: true
            }
          }
        }
      });
    }

    window.filterTrend = function(period) {
      const btns = { monthly:'btnM', quarterly:'btnQ', annually:'btnA' };
      Object.values(btns).forEach(id => {
        const b = document.getElementById(id);
        if (b) b.classList.remove('selected');
      });
      const active = document.getElementById(btns[period]);
      if (active) active.classList.add('selected');

      const subs = {
        monthly: `Monthly trends CY ${currentPtaDashYear}`,
        quarterly: `Quarterly trends CY ${currentPtaDashYear}`,
        annually: `Annual accomplishments (${annualLabels[0]}–${annualLabels[annualLabels.length-1]})`
      };
      const el = document.getElementById('trendSubtitle');
      if (el) el.textContent = subs[period] || '';

      if (period === 'monthly')   buildTrendChart(monthlyLabels, monthlyValues);
      if (period === 'quarterly') buildTrendChart(quarterlyLabels, quarterlyValues);
      if (period === 'annually')  buildTrendChart(annualLabels, annualValues);
    };

    filterTrend('monthly');

    // 2. Status Comparison (HTML Progress Bars matching Manus design)
    const s = stats;
    const cSub = s.submitted || 0;
    const cProg = s.in_progress || 0;
    const cAcc = s.accepted || 0;
    const cRet = s.returned || 0;
    const maxStatusVal = Math.max(cSub, cProg, cAcc, cRet, 1);

    const elSub = document.getElementById('barCountSubmitted');
    const elProg = document.getElementById('barCountInProgress');
    const elAcc = document.getElementById('barCountAccepted');
    const elRet = document.getElementById('barCountReturned');
    if (elSub) elSub.textContent = cSub;
    if (elProg) elProg.textContent = cProg;
    if (elAcc) elAcc.textContent = cAcc;
    if (elRet) elRet.textContent = cRet;

    const fillSub = document.getElementById('barFillSubmitted');
    const fillProg = document.getElementById('barFillInProgress');
    const fillAcc = document.getElementById('barFillAccepted');
    const fillRet = document.getElementById('barFillReturned');
    if (fillSub) fillSub.style.width = Math.round((cSub / maxStatusVal) * 100) + '%';
    if (fillProg) fillProg.style.width = Math.round((cProg / maxStatusVal) * 100) + '%';
    if (fillAcc) fillAcc.style.width = Math.round((cAcc / maxStatusVal) * 100) + '%';
    if (fillRet) fillRet.style.width = Math.round((cRet / maxStatusVal) * 100) + '%';

    // 3. Donut Chart (Status Breakdown) & Custom Legend
    const ctxDonut = document.getElementById('statusDonutChart')?.getContext('2d');
    if (ctxDonut) {
      if (donutChartInst) donutChartInst.destroy();
      const s = stats;
      const dData = [
        s.submitted || 0,
        s.accepted || 0,
        s.in_progress || 0,
        s.returned || 0,
        s.not_started || 0
      ];
      const dLabels = ['Submitted', 'Accepted', 'In Progress', 'Returned', 'Unstarted'];
      const dColors = ['#1aaa80', '#075b42', '#d8a520', '#df443d', '#dfe4e1'];

      donutChartInst = new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
          labels: dLabels,
          datasets: [{
            data: dData,
            backgroundColor: dColors,
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '72%',
          plugins: {
            legend: { display: false }
          }
        }
      });

      // Populate custom legend matching Manus
      const legendWrap = document.getElementById('donutLegendList');
      if (legendWrap) {
        legendWrap.innerHTML = dLabels.map((lbl, i) => `
          <div class="donut-legend-row">
            <div class="donut-legend-label">
              <span class="donut-legend-dot" style="background:${dColors[i]}"></span>
              <span>${lbl}</span>
            </div>
            <strong>${dData[i]}</strong>
          </div>
        `).join('');
      }
    }

    // 4. Real Institution Progress Bars
    const progWrap = document.getElementById('institutionProgressList');
    if (progWrap) {
      const cmiList = json.cmi_status_list || json.cmi_list || [];
      if (cmiList && cmiList.length > 0) {
        progWrap.innerHTML = cmiList.map(item => {
          const pct = item.total_tables > 0 ? Math.round((item.tables_done / item.total_tables) * 100) : 0;
          return `
            <div class="progress-item">
              <div class="progress-info">
                <span class="progress-label" title="${item.institution}">${item.institution}</span>
                <span class="progress-pct">${item.tables_done}/${item.total_tables} (${pct}%)</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: ${pct}%;"></div>
              </div>
            </div>
          `;
        }).join('');
      } else {
        progWrap.innerHTML = '<div style="text-align:center; color:#74807c; font-size:12px; padding:30px;">No institution data available for this year.</div>';
      }
    }

  } catch (e) {
    console.error('PTA Dashboard load error:', e);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  fetch('/api/formats')
    .then(r => r.json())
    .then(data => {
      const urlParams = new URLSearchParams(window.location.search);
      const urlYear   = urlParams.get('year') ? parseInt(urlParams.get('year')) : null;
      const currentYear = new Date().getFullYear();

      let targetYear = urlYear || 2026;
      if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
        const uniqueYears = [...new Set(data.years.map(Number))].sort((a,b) => b - a);
        if (!uniqueYears.includes(targetYear)) {
          targetYear = uniqueYears.includes(currentYear) ? currentYear : (data.active_year || uniqueYears[0]);
        }
        const yearSel = document.getElementById('ptaDashYearFilter');
        if (yearSel) {
          yearSel.innerHTML = uniqueYears.map(y => `<option value="${y}" ${y === targetYear ? 'selected' : ''}>CY ${y}</option>`).join('');
        }
      }
      loadPtaDashboard(targetYear);
    })
    .catch(() => loadPtaDashboard(2026));

  document.getElementById('ptaDashYearFilter')?.addEventListener('change', function(e) {
    loadPtaDashboard(parseInt(e.target.value) || 2026);
  });
});
</script>
@endsection
