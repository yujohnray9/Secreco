// =====================================================
//  assets/js/pta/users.js
//  User Management — depends on core.js
// =====================================================

// ── STATE ──
let allUsers      = [];
let pendingUsers  = [];
let filteredUsers = [];

// ── HELPERS ──
function initials(name) {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function roleBadge(role) {
  return role === 'CMI Representative'
    ? `<span class="badge badge-gold">CMI Representative</span>`
    : `<span class="badge badge-blue">Viewer</span>`;
}

function statusBadge(status) {
  return status === 'Active'
    ? `<span class="badge badge-green">Active</span>`
    : `<span class="badge badge-gray">Inactive</span>`;
}

// ── FETCH PENDING ──
async function fetchPendingUsers() {
  try {
    const res = await fetch('/api/pta/users/get_pending.php');
    pendingUsers = await res.json();
  } catch {
    // fallback sample data
    pendingUsers = [
      { id: 101, name: 'Rosa Lim',     email: 'r.lim@clsu.edu.ph',  role: 'CMI Representative', position: 'Research Associate II', institution: 'CLSU',     dateRequested: 'Jun 10, 2025' },
      { id: 102, name: 'Pedro Valdez', email: 'p.valdez@da.gov.ph', role: 'Viewer',             position: 'Agriculturist II',      institution: 'DA-RFO 2', dateRequested: 'Jun 10, 2025' },
    ];
  }
  renderPending();
}

// ── RENDER PENDING ──
function renderPending() {
  const tbody   = document.getElementById('pendingTbody');
  const alert   = document.getElementById('pendingAlert');
  const countEl = document.getElementById('pendingCount');
  if (!tbody) return;

  if (pendingUsers.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="tbl-empty">No pending approvals</td></tr>`;
    if (alert) alert.style.display = 'none';
    return;
  }

  if (alert) {
    alert.style.display = 'flex';
    if (countEl) countEl.textContent = `${pendingUsers.length} account${pendingUsers.length !== 1 ? 's' : ''} pending approval`;
  }

  tbody.innerHTML = pendingUsers.map(u => `
    <tr>
      <td><strong>${u.name}</strong></td>
      <td>${u.email}</td>
      <td>${roleBadge(u.role)}</td>
      <td style="font-size:11px">${u.position || '—'}</td>
      <td>${u.institution || '—'}</td>
      <td>${u.dateRequested}</td>
      <td>
        <div class="user-actions">
          <button class="btn btn-sm btn-primary" onclick="approveUser(${u.id},'${u.name}')">Approve</button>
          <button class="btn btn-sm btn-danger"  onclick="rejectUser(${u.id},'${u.name}')">Reject</button>
        </div>
      </td>
    </tr>
  `).join('');
}

// ── APPROVE / REJECT ──
async function approveUser(id, name) {
  try {
    await fetch('/api/pta/users/approve.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: id })
    });
  } catch {}
  pendingUsers = pendingUsers.filter(u => u.id !== id);
  renderPending();
  fetchAllUsers(); // refresh main table
  toast(`${name} approved and notified`);
}

async function rejectUser(id, name) {
  try {
    await fetch('/api/pta/users/reject.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: id })
    });
  } catch {}
  pendingUsers = pendingUsers.filter(u => u.id !== id);
  renderPending();
  toast(`❌ ${name} rejected`);
}

// ── FETCH ALL USERS ──
async function fetchAllUsers() {
  try {
    const res = await fetch('/api/pta/users/get_all.php');
    allUsers  = await res.json();
  } catch {
    allUsers = [
      { id: 1, name: 'Maria Santos',   email: 'm.santos@bas.da.gov.ph',  role: 'CMI Representative', position: 'Science Research Specialist II', institution: 'BAS',               status: 'Active', lastLogin: 'Jun 10' },
      { id: 2, name: 'Ana Reyes',      email: 'a.reyes@philrice.gov.ph', role: 'CMI Representative', position: 'Researcher I',                   institution: 'PhilRice – Isabela', status: 'Active', lastLogin: 'Jun 9'  },
      { id: 3, name: 'Juan Cruz',      email: 'j.cruz@isu.edu.ph',       role: 'CMI Representative', position: 'Instructor I',                   institution: 'ISU – Echague',      status: 'Active', lastLogin: 'Jun 9'  },
      { id: 4, name: 'Linda Bautista', email: 'l.bautista@ched.gov.ph',  role: 'Viewer',             position: 'Education Supervisor II',        institution: 'CHED-RO2',           status: 'Active', lastLogin: 'Jun 8'  },
    ];
  }
  filteredUsers = [...allUsers];
  renderUsers();
}

// ── RENDER ALL USERS ──
function renderUsers() {
  const tbody   = document.getElementById('usersTbody');
  const countEl = document.getElementById('userCount');
  if (!tbody) return;

  if (filteredUsers.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="tbl-empty">No users found</td></tr>`;
    if (countEl) countEl.textContent = '0 total';
    return;
  }

  tbody.innerHTML = filteredUsers.map(u => `
    <tr>
      <td>
        <div class="user-name-cell">
          <span class="user-av-sm">${initials(u.name)}</span>
          <strong>${u.name}</strong>
        </div>
      </td>
      <td>${u.email}</td>
      <td>${roleBadge(u.role)}</td>
      <td style="font-size:11px">${u.position || '—'}</td>
      <td>${u.institution || '—'}</td>
      <td>${statusBadge(u.status)}</td>
      <td>${u.lastLogin}</td>
      <td>
        <div class="user-actions">
          <button class="btn btn-xs" onclick="openEditUser(${u.id})">Edit</button>
          ${u.status === 'Active'
            ? `<button class="btn btn-xs btn-danger" onclick="openToggleStatus(${u.id},'${u.name}','inactive')">Deactivate</button>`
            : `<button class="btn btn-xs btn-primary" onclick="openToggleStatus(${u.id},'${u.name}','active')">Activate</button>`
          }
        </div>
      </td>
    </tr>
  `).join('');

  if (countEl) countEl.textContent = `${filteredUsers.length} total`;
}

// ── FILTER ──
function filterUsers() {
  const q      = (document.getElementById('userSearch')?.value || '').toLowerCase();
  const role   = document.getElementById('roleFilter')?.value   || '';
  const status = document.getElementById('statusFilter')?.value || '';

  filteredUsers = allUsers.filter(u => {
    const matchQ = !q
      || u.name.toLowerCase().includes(q)
      || u.email.toLowerCase().includes(q)
      || (u.institution || '').toLowerCase().includes(q);
    const matchR = !role   || u.role   === role;
    const matchS = !status || u.status === status;
    return matchQ && matchR && matchS;
  });
  renderUsers();
}

// ── EDIT USER ──
function openEditUser(id) {
  const u = allUsers.find(u => u.id === id);
  if (!u) return;
  const parts = u.name.split(' ');
  document.getElementById('editUserId').value    = u.id;
  document.getElementById('editFirstName').value = parts[0] || '';
  document.getElementById('editLastName').value  = parts.slice(1).join(' ');
  document.getElementById('editEmail').value     = u.email;
  document.getElementById('editRole').value      = u.role;
  document.getElementById('editPosition').value  = u.position || '';

  const editInst = document.getElementById('editInstitution');
  if (editInst) editInst.value = u.institution || '';

  openModal('modalEditUser');
}

async function saveEditUser() {
  const id = document.getElementById('editUserId').value;
  const payload = {
    user_id:     parseInt(id),
    name:        document.getElementById('editFirstName').value + ' ' + document.getElementById('editLastName').value,
    email:       document.getElementById('editEmail').value,
    role:        document.getElementById('editRole').value,
    position:    document.getElementById('editPosition').value,
    institution: document.getElementById('editInstitution')?.value || '',
  };
  try {
    await fetch('/api/pta/users/update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
  } catch {}
  // Update local state
  const u = allUsers.find(u => u.id === payload.user_id);
  if (u) {
    u.name        = payload.name;
    u.email       = payload.email;
    u.role        = payload.role;
    u.position    = payload.position;
    u.institution = payload.institution;
  }
  filteredUsers = [...allUsers];
  renderUsers();
  closeModal('modalEditUser');
  toast('User updated successfully!');
}

// ── ADD USER ──
async function addUser() {
  const payload = {
    name:        document.getElementById('addFirstName').value + ' ' + document.getElementById('addLastName').value,
    email:       document.getElementById('addEmail').value,
    role:        document.getElementById('addRole').value,
    position:    document.getElementById('addPosition').value,
    institution: document.getElementById('addInstitution')?.value || '',
  };
  try {
    await fetch('/api/pta/users/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
  } catch {}

  // Reset form fields
  ['addFirstName','addLastName','addEmail','addPosition'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const addInst = document.getElementById('addInstitution');
  if (addInst) addInst.value = '';

  closeModal('modalAddUser');
  fetchAllUsers();
  toast('User created and invite sent!');
}

// ── TOGGLE STATUS ──
function openToggleStatus(id, name, targetStatus) {
  const isDeactivating = targetStatus === 'inactive';
  document.getElementById('toggleStatusUserId').value = id;
  document.getElementById('toggleStatusValue').value = targetStatus;
  
  document.getElementById('toggleStatusTitle').textContent = isDeactivating ? '⚠️ Deactivate User' : 'Activate User';
  document.getElementById('toggleStatusDesc').textContent = isDeactivating
    ? `Are you sure you want to deactivate ${name}? They will no longer be able to log in.`
    : `Are you sure you want to activate ${name}? They will regain access to the system.`;
    
  const btn = document.getElementById('toggleStatusBtn');
  btn.textContent = isDeactivating ? 'Deactivate' : 'Activate';
  btn.className = isDeactivating ? 'btn btn-danger' : 'btn btn-primary';

  openModal('modalToggleStatus');
}

async function toggleUserStatus() {
  const id = parseInt(document.getElementById('toggleStatusUserId').value);
  const status = document.getElementById('toggleStatusValue').value;
  const isDeactivating = status === 'inactive';

  try {
    await fetch('/api/pta/users/toggle_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: id, status: status })
    });
  } catch {}
  
  const u = allUsers.find(u => u.id === id);
  if (u) u.status = isDeactivating ? 'Inactive' : 'Active';
  
  filteredUsers = [...allUsers];
  renderUsers();
  closeModal('modalToggleStatus');
  
  toast(isDeactivating ? `🔒 ${u?.name || 'User'} has been deactivated` : `${u?.name || 'User'} is now active`);
}

// ── BOOT ──
document.addEventListener('DOMContentLoaded', () => {
  fetchPendingUsers();
  fetchAllUsers();
});
