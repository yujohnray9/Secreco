@extends('layouts.cmi')

@section('styles')
<style>
:root {
  --forest: #005b45;
  --forest-dark: #004534;
  --forest-light: #e9f7f1;
  --ink: #111827;
  --muted: #64748b;
  --line: #e2e8f0;
  --card-border: #e6ece8;
  --white: #ffffff;
  --danger: #dc2626;
  --danger-bg: #fff5f5;
  --danger-border: #fecaca;
  --info: #2563eb;
  --info-bg: #eef7ff;
  --info-border: #bfdbfe;
  --success: #059669;
  --success-bg: #ecfdf5;
  --success-border: #bbf7d0;
  --radius: 10px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
}

.cmi-notif-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
  min-height: calc(100vh - 120px);
  align-items: start;
  transition: grid-template-columns 0.2s ease;
}

.cmi-notif-layout.has-detail {
  grid-template-columns: minmax(0, 1fr) 390px;
}

/* ── Main Left Section ── */
.cmi-notif-main {
  min-width: 0;
}

.cmi-header h1 {
  font-size: 30px;
  font-weight: 800;
  color: #0d2b22;
  letter-spacing: -0.03em;
  margin: 0 0 6px;
}

.cmi-header p {
  font-size: 14px;
  color: #556760;
  margin: 0 0 20px;
}

/* ── Tabs (with counts) ── */
.cmi-tabs {
  display: flex;
  align-items: center;
  gap: 4px;
  border-bottom: 1px solid var(--line);
  margin-bottom: 20px;
  overflow-x: auto;
}

.cmi-tab {
  border: 0;
  background: transparent;
  padding: 11px 15px;
  font-size: 13.5px;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  border-bottom: 2.5px solid transparent;
  white-space: nowrap;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.cmi-tab:hover {
  color: var(--forest);
}

.cmi-tab.active {
  color: var(--forest);
  font-weight: 700;
  border-bottom-color: var(--forest);
}

.cmi-tab-badge {
  font-size: 11.5px;
  padding: 1px 7px;
  border-radius: 12px;
  background: #f1f5f3;
  color: #4b5563;
  font-weight: 600;
}

.cmi-tab.active .cmi-tab-badge {
  background: rgba(0, 91, 69, 0.12);
  color: var(--forest);
}

/* ── Toolbar (Search Removed) ── */
.cmi-toolbar {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.btn-cmi-filter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 38px;
  padding: 0 16px;
  border-radius: 6px;
  border: 1px solid #cbd5d1;
  background: #ffffff;
  color: #1f2937;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: all 0.15s ease;
}

.btn-cmi-filter:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

.btn-cmi-mark-all {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  height: 38px;
  padding: 0 16px;
  border-radius: 6px;
  border: none;
  background: var(--forest);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0, 91, 69, 0.18);
  transition: all 0.15s ease;
}

.btn-cmi-mark-all:hover {
  background: var(--forest-dark);
}

/* ── Loading State ── */
.cmi-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #64748b;
  gap: 12px;
}

.cmi-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(0, 91, 69, 0.15);
  border-top-color: var(--forest);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ── Cards List ── */
.cmi-card-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cmi-card {
  background: #ffffff;
  border: 1px solid var(--card-border);
  border-radius: var(--radius);
  padding: 16px 18px;
  display: grid;
  grid-template-columns: 50px minmax(0, 1fr) auto;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: all 0.18s ease;
  position: relative;
}

.cmi-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.cmi-card.selected {
  outline: 2px solid var(--forest);
  outline-offset: 1px;
}

/* Action Required Card */
.cmi-card.type-action {
  background: #fffafa;
  border-color: var(--danger-border);
  border-left: 3.5px solid var(--danger);
}

/* Accepted Card */
.cmi-card.type-accepted {
  background: #ffffff;
  border-color: var(--success-border);
  border-left: 3.5px solid var(--success);
}

/* Reminder Card */
.cmi-card.type-reminder {
  background: #ffffff;
  border-color: var(--info-border);
  border-left: 3.5px solid var(--info);
}

/* Unread indicator */
.cmi-card.unread-item {
  box-shadow: 0 2px 6px rgba(0, 91, 69, 0.08);
}

/* ── Card Icon Wrap ── */
.cmi-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  position: relative;
}

.type-action .cmi-icon-wrap {
  background: #fee2e2;
  color: var(--danger);
}

.type-accepted .cmi-icon-wrap {
  background: var(--success-bg);
  color: var(--success);
}

.type-reminder .cmi-icon-wrap {
  background: var(--info-bg);
  color: var(--info);
}

.unread-dot-badge {
  position: absolute;
  top: 0px;
  right: 0px;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #0284c7;
  border: 2px solid #ffffff;
}

/* ── Card Content ── */
.cmi-card-body {
  min-width: 0;
}

.cmi-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.03em;
  margin-bottom: 6px;
}

.cmi-badge.action {
  background: #ffe5e5;
  color: var(--danger);
}

.cmi-badge.accepted {
  background: #dcf4e8;
  color: var(--success);
}

.cmi-badge.reminder {
  background: #e1f0fc;
  color: var(--info);
}

.cmi-card-title {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
  line-height: 1.35;
}

.cmi-card-sub {
  font-size: 13px;
  color: #53605b;
  margin: 0 0 8px;
  line-height: 1.45;
}

.cmi-card-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  font-size: 12px;
  color: #718096;
}

.cmi-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

/* ── Card Action Buttons ── */
.cmi-card-actions-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cmi-card-btn-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s ease;
}

.btn-solid-green {
  background: var(--forest);
  color: #ffffff;
  border: none;
}

.btn-solid-green:hover {
  background: var(--forest-dark);
}

.btn-outline-green {
  background: #ffffff;
  color: var(--forest);
  border: 1.5px solid var(--forest);
}

.btn-outline-green:hover {
  background: var(--forest-light);
}

/* Mark as read button on individual card (same as PTA) */
.btn-cmi-read {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 12px;
  border-radius: 6px;
  background: #f1f5f3;
  color: #374151;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #d1dbd5;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-cmi-read:hover {
  background: #e2e8e5;
  color: var(--forest);
}

/* ── Right Detail Drawer (Hidden initially) ── */
.cmi-detail-pane {
  display: none;
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 28px 24px;
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 80px;
  animation: fadeInPane 0.2s ease-out;
}

.has-detail .cmi-detail-pane {
  display: block;
}

@keyframes fadeInPane {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.cmi-detail-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.btn-back-notif {
  border: none;
  background: transparent;
  color: #55615d;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.15s ease;
}

.btn-back-notif:hover {
  color: var(--forest);
  background: #edf3f0;
}

.cmi-detail-title {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin: 0 0 8px;
  line-height: 1.3;
}

.cmi-detail-intro {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 20px;
}

.cmi-detail-hr {
  border: 0;
  border-top: 1px solid var(--line);
  margin: 0 0 20px;
}

.cmi-detail-sec-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--forest-dark);
  margin: 0 0 14px;
}

.cmi-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  padding: 11px 0;
  border-bottom: 1px solid var(--line);
  font-size: 12.5px;
}

.cmi-detail-label {
  color: #64748b;
  font-weight: 500;
  flex: 0 0 110px;
}

.cmi-detail-val {
  color: #111827;
  font-weight: 600;
  text-align: right;
  word-break: break-word;
}

.cmi-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot.red { background: var(--danger); }
.status-dot.green { background: var(--success); }
.status-dot.blue { background: var(--info); }

/* Reviewer comment box */
.cmi-comment-box {
  margin: 20px 0;
  padding: 14px 16px;
  background: #fff5f5;
  border: 1px solid #fed7d7;
  border-radius: 8px;
  color: #3b2a29;
  font-size: 13px;
  line-height: 1.45;
}

.cmi-comment-box strong {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #991b1b;
  margin-bottom: 5px;
}

.btn-detail-cta {
  width: 100%;
  height: 42px;
  border-radius: 6px;
  background: var(--forest);
  color: #ffffff;
  border: none;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-detail-cta:hover {
  background: var(--forest-dark);
}

/* Empty state */
.cmi-empty {
  display: none;
  padding: 50px 20px;
  text-align: center;
  background: #ffffff;
  border: 1px dashed var(--line);
  border-radius: var(--radius);
  color: var(--muted);
}

@media (max-width: 1080px) {
  .cmi-notif-layout.has-detail {
    grid-template-columns: 1fr;
  }
  .cmi-detail-pane {
    position: relative;
    top: 0;
  }
}

@media (max-width: 680px) {
  .cmi-card {
    grid-template-columns: 44px 1fr;
  }
  .cmi-card-actions-cell {
    grid-column: 2;
    flex-wrap: wrap;
  }
}
</style>
@endsection

@section('content')
<div class="page active" id="page-notifications">
  <div class="cmi-notif-layout" id="cmiNotifLayout">

    <!-- Left Notifications Column -->
    <main class="cmi-notif-main">
      <div class="cmi-header">
        <h1>Notifications</h1>
        <p>Review updates, submission results, reminders, and actions required for your account.</p>
      </div>

      <!-- Filter Tabs with Counts -->
      <div class="cmi-tabs" role="tablist">
        <button type="button" class="cmi-tab active" data-filter="all">All <span class="cmi-tab-badge" id="tabCountAll">0</span></button>
        <button type="button" class="cmi-tab" data-filter="unread">Unread <span class="cmi-tab-badge" id="tabCountUnread">0</span></button>
        <button type="button" class="cmi-tab" data-filter="action">Needs Action <span class="cmi-tab-badge" id="tabCountAction">0</span></button>
        <button type="button" class="cmi-tab" data-filter="accepted">Accepted <span class="cmi-tab-badge" id="tabCountAccepted">0</span></button>
        <button type="button" class="cmi-tab" data-filter="reminder">Reminders <span class="cmi-tab-badge" id="tabCountReminder">0</span></button>
      </div>

      <!-- Toolbar: Action Buttons (Search Removed) -->
      <div class="cmi-toolbar">
        <button type="button" class="btn-cmi-filter" id="btnCmiFilter">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="4" y1="6" x2="20" y2="6"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
            <line x1="10" y1="18" x2="14" y2="18"/>
          </svg>
          Filter
        </button>
        <button type="button" class="btn-cmi-mark-all" id="btnCmiMarkAll">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Mark all as read
        </button>
      </div>

      <!-- Loading State -->
      <div class="cmi-loading-state" id="cmiLoadingState">
        <div class="cmi-spinner"></div>
        <span>Loading notifications...</span>
      </div>

      <!-- Card List -->
      <div class="cmi-card-list" id="cmiCardList"></div>

      <!-- Empty State -->
      <div class="cmi-empty" id="cmiEmptyState">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="display:block;margin:0 auto 12px">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <div style="font-size:14.5px;font-weight:700;color:#334155">No notifications found</div>
        <div style="font-size:13px;color:#64748b;margin-top:4px">You're all caught up with your report updates and remarks.</div>
      </div>
    </main>

    <!-- Right Notification Detail Pane (Hidden by default, shown when card / action is clicked) -->
    <aside class="cmi-detail-pane" id="cmiDetailPane" aria-label="Notification details">
      <div class="cmi-detail-top">
        <button type="button" class="btn-back-notif" id="btnBackToNotif" onclick="closeDetailDrawer()">
          ‹ Back to notifications
        </button>
        <span class="cmi-badge action" id="detailBadge">ACTION REQUIRED</span>
      </div>
      <h2 class="cmi-detail-title" id="detailTitle">Notification Details</h2>
      <p class="cmi-detail-intro" id="detailIntro">Review comments and take appropriate action.</p>
      
      <hr class="cmi-detail-hr">

      <div class="cmi-detail-sec-title">Notification details</div>

      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Reference number</span>
        <span class="cmi-detail-val" id="detailRef">—</span>
      </div>
      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Report</span>
        <span class="cmi-detail-val" id="detailReport">—</span>
      </div>
      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Date &amp; time</span>
        <span class="cmi-detail-val" id="detailDateTime">—</span>
      </div>
      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Reviewer</span>
        <span class="cmi-detail-val" id="detailReviewer">PTA Admin</span>
      </div>
      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Reason</span>
        <span class="cmi-detail-val" id="detailReason">—</span>
      </div>
      <div class="cmi-detail-row">
        <span class="cmi-detail-label">Status</span>
        <span class="cmi-detail-val cmi-status-pill" id="detailStatus"><i class="status-dot red"></i>Needs Action</span>
      </div>

      <div class="cmi-comment-box" id="detailCommentWrap" style="display:none">
        <strong>Reviewer's comment</strong>
        <span id="detailComment"></span>
      </div>

      <a href="/dashboard/cmi/fillup" class="btn-detail-cta" id="detailCtaBtn">Review and correct →</a>
    </aside>

  </div>
</div>
@endsection

@section('scripts')
<script>
let _cmiNotifications = [];
let _cmiFilter = 'all';
let _selectedNotifId = null;

function getCmiCategory(n) {
  const t = ((n.notif_type || '') + ' ' + (n.type || '') + ' ' + (n.msg || '')).toLowerCase();
  if (t.includes('corr') || t.includes('return') || t.includes('action') || t.includes('red') || t.includes('flag')) {
    return 'action';
  }
  if (t.includes('accept') || t.includes('approv') || t.includes('green')) {
    return 'accepted';
  }
  return 'reminder';
}

function getCmiCardSvg(cat) {
  if (cat === 'action') {
    return `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <path d="m12 4 9 16H3L12 4Z"/>
      <path d="M12 9v5"/>
      <path d="M12 17h.01"/>
    </svg>`;
  }
  if (cat === 'accepted') {
    return `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/>
      <path d="m8 12 2.5 2.5L16 9"/>
    </svg>`;
  }
  return `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M18 9a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
    <path d="M10 21h4"/>
  </svg>`;
}

function getCmiReportName(n) {
  if (n.table_name) return n.table_name;
  if (n.table_id) return 'CY 2026 Table ' + String(n.table_id).toUpperCase();
  const match = (n.msg || '').match(/Table\s+[A-Za-z0-9]+/i);
  if (match) return 'CY 2026 ' + match[0];
  return 'CY 2026 Accomplishment Report';
}

function hashCode(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = ((hash << 5) - hash) + str.charCodeAt(i);
    hash |= 0;
  }
  return hash;
}

function formatNiceDateTime(dtStr) {
  try {
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let hours = d.getHours();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    const mins = String(d.getMinutes()).padStart(2, '0');
    return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${hours}:${mins} ${ampm}`;
  } catch(e) {
    return dtStr || 'Just now';
  }
}

function updateBellBadge(count) {
  const dot = document.querySelector('.notif-dot');
  if (!dot) return;
  if (count > 0) {
    dot.textContent = count > 99 ? '99+' : count;
    dot.style.display = 'flex';
  } else {
    dot.style.display = 'none';
    dot.textContent = '';
  }
}

// Mark single notification as read (Same as PTA)
async function markItemAsRead(id, key) {
  try {
    await fetch('/api/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id || null, key: key || null }),
    });
  } catch (e) {
    console.warn('[markItemAsRead] error:', e);
  }

  const item = _cmiNotifications.find(n => (n.id && n.id == id) || (n.key && n.key == key));
  if (item) {
    item.unread = false;
  }

  const unreadCount = _cmiNotifications.filter(n => n.unread).length;
  updateBellBadge(unreadCount);
  renderCmiList();
  if (typeof showToast === 'function') showToast('Marked as read');
}

// Mark all as read (Same as PTA)
async function markAllAsRead() {
  try {
    await fetch('/api/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ all: true })
    });
  } catch (e) {
    console.warn('[markAllAsRead] error:', e);
  }

  _cmiNotifications.forEach(n => { n.unread = false; });
  updateBellBadge(0);
  renderCmiList();
  if (typeof showToast === 'function') showToast('All notifications marked as read');
}

// Open Detail Drawer for a notification
function openNotificationDetail(id) {
  const item = _cmiNotifications.find(n => (n.id || n.key) == id);
  if (!item) return;

  _selectedNotifId = id;

  // If unread, mark it as read immediately when viewed
  if (item.unread) {
    markItemAsRead(item.id, item.key);
  }

  // Show drawer and activate split layout
  const layout = document.getElementById('cmiNotifLayout');
  const drawer = document.getElementById('cmiDetailPane');
  layout.classList.add('has-detail');
  drawer.style.display = 'block';

  // Highlight selected card
  document.querySelectorAll('.cmi-card').forEach(c => {
    c.classList.toggle('selected', c.dataset.notifId == _selectedNotifId);
  });

  const cat = getCmiCategory(item);
  const badgeEl = document.getElementById('detailBadge');
  const titleEl = document.getElementById('detailTitle');
  const introEl = document.getElementById('detailIntro');
  const refEl = document.getElementById('detailRef');
  const reportEl = document.getElementById('detailReport');
  const dtEl = document.getElementById('detailDateTime');
  const reviewerEl = document.getElementById('detailReviewer');
  const reasonEl = document.getElementById('detailReason');
  const statusEl = document.getElementById('detailStatus');
  const commentWrap = document.getElementById('detailCommentWrap');
  const commentEl = document.getElementById('detailComment');
  const ctaBtn = document.getElementById('detailCtaBtn');

  const refNum = 'NOTIF-2026-' + String(item.id || Math.abs(hashCode(item.msg || '')) % 900000 + 100000).padStart(6, '0');
  const report = getCmiReportName(item);
  const dt = item.time ? formatNiceDateTime(item.time) : 'Sep 1, 2026 10:18 AM';

  refEl.textContent = refNum;
  reportEl.textContent = report;
  dtEl.textContent = dt;
  reviewerEl.textContent = item.reviewer || 'PTA Admin';

  if (cat === 'action') {
    badgeEl.className = 'cmi-badge action';
    badgeEl.textContent = 'ACTION REQUIRED';
    titleEl.textContent = item.msg || 'Action required on report';
    introEl.innerHTML = 'PTA Admin requested an update.<br>Please review the comments and resubmit the form.';
    reasonEl.textContent = item.reason || item.msg || 'Please update the highlighted items.';
    statusEl.innerHTML = '<i class="status-dot red"></i>Needs Action';
    
    if (item.comment) {
      commentWrap.style.display = 'block';
      commentEl.textContent = item.comment;
    } else {
      commentWrap.style.display = 'block';
      commentEl.textContent = 'Kindly review the highlighted fields and provide the corrected values.';
    }

    ctaBtn.textContent = 'Review and correct →';
    ctaBtn.className = 'btn-detail-cta';
    ctaBtn.href = item.action || '/dashboard/cmi/fillup';
  } else if (cat === 'accepted') {
    badgeEl.className = 'cmi-badge accepted';
    badgeEl.textContent = 'ACCEPTED';
    titleEl.textContent = item.msg || 'Report was accepted';
    introEl.innerHTML = 'Your ' + report + ' submission was accepted by the PTA.';
    reasonEl.textContent = item.reason || 'Report verified and complies with consortium guidelines.';
    statusEl.innerHTML = '<i class="status-dot green"></i>Accepted';
    commentWrap.style.display = 'none';
    ctaBtn.textContent = 'View submission →';
    ctaBtn.className = 'btn-detail-cta';
    ctaBtn.href = item.action || '/dashboard/cmi/submissions';
  } else {
    badgeEl.className = 'cmi-badge reminder';
    badgeEl.textContent = 'REMINDER';
    titleEl.textContent = item.msg || 'Submission deadline approaching';
    introEl.innerHTML = 'Please complete and submit remaining reports before the deadline.';
    reasonEl.textContent = item.reason || 'Scheduled reporting period deadline reminder.';
    statusEl.innerHTML = '<i class="status-dot blue"></i>Pending Submission';
    commentWrap.style.display = 'none';
    ctaBtn.textContent = 'Continue submission →';
    ctaBtn.className = 'btn-detail-cta';
    ctaBtn.href = item.action || '/dashboard/cmi/fillup';
  }

  // Smooth scroll into view on smaller displays
  if (window.innerWidth <= 1080) {
    drawer.scrollIntoView({ behavior: 'smooth' });
  }
}

// Close Detail Drawer (Back to notifications)
function closeDetailDrawer() {
  const layout = document.getElementById('cmiNotifLayout');
  const drawer = document.getElementById('cmiDetailPane');
  layout.classList.remove('has-detail');
  drawer.style.display = 'none';
  _selectedNotifId = null;

  document.querySelectorAll('.cmi-card').forEach(c => {
    c.classList.remove('selected');
  });

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function renderCmiList() {
  const container = document.getElementById('cmiCardList');
  const emptyState = document.getElementById('cmiEmptyState');
  const loadingState = document.getElementById('cmiLoadingState');

  if (loadingState) loadingState.style.display = 'none';

  let filtered = _cmiNotifications.filter(n => {
    const cat = getCmiCategory(n);
    const isUnread = !!n.unread;
    if (_cmiFilter === 'all') return true;
    if (_cmiFilter === 'unread') return isUnread;
    if (_cmiFilter === 'action') return cat === 'action';
    if (_cmiFilter === 'accepted') return cat === 'accepted';
    if (_cmiFilter === 'reminder') return cat === 'reminder';
    return true;
  });

  // Update count tabs
  const allCount = _cmiNotifications.length;
  const unreadCount = _cmiNotifications.filter(n => n.unread).length;
  const actionCount = _cmiNotifications.filter(n => getCmiCategory(n) === 'action').length;
  const acceptedCount = _cmiNotifications.filter(n => getCmiCategory(n) === 'accepted').length;
  const reminderCount = _cmiNotifications.filter(n => getCmiCategory(n) === 'reminder').length;

  document.getElementById('tabCountAll').textContent = allCount;
  document.getElementById('tabCountUnread').textContent = unreadCount;
  document.getElementById('tabCountAction').textContent = actionCount;
  document.getElementById('tabCountAccepted').textContent = acceptedCount;
  document.getElementById('tabCountReminder').textContent = reminderCount;

  if (filtered.length === 0) {
    container.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }

  emptyState.style.display = 'none';

  container.innerHTML = filtered.map(n => {
    const cat = getCmiCategory(n);
    const svgIcon = getCmiCardSvg(cat);
    const report = getCmiReportName(n);
    const notifId = n.id || n.key;
    const isSelected = (_selectedNotifId == notifId);
    const isUnread = !!n.unread;

    let badgeHtml = '';
    let btnText = '';
    let btnClass = 'btn-outline-green';
    let subHtml = '';

    if (cat === 'action') {
      badgeHtml = '<span class="cmi-badge action">ACTION REQUIRED</span>';
      subHtml = 'PTA Admin requested an update. Please review the comments and resubmit the form.';
      btnText = 'Review and correct →';
      btnClass = 'btn-solid-green';
    } else if (cat === 'accepted') {
      badgeHtml = '<span class="cmi-badge accepted">ACCEPTED</span>';
      subHtml = 'Your ' + report + ' submission was accepted by the PTA.';
      btnText = 'View submission →';
      btnClass = 'btn-outline-green';
    } else {
      badgeHtml = '<span class="cmi-badge reminder">REMINDER</span>';
      subHtml = 'Please complete the remaining reports before September 15, 2026.';
      btnText = 'Continue submission →';
      btnClass = 'btn-outline-green';
    }

    const dt = n.time ? formatNiceDateTime(n.time) : 'Sep 1, 2026 10:18 AM';
    const dtParts = dt.split(',');
    const datePart = dtParts[0] ? dtParts[0] + (dtParts[1] ? ',' + dtParts[1].substring(0, 5) : '') : 'Sep 1, 2026';
    const timePart = dtParts[1] ? dtParts[1].substring(6).trim() : '10:18 AM';

    const unreadDotHtml = isUnread ? '<span class="unread-dot-badge"></span>' : '';

    return `
    <article class="cmi-card type-${cat} ${isSelected ? 'selected' : ''} ${isUnread ? 'unread-item' : ''}" data-notif-id="${notifId}" onclick="openNotificationDetail('${notifId}')">
      <div class="cmi-icon-wrap">
        ${svgIcon}
        ${unreadDotHtml}
      </div>
      <div class="cmi-card-body">
        ${badgeHtml}
        <h3 class="cmi-card-title">${n.msg || 'Report Update'}</h3>
        <p class="cmi-card-sub">${subHtml}</p>
        <div class="cmi-card-meta">
          <span class="cmi-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            ${report}
          </span>
          <span class="cmi-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            ${datePart}
          </span>
          <span class="cmi-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${timePart}
          </span>
        </div>
      </div>
      <div class="cmi-card-actions-cell">
        ${isUnread ? `
          <button type="button" class="btn-cmi-read" onclick="event.stopPropagation(); markItemAsRead(${n.id || 'null'}, '${n.key || ''}')" title="Mark as read">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Mark read
          </button>
        ` : ''}
        <button type="button" class="cmi-card-btn-action ${btnClass}" onclick="event.stopPropagation(); openNotificationDetail('${notifId}')">
          ${btnText}
        </button>
      </div>
    </article>`;
  }).join('');
}

async function loadCmiNotifs() {
  const year = new Date().getFullYear();
  try {
    const res = await fetch(`/api/notifications?year=${year}`);
    const json = await res.json();
    if (json.ok && Array.isArray(json.notifications)) {
      _cmiNotifications = json.notifications;
      const unreadCount = json.unread_count ?? _cmiNotifications.filter(n => n.unread).length;
      updateBellBadge(unreadCount);
    } else {
      _cmiNotifications = [];
    }
  } catch (e) {
    console.warn('[loadCmiNotifs] API fetch error:', e);
    _cmiNotifications = [];
  }

  renderCmiList();
}

document.addEventListener('DOMContentLoaded', () => {
  // Tabs click
  const tabs = document.querySelectorAll('.cmi-tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      _cmiFilter = tab.dataset.filter;
      renderCmiList();
    });
  });

  // Mark all as read button (Same as PTA)
  document.getElementById('btnCmiMarkAll').addEventListener('click', markAllAsRead);

  // Filter toggle button (All <-> Unread)
  document.getElementById('btnCmiFilter').addEventListener('click', function() {
    _cmiFilter = _cmiFilter === 'all' ? 'unread' : 'all';
    tabs.forEach(tab => {
      tab.classList.toggle('active', tab.dataset.filter === _cmiFilter);
    });
    renderCmiList();
  });

  // Fetch real notifications from database
  loadCmiNotifs();
});
</script>
@endsection
