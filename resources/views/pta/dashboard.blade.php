@extends('layouts.pta')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/dashboard.css?v=20260905"/>
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
      <div class="stat-card-fc pta-kpi-total">
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
      <div class="stat-card-fc pta-kpi-submitted">
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
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/submissions', 'submitted'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- In Progress -->
      <div class="stat-card-fc pta-kpi-inprogress">
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
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/submissions', 'in-progress'); return false;" class="sc-fc-link">
            <span>View details</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- Unstarted -->
      <div class="stat-card-fc pta-kpi-unstarted">
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
          <a href="#" onclick="goToPtaYearPage('/dashboard/pta/submissions', 'not-started'); return false;" class="sc-fc-link">
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
        <div style="height:250px; position:relative; padding:10px 0;">
          <canvas id="statusBarComparisonChart"></canvas>
        </div>
      </div>
    </div>

    <!-- ── BOTTOM ROW: DONUT CHART & INSTITUTION PROGRESS ── -->
    <div class="bottom-widgets-grid">
      <!-- Recent Activity Feed -->
      <div class="card-fc">
        <div class="card-fc-header">
          <div>
            <h2 class="card-fc-title">Recent Activity</h2>
            <p class="card-fc-sub">Latest submissions, reviews &amp; system updates</p>
          </div>
          <div class="section-badge-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="activity-feed-list" id="ptaRecentActivityList" style="max-height:330px;overflow-y:auto;padding:8px 4px;display:flex;flex-direction:column;gap:10px;">
          <div style="text-align:center; color:#74807c; font-size:12px; padding:30px;">Loading recent activities...</div>
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

function getProgressColorSpec(pct) {
  if (pct === 0) return { fill: '#d1d5db', text: '#6b7280' };
  if (pct < 30) return { fill: '#10b981', text: '#059669' };
  if (pct < 60) return { fill: '#059669', text: '#047857' };
  if (pct < 90) return { fill: '#047857', text: '#064e3b' };
  return { fill: '#034a36', text: '#022c20' };
}

function goToPtaYearPage(base, status) {
  const yr = document.getElementById('ptaDashYearFilter')?.value || currentPtaDashYear;
  let url = `${base}?year=${yr}`;
  if (status) url += `&autoStatus=${encodeURIComponent(status)}`;
  window.location.href = url;
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

    // 1. Line Growth Trend Chart with 3 lines: Submitted (Green), Accepted (Yellow), Returned (Red)
    const monthlyLabels    = json.monthly_labels    || ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const monthlySubmitted = json.monthly_submitted || new Array(12).fill(0);
    const monthlyAccepted  = json.monthly_accepted  || new Array(12).fill(0);
    const monthlyReturned  = json.monthly_returned  || new Array(12).fill(0);

    const quarterlyLabels    = json.quarterly_labels    || ['Q1 (Jan–Mar)', 'Q2 (Apr–Jun)', 'Q3 (Jul–Sep)', 'Q4 (Oct–Dec)'];
    const quarterlySubmitted = json.quarterly_submitted || [0, 0, 0, 0];
    const quarterlyAccepted  = json.quarterly_accepted  || [0, 0, 0, 0];
    const quarterlyReturned  = json.quarterly_returned  || [0, 0, 0, 0];

    const annualLabels    = json.annual_labels    || ['2022','2023','2024','2025','2026'];
    const annualSubmitted = json.annual_submitted || [0,0,0,0,0];
    const annualAccepted  = json.annual_accepted  || [0,0,0,0,0];
    const annualReturned  = json.annual_returned  || [0,0,0,0,0];

    function buildTrendChart(labels, subVals, accVals, retVals) {
      const ctx = document.getElementById('growthTrendChart')?.getContext('2d');
      if (!ctx) return;

      if (trendChartInst) trendChartInst.destroy();
      trendChartInst = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Submitted',
              data: subVals,
              borderColor: '#10b981', // Green
              backgroundColor: 'rgba(16, 185, 129, 0.08)',
              borderWidth: 2.5,
              fill: false,
              tension: 0.35,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#10b981',
              pointBorderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6
            },
            {
              label: 'Accepted',
              data: accVals,
              borderColor: '#eab308', // Yellow
              backgroundColor: 'rgba(234, 179, 8, 0.08)',
              borderWidth: 2.5,
              fill: false,
              tension: 0.35,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#eab308',
              pointBorderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6
            },
            {
              label: 'Returned',
              data: retVals,
              borderColor: '#ef4444', // Red
              backgroundColor: 'rgba(239, 68, 68, 0.08)',
              borderWidth: 2.5,
              fill: false,
              tension: 0.35,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#ef4444',
              pointBorderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
              align: 'end',
              labels: {
                boxWidth: 10,
                boxHeight: 10,
                usePointStyle: true,
                pointStyle: 'circle',
                font: { family: 'DM Sans', size: 11, weight: '600' },
                color: '#374151',
                padding: 12
              }
            },
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

      if (period === 'monthly')   buildTrendChart(monthlyLabels, monthlySubmitted, monthlyAccepted, monthlyReturned);
      if (period === 'quarterly') buildTrendChart(quarterlyLabels, quarterlySubmitted, quarterlyAccepted, quarterlyReturned);
      if (period === 'annually')  buildTrendChart(annualLabels, annualSubmitted, annualAccepted, annualReturned);
    };

    filterTrend('monthly');

    // 2. Submissions Status Comparison (Bar Graph)
    const ctxBar = document.getElementById('statusBarComparisonChart')?.getContext('2d');
    if (ctxBar) {
      if (barChartInst) barChartInst.destroy();
      const s = stats;
      const barLabels = ['Submitted', 'Accepted', 'In Progress', 'Returned', 'Unstarted'];
      const barData   = [
        s.submitted || 0,
        s.accepted || 0,
        s.in_progress || 0,
        s.returned || 0,
        s.not_started || 0
      ];
      const barColors = ['#10b981', '#eab308', '#3b82f6', '#ef4444', '#9ca3af'];

      barChartInst = new Chart(ctxBar, {
        type: 'bar',
        data: {
          labels: barLabels,
          datasets: [{
            label: 'Reports / CMIs',
            data: barData,
            backgroundColor: barColors,
            borderRadius: 6,
            barThickness: 28,
            maxBarThickness: 36
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
              ticks: { color: '#374151', font: { family: 'DM Sans', size: 11, weight: '600' } }
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

    // 3. Recent Consortium Activity Feed
    const actWrap = document.getElementById('ptaRecentActivityList');
    if (actWrap) {
      const activities = json.recent_activity || [];
      if (activities.length > 0) {
        actWrap.innerHTML = activities.map(act => {
          const timeAgo = act.created_at ? new Date(act.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Recently';
          return `
            <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 13px;background:#fbfcfb;border:1px solid #edf0ee;border-radius:10px;transition:background 0.15s ease;">
              <div style="width:30px;height:30px;border-radius:50%;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#1f2937;line-height:1.45;word-break:break-word;">${act.description || 'System event recorded'}</div>
                <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                  <span style="font-size:10.5px;color:#9ca3af;">${timeAgo}</span>
                  <span style="font-size:10.5px;color:#d1d5db;">•</span>
                  <span style="font-size:10.5px;color:#059669;font-weight:600;">${act.actor || 'System'}</span>
                </div>
              </div>
            </div>
          `;
        }).join('');
      } else {
        actWrap.innerHTML = '<div style="text-align:center; color:#74807c; font-size:12px; padding:30px;">No recent activities for CY ' + currentPtaDashYear + '.</div>';
      }
    }

    // 4. Real Institution Progress Bars - Dynamic SecReCo Percentage Colors
    const progWrap = document.getElementById('institutionProgressList');
    if (progWrap) {
      const cmiList = json.cmi_status_list || json.cmi_list || json.institution_progress || [];
      if (cmiList && cmiList.length > 0) {
        progWrap.innerHTML = cmiList.map(item => {
          const pct = item.total_tables > 0 ? Math.round((item.tables_done / item.total_tables) * 100) : (item.pct || 0);
          const color = getProgressColorSpec(pct);
          return `
            <div class="progress-item" style="margin-bottom:12px;">
              <div class="progress-info">
                <span class="progress-label" title="${item.institution}" style="font-weight:600;font-size:12px;color:#374151;">${item.institution}</span>
                <span class="progress-pct" style="color:${color.text}; font-weight:700;font-size:11.5px;">${item.tables_done}/${item.total_tables} (${pct}%)</span>
              </div>
              <div class="progress-track" style="height:9px;background:#e5e7eb !important;border-radius:20px;overflow:hidden;position:relative;margin-top:5px;box-shadow:inset 0 1px 2px rgba(0,0,0,0.06);">
                <div class="progress-fill" style="height:100%;border-radius:20px;width:${pct}%;background:${color.fill};transition:width 0.8s cubic-bezier(0.4,0,0.2,1);"></div>
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
