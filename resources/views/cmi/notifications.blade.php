@extends('layouts.cmi')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub { font-size:13px; color:#6b7280; margin-top:3px; }

.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body { padding:0 24px 24px; }

.badge-unread { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; background:#ecfdf5; color:#059669; }

/* ── Notification Items ── */
.notif-list { display:flex; flex-direction:column; gap:8px; padding:16px 0; }
.notif-item { display:flex; align-items:flex-start; gap:14px; padding:14px 16px; border-radius:12px; background:#fff; border:1px solid #f0f0f0; transition:background .15s, border-color .15s; }
.notif-item:hover { background:#f9fafe; }
.notif-item.unread { background:#f0fdf4; border-color:#a7f3d0; }

.notif-icon-wrap { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.notif-icon-wrap.blue   { background:#eff6ff; color:#2563eb; border:1px solid #dbeafe; }
.notif-icon-wrap.green  { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
.notif-icon-wrap.red    { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.notif-icon-wrap.yellow { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }

.notif-body { flex:1; min-width:0; }
.notif-msg  { font-size:13.5px; color:#1f2937; font-weight:500; line-height:1.45; }
.notif-item.unread .notif-msg { font-weight:600; color:#111827; }
.notif-time { font-size:11.5px; color:#9ca3af; margin-top:4px; display:flex; align-items:center; gap:5px; }
.notif-time svg { width:12px; height:12px; }

.notif-actions { display:flex; align-items:center; gap:6px; flex-shrink:0; }
.btn-notif { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; text-decoration:none; transition:all .15s; }
.btn-notif-view { background:#10b981; color:#fff; }
.btn-notif-view:hover { background:#059669; }
.btn-notif-read { background:#e5e7eb; color:#374151; }
.btn-notif-read:hover { background:#d1d5db; }
.btn-notif-del { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.btn-notif-del:hover { background:#fee2e2; }

.btn-header { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-header-read { border:1.5px solid #10b981; background:#fff; color:#10b981; }
.btn-header-read:hover { background:#ecfdf5; }
.btn-header-del { border:1.5px solid #ef4444; background:#fff; color:#ef4444; }
.btn-header-del:hover { background:#fef2f2; }
</style>
@endsection

@section('content')
<div class="page active" id="page-notifications">
  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Notifications</div>
      <div class="pg-banner-sub">System updates, PTA reviews, and table activity</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <button type="button" class="btn-header btn-header-read" id="btnMarkAllRead">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Mark All as Read
      </button>
      <button type="button" class="btn-header btn-header-del" id="btnDeleteAll">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
        </svg>
        Delete All
      </button>
    </div>
  </div>

  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        All Notifications
      </div>
      <span class="badge-unread" id="unreadBadge" style="display:none">0 unread</span>
    </div>
    <div class="fc-card-body">
      <div id="notifContainer" class="notif-list">
        <div style="padding:40px;text-align:center;color:#9ca3af">Loading notifications...</div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {

  function getNotifSvg(type, notifType, icon) {
    const t = ((notifType || '') + ' ' + (type || '')).toLowerCase();
    const ic = (icon || '').toLowerCase();

    // 1. Edit / Update (PTA edit or table update)
    if (t.includes('edit') || t.includes('update') || ic.includes('edit') || ic.includes('pencil') || ic === '✏️' || ic === '📝') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>`;
    }

    // 2. Correction / Returned / Urgent
    if (t.includes('corr') || t.includes('return') || t.includes('flag') || t === 'red' || ic.includes('alert') || ic === '🔴' || ic === '↩️') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
    }

    // 3. Accepted / Approved
    if (t.includes('accept') || t.includes('approv') || t === 'green' || ic.includes('check') || ic === '✅') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
    }

    // 4. Submitted / Sent / Mail
    if (t.includes('submit') || ic.includes('mail') || ic.includes('send') || ic === '📨') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`;
    }

    // 5. User / Account
    if (t.includes('user') || ic.includes('user') || ic === '👥') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
    }

    // 6. Delete / Trash
    if (t.includes('delete') || ic.includes('trash') || ic === '🗑️') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>`;
    }

    // 7. Year Activation / Calendar
    if (t.includes('year') || t.includes('activ') || ic.includes('calendar') || ic === '🎉') {
      return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;
    }

    // Default: Document / Log
    return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;
  }

  function getNotifColorClass(type, notifType) {
    const t = ((notifType || '') + ' ' + (type || '')).toLowerCase();
    if (t.includes('corr') || t.includes('return') || t.includes('red') || t.includes('delete')) return 'red';
    if (t.includes('accept') || t.includes('green') || t.includes('activ')) return 'green';
    if (t.includes('user') || t.includes('yellow') || t.includes('pend')) return 'yellow';
    return 'blue';
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

  window.markItemRead = async function(id, key) {
    await fetch('/api/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, key: key }),
    });
    if (typeof showToast === 'function') showToast('Marked as read');
    loadNotifs();
  };

  window.deleteNotifItem = async function(id, key) {
    showConfirmModal({
      title: 'Delete Notification?',
      message: 'Are you sure you want to delete this notification?',
      confirmText: 'Delete Notification',
      type: 'red',
      onConfirm: async function() {
        await fetch('/api/notifications/delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, key: key }),
        });
        if (typeof showToast === 'function') showToast('Notification deleted');
        loadNotifs();
      }
    });
  };

  async function loadNotifs() {
    try {
      const year = new Date().getFullYear();
      const res = await fetch(`/api/notifications?year=${year}`);
      const json = await res.json();
      const container = document.getElementById('notifContainer');

      if (json.ok) {
        const unread = json.unread_count || 0;
        updateBellBadge(unread);

        const badge = document.getElementById('unreadBadge');
        if (badge) {
          badge.textContent = `${unread} unread`;
          badge.style.display = unread > 0 ? 'inline-flex' : 'none';
        }

        if (!json.notifications || json.notifications.length === 0) {
          container.innerHTML = `
            <div style="padding:48px 24px;text-align:center;color:#9ca3af">
              <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <div style="font-size:14px;font-weight:600;color:#6b7280">No notifications yet</div>
              <div style="font-size:12px;color:#9ca3af;margin-top:3px">You're all caught up with your report updates and remarks.</div>
            </div>`;
        } else {
          container.innerHTML = json.notifications.map(n => {
            const colorClass = getNotifColorClass(n.type, n.notif_type);
            const svgIcon = getNotifSvg(n.type, n.notif_type, n.icon);
            return `
            <div class="notif-item ${n.unread ? 'unread' : ''}">
              <div class="notif-icon-wrap ${colorClass}">
                ${svgIcon}
              </div>
              <div class="notif-body">
                <div class="notif-msg">${n.msg}</div>
                <div class="notif-time">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                  </svg>
                  ${n.time ? n.time.substring(0, 16).replace('T', ' ') : 'Just now'}
                </div>
              </div>
              <div class="notif-actions">
                ${n.unread ? `
                  <button type="button" class="btn-notif btn-notif-read" onclick="markItemRead(${n.id || 'null'}, '${n.key || ''}')" title="Mark as read">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Mark read
                  </button>` : ''}
                ${n.action ? `
                  <a href="${n.action}" class="btn-notif btn-notif-view">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    ${n.action_label || 'View'}
                  </a>` : ''}
                <button type="button" class="btn-notif btn-notif-del" onclick="deleteNotifItem(${n.id || 'null'}, '${n.key || ''}')" title="Delete notification">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                  Delete
                </button>
              </div>
            </div>`;
          }).join('');
        }
      }
    } catch (e) {
      console.error('Notif load error:', e);
    }
  }

  document.getElementById('btnMarkAllRead').addEventListener('click', async function() {
    await fetch('/api/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ all: true }),
    });
    if (typeof showToast === 'function') showToast('All notifications marked as read');
    loadNotifs();
  });

  document.getElementById('btnDeleteAll')?.addEventListener('click', function() {
    showConfirmModal({
      title: 'Delete All Notifications?',
      message: 'Are you sure you want to delete all notifications? This action cannot be undone.',
      confirmText: 'Delete All',
      type: 'red',
      onConfirm: async function() {
        await fetch('/api/notifications/delete-all', { method: 'POST' });
        if (typeof showToast === 'function') showToast('All notifications deleted');
        loadNotifs();
      }
    });
  });

  loadNotifs();
});
</script>
@endsection
