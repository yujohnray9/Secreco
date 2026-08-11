@extends('layouts.pta')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:0 24px 24px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-orange { background:#fff7ed; color:#d97706; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#eff6ff; color:#2563eb; }

/* ── Notification Item ── */
.notif-list { display:flex; flex-direction:column; gap:6px; padding:16px 0; }
.notif-item { display:flex; align-items:flex-start; gap:14px; padding:14px 16px; border-radius:12px; background:#fff; border:1px solid #f0f0f0; transition:background .15s; }
.notif-item:hover { background:#f9fafe; }
.notif-item.unread { background:#f0fdf4; border-color:#a7f3d0; }
.notif-icon { width:40px; height:40px; border-radius:10px; background:#ecfdf5; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.notif-icon svg { color:#10b981; }
.notif-body { flex:1; min-width:0; }
.notif-msg  { font-size:13.5px; color:#1f2937; font-weight:500; line-height:1.45; }
.notif-time { font-size:11.5px; color:#9ca3af; margin-top:3px; }
.notif-action { flex-shrink:0; }
.btn-sm-fc { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.btn-sm-view { background:#ecfdf5; color:#059669; }
.btn-sm-view:hover { background:#d1fae5; }
.btn-mark-read { border:1.5px solid #10b981; background:#fff; color:#10b981; border-radius:10px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:7px; }
.btn-mark-read:hover { background:#ecfdf5; }
</style>
@endsection

@section('content')
<div class="page active" id="page-notifications">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Notifications</div>
      <div class="pg-banner-sub">System notifications, user registrations, and submission updates</div>
    </div>
    <button type="button" class="btn-mark-read" id="btnMarkAllRead">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      Mark All as Read
    </button>
  </div>

  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        All Notifications
      </div>
      <span class="badge badge-orange" id="unreadBadge" style="display:none">0 unread</span>
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
const notifIconMap = {
  default: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
  user:    `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  submit:  `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>`,
};

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

window.markPtaItemRead = async function(id, key) {
  await fetch('/api/pta/notifications/mark-read', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id, key: key }),
  });
  if (typeof showToast === 'function') showToast('Marked as read');
  loadNotifs();
};

async function loadNotifs() {
  const year      = new Date().getFullYear();
  const container = document.getElementById('notifContainer');
  try {
    const res  = await fetch(`/api/pta/notifications?year=${year}`);
    const json = await res.json();
    const notifs = json.notifications || [];
    const unread = json.unread_count || 0;

    updateBellBadge(unread);

    const badge = document.getElementById('unreadBadge');
    if (badge) {
      badge.textContent = `${unread} unread`;
      badge.style.display = unread > 0 ? 'inline-flex' : 'none';
    }

    if (notifs.length === 0) {
      container.innerHTML = `<div style="padding:48px;text-align:center;color:#9ca3af">
        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>No notifications.
      </div>`;
      return;
    }

    const iconType = n => n.type?.includes('user') ? 'user' : n.type?.includes('submit') ? 'submit' : 'default';

    container.innerHTML = `<div class="notif-list">${notifs.map(n => `
      <div class="notif-item ${n.unread ? 'unread' : ''}">
        <div class="notif-icon">${notifIconMap[iconType(n)]}</div>
        <div class="notif-body">
          <div class="notif-msg">${n.msg}</div>
          <div class="notif-time">${n.time ? n.time.substring(0,16).replace('T',' ') : ''}</div>
        </div>
        <div class="notif-action" style="display:flex;align-items:center;gap:6px">
          ${n.unread ? `<button type="button" class="btn-sm-fc" onclick="markPtaItemRead(${n.id || 'null'}, '${n.key || ''}')" style="background:#e5e7eb;color:#374151">Mark read</button>` : ''}
          ${n.action ? `<a href="/dashboard/pta/${n.action}" class="btn-sm-fc btn-sm-view">${n.action_label || 'View'}</a>` : ''}
        </div>
      </div>
    `).join('')}</div>`;
  } catch(e) { console.error('Notif load error:', e); }
}

document.getElementById('btnMarkAllRead').addEventListener('click', async function() {
  await fetch('/api/pta/notifications/mark-read', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({all:true}) });
  if (typeof showToast === 'function') showToast('All notifications marked as read');
  loadNotifs();
});

document.addEventListener('DOMContentLoaded', loadNotifs);
</script>
@endsection
