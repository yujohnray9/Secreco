@extends('layouts.cmi')

@section('styles')
<style>
.profile-photo-row { display:flex; align-items:center; gap:20px; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #f3f4f6; }
.profile-avatar { width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #a7f3d0; background:#ecfdf5; flex-shrink:0; }
.upload-btn { display:inline-flex; align-items:center; gap:7px; background:#ecfdf5; color:#059669; border:1.5px solid #a7f3d0; border-radius:10px; padding:7px 14px; font-size:12.5px; font-weight:600; cursor:pointer; transition:all .15s; }
.upload-btn:hover { background:#d1fae5; }
.profile-photo-hint { font-size:11.5px; color:#9ca3af; margin-top:4px; }

/* ── Password Wrapper with Eye Icon ── */
.pw-wrapper { position:relative; width:100%; display:flex; align-items:center; }
.pw-wrapper .form-input { padding-right:42px; width:100%; }
.pw-toggle-btn { position:absolute; right:10px; background:none; border:none; padding:4px; cursor:pointer; color:#9ca3af; display:flex; align-items:center; justify-content:center; transition:color .15s; border-radius:6px; }
.pw-toggle-btn:hover { color:#10b981; background:#f3f4f6; }
</style>
@endsection

@section('content')
<div class="page active" id="page-profile">
  <div class="page-hdr" style="margin-bottom:24px">
    <div>
      <div class="page-title" style="font-size:22px;font-weight:700;color:#111827">My Profile</div>
      <div class="page-sub" style="font-size:13px;color:#6b7280;margin-top:3px">Manage your account information, profile picture, and password</div>
    </div>
  </div>

  <div class="card" style="max-width:600px;margin-bottom:24px">
    <div class="card-hdr" style="padding:16px 20px;border-bottom:1px solid #f3f4f6">
      <div class="card-title" style="font-size:15px;font-weight:700;color:#111827">Profile Information</div>
    </div>
    <div class="card-body" style="padding:20px">
      <!-- Profile Photo -->
      <div class="profile-photo-row">
        <img id="cmiAvatarPreview" class="profile-avatar" src="/assets/img/default-avatar.svg" alt="avatar"/>
        <div>
          <label class="upload-btn" for="cmiAvatarFile">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Change Photo
          </label>
          <input type="file" id="cmiAvatarFile" accept="image/*" style="display:none"/>
          <div class="profile-photo-hint">JPG, PNG or WEBP (Max 5MB)</div>
        </div>
      </div>

      <form id="profileForm">
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">First Name <span style="color:#ef4444">*</span></label>
          <input type="text" class="form-input" id="profFirstName" name="first_name" required/>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Last Name <span style="color:#ef4444">*</span></label>
          <input type="text" class="form-input" id="profLastName" name="last_name" required/>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-input" id="profEmail" readonly disabled style="background:#f9fafb;color:#9ca3af"/>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Institution</label>
          <input type="text" class="form-input" id="profInst" readonly disabled style="background:#f9fafb;color:#9ca3af"/>
        </div>
        <div class="form-group" style="margin-bottom:18px">
          <label class="form-label">Designation / Position <span style="color:#ef4444">*</span></label>
          <input type="text" class="form-input" id="profDesig" name="designation" required/>
        </div>
        <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-size:13.5px;font-weight:700;cursor:pointer">Save Profile</button>
      </form>
    </div>
  </div>

  <div class="card" style="max-width:600px">
    <div class="card-hdr" style="padding:16px 20px;border-bottom:1px solid #f3f4f6">
      <div class="card-title" style="font-size:15px;font-weight:700;color:#111827">Change Password</div>
    </div>
    <div class="card-body" style="padding:20px">
      <form id="pwForm">
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">Current Password <span style="color:#ef4444">*</span></label>
          <div class="pw-wrapper">
            <input type="password" class="form-input" id="pwCurrent" name="current_password" required autocomplete="current-password"/>
            <button type="button" class="pw-toggle-btn" onclick="togglePwVisibility('pwCurrent', this)" title="Show/Hide Password">
              <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label class="form-label">New Password <span style="color:#ef4444">*</span></label>
          <div class="pw-wrapper">
            <input type="password" class="form-input" id="pwNew" name="new_password" required minlength="8" autocomplete="new-password" oninput="checkCmiPwStrength(this.value)"/>
            <button type="button" class="pw-toggle-btn" onclick="togglePwVisibility('pwNew', this)" title="Show/Hide Password">
              <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <!-- Password Strength Indicator -->
          <div class="pw-strength-box" style="margin-top:6px">
            <div style="display:flex;gap:4px;height:4px;margin-bottom:4px">
              <div class="pws-seg" id="cmiPws1" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="cmiPws2" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="cmiPws3" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
              <div class="pws-seg" id="cmiPws4" style="flex:1;height:100%;border-radius:2px;background:#e5e7eb;transition:all .2s"></div>
            </div>
            <div id="cmiPwHint" style="font-size:11.5px;color:#6b7280;font-weight:500">Min. 8 chars, uppercase, number, symbol</div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-size:13.5px;font-weight:700;cursor:pointer">Update Password</button>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
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

function checkCmiPwStrength(val) {
  const segs = [1,2,3,4].map(n => document.getElementById('cmiPws' + n));
  const hint = document.getElementById('cmiPwHint');
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

document.addEventListener('DOMContentLoaded', async function () {
  try {
    const res = await fetch('/api/cmi/profile');
    const json = await res.json();
    if (json.success) {
      document.getElementById('profFirstName').value = json.first_name || '';
      document.getElementById('profLastName').value = json.last_name || '';
      document.getElementById('profEmail').value = json.email || '';
      document.getElementById('profInst').value = json.institution || '';
      document.getElementById('profDesig').value = json.designation || '';
    }
  } catch (e) {
    console.error('Profile load error:', e);
  }

  // Upload photo handler
  const cmiPhotoInput = document.getElementById('cmiAvatarFile');
  if (cmiPhotoInput) {
    cmiPhotoInput.addEventListener('change', async function() {
      const file = this.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = e => {
        const preview = document.getElementById('cmiAvatarPreview');
        if (preview) preview.src = e.target.result;
      };
      reader.readAsDataURL(file);

      const formData = new FormData();
      formData.append('photo', file);

      try {
        const res  = await fetch('/api/cmi/profile/upload-photo', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          const freshUrl = '/' + data.photo + '?t=' + Date.now();
          const preview = document.getElementById('cmiAvatarPreview');
          if (preview) preview.src = freshUrl;
          document.querySelectorAll('.hdr-user-avatar').forEach(img => img.src = freshUrl);
          showToast('Profile photo updated!');
        } else {
          showToast(data.message || 'Photo upload failed.');
        }
      } catch (err) {
        showToast('Failed to upload photo.');
      }
    });
  }

  document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const body = {
      first_name: document.getElementById('profFirstName').value,
      last_name: document.getElementById('profLastName').value,
      designation: document.getElementById('profDesig').value,
    };
    const pRes = await fetch('/api/cmi/profile/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const pJson = await pRes.json();
    if (pJson.success) {
      showToast('Profile saved!');
      if (pJson.name) {
        document.querySelectorAll('.hdr-user-name').forEach(el => el.textContent = pJson.name);
      }
    } else {
      showToast(pJson.message || 'Error saving profile.');
    }
  });

  document.getElementById('pwForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const body = {
      current_password: document.getElementById('pwCurrent').value,
      new_password: document.getElementById('pwNew').value,
    };
    const pwRes = await fetch('/api/cmi/profile/change-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const pwJson = await pwRes.json();
    if (pwJson.success) {
      showToast('Password changed successfully!');
      document.getElementById('pwForm').reset();
      checkCmiPwStrength('');
    } else {
      showToast(pwJson.message || 'Error changing password.');
    }
  });
});
</script>
@endsection
