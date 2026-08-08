/**
 * dashboard.js
 * Path: /assets/js/cmi/dashboard.js
 */

(function () {
  'use strict';

  // ── Inject required styles ───────────────────────────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    /* ── Section Progress ── */
    .prog-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .prog-row:last-child { border-bottom: none; }
    .prog-label {
      flex: 0 0 180px;
      font-size: 13px;
      color: #444;
      white-space: nowrap;
    }
    .prog-track {
      flex: 1;
      height: 8px;
      background: #e9ecef;
      border-radius: 99px;
      overflow: hidden;
    }
    .prog-bar {
      height: 100%;
      border-radius: 99px;
      transition: width 0.5s ease;
    }
    .prog-bar-green  { background: #3d7a3f; }
    .prog-bar-orange { background: #EF9F27; }
    .prog-bar-grey   { background: #E24B4A; }
    .prog-pct {
      flex: 0 0 36px;
      font-size: 13px;
      font-weight: 600;
      text-align: right;
      color: #2d7a3a;
    }
    .prog-pct.pct-zero { color: #aaa; }
    .no-data { color: #aaa; font-size: 13px; padding: 12px 0; }

    /* ── Recent Activity ── */
    .act-row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .act-row:last-child { border-bottom: none; }
    .act-icon {
      flex: 0 0 32px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .act-icon svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .act-icon-submitted { background: #d1fae5; color: #065f46; }
    .act-icon-draft     { background: #fef3c7; color: #92400e; }
    .act-icon-flagged   { background: #dbeafe; color: #1e40af; }
    .act-icon-started   { background: #ede9fe; color: #5b21b6; }
    .act-body { flex: 1; min-width: 0; }
    .act-desc { font-size: 13px; color: #333; line-height: 1.4; }
    .act-desc strong { font-weight: 600; }
    .act-time { font-size: 11px; color: #999; margin-top: 2px; }
  `;
  document.head.appendChild(style);

  // ── Helpers ──────────────────────────────────────────────────────────────────
  function formatTimestamp(ts) {
    if (!ts) return '';
    const date    = new Date(ts.replace(' ', 'T'));
    const now     = new Date();
    const today   = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const d       = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const timeStr = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    const diff    = (today - d) / 86400000;
    if (diff === 0) return 'Today ' + timeStr;
    if (diff === 1) return 'Yesterday ' + timeStr;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  }

  function activityIcon(type) {
    const icons = {
      submitted: {
        cls: 'act-icon-submitted',
        svg: '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
      },
      draft: {
        cls: 'act-icon-draft',
        svg: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
      },
      flagged: {
        cls: 'act-icon-flagged',
        svg: '<svg viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>',
      },
      started: {
        cls: 'act-icon-started',
        svg: '<svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
      },
    };
    const icon = icons[type] || icons.started;
    return `<div class="act-icon ${icon.cls}">${icon.svg}</div>`;
  }

  // ── DOM refs ─────────────────────────────────────────────────────────────────
  const elComplete       = document.getElementById('statComplete');
  const elDraft          = document.getElementById('statDraft');
  const elNotStarted     = document.getElementById('statNotStarted');
  const elCorrection     = document.getElementById('statCorrection');
  const elCorrMeta       = document.getElementById('statCorrectionMeta');
  const elDaysLeft       = document.getElementById('daysLeft');
  const elDeadlineDate   = document.querySelector('.ctx-deadline-date');
  const elSectionProg    = document.getElementById('sectionProgress');
  const elRecentActivity = document.getElementById('recentActivity');

  // ── Fetch ────────────────────────────────────────────────────────────────────
  fetch('/api/cmi/dashboard', { credentials: 'same-origin' })
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (data) {
      renderStats(data.stats);
      renderDeadline(data.deadline);
      renderSectionProgress(data.sectionProgress);
      renderRecentActivity(data.recentActivity);
    })
    .catch(function (err) {
      console.error('[Dashboard] Failed to load data:', err);
      [elComplete, elDraft, elNotStarted, elCorrection].forEach(function (el) {
        if (el) el.textContent = '—';
      });
    });

  // ── Renderers ────────────────────────────────────────────────────────────────
  function renderStats(stats) {
    if (elComplete)   elComplete.textContent   = stats.complete;
    if (elDraft)      elDraft.textContent      = stats.draft;
    if (elNotStarted) elNotStarted.textContent = stats.notStarted;
    if (elCorrection) elCorrection.textContent = stats.correction;
    if (elCorrMeta)   elCorrMeta.textContent   = stats.correctionMeta;
    const complMeta = document.querySelector('.sc-green .sc-meta');
    if (complMeta) complMeta.textContent = 'of ' + stats.totalRequired + ' required';
  }

  function renderDeadline(deadline) {
    if (elDaysLeft && deadline.daysLeft !== undefined)
      elDaysLeft.textContent = deadline.daysLeft;
    if (elDeadlineDate && deadline.date)
      elDeadlineDate.textContent = deadline.date;
  }

  function renderSectionProgress(sections) {
    if (!elSectionProg) return;
    if (!sections || sections.length === 0) {
      elSectionProg.innerHTML = '<p class="no-data">No progress data available.</p>';
      return;
    }
    elSectionProg.innerHTML = sections.map(function (sec) {
      const isZero   = sec.pct === 0;
      const barColor = isZero ? '#E24B4A' : sec.pct < 60 ? '#EF9F27' : '#3d7a3f';
      const barWidth = isZero ? '6px' : sec.pct + '%';
      const pctColor = isZero ? '#E24B4A' : sec.pct < 60 ? '#b97300' : '#3d7a3f';
      return `
        <div class="prog-row">
          <span class="prog-label">${sec.section}</span>
          <div class="prog-track">
            <div class="prog-bar" style="width:${barWidth};background:${barColor};${isZero?'opacity:0.5':''}"></div>
          </div>
          <span class="prog-pct" style="color:${pctColor}">${sec.pct}%</span>
        </div>`;
    }).join('');
  }

  function renderRecentActivity(activities) {
    if (!elRecentActivity) return;
    if (!activities || activities.length === 0) {
      elRecentActivity.innerHTML = '<p class="no-data">No recent activity.</p>';
      return;
    }
    elRecentActivity.innerHTML = activities.map(function (act) {
      return `
        <div class="act-row">
          ${activityIcon(act.icon)}
          <div class="act-body">
            <div class="act-desc">${act.desc}</div>
            <div class="act-time">${formatTimestamp(act.timestamp)}</div>
          </div>
        </div>`;
    }).join('');
  }

})();
