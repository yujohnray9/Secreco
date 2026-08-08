@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-notifications">
  <div class="page-hdr">
    <div>
      <div class="page-title">Notifications</div>
      <div class="page-sub">System updates and announcements</div>
    </div>
  </div>

  <div class="card">
    <div class="card-body" id="vNotifContainer">
      <div style="padding:16px;text-align:center;color:#6b7280">Loading notifications...</div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
  try {
    const year = new Date().getFullYear();
    const res = await fetch(`/api/notifications?year=${year}`);
    const json = await res.json();
    const container = document.getElementById('vNotifContainer');

    if (json.notifications) {
      if (json.notifications.length === 0) {
        container.innerHTML = '<div style="padding:16px;text-align:center;color:#6b7280">No notifications.</div>';
      } else {
        container.innerHTML = json.notifications.map(n => `
          <div style="padding:12px;border-bottom:1px solid #f3f4f6;background:${n.unread ? '#fefce8' : '#fff'};border-radius:6px;margin-bottom:8px">
            <div style="display:flex;align-items:flex-start;gap:10px">
              <div style="font-size:20px">${n.icon || '📋'}</div>
              <div style="flex:1">
                <div style="font-size:14px;color:#1f2937">${n.msg}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:4px">${n.time ? n.time.substring(0, 16).replace('T', ' ') : ''}</div>
              </div>
            </div>
          </div>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Viewer Notif load error:', e);
  }
});
</script>
@endsection
