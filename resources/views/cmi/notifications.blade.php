@extends('layouts.cmi')

@section('content')
<div class="page active" id="page-notifications">
  <div class="page-hdr" style="display:flex;justify-content:space-between;align-items:center">
    <div>
      <div class="page-title">Notifications</div>
      <div class="page-sub">System updates and PTA remarks</div>
    </div>
    <button type="button" class="btn" id="btnMarkAllRead">Mark All as Read</button>
  </div>

  <div class="card">
    <div class="card-body" id="notifContainer">
      <div style="padding:16px;text-align:center;color:#6b7280">Loading notifications...</div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
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

  async function loadNotifs() {
    try {
      const year = new Date().getFullYear();
      const res = await fetch(`/api/notifications?year=${year}`);
      const json = await res.json();
      const container = document.getElementById('notifContainer');

      if (json.ok) {
        updateBellBadge(json.unread_count || 0);
        if (!json.notifications || json.notifications.length === 0) {
          container.innerHTML = '<div style="padding:24px;text-align:center;color:#6b7280">No notifications.</div>';
        } else {
          container.innerHTML = json.notifications.map(n => `
            <div style="padding:14px;border:1px solid ${n.unread ? '#a7f3d0' : '#e5e7eb'};background:${n.unread ? '#f0fdf4' : '#fff'};border-radius:10px;margin-bottom:10px;display:flex;align-items:flex-start;gap:12px;justify-content:space-between">
              <div style="display:flex;align-items:flex-start;gap:12px;flex:1">
                <div style="font-size:22px;line-height:1">${n.icon || '📋'}</div>
                <div>
                  <div style="font-size:13.5px;color:#1f2937;font-weight:${n.unread ? '600' : '400'}">${n.msg}</div>
                  <div style="font-size:11px;color:#9ca3af;margin-top:4px">${n.time ? n.time.substring(0, 16).replace('T', ' ') : ''}</div>
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                ${n.unread ? `<button type="button" class="btn btn-sm" onclick="markItemRead(${n.id || 'null'}, '${n.key || ''}')" style="background:#e5e7eb;color:#374151;border:none;padding:4px 10px;font-size:12px">Mark read</button>` : ''}
                ${n.action ? `<a href="${n.action}" class="btn btn-sm" style="background:#10b981;color:#fff;padding:4px 10px;font-size:12px">${n.action_label || 'View'}</a>` : ''}
              </div>
            </div>
          `).join('');
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

  loadNotifs();
});
</script>
@endsection
