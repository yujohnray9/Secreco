@extends('layouts.cmi')

@section('content')
<div class="page active" id="page-profile">
  <div class="page-hdr">
    <div>
      <div class="page-title">My Profile</div>
      <div class="page-sub">Manage your account information and password</div>
    </div>
  </div>

  <div class="card" style="max-width:600px;margin-bottom:24px">
    <div class="card-hdr">
      <div class="card-title">Profile Information</div>
    </div>
    <div class="card-body">
      <form id="profileForm">
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">First Name</label>
          <input type="text" class="form-input" id="profFirstName" name="first_name" required/>
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-input" id="profLastName" name="last_name" required/>
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" id="profEmail" readonly disabled style="background:#f3f4f6"/>
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Institution</label>
          <input type="text" class="form-input" id="profInst" readonly disabled style="background:#f3f4f6"/>
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Designation / Position</label>
          <input type="text" class="form-input" id="profDesig" name="designation" required/>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
      </form>
    </div>
  </div>

  <div class="card" style="max-width:600px">
    <div class="card-hdr">
      <div class="card-title">Change Password</div>
    </div>
    <div class="card-body">
      <form id="pwForm">
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-input" id="pwCurrent" name="current_password" required/>
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">New Password</label>
          <input type="password" class="form-input" id="pwNew" name="new_password" required minlength="8"/>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
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
      alert(pJson.success ? 'Profile saved!' : (pJson.message || 'Error saving profile.'));
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
        alert('Password changed successfully!');
        document.getElementById('pwForm').reset();
      } else {
        alert(pwJson.message || 'Error changing password.');
      }
    });
  } catch (e) {
    console.error('Profile load error:', e);
  }
});
</script>
@endsection
