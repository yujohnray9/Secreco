@extends('layouts.pta')

@section('styles')
<style>
/* ── Shared page styles ── */
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; flex-wrap:wrap; gap:10px; }
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
.user-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; background:#ecfdf5; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#059669; border:2px solid #a7f3d0; flex-shrink:0; }

/* ── Add User Button ── */
.btn-primary-fc { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(16,185,129,.3); transition:all .2s; }
.btn-primary-fc:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(16,185,129,.4); }

/* ── Modal ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(17,24,39,.55); backdrop-filter:blur(6px); z-index:9990; align-items:center; justify-content:center; padding:16px; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:20px; padding:28px 32px; width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,.18); max-height:90vh; overflow-y:auto; }
.modal-title { font-size:18px; font-weight:800; color:#111827; margin-bottom:4px; display:flex; align-items:center; gap:9px; }
.modal-title svg { color:#10b981; }
.modal-desc { font-size:13px; color:#6b7280; margin-bottom:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.form-label { font-size:12.5px; font-weight:600; color:#374151; }
.form-input, .form-select { border:1.5px solid #e5e7eb; border-radius:10px; padding:9px 14px; font-size:13.5px; color:#111827; background:#fafafa; outline:none; font-family:inherit; transition:all .15s; width:100%; box-sizing:border-box; }
.form-input:focus, .form-select:focus { border-color:#10b981; background:#fff; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.btn-cancel { background:#f3f4f6; color:#374151; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-cancel:hover { background:#e5e7eb; }

/* ── Temp password success box ── */
.temp-pw-box { background:#f0fdf4; border:1px solid #a7f3d0; border-radius:12px; padding:16px; margin-top:16px; display:none; }
.temp-pw-label { font-size:12px; color:#6b7280; margin-bottom:4px; }
.temp-pw-val { font-size:17px; font-weight:800; color:#059669; letter-spacing:.05em; font-family:monospace; }
</style>
@endsection

@section('content')
<div class="page active" id="page-users">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">User Management</div>
      <div class="pg-banner-sub">Manage system users, CMI representatives, and pending account approvals</div>
    </div>
    <button class="btn-primary-fc" onclick="openAddUserModal()">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="8.5" cy="7" r="4"/>
        <line x1="20" y1="8" x2="20" y2="14"/>
        <line x1="23" y1="11" x2="17" y2="11"/>
      </svg>
      Add User
    </button>
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

<!-- ═══ ADD USER MODAL ═══ -->
<div class="modal-overlay" id="modalAddUser">
  <div class="modal-box">
    <div class="modal-title">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="8.5" cy="7" r="4"/>
        <line x1="20" y1="8" x2="20" y2="14"/>
        <line x1="23" y1="11" x2="17" y2="11"/>
      </svg>
      Add New User
    </div>
    <div class="modal-desc">Create a user account directly. A temporary password will be generated — share it with the user.</div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">First Name <span style="color:#ef4444">*</span></label>
        <input class="form-input" id="addUserFirstName" placeholder="e.g. Juan"/>
      </div>
      <div class="form-group">
        <label class="form-label">Last Name <span style="color:#ef4444">*</span></label>
        <input class="form-input" id="addUserLastName" placeholder="e.g. Dela Cruz"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Email Address <span style="color:#ef4444">*</span></label>
      <input class="form-input" id="addUserEmail" type="email" placeholder="user@institution.gov.ph"/>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Role <span style="color:#ef4444">*</span></label>
        <select class="form-select" id="addUserRole" onchange="onRoleChange()">
          <option value="cmi">CMI Representative</option>
          <option value="viewer">Viewer</option>
        </select>
      </div>
      <div class="form-group" id="addUserInstGroup">
        <label class="form-label">Institution</label>
        <input class="form-input" id="addUserInstitution" placeholder="e.g. Isabela State University"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Designation / Position</label>
      <input class="form-input" id="addUserPosition" placeholder="e.g. Research Specialist"/>
    </div>

    <!-- Temp password display after creation -->
    <div class="temp-pw-box" id="tempPwBox">
      <div class="temp-pw-label">User created! Temporary password (share securely):</div>
      <div class="temp-pw-val" id="tempPwVal"></div>
      <div style="font-size:11px;color:#9ca3af;margin-top:6px">The user must change this password after first login.</div>
    </div>

    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modalAddUser')">Cancel</button>
      <button class="btn-primary-fc" id="addUserSubmitBtn" onclick="submitAddUser()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Create User
      </button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function initials(name) {
  return name ? name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase() : '?';
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  // Reset form if it's the add user modal
  if (id === 'modalAddUser') {
    ['addUserFirstName','addUserLastName','addUserEmail','addUserInstitution','addUserPosition'].forEach(f => {
      const el = document.getElementById(f);
      if (el) el.value = '';
    });
    document.getElementById('addUserRole').value = 'cmi';
    document.getElementById('tempPwBox').style.display = 'none';
    onRoleChange();
  }
}

document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', function(e){ if(e.target===this) closeModal(this.id); }));

function onRoleChange() {
  const role = document.getElementById('addUserRole').value;
  const instGroup = document.getElementById('addUserInstGroup');
  if (instGroup) instGroup.style.display = role === 'cmi' ? '' : 'none';
}

function openAddUserModal() {
  openModal('modalAddUser');
  onRoleChange();
}

async function submitAddUser() {
  const firstName = document.getElementById('addUserFirstName').value.trim();
  const lastName  = document.getElementById('addUserLastName').value.trim();
  const email     = document.getElementById('addUserEmail').value.trim();
  const role      = document.getElementById('addUserRole').value;
  const inst      = document.getElementById('addUserInstitution')?.value?.trim() || '';
  const position  = document.getElementById('addUserPosition').value.trim();

  if (!firstName || !lastName) { showToast('First and last name are required.'); return; }
  if (!email) { showToast('Email is required.'); return; }

  const btn = document.getElementById('addUserSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Creating…';

  try {
    const res  = await fetch('/api/pta/users/create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name        : firstName + ' ' + lastName,
        email,
        role,
        institution : inst,
        position,
      })
    });
    const json = await res.json();

    if (json.success) {
      document.getElementById('tempPwVal').textContent = json.temp_password;
      document.getElementById('tempPwBox').style.display = 'block';
      showToast('User created successfully!');
      btn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Done`;
      btn.onclick = function() { closeModal('modalAddUser'); };
      loadUsers();
    } else {
      showToast(json.error || json.message || 'Failed to create user.');
      btn.disabled = false;
      btn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create User`;
    }
  } catch(e) {
    showToast('Network error. Please try again.');
    btn.disabled = false;
    btn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create User`;
  }
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
