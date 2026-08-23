/**
 * dashboard.js — SecReCo · PTA Dashboard
 * Fetches real stats from API and renders all dynamic sections.
 */
'use strict';

// ── INIT ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  loadDashboard();

  // Search filter for CMI table
  const searchInput = document.querySelector('#cmiStatusCard .search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      document.querySelectorAll('#cmiStatusTbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
});

// ── LOAD DASHBOARD ────────────────────────────────────────────
async function loadDashboard() {
  const year = new Date().getFullYear();
  try {
    const res  = await fetch(`/api/pta/dashboard/get_stats.php?year=${year}`);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error ?? 'API error');

    renderStatCards(json.stats);
    renderSectionProgress(json.section_progress);
    renderTrendChart(json.trend_labels, json.trend_values);
    renderRecentActivity(json.recent_activity);
    renderCMITable(json.cmi_list);
  } catch (e) {
    console.error('[dashboard] Failed to load stats:', e);
    toast('⚠️ Could not load dashboard data.');
  }
}

let trendChartInst = null;
function renderTrendChart(labels, values) {
  const ctx = document.getElementById('trendChart');
  const parent = ctx?.parentElement;
  if (!ctx || !parent || typeof Chart === 'undefined') return;

  const hasData = values.some(v => v > 0);

  if (!hasData) {
    if (trendChartInst) { trendChartInst.destroy(); trendChartInst = null; }
    ctx.style.display = 'none';
    
    // Check if empty state already exists
    let emptyEl = document.getElementById('trendEmptyState');
    if (!emptyEl) {
      emptyEl = document.createElement('div');
      emptyEl.id = 'trendEmptyState';
      emptyEl.style.cssText = 'height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted)';
      emptyEl.innerHTML = `
        <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;opacity:0.5">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
        </svg>
        <div style="font-size:14px;font-weight:500">No activity in the last 14 days</div>
        <div style="font-size:12px">Waiting for CMI submissions</div>
      `;
      parent.appendChild(emptyEl);
    }
    emptyEl.style.display = 'flex';
    return;
  }

  // Has data, so ensure canvas is visible and empty state is hidden
  ctx.style.display = 'block';
  const emptyEl = document.getElementById('trendEmptyState');
  if (emptyEl) emptyEl.style.display = 'none';

  if (trendChartInst) {
    trendChartInst.destroy();
  }

  trendChartInst = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Tables Completed',
        data: values,
        borderColor: '#16a34a',
        backgroundColor: 'rgba(22, 163, 74, 0.1)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointBackgroundColor: '#16a34a',
        pointRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── STAT CARDS ────────────────────────────────────────────────
function renderStatCards(stats) {
  const submitted   = stats.submitted;
  const inProgress  = stats.in_progress;
  const notStarted  = stats.not_started;
  const total       = stats.total_cmis;

  setStatCard('scTotal',      total,        `${total} member institution${total !== 1 ? 's' : ''}`);
  setStatCard('scSubmitted',  submitted,    submitted === 1 ? '1 report submitted for review' : `${submitted} reports submitted`);
  setStatCard('scInProgress', inProgress,   inProgress === 1 ? '1 CMI still encoding' : `${inProgress} CMIs still encoding`);
  setStatCard('scNotStarted', notStarted,   notStarted === 0 ? 'All CMIs have started' : `${notStarted} need${notStarted === 1 ? 's' : ''} follow-up`);

  // Update pending badge in sidebar if any
  if (stats.pending_approvals > 0) {
    const badge = document.querySelector('.nav-item[href*="users"] .nav-badge');
    if (badge) badge.textContent = stats.pending_approvals;
  }
}

function setStatCard(id, value, meta) {
  const card = document.getElementById(id);
  if (!card) return;
  const valEl  = card.querySelector('.sc-val');
  const metaEl = card.querySelector('.sc-meta');
  if (valEl)  valEl.textContent  = value;
  if (metaEl) metaEl.textContent = meta;
}

// ── SECTION PROGRESS ──────────────────────────────────────────
function renderSectionProgress(sections) {
  const container = document.getElementById('sectionProgress');
  if (!container || !sections?.length) return;

  const colorFor = pct =>
    pct >= 80 ? 'var(--success)'  :
    pct >= 50 ? 'var(--green)'    :
    pct >= 30 ? 'var(--warning)'  : 'var(--danger)';

  container.innerHTML = sections.map(s => `
    <div class="prog-row">
      <span class="prog-label">${esc(s.label)}</span>
      <div class="prog-bar">
        <div class="prog-fill" style="width:${s.pct}%;background:${colorFor(s.pct)}"></div>
      </div>
      <span class="prog-pct" style="color:${colorFor(s.pct)}">${s.pct}%</span>
    </div>`).join('');
}

// ── RECENT ACTIVITY ───────────────────────────────────────────
function renderRecentActivity(activities) {
  const container = document.getElementById('recentActivity');
  if (!container) return;

  if (!activities?.length) {
    container.innerHTML = '<div class="feed-empty">No recent activity.</div>';
    return;
  }

  container.innerHTML = activities.map(a => {
    const timeStr = formatRelativeTime(a.created_at);
    return `
      <div class="feed-item">
        <div class="feed-dot" style="background:var(--success)22;color:var(--success)">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div>
          <div class="feed-content">${esc(a.description)}</div>
          <div class="feed-time">${timeStr}</div>
        </div>
      </div>`;
  }).join('');
}

// ── CMI STATUS TABLE ─────────────────────────────────────────
function renderCMITable(cmiList) {
  const tbody = document.getElementById('cmiStatusTbody');
  if (!tbody || !cmiList?.length) return;

  tbody.innerHTML = cmiList.map(cmi => {
    const badge = statusBadge(cmi.status);
    const isSubmitted = cmi.status === 'Submitted';
    const short = institutionShort(cmi.institution);
    const fullName = esc(cmi.institution ?? '—');
    const preview = fullName.length > 40 ? fullName.substring(0, 40) + '...' : fullName;

    return `
      <tr>
        <td>
          <strong>${esc(short)}</strong><br/>
          <span style="font-size:10px;color:var(--text-muted)">${preview}</span>
        </td>
        <td style="font-size:11px">${esc(cmi.tables_done)}/${cmi.total_tables}</td>
        <td>${badge}</td>
        <td style="font-size:11px">${esc(cmi.encoder ?? '—')}</td>
        <td>
          <button class="btn btn-xs btn-primary" onclick="navTo('submissions')">View</button>
          ${!isSubmitted
            ? `<button class="btn btn-xs" onclick="sendReminder('${esc(short)}')" style="margin-left:4px">Remind</button>`
            : ''}
        </td>
      </tr>`;
  }).join('');
}

// ── HELPERS ──────────────────────────────────────────────────
function statusBadge(status) {
  const map = {
    'Submitted':   '<span class="badge badge-blue">Submitted</span>',
    'In Progress': '<span class="badge badge-yellow">In Progress</span>',
    'Not Started': '<span class="badge badge-gray">Not Started</span>',
    'Returned':    '<span class="badge badge-orange">Returned</span>',
    'Accepted':    '<span class="badge badge-green">Accepted</span>',
  };
  return map[status] ?? map['Not Started'];
}

function institutionShort(name) {
  if (!name) return '—';
  // Use first letters of each significant word as abbreviation
  const skip = ['of','the','and','for','–','-','&'];
  const parts = name.split(/[\s–\-]+/);
  const abbr  = parts
    .filter(p => p.length > 1 && !skip.includes(p.toLowerCase()))
    .map(p => p[0].toUpperCase())
    .join('');
  return abbr || name.substring(0, 6).toUpperCase();
}

function formatRelativeTime(datetimeStr) {
  if (!datetimeStr) return '—';
  const date = new Date(datetimeStr);
  const now  = new Date();

  const dateDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const today   = new Date(now.getFullYear(),  now.getMonth(),  now.getDate());
  const diffDays = Math.round((today - dateDay) / 86400000);

  const diffMins = Math.floor((now - date) / 60000);
  const timeStr  = date.toLocaleTimeString('en-US', {hour:'numeric', minute:'2-digit'});

  if (diffMins < 2)   return 'Just now';
  if (diffMins < 60)  return `${diffMins} mins ago`;
  if (diffDays === 0) return `Today ${timeStr}`;
  if (diffDays === 1) return `Yesterday ${timeStr}`;
  return date.toLocaleDateString('en-US', {month:'short', day:'numeric'}) + ` ${timeStr}`;
}

function sendReminder(short) {
  toast(`📧 Reminder sent to ${short}`);
  // TODO: wire to actual email API
}

function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
