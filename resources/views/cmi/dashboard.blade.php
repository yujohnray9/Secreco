@extends('layouts.cmi')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/dashboard.css?v=3"/>
@endsection

@section('content')
<div class="page active" id="page-dashboard">
  <div class="dashboard-grid">

    <!-- ── TOP ROW: 4 STAT CARDS ── -->
    <div class="stats-cards-grid">
      <!-- Tables Complete -->
      <div class="stat-card-fc">
        <div class="sc-fc-head">
          <span class="sc-fc-title">Tables Complete</span>
          <div class="sc-fc-icon icon-green">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
        <div class="sc-fc-body">
          <div class="sc-fc-val" id="statComplete">—</div>
          <span class="sc-fc-badge up">↑ 100%</span>
        </div>
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">of 20 required tables</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/cmi/fillup'">→</button>
        </div>
      </div>

      <!-- In Draft -->
      <div class="stat-card-fc">
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
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Saved, pending submit</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/cmi/drafts'">→</button>
        </div>
      </div>

      <!-- Not Started -->
      <div class="stat-card-fc">
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
        <div class="sc-fc-foot">
          <span class="sc-fc-sub">Tables remaining</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/cmi/fillup'">→</button>
        </div>
      </div>

      <!-- For Correction -->
      <div class="stat-card-fc">
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
        <div class="sc-fc-foot">
          <span class="sc-fc-sub" id="statCorrectionMeta">Check remarks</span>
          <button class="sc-fc-arrow" onclick="window.location.href='/dashboard/cmi/submissions'">→</button>
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
  try {
    const res = await fetch('/api/cmi/dashboard');
    const json = await res.json();

    if (json.stats) {
      document.getElementById('statComplete').textContent    = json.stats.complete    ?? 0;
      document.getElementById('statDraft').textContent       = json.stats.draft       ?? 0;
      document.getElementById('statNotStarted').textContent  = json.stats.notStarted  ?? 0;
      document.getElementById('statCorrection').textContent  = json.stats.correction  ?? 0;
      if (document.getElementById('statCorrectionMeta')) {
        document.getElementById('statCorrectionMeta').textContent = json.stats.correctionMeta || 'Check remarks';
      }

      // Update 'of X required tables' dynamically
      const subEl = document.querySelector('.sc-fc-sub');
      if (subEl && json.stats.totalRequired) subEl.textContent = 'of ' + json.stats.totalRequired + ' required tables';

      // Update % badge dynamically
      const total = json.stats.totalRequired || 1;
      const pct   = Math.round(((json.stats.complete || 0) / total) * 100);
      const badge = document.querySelector('.sc-fc-badge.up');
      if (badge) badge.textContent = '↑ ' + pct + '%';
    }

    if (json.sectionProgress) {
      const spContainer = document.getElementById('sectionProgress');
      spContainer.innerHTML = json.sectionProgress.map(sp => `
        <div class="progress-item">
          <div class="progress-info">
            <span class="progress-label">${sp.section}</span>
            <span class="progress-pct">${sp.done}/${sp.total} (${sp.pct}%)</span>
          </div>
          <div class="progress-track"><div class="progress-fill" style="width:${sp.pct}%;"></div></div>
        </div>
      `).join('');
    } else {
      document.getElementById('sectionProgress').innerHTML = `
        <div class="progress-item">
          <div class="progress-info"><span class="progress-label">Research & Development</span><span class="progress-pct">50%</span></div>
          <div class="progress-track"><div class="progress-fill" style="width:50%;"></div></div>
        </div>
        <div class="progress-item">
          <div class="progress-info"><span class="progress-label">Technology Transfer</span><span class="progress-pct">25%</span></div>
          <div class="progress-track"><div class="progress-fill" style="width:25%;"></div></div>
        </div>
        <div class="progress-item">
          <div class="progress-info"><span class="progress-label">Publications & IP</span><span class="progress-pct">0%</span></div>
          <div class="progress-track"><div class="progress-fill" style="width:0%;"></div></div>
        </div>
      `;
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
          No recent activity yet. Start by filling up your first table.
        </div>
      `;
    }
  } catch (e) {
    console.error('CMI Dashboard load error:', e);
  }
});
</script>
@endsection
