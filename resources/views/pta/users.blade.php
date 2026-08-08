@extends('layouts.pta')

@section('styles')
<style>
/* ── Shared page styles ── */
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:0 24px 24px; }
.fc-table-wrap { overflow-x:auto; margin-top:4px; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:last-child { border-bottom:none; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-orange { background:#fff7ed; color:#d97706; }
.badge-red    { background:#fef2f2; color:#dc2626; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#eff6ff; color:#2563eb; }
.badge-purple { background:#f5f3ff; color:#7c3aed; }
.btn-sm-fc { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.btn-sm-view    { background:#ecfdf5; color:#059669; }
.btn-sm-view:hover { background:#d1fae5; }
.btn-sm-danger  { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.btn-sm-danger:hover { background:#fee2e2; }
.btn-sm-toggle  { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
.btn-sm-toggle:hover { background:#dbeafe; }

/* ── User Avatar ── */
.user-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; background:#ecfdf5; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#059669; border:2px solid #a7f3d0; }
</style>
@endsection

@section('content')
<div class="page active" id="page-users">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">User Management</div>
      <div class="pg-banner-sub">Manage system users, CMI representatives, and pending account approvals</div>
    </div>
  </div>

  <!-- Pending Approvals Card -->
  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Pending Account Approvals
      </div>
      <span class="badge badge-orange" id="pendingCount">—</span>
    </div>
    <div class="fc-card-body">
      <div class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Role</th><th>Institution</th><th>Designation</th><th>Action</th>
            </tr>
          </thead>
          <tbody id="pendingTbody">
            <tr><td colspan="6" style="text-align:center;padding:32px;color:#9ca3af">Loading pending users...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Active Users Card -->
  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Active Users
      </div>
      <span class="badge badge-green" id="activeCount">—</span>
    </div>
    <div class="fc-card-body">
      <div class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Role</th><th>Institution</th><th>Position</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody id="activeTbody">
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">Loading active users...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script>
function initials(name) {
  return name ? name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase() : '?';
}

async function loadUsers() {
  try {
    // --- Pending ---
    const pRes   = await fetch('/api/pta/users/pending');
    const pJson  = await pRes.json();
    const pTbody = document.getElementById('pendingTbody');
    const pending = pJson.pending || [];
    document.getElementById('pendingCount').textContent = pending.length + ' pending';

    if (pending.length === 0) {
      pTbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af">
        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 10px">
          <circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/>
        </svg>No accounts pending approval.
      </td></tr>`;
    } else {
      pTbody.innerHTML = pending.map(u => `
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px">
            <div class="user-avatar">${initials(u.first_name+' '+u.last_name)}</div>
            <strong>${u.first_name} ${u.last_name}</strong>
          </div></td>
          <td style="color:#6b7280">${u.email}</td>
          <td><span class="badge badge-purple">${u.role.toUpperCase()}</span></td>
          <td>${u.institution || '—'}</td>
          <td>${u.designation || '—'}</td>
          <td style="display:flex;gap:6px">
            <button class="btn-sm-fc btn-sm-view" onclick="approveUser(${u.id}, '${u.first_name} ${u.last_name}', 'approve')">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Approve
            </button>
            <button class="btn-sm-fc btn-sm-danger" onclick="approveUser(${u.id}, '${u.first_name} ${u.last_name}', 'reject')">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Reject
            </button>
          </td>
        </tr>
      `).join('');
    }

    // --- Active ---
    const uRes   = await fetch('/api/pta/users');
    const uJson  = await uRes.json();
    const aTbody = document.getElementById('activeTbody');
    const users  = Array.isArray(uJson) ? uJson : [];
    document.getElementById('activeCount').textContent = users.length + ' users';

    if (users.length === 0) {
      aTbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">No registered users found.</td></tr>';
    } else {
      aTbody.innerHTML = users.map(u => `
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px">
            <div class="user-avatar">${initials(u.name)}</div>
            <strong>${u.name}</strong>
          </div></td>
          <td style="color:#6b7280">${u.email}</td>
          <td><span class="badge badge-blue">${u.role}</span></td>
          <td>${u.institution || '—'}</td>
          <td>${u.position || '—'}</td>
          <td><span class="badge ${u.status==='Active' ? 'badge-green' : 'badge-red'}">${u.status}</span></td>
          <td>
            <button class="btn-sm-fc ${u.status==='Active' ? 'btn-sm-danger' : 'btn-sm-toggle'}"
              onclick="toggleUser(${u.id}, '${u.name}', '${u.status==='Active' ? 'inactive' : 'active'}')">
              ${u.status==='Active' ? 'Deactivate' : 'Activate'}
            </button>
          </td>
        </tr>
      `).join('');
    }
  } catch(e) { console.error('Users load error:', e); }
}

window.approveUser = function(id, userName, action) {
  showConfirmModal({
    title: action === 'approve' ? 'Approve User Account?' : 'Reject User Request?',
    message: `Are you sure you want to ${action} the account for ${userName}?`,
    confirmText: action === 'approve' ? 'Approve User' : 'Reject Request',
    type: action === 'approve' ? 'green' : 'red',
    onConfirm: async function() {
      const res  = await fetch('/api/pta/users/approve-user', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({user_id:id, action}) });
      const json = await res.json();
      showToast(json.message || 'Done');
      loadUsers();
    }
  });
};

window.toggleUser = function(id, userName, newStatus) {
  showConfirmModal({
    title: newStatus === 'active' ? 'Activate User Account?' : 'Deactivate User Account?',
    message: `Are you sure you want to set account status for ${userName} to ${newStatus}?`,
    confirmText: newStatus === 'active' ? 'Activate' : 'Deactivate',
    type: newStatus === 'active' ? 'green' : 'red',
    onConfirm: async function() {
      const res  = await fetch('/api/pta/users/toggle-user-status', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({user_id:id, new_status:newStatus}) });
      const json = await res.json();
      showToast(json.message || 'Done');
      loadUsers();
    }
  });
};

document.addEventListener('DOMContentLoaded', loadUsers);
</script>
@endsection
