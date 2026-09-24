@extends('layouts.cmi')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/dashboard.css?v=20260905"/>
@endsection

@section('content')
<div class="page active" id="page-dashboard">
  <!-- ── DASHBOARD HEADER & YEAR FILTER ── -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div>
      <div style="font-size:22px;font-weight:700;color:#1e293b;letter-spacing:-0.01em">CMI Dashboard</div>
      <div id="dashYearSub" style="font-size:13px;color:#64748b;margin-top:2px">Overview of your institution's annual report progress for CY {{ date('Y') }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <label for="cmiDashYearFilter" style="font-size:12.5px;font-weight:600;color:#475569">Reporting Year:</label>
      <select id="cmiDashYearFilter" style="border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;font-size:13px;font-weight:600;color:#1e293b;background:#fff;cursor:pointer;outline:none;box-shadow:0 1px 2px rgba(0,0,0,0.05)">
        <option value="2026">CY 2026</option>
      </select>
    </div>
  </div>

  <div class="dashboard-grid">

    <!-- ── TOP ROW: 4 STAT CARDS ── -->
    <div class="stats-cards-grid">
      <!-- Tables Complete -->
      <div class="stat-card-fc cmi-kpi-complete">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Tables Complete</span>
          <div class="sc-fc-icon icon-green">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statComplete">—</div>
          <span class="sc-fc-badge up" id="statCompleteBadge">↑ 0%</span>
        </div>
        <span class="sc-fc-sub" id="statCompleteSub">of 24 required tables</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToYearPage('/dashboard/cmi/fillup'); return false;" class="sc-fc-link">
            <span>Fill out report</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- In Draft -->
      <div class="stat-card-fc cmi-kpi-draft">
        <div class="sc-fc-head">
          <span class="sc-fc-title">In Draft</span>
          <div class="sc-fc-icon icon-orange">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statDraft">—</div>
          <span class="sc-fc-badge up">Active</span>
        </div>
        <span class="sc-fc-sub">Saved, pending submit</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToYearPage('/dashboard/cmi/drafts'); return false;" class="sc-fc-link">
            <span>View drafts</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- Not Started -->
      <div class="stat-card-fc cmi-kpi-notstarted">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Not Started</span>
          <div class="sc-fc-icon icon-purple">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statNotStarted">—</div>
          <span class="sc-fc-badge down">To Begin</span>
        </div>
        <span class="sc-fc-sub">Tables remaining</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="goToYearPage('/dashboard/cmi/fillup'); return false;" class="sc-fc-link">
            <span>Start filling out</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <!-- For Correction -->
      <div class="stat-card-fc cmi-kpi-correction">
        <div class="sc-fc-head">
          <span class="sc-fc-title">For Correction</span>
          <div class="sc-fc-icon icon-blue">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statCorrection">—</div>
          <span class="sc-fc-badge up">Action Required</span>
        </div>
        <span class="sc-fc-sub" id="statCorrectionMeta">Check remarks</span>
        <div class="sc-fc-foot">
          <a href="#" onclick="openRemarksModal(); return false;" class="sc-fc-link">
            <span>View remarks</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- ── MIDDLE ROW: SECTION PROGRESS & RECENT ACTIVITY ── -->
    <div class="middle-charts-grid">
      <div class="card-fc">
        <div class="card-fc-header">
          <div class="card-fc-title">Section Progress</div>
          <span class="card-fc-dots">•••</span>
        </div>
        <div id="sectionProgress">
          <div class="loading-state" style="padding:16px;text-align:center;color:#6b7280;">Loading progress...</div>
        </div>
      </div>

      <div class="card-fc">
        <div class="card-fc-header">
          <div class="card-fc-title">Recent Activity</div>
          <span class="card-fc-dots">•••</span>
        </div>
        <div id="recentActivity">
          <div class="loading-state" style="padding:16px;text-align:center;color:#6b7280;">Loading activity...</div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- CORRECTION REMARKS MODAL -->
<div class="modal-overlay" id="modalRemarks" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);align-items:center;justify-content:center;z-index:9999;padding:16px;backdrop-filter:blur(2px);">
  <div class="modal-box" style="background:#fff;border-radius:14px;width:100%;max-width:680px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 48px rgba(0,0,0,.22);animation:modalSlideIn .18s ease-out;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:16px 22px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
      <div>
        <div style="font-weight:700;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px">
          <span style="font-size:18px">📝</span> Correction Remarks from PTA
        </div>
        <div id="remarksModalSub" style="font-size:12.5px;color:#64748b;margin-top:3px">Tables returned for revision</div>
      </div>
      <button type="button" onclick="closeRemarksModal()" style="border:none;background:none;font-size:24px;line-height:1;cursor:pointer;color:#64748b;padding:2px 6px;">&times;</button>
    </div>
    <div id="remarksModalBody" style="padding:20px 22px;overflow-y:auto;flex:1;">
      <!-- Populated dynamically -->
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 22px;border-top:1px solid #e2e8f0;background:#f8fafc;">
      <a href="#" onclick="closeRemarksModal(); goToYearPage('/dashboard/cmi/submissions', 'returned'); return false;" style="font-size:12.5px;font-weight:600;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
        View in Submissions Table &rarr;
      </a>
      <button type="button" class="btn btn-outline" onclick="closeRemarksModal()" style="padding:6px 16px;font-size:13px;border-radius:8px;border:1px solid #d1d5db;background:#fff;cursor:pointer;">Close</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let currentDashYear = 2026;
let currentDashCorrections = [];

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str).replace(/[&<>"']/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' })[m]);
}

function openRemarksModal() {
  const modal = document.getElementById('modalRemarks');
  if (!modal) return;
  const sub = document.getElementById('remarksModalSub');
  if (sub) sub.textContent = `Tables returned for revision in CY ${currentDashYear}`;

  const body = document.getElementById('remarksModalBody');
  if (!body) return;

  if (!currentDashCorrections || currentDashCorrections.length === 0) {
    body.innerHTML = `
      <div style="text-align:center;padding:36px 16px;color:#64748b;">
        <div style="font-size:40px;margin-bottom:10px">✅</div>
        <div style="font-weight:600;font-size:15px;color:#0f172a;margin-bottom:4px">No Open Corrections</div>
        <div style="font-size:13px">All submitted tables for CY ${currentDashYear} are accepted or under review.</div>
      </div>
    `;
  } else {
    body.innerHTML = currentDashCorrections.map(c => `
      <div style="background:#fff;border:1px solid #fee2e2;border-left:4px solid #ef4444;border-radius:8px;padding:14px 16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px;flex-wrap:wrap">
          <div>
            <span style="display:inline-block;background:#fee2e2;color:#991b1b;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;margin-right:6px">${escapeHtml(c.table_no)}</span>
            <strong style="font-size:13.5px;color:#0f172a">${escapeHtml(c.title)}</strong>
          </div>
          <span style="font-size:11.5px;color:#94a3b8">${escapeHtml(c.created_at || '')}</span>
        </div>
        <div style="background:#fef2f2;border-radius:6px;padding:10px 12px;font-size:13px;color:#7f1d1d;line-height:1.5;margin-bottom:10px">
          <strong>PTA Remarks:</strong> ${escapeHtml(c.reason || 'Please review and revise the submitted data.')}
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px">
          <a href="/dashboard/cmi/fillup?table=${encodeURIComponent(c.table_no)}&year=${currentDashYear}" class="btn btn-sm" style="background:#ef4444;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:12.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
            ✏️ Fix / Revise Table
          </a>
        </div>
      </div>
    `).join('');
  }

  modal.style.display = 'flex';
}

function closeRemarksModal() {
  const modal = document.getElementById('modalRemarks');
  if (modal) modal.style.display = 'none';
}

// Close remarks modal on backdrop click
document.getElementById('modalRemarks')?.addEventListener('click', function(e) {
  if (e.target === this) closeRemarksModal();
});

function getProgressColorSpec(pct) {
  if (pct === 0) return { fill: '#A63A2E', text: '#501313' };
  if (pct < 30) return { fill: '#8A9A5B', text: '#3f4b23' };
  if (pct < 60) return { fill: '#D4A017', text: '#412402' };
  if (pct < 90) return { fill: '#6B8E4E', text: '#2b421a' };
  return { fill: '#1F5C4A', text: '#173404' };
}

function goToYearPage(base, status) {
  const yr = document.getElementById('cmiDashYearFilter')?.value || currentDashYear;
  let url = `${base}?year=${yr}`;
  if (status) url += `&autoStatus=${encodeURIComponent(status)}`;
  window.location.href = url;
}

async function loadDashboard(year) {
  currentDashYear = year || document.getElementById('cmiDashYearFilter')?.value || 2026;
  const subEl = document.getElementById('dashYearSub');
  if (subEl) subEl.textContent = `Overview of your institution's annual report progress for CY ${currentDashYear}`;

  try {
    const res = await fetch(`/api/cmi/dashboard?year=${currentDashYear}`);
    const json = await res.json();
    currentDashCorrections = json.corrections || [];

    if (json.stats) {
      document.getElementById('statComplete').textContent    = json.stats.complete    ?? 0;
      document.getElementById('statDraft').textContent       = json.stats.draft       ?? 0;
      document.getElementById('statNotStarted').textContent  = json.stats.notStarted  ?? 0;
      document.getElementById('statCorrection').textContent  = json.stats.correction  ?? 0;
      if (document.getElementById('statCorrectionMeta')) {
        document.getElementById('statCorrectionMeta').textContent = json.stats.correctionMeta || 'Check remarks';
      }

      // Update 'of X required tables' dynamically
      const subElReq = document.getElementById('statCompleteSub');
      if (subElReq && json.stats.totalRequired) subElReq.textContent = 'of ' + json.stats.totalRequired + ' required tables';

      // Update % badge dynamically
      const total = json.stats.totalRequired || 1;
      const pct   = Math.round(((json.stats.complete || 0) / total) * 100);
      const badge = document.getElementById('statCompleteBadge');
      if (badge) badge.textContent = '↑ ' + pct + '%';
    }

    if (json.sectionProgress) {
      const spContainer = document.getElementById('sectionProgress');
      spContainer.innerHTML = json.sectionProgress.map(sp => {
        const color = getProgressColorSpec(sp.pct);
        return `
        <div class="progress-item">
          <div class="progress-info">
            <span class="progress-label">${sp.section}</span>
            <span class="progress-pct" style="color:${color.text}; font-weight:700;">${sp.done}/${sp.total} (${sp.pct}%)</span>
          </div>
          <div class="progress-track"><div class="progress-fill" style="width:${sp.pct}%; background:${color.fill};"></div></div>
        </div>
        `;
      }).join('');
    }

    if (json.recentActivity && json.recentActivity.length > 0) {
      const raContainer = document.getElementById('recentActivity');
      raContainer.innerHTML = json.recentActivity.map(act => `
        <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f3f4f6;font-size:13px">
          <div style="font-weight:500;color:#374151">${act.desc}</div>
          <div style="color:#9ca3af;font-size:11px">${act.timestamp ? act.timestamp.substring(0, 16) : ''}</div>
        </div>
      `).join('');
    } else {
      document.getElementById('recentActivity').innerHTML = `
        <div style="padding:24px 0;text-align:center;color:#9ca3af;font-size:13px">
          No recent activity yet for CY ${currentDashYear}.
        </div>
      `;
    }
  } catch (e) {
    console.error('CMI Dashboard load error:', e);
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
        const yearSel = document.getElementById('cmiDashYearFilter');
        if (yearSel) {
          yearSel.innerHTML = uniqueYears.map(y => `<option value="${y}" ${y === targetYear ? 'selected' : ''}>CY ${y}</option>`).join('');
        }
      }
      loadDashboard(targetYear);
    })
    .catch(() => loadDashboard(2026));

  document.getElementById('cmiDashYearFilter')?.addEventListener('change', function(e) {
    loadDashboard(parseInt(e.target.value) || 2026);
  });
});
</script>
@endsection
