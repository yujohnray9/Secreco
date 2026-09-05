@extends('layouts.pta')

@section('styles')
<style>
:root {
  --forest: #005b45;
  --forest-dark: #004534;
  --forest-light: #e9f7f1;
  --ink: #111827;
  --muted: #64748b;
  --card-border: #e6ece8;
  --white: #ffffff;
  --danger: #dc2626;
  --danger-bg: #fff2f2;
  --danger-border: #fed7d7;
  --info: #0284c7;
  --info-bg: #eaf4fd;
  --success: #059669;
  --success-bg: #e8f8f0;
  --radius: 12px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
  --shadow-md: 0 4px 12px -2px rgba(0, 91, 69, 0.06), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
}

.notif-page-wrap {
  padding: 6px 0 40px;
  max-width: 1280px;
}

/* ── Header ── */
.notif-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 22px;
}

.notif-title-group h1 {
  font-size: 30px;
  font-weight: 800;
  color: #0d2b22;
  letter-spacing: -0.03em;
  line-height: 1.2;
  margin: 0;
}

.notif-title-group p {
  font-size: 14px;
  color: #5a6e66;
  margin-top: 5px;
  font-weight: 400;
}

.unread-pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: 20px;
  background: #e6f7f0;
  color: var(--forest);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: -0.01em;
  transition: all 0.2s ease;
}

/* ── Toolbar (Search Removed) ── */
.toolbar-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 22px;
}

.filter-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pill-btn {
  border: 1px solid #d1dbd5;
  background: transparent;
  color: #4b5a54;
  padding: 7px 18px;
  border-radius: 24px;
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.pill-btn:hover {
  background: #edf3f0;
  color: var(--forest-dark);
  border-color: #b5c7be;
}

.pill-btn.active {
  background: var(--forest-light);
  color: var(--forest);
  border-color: #a3d9c3;
  font-weight: 700;
}

.actions-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
}

.btn-filter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 16px;
  border-radius: 6px;
  border: 1px solid #cbd5d1;
  background: #ffffff;
  color: #1f2937;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: all 0.15s ease;
}

.btn-filter:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

.btn-mark-all {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 18px;
  border-radius: 6px;
  border: none;
  background: var(--forest);
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 91, 69, 0.2);
  transition: all 0.15s ease;
}

.btn-mark-all:hover {
  background: var(--forest-dark);
  box-shadow: 0 4px 10px rgba(0, 91, 69, 0.28);
}

/* ── Loading Spinner ── */
.pta-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #64748b;
  gap: 12px;
}

.pta-spinner {
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

/* ── Notification List & Cards ── */
.notif-list-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.notif-card {
  background: #ffffff;
  border: 1px solid var(--card-border);
  border-radius: var(--radius);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
}

.notif-card:hover {
  border-color: #bcd4c8;
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.notif-card.unread {
  background: #ffffff;
  border-left: 3.5px solid var(--forest);
}

.notif-left {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  flex: 1;
  min-width: 0;
}

/* ── Icon Box ── */
.notif-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.icon-blue {
  background: #eaf4fd;
  color: #1976d2;
}

.icon-green {
  background: #e8f8f0;
  color: #087443;
}

.icon-yellow {
  background: #fef9c3;
  color: #ca8a04;
}

.icon-red {
  background: #fee2e2;
  color: #dc2626;
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-card-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #13221b;
  line-height: 1.4;
  margin-bottom: 4px;
  letter-spacing: -0.01em;
}

.notif-card-desc {
  font-size: 13.5px;
  color: #556760;
  line-height: 1.45;
  margin-bottom: 7px;
}

.notif-meta-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #7b8c84;
}

.notif-meta-row .meta-time {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.notif-meta-row .bullet {
  color: #94a39d;
  font-size: 10px;
}

.notif-meta-row .table-tag {
  font-weight: 500;
  color: #4b5d56;
}

/* ── Right Actions ── */
.notif-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.btn-action-view {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 16px;
  border-radius: 6px;
  background: var(--forest-light);
  color: var(--forest);
  font-size: 12.5px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-action-view:hover {
  background: #d8f2e5;
  color: var(--forest-dark);
}

.btn-action-read {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  background: #f1f5f3;
  color: #374151;
  font-size: 12.5px;
  font-weight: 600;
  border: 1px solid #d1dbd5;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-action-read:hover {
  background: #e2e8e5;
  color: var(--forest);
}

.btn-action-delete {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 6px;
  background: var(--danger-bg);
  border: 1px solid var(--danger-border);
  color: var(--danger);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-action-delete:hover {
  background: #fee2e2;
  border-color: #fca5a5;
}

/* ── Empty State ── */
.empty-state {
  display: none;
  padding: 60px 20px;
  text-align: center;
  background: #ffffff;
  border: 1px dashed var(--card-border);
  border-radius: var(--radius);
  color: var(--muted);
}

.empty-state svg {
  margin-bottom: 12px;
  color: #94a3b8;
}

.empty-state h3 {
  font-size: 16px;
  color: #334155;
  font-weight: 600;
  margin-bottom: 4px;
}

.empty-state p {
  font-size: 13.5px;
}

@media (max-width: 768px) {
  .notif-header {
    flex-direction: column;
    gap: 12px;
  }
  .toolbar-row {
    flex-direction: column;
    align-items: stretch;
  }
  .actions-group {
    margin-left: 0;
    justify-content: flex-end;
  }
  .notif-card {
    flex-direction: column;
    align-items: stretch;
  }
  .notif-actions {
    justify-content: flex-end;
    padding-top: 10px;
    border-top: 1px solid #f1f5f3;
  }
}
</style>
@endsection

@section('content')
<div class="page active notif-page-wrap" id="page-notifications">

  <!-- Header / Title -->
  <header class="notif-header">
    <div class="notif-title-group">
      <h1>Notifications</h1>
      <p>Monitor institutional submissions, updates, and platform activity.</p>
    </div>
    <div class="unread-pill-badge" id="unreadBadge">
      <span id="unreadCountNum">0</span> unread
    </div>
  </header>

  <!-- Toolbar: Filter Pills & Action Buttons (Search Removed) -->
  <div class="toolbar-row">
    <div class="filter-pills" role="tablist">
      <button type="button" class="pill-btn active" data-filter="all">All</button>
      <button type="button" class="pill-btn" data-filter="unread">Unread</button>
      <button type="button" class="pill-btn" data-filter="submissions">Submissions</button>
      <button type="button" class="pill-btn" data-filter="updates">Updates</button>
    </div>

    <div class="actions-group">
      <button type="button" class="btn-filter" id="btnFilterToggle">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" y1="6" x2="20" y2="6"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
          <line x1="10" y1="18" x2="14" y2="18"/>
        </svg>
        Filter
      </button>
      <button type="button" class="btn-mark-all" id="btnMarkAllRead">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Mark all as read
      </button>
    </div>
  </div>

  <!-- Loading State -->
  <div class="pta-loading-state" id="ptaLoadingState">
    <div class="pta-spinner"></div>
    <span>Loading notifications...</span>
  </div>

  <!-- Notification List -->
  <div class="notif-list-container" id="notifContainer"></div>

  <!-- Empty State -->
  <div class="empty-state" id="emptyState">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
      <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    </svg>
    <h3>No notifications found</h3>
    <p>There are no notifications matching the selected filter.</p>
  </div>

</div>
@endsection

@section('scripts')
<script>
let _allPtaNotifications = [];
let _currentFilter = 'all';

function getPtaCardCategory(n) {
  const t = ((n.notif_type || '') + ' ' + (n.type || '') + ' ' + (n.msg || '')).toLowerCase();
  if (t.includes('submit') || t.includes('submission') || t.includes('accomplishment')) return 'submissions';
  if (t.includes('update') || t.includes('table') || t.includes('edit')) return 'updates';
  return 'updates';
}

function getPtaNotifSvg(category) {
  if (category === 'submissions') {
    return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="3" width="18" height="18" rx="3" ry="3"/>
      <polyline points="9 12 11 14 15 10"/>
    </svg>`;
  }
  return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 20h9"/>
    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
  </svg>`;
}

function getPtaTagLabel(n) {
  if (n.table_name) return n.table_name;
  if (n.table_id) return 'Table ' + String(n.table_id).toUpperCase();
  const m = (n.msg || '');
  const match = m.match(/Table\s+[A-Za-z0-9]+/i);
  if (match) return match[0];
  if (m.toLowerCase().includes('accomplishment')) return 'Accomplishment reports';
  return 'System Report';
}

function renderPtaCards() {
  const container = document.getElementById('notifContainer');
  const emptyState = document.getElementById('emptyState');
  const loadingState = document.getElementById('ptaLoadingState');

  if (loadingState) loadingState.style.display = 'none';

  let filtered = _allPtaNotifications.filter(n => {
    const isUnread = !!n.unread;
    const cat = getPtaCardCategory(n);
    if (_currentFilter === 'all') return true;
    if (_currentFilter === 'unread') return isUnread;
    if (_currentFilter === 'submissions') return cat === 'submissions';
    if (_currentFilter === 'updates') return cat === 'updates';
    return true;
  });

  const unreadCount = _allPtaNotifications.filter(n => n.unread).length;
  const countNum = document.getElementById('unreadCountNum');
  const badge = document.getElementById('unreadBadge');
  if (countNum) countNum.textContent = unreadCount;
  if (badge) badge.style.opacity = unreadCount > 0 ? '1' : '0.6';

  if (filtered.length === 0) {
    container.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }

  emptyState.style.display = 'none';

  container.innerHTML = filtered.map(n => {
    const category = getPtaCardCategory(n);
    const isUnread = !!n.unread;
    const iconClass = category === 'submissions' ? 'icon-green' : 'icon-blue';
    const svgIcon = getPtaNotifSvg(category);
    const tag = getPtaTagLabel(n);
    const timeStr = n.time ? n.time.substring(0, 16).replace('T', ' ') : 'Just now';

    let desc = 'Institutional report information was updated and is ready for review.';
    if (category === 'submissions') {
      desc = 'A new institutional submission has been received and requires processing.';
    }

    const actionUrl = n.action ? (n.action.startsWith('/') ? n.action : '/dashboard/pta/' + n.action) : '/dashboard/pta/submissions';
    const actionLabel = category === 'submissions' ? 'View' : 'View Submissions';

    return `
    <article class="notif-card ${isUnread ? 'unread' : ''}" data-id="${n.id || ''}" data-key="${n.key || ''}">
      <div class="notif-left">
        <div class="notif-icon-box ${iconClass}">
          ${svgIcon}
        </div>
        <div class="notif-content">
          <h2 class="notif-card-title">${n.msg}</h2>
          <p class="notif-card-desc">${desc}</p>
          <div class="notif-meta-row">
            <span class="meta-time">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              ${timeStr}
            </span>
            <span class="bullet">•</span>
            <span class="table-tag">${tag}</span>
          </div>
        </div>
      </div>
      <div class="notif-actions">
        ${isUnread ? `
          <button type="button" class="btn-action-read" onclick="markPtaItemRead(${n.id || 'null'}, '${n.key || ''}')" title="Mark as read">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Mark read
          </button>
        ` : ''}
        <a href="${actionUrl}" class="btn-action-view">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
          ${actionLabel}
        </a>
        <button type="button" class="btn-action-delete" onclick="deletePtaNotifItem(${n.id || 'null'}, '${n.key || ''}')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          </svg>
          Delete
        </button>
      </div>
    </article>`;
  }).join('');
}

async function updateBellBadge(unreadCount) {
  const dot = document.querySelector('.notif-dot');
  if (!dot) return;
  if (unreadCount > 0) {
    dot.textContent = unreadCount > 99 ? '99+' : unreadCount;
    dot.style.display = 'flex';
  } else {
    dot.style.display = 'none';
    dot.textContent = '';
  }
}

// Mark single notification as read in PTA
window.markPtaItemRead = async function(id, key) {
  try {
    await fetch('/api/pta/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id || null, key: key || null }),
    });
  } catch(e) {
    console.warn('markPtaItemRead error:', e);
  }
  const item = _allPtaNotifications.find(n => (n.id && n.id == id) || (n.key && n.key == key));
  if (item) item.unread = false;
  const unread = _allPtaNotifications.filter(n => n.unread).length;
  updateBellBadge(unread);
  renderPtaCards();
  if (typeof showToast === 'function') showToast('Marked as read');
};

async function loadNotifs() {
  const year = new Date().getFullYear();
  try {
    const res = await fetch(`/api/pta/notifications?year=${year}`);
    const json = await res.json();
    if (json.ok && Array.isArray(json.notifications)) {
      _allPtaNotifications = json.notifications;
      const unread = json.unread_count ?? _allPtaNotifications.filter(n => n.unread).length;
      updateBellBadge(unread);
    } else {
      _allPtaNotifications = [];
    }
  } catch(e) {
    console.warn('PTA notif API load failed:', e);
    _allPtaNotifications = [];
  }

  renderPtaCards();
}

window.deletePtaNotifItem = async function(id, key) {
  showConfirmModal({
    title: 'Delete Notification?',
    message: 'Are you sure you want to delete this notification?',
    confirmText: 'Delete Notification',
    type: 'red',
    onConfirm: async function() {
      try {
        await fetch('/api/notifications/delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, key: key }),
        });
      } catch(e) {}
      _allPtaNotifications = _allPtaNotifications.filter(n => (n.id != id && n.key != key));
      renderPtaCards();
      if (typeof showToast === 'function') showToast('Notification deleted');
    }
  });
};

document.addEventListener('DOMContentLoaded', () => {
  // Filter pills
  const pillButtons = document.querySelectorAll('.pill-btn');
  pillButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      pillButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      _currentFilter = btn.dataset.filter;
      renderPtaCards();
    });
  });

  // Mark all as read
  document.getElementById('btnMarkAllRead').addEventListener('click', async function() {
    try {
      await fetch('/api/pta/notifications/mark-read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ all: true })
      });
    } catch(e) {}
    _allPtaNotifications.forEach(n => n.unread = false);
    updateBellBadge(0);
    renderPtaCards();
    if (typeof showToast === 'function') showToast('All notifications marked as read');
  });

  // Filter toggle button
  document.getElementById('btnFilterToggle').addEventListener('click', function() {
    _currentFilter = _currentFilter === 'all' ? 'unread' : 'all';
    pillButtons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filter === _currentFilter);
    });
    renderPtaCards();
  });

  loadNotifs();
});
</script>
@endsection
