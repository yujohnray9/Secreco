@extends('layouts.pta')

@section('styles')
<style>
/* ── Page Banner ── */
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }

/* ── Card ── */
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }

/* ── Tab Bar ── */
.settings-tab-bar { display:flex; gap:2px; border-bottom:2px solid #f3f4f6; padding:0 24px; overflow-x:auto; }
.settings-tab-btn { padding:14px 18px; font-size:13.5px; font-weight:600; color:#6b7280; border:none; background:transparent; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; white-space:nowrap; transition:all .15s; display:flex; align-items:center; gap:7px; }
.settings-tab-btn:hover { color:#374151; }
.settings-tab-btn.active { color:#10b981; border-bottom-color:#10b981; }
.settings-tab-btn svg { width:16px; height:16px; min-width:16px; min-height:16px; flex-shrink:0; }
.section-title svg { width:18px; height:18px; min-width:18px; min-height:18px; flex-shrink:0; display:inline-block; vertical-align:middle; }
.btn-save svg, .upload-btn svg { width:15px; height:15px; flex-shrink:0; }

/* ── Tab Panel ── */
.settings-panel { display:none; padding:28px 28px 32px; }
.settings-panel.active { display:block; }

/* ── Profile Layout ── */
.profile-photo-row { display:flex; align-items:center; gap:20px; margin-bottom:28px; padding-bottom:24px; border-bottom:1px solid #f3f4f6; }
.profile-avatar { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #a7f3d0; background:#ecfdf5; flex-shrink:0; }
.profile-avatar-text { width:80px; height:80px; border-radius:50%; border:3px solid #a7f3d0; background:#ecfdf5; color:#059669; font-size:28px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.profile-photo-hint { font-size:12px; color:#9ca3af; margin-top:6px; }

/* ── Form ── */
.form-row  { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:700px) { .form-row { grid-template-columns:1fr; } }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:16px; }
.form-label { font-size:12.5px; font-weight:600; color:#374151; }
.form-input, .form-select { border:1.5px solid #e5e7eb; border-radius:10px; padding:9px 14px; font-size:13.5px; color:#111827; background:#fafafa; outline:none; font-family:inherit; transition:all .15s; }
.form-input:focus, .form-select:focus { border-color:#10b981; background:#fff; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
.form-input:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; }
.section-title { font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.section-title svg { color:#10b981; }
.section-divider { border:none; border-top:1px solid #f3f4f6; margin:24px 0; }

/* ── Password Wrapper with Eye Icon ── */
.pw-wrapper { position:relative; width:100%; display:flex; align-items:center; }
.pw-wrapper .form-input { padding-right:42px; width:100%; }
.pw-toggle-btn { position:absolute; right:10px; background:none; border:none; padding:4px; cursor:pointer; color:#9ca3af; display:flex; align-items:center; justify-content:center; transition:color .15s; border-radius:6px; }
.pw-toggle-btn:hover { color:#10b981; background:#f3f4f6; }

/* ── Buttons ── */
.btn-save { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:12px; padding:10px 22px; font-size:13.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(16,185,129,.3); transition:all .2s; }
.btn-save:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(16,185,129,.4); }
.upload-btn { display:inline-flex; align-items:center; gap:7px; background:#ecfdf5; color:#059669; border:1.5px solid #a7f3d0; border-radius:10px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.upload-btn:hover { background:#d1fae5; }

/* ── Access Control Table ── */
.perm-tbl { width:100%; border-collapse:collapse; font-size:13px; }
.perm-tbl thead th { background:#f9fafb; padding:10px 16px; text-align:left; font-size:11.5px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.perm-tbl tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.perm-tbl tbody tr:hover { background:#f9fafe; }
.perm-tbl td { padding:11px 16px; color:#374151; vertical-align:middle; }
.perm-tbl td:first-child { font-weight:600; color:#111827; }
.perm-chk { color:#10b981; font-weight:700; font-size:15px; }
.perm-crs { color:#ef4444; font-weight:700; font-size:15px; }
.perm-part { font-size:11.5px; color:#f59e0b; font-weight:600; }

/* ── Audit ── */
.search-input { border:1.5px solid #e5e7eb; border-radius:10px; padding:9px 14px; font-size:13.5px; color:#111827; background:#fafafa; outline:none; width:100%; max-width:320px; transition:all .15s; }
.search-input:focus { border-color:#10b981; background:#fff; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
.fc-table-wrap { overflow-x:auto; margin-top:12px; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:12px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }

/* ── Notif Toggle ── */
.toggle-row { display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid #f3f4f6; }
.toggle-row:last-child { border-bottom:none; }
.toggle-label { font-size:13.5px; font-weight:600; color:#374151; }
.toggle-sub   { font-size:12px; color:#9ca3af; margin-top:2px; }
.toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; top:0; left:0; right:0; bottom:0; background:#e5e7eb; border-radius:24px; cursor:pointer; transition:.3s; }
.toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.toggle-switch input:checked + .toggle-slider { background:#10b981; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(20px); }
</style>
@endsection

@section('content')
<div class="page active" id="page-settings">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Settings</div>
      <div class="pg-banner-sub">Manage your profile and system configuration</div>
    </div>
  </div>

  <div class="fc-card">

    <!-- ── TAB BAR ── -->
    <div class="settings-tab-bar">
      <button class="settings-tab-btn active" onclick="switchTab('stab-profile',this)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        My Profile
      </button>
      <button class="settings-tab-btn" onclick="switchTab('stab-security',this)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Change Password
      </button>
      <button class="settings-tab-btn" onclick="switchTab('stab-access',this)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Access Control
      </button>
      <button class="settings-tab-btn" onclick="switchTab('stab-notifs',this)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Notification Settings
      </button>
      <button class="settings-tab-btn" onclick="switchTab('stab-audit',this); loadAuditLogs()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Audit Logs
      </button>
      <button class="settings-tab-btn" onclick="switchTab('stab-report',this)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Report Settings
      </button>
    </div>

    <!-- ── MY PROFILE ── -->
    <div class="settings-panel active" id="stab-profile">
      <!-- Avatar -->
      <div class="profile-photo-row">
        <img id="avatarPreview" class="profile-avatar" src="/assets/img/default-avatar.svg" alt="avatar"
          onerror="this.style.display='none';document.getElementById('avatarInitials').style.display='flex'"/>
        <div id="avatarInitials" class="profile-avatar-text" style="display:none">PTA</div>
        <div>
          <label class="upload-btn" for="avatarFile">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Change Photo
          </label>
          <input type="file" id="avatarFile" accept="image/*" style="display:none"/>
          <div class="profile-photo-hint">JPG or PNG, at least 200×200px</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First Name <span style="color:#ef4444">*</span></label>
          <input class="form-input" type="text" id="profFirstName" placeholder="Enter first name"/>
        </div>
        <div class="form-group">
          <label class="form-label">Last Name <span style="color:#ef4444">*</span></label>
          <input class="form-input" type="text" id="profLastName" placeholder="Enter last name"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Designation / Position</label>
          <input class="form-input" type="text" id="profDesig" placeholder="e.g. Project Technical Assistant II"/>
        </div>
        <div class="form-group">
          <label class="form-label">Institution</label>
          <input class="form-input" type="text" value="CVAARRD Consortium Office" disabled/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input class="form-input" type="email" id="profEmail" disabled/>
      </div>
      <button class="btn-save" onclick="saveProfile()">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Changes
      </button>
    </div>

    <!-- ── CHANGE PASSWORD ── -->
    <div class="settings-panel" id="stab-security">
      <div class="section-title">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Change Password
      </div>
      <div class="form-row" style="max-width:540px">
        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">Current Password <span style="color:#ef4444">*</span></label>
          <div class="pw-wrapper">
            <input class="form-input" type="password" id="pwCurrent" placeholder="Enter current password" autocomplete="current-password"/>
            <button type="button" class="pw-toggle-btn" onclick="togglePwVisibility('pwCurrent', this)" title="Show/Hide Password">
              <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>

        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">New Password <span style="color:#ef4444">*</span></label>
          <div class="pw-wrapper">
            <input class="form-input" type="password" id="pwNew" placeholder="Min. 8 characters" autocomplete="new-password" oninput="checkChangePwStrength(this.value)"/>
            <button type="button" class="pw-toggle-btn" onclick="togglePwVisibility('pwNew', this)" title="Show/Hide Password">
              <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <!-- Password Strength Indicator -->
          <div class="pw-strength-box" style="margin-top:6px">
            <div style="display:flex;gap:4px;height:4px;margin-bottom:4px">
              <div class="pws-seg" id="pws1" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="pws2" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="pws3" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="pws4" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
            </div>
            <div id="pwHint" style="font-size:11.5px;color:#6b7280;font-weight:500">Min. 8 chars, uppercase, number, symbol</div>
          </div>
        </div>

        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">Confirm New Password <span style="color:#ef4444">*</span></label>
          <div class="pw-wrapper">
            <input class="form-input" type="password" id="pwConfirm" placeholder="Re-enter new password" autocomplete="new-password"/>
            <button type="button" class="pw-toggle-btn" onclick="togglePwVisibility('pwConfirm', this)" title="Show/Hide Password">
              <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
      </div>
      <button class="btn-save" onclick="changePassword()">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Update Password
      </button>
    </div>

    <!-- ── ACCESS CONTROL ── -->
    <div class="settings-panel" id="stab-access">
      <div class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Role Permission Matrix
      </div>
      <div class="fc-table-wrap">
        <table class="perm-tbl">
          <thead><tr><th>Permission</th><th>PTA Admin</th><th>CMI Rep</th><th>Viewer</th></tr></thead>
          <tbody>
            <tr><td>Fill Up / Submit Tables</td><td><span class="perm-chk">✔</span></td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>View All CMI Submissions</td><td><span class="perm-chk">✔</span></td><td><span class="perm-part">Own only</span></td><td><span class="perm-chk">✔</span></td></tr>
            <tr><td>Accept Submissions</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>Request Correction</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>Consolidate / Export Reports</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>View Reports (screen only)</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-chk">✔</span></td></tr>
            <tr><td>Manage Users</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>Manage Formats</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>View Institutions</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-chk">✔</span></td></tr>
            <tr><td>Access Settings</td><td><span class="perm-chk">✔</span></td><td><span class="perm-crs">✘</span></td><td><span class="perm-crs">✘</span></td></tr>
            <tr><td>Change Own Password</td><td><span class="perm-chk">✔</span></td><td><span class="perm-chk">✔</span></td><td><span class="perm-chk">✔</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── NOTIFICATION SETTINGS ── -->
    <div class="settings-panel" id="stab-notifs">
      <div class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Notification Preferences
      </div>

      <div class="toggle-row">
        <div>
          <div class="toggle-label">Email Reminders to CMIs</div>
          <div class="toggle-sub">Send automated email reminders to CMIs about upcoming deadlines</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="togEmailReminders" checked/>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="toggle-row">
        <div>
          <div class="toggle-label">New Submission Alerts</div>
          <div class="toggle-sub">Notify PTA when a CMI submits a new report</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="togSubmitAlert" checked/>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="toggle-row">
        <div>
          <div class="toggle-label">New User Registration Alerts</div>
          <div class="toggle-sub">Notify PTA when a new user registers and awaits approval</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="togUserAlert" checked/>
          <span class="toggle-slider"></span>
        </label>
      </div>

      <hr class="section-divider"/>

      <div class="form-group" style="max-width:300px">
        <label class="form-label">Deadline Reminder (days before)</label>
        <select class="form-select" id="sysReminderDays">
          <option value="7,1">7 days &amp; 1 day before</option>
          <option value="7">7 days only</option>
          <option value="3,1">3 days &amp; 1 day</option>
          <option value="1">1 day only</option>
        </select>
      </div>

      <button class="btn-save" onclick="saveNotifSettings()">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Notification Settings
      </button>
    </div>

    <!-- ── AUDIT LOGS ── -->
    <div class="settings-panel" id="stab-audit">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px">
        <div class="section-title" style="margin-bottom:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          System Audit Log
        </div>
        <input class="search-input" id="auditSearch" placeholder="Search logs..." oninput="filterAuditLogs()"/>
      </div>
      <div class="fc-table-wrap">
        <table class="fc-table" id="auditTable">
          <thead>
            <tr><th>Date &amp; Time</th><th>User</th><th>Action</th></tr>
          </thead>
          <tbody id="auditBody">
            <tr><td colspan="3" style="text-align:center;padding:32px;color:#9ca3af">Click the Audit Logs tab to load logs.</td></tr>
          </tbody>
        </table>
      </div>
      <div id="auditEmpty" style="display:none;padding:24px;text-align:center;color:#9ca3af;font-size:13px">No logs match your search.</div>
    </div>

    <!-- ── REPORT SETTINGS ── -->
    <div class="settings-panel" id="stab-report">
      <div class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Report &amp; Submission Settings
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Active Report Year</label>
          <input class="form-input" type="text" id="cfgActiveYear" value="CY {{ date('Y') }}" disabled/>
          <div style="font-size:11px;color:#9ca3af;margin-top:4px">Change via Manage Formats → Activate Year</div>
        </div>
        <div class="form-group">
          <label class="form-label">Submission Deadline</label>
          <input class="form-input" type="date" id="sysDeadline" value="{{ date('Y') }}-12-31"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Late Submission Policy</label>
          <select class="form-select" id="sysLatePolicy">
            <option value="not_allowed">Not Allowed</option>
            <option value="allowed_with_approval">Allowed with PTA Approval</option>
            <option value="always_allowed">Always Allowed</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Consortium Name</label>
          <input class="form-input" type="text" id="sysConsortium" value="CVAARRD Consortium Office"/>
        </div>
      </div>
      <button class="btn-save" onclick="saveReportSettings()">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Report Settings
      </button>
    </div>

  </div><!-- /fc-card -->
</div>
@endsection

@section('scripts')
<script>
// ── Tab Switching ────────────────────────────────────────────────
function switchTab(panelId, btn) {
  document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(panelId).classList.add('active');
  if (panelId === 'stab-profile') loadProfile();
}

// ── Show/Hide Password Toggle ──────────────────────────────────────
function togglePwVisibility(inputId, btn) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  const isPw = inp.type === 'password';
  inp.type = isPw ? 'text' : 'password';
  const openIcon = btn.querySelector('.eye-open');
  const closedIcon = btn.querySelector('.eye-closed');
  if (openIcon && closedIcon) {
    openIcon.style.display = isPw ? 'none' : 'block';
    closedIcon.style.display = isPw ? 'block' : 'none';
  }
}

// ── Password Strength Checker ─────────────────────────────────────
function checkChangePwStrength(val) {
  const segs = [1,2,3,4].map(n => document.getElementById('pws' + n));
  const hint = document.getElementById('pwHint');
  if (!segs[0] || !hint) return;

  if (!val) {
    segs.forEach(s => { if (s) s.style.background = '#e5e7eb'; });
    hint.textContent = 'Min. 8 chars, uppercase, number, symbol';
    hint.style.color = '#6b7280';
    return;
  }

  let score = 0;
  if (val.length >= 8)           score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
  const labels = ['Weak', 'Fair — add uppercase & number', 'Good — add a symbol', 'Strong password'];

  const color = colors[Math.max(0, score - 1)];
  segs.forEach((s, i) => {
    if (s) s.style.background = i < score ? color : '#e5e7eb';
  });
  hint.textContent = labels[Math.max(0, score - 1)] || 'Weak';
  hint.style.color = color;
}

function formatPhotoUrl(path) {
  if (!path) return '/assets/img/default-avatar.svg';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  let clean = path.replace(/^\/+/, '');
  if (!clean.startsWith('storage/') && !clean.startsWith('assets/')) {
    clean = 'storage/' + clean;
  }
  return '/' + clean;
}

// ── Load Profile ─────────────────────────────────────────────────
async function loadProfile() {
  try {
    const res  = await fetch('/api/pta/settings/save', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'get_profile'}) });
    const json = await res.json();
    if (json.user) {
      document.getElementById('profFirstName').value = json.user.first_name || '';
      document.getElementById('profLastName').value  = json.user.last_name  || '';
      document.getElementById('profDesig').value     = json.user.designation || 'Project Technical Assistant II';
      if (json.user.email) document.getElementById('profEmail').value = json.user.email;
      if (json.user.photo) {
        const photoUrl = formatPhotoUrl(json.user.photo) + '?t=' + Date.now();
        document.getElementById('avatarPreview').src = photoUrl;
        document.getElementById('avatarPreview').style.display = 'block';
        document.querySelectorAll('.hdr-user-avatar').forEach(img => img.src = photoUrl);
      }
      // Initials fallback
      const init = ((json.user.first_name||'')[0]||'') + ((json.user.last_name||'')[0]||'');
      document.getElementById('avatarInitials').textContent = init.toUpperCase();
    }
  } catch(e) {}
}

// ── Save Profile ─────────────────────────────────────────────────
async function saveProfile() {
  const first = document.getElementById('profFirstName').value.trim();
  const last  = document.getElementById('profLastName').value.trim();
  if (!first || !last) { showToast('First and Last name are required.'); return; }
  const res  = await fetch('/api/pta/settings/save', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'profile', first_name:first, last_name:last, designation:document.getElementById('profDesig').value}) });
  const json = await res.json();
  showToast(json.message || 'Saved!');
  if (json.name) {
    document.querySelectorAll('.hdr-user-name').forEach(el => el.textContent = json.name);
  }
}

// ── Change Password ───────────────────────────────────────────────
async function changePassword() {
  const cur = document.getElementById('pwCurrent').value;
  const nw  = document.getElementById('pwNew').value;
  const cnf = document.getElementById('pwConfirm').value;
  if (!cur || !nw || !cnf) { showToast('All fields are required.'); return; }
  if (nw.length < 8)       { showToast('Password must be at least 8 characters.'); return; }
  if (nw !== cnf)          { showToast('Passwords do not match.'); return; }
  const res  = await fetch('/api/pta/settings/save', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'password', current_password:cur, new_password:nw, confirm_password:cnf}) });
  const json = await res.json();
  showToast(json.message || 'Done');
  if (json.ok) {
    document.getElementById('pwCurrent').value = document.getElementById('pwNew').value = document.getElementById('pwConfirm').value = '';
    checkChangePwStrength('');
  }
}

// ── Notification Settings ─────────────────────────────────────────
async function saveNotifSettings() {
  showToast('Notification settings saved!');
}

// ── Report Settings ───────────────────────────────────────────────
async function saveReportSettings() {
  const res  = await fetch('/api/pta/settings/save', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'system', submission_deadline:document.getElementById('sysDeadline').value, late_submission_policy:document.getElementById('sysLatePolicy').value, consortium_name:document.getElementById('sysConsortium').value}) });
  const json = await res.json();
  showToast(json.message || 'Saved!');
}

// ── Audit Logs ───────────────────────────────────────────────────
async function loadAuditLogs() {
  const tbody = document.getElementById('auditBody');
  tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:24px;color:#9ca3af">Loading...</td></tr>';
  try {
    const res  = await fetch('/api/pta/settings/audit');
    const json = await res.json();
    const logs = json.logs || [];
    if (!logs.length) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:40px;color:#9ca3af">No audit log entries found.</td></tr>';
      return;
    }
    tbody.innerHTML = logs.map(l => `
      <tr data-search="${esc(l.description+' '+l.actor)}">
        <td style="color:#6b7280;font-size:12.5px;white-space:nowrap">${formatDate(l.created_at)}</td>
        <td><strong>${esc(l.actor||'System')}</strong></td>
        <td>${esc(l.description)}</td>
      </tr>
    `).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:40px;color:#9ca3af">Failed to load audit logs.</td></tr>'; }
}

function filterAuditLogs() {
  const q = document.getElementById('auditSearch').value.toLowerCase();
  let any = false;
  document.querySelectorAll('#auditTable tbody tr').forEach(tr => {
    const show = !q || (tr.dataset.search||'').toLowerCase().includes(q);
    tr.style.display = show ? '' : 'none';
    if (show) any = true;
  });
  document.getElementById('auditEmpty').style.display = (!any && q) ? 'block' : 'none';
}

function formatDate(str) {
  try { return new Date(str).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}); }
  catch { return str; }
}
function esc(s) { const el=document.createElement('span'); el.textContent=String(s??''); return el.innerHTML; }

// ── Avatar Photo Upload Handler ──────────────────────────────────
document.getElementById('avatarFile').addEventListener('change', async function() {
  const file = this.files[0];
  if (!file) return;

  // Immediate preview
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('avatarPreview').src = e.target.result;
    document.getElementById('avatarPreview').style.display = 'block';
  };
  reader.readAsDataURL(file);

  // Upload to server
  const formData = new FormData();
  formData.append('photo', file);

  try {
    const res  = await fetch('/api/pta/profile/upload-photo', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      const freshPhotoUrl = formatPhotoUrl(data.photo) + '?t=' + Date.now();
      document.getElementById('avatarPreview').src = freshPhotoUrl;
      document.querySelectorAll('.hdr-user-avatar').forEach(img => img.src = freshPhotoUrl);
      showToast('Profile photo updated!');
    } else {
      showToast(data.message || 'Photo upload failed');
    }
  } catch (err) {
    showToast('Failed to upload profile photo.');
  }
});

document.addEventListener('DOMContentLoaded', loadProfile);
</script>
@endsection
