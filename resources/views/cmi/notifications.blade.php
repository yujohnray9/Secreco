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
  async function loadNotifs() {
    try {
      const year = new Date().getFullYear();
      const res = await fetch(`/api/notifications?year=${year}`);
      const json = await res.json();
      const container = document.getElementById('notifContainer');

      if (json.notifications) {
        if (json.notifications.length === 0) {
          container.innerHTML = '<div style="padding:16px;text-align:center;color:#6b7280">No notifications.</div>';
        } else {
          container.innerHTML = json.notifications.map(n => `
            <div style="padding:12px;border-bottom:1px solid #f3f4f6;background:${n.unread ? '#f0fdf4' : '#fff'};border-radius:6px;margin-bottom:8px">
              <div style="display:flex;align-items:flex-start;gap:10px">
                <div style="font-size:20px">${n.icon || '📋'}</div>
                <div style="flex:1">
                  <div style="font-size:14px;color:#1f2937">${n.msg}</div>
                  <div style="font-size:11px;color:#9ca3af;margin-top:4px">${n.time ? n.time.substring(0, 16).replace('T', ' ') : ''}</div>
                </div>
                ${n.action ? `<a href="${n.action}" class="btn btn-sm">${n.action_label || 'View'}</a>` : ''}
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
    loadNotifs();
  });

  loadNotifs();
});
</script>
@endsection
