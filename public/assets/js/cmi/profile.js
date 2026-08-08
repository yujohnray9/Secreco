/**
 * profile.js — CMI My Profile
 *
 * Loads from /api/cmi/profile.php and saves via
 * /api/cmi/save_profile.php and /api/cmi/change_password.php.
 *
 * EDITABLE: Full Name, Designation / Position.
 * READ-ONLY (never sent to the server): Email, Institution, Role.
 */
(function () {
  'use strict';

  const API_PROFILE   = '/api/cmi/profile';
  const API_CHANGE_PW = '/api/cmi/profile/change-password';
  const API_SAVE_INFO = '/api/cmi/profile/save';

  /* ── Load profile ── */
  function loadProfile() {
    fetch(API_PROFILE, { credentials: 'same-origin' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(d => {
        if (!d.success) throw new Error(d.message || 'Failed to load.');
        renderProfile(d);
      })
      .catch(err => {
        console.error('Profile error:', err);
        showInfoMsg('error', 'Could not load profile data. Please refresh the page. (' + err.message + ')');
      });
  }

  function initialsFromName(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[parts.length - 1]?.[0] || '')).toUpperCase();
  }

  function renderProfile(d) {
    const fullName = d.name || [d.first_name, d.last_name].filter(Boolean).join(' ');
    const initials = initialsFromName(fullName);

    setText('profAvatar',      initials || '?');
    setText('profHeaderName',  fullName || '—');
    setText('profHeaderRole',  d.role_label || d.role || '—');
    setText('profHeaderSince', d.member_since || '—');

    // Editable
    setVal('editName',        fullName       || '');
    setVal('editDesignation', d.designation  || '');

    // Read-only — institution & role can never be changed from this form.
    // Government accounts are tied to the institution/role approved at
    // registration; only an admin re-approval can change them.
    setVal('editEmail',  d.email       || '');
    setVal('editAgency', d.institution || '— Not set —');
    setVal('editRole',   d.role_label  || d.role || '—');
    setVal('editSince',  d.member_since || '');

    lockReadOnlyFields();
  }

  // Belt-and-suspenders: enforce readonly/disabled in JS even if the HTML
  // markup is ever edited without the readonly attribute.
  function lockReadOnlyFields() {
    ['editEmail', 'editAgency', 'editRole', 'editSince'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.readOnly = true;
      el.classList.add('readonly');
      el.tabIndex = -1;
    });
  }

  /* ── Save info ── */
  window.saveProfileInfo = function () {
    const fullName     = document.getElementById('editName')?.value?.trim();
    const designation  = document.getElementById('editDesignation')?.value?.trim();

    if (!fullName) return showInfoMsg('error', 'Full name cannot be empty.');

    // Split "First Last" (and "First Middle Last") into first_name / last_name
    // the way save_profile.php expects: first token is the first name, the
    // rest (joined) is treated as the last name.
    const nameParts = fullName.split(/\s+/);
    const firstName = nameParts[0];
    const lastName  = nameParts.slice(1).join(' ');

    if (!lastName) return showInfoMsg('error', 'Please enter both first and last name.');

    withBtn('[onclick="saveProfileInfo()"]', 'Saving…', btn => {
      fetch(API_SAVE_INFO, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ first_name: firstName, last_name: lastName, designation }),
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showInfoMsg('success', 'Profile updated successfully.');

            const savedName = data.name || [data.first_name, data.last_name].filter(Boolean).join(' ');
            const initials   = initialsFromName(savedName);

            setText('profAvatar',     initials || '?');
            setText('profHeaderName', savedName);
            setVal('editName',        savedName);

            // Keep the form in sync with whatever the server actually stored
            // (e.g. it may upper-case names to match DB convention).
            if (typeof data.designation === 'string') {
              setVal('editDesignation', data.designation);
            }

            broadcastProfileUpdate({ name: savedName, designation: data.designation });

            if (typeof toast === 'function') toast('✅ Profile updated');
          } else {
            showInfoMsg('error', data.message || 'Failed to save.');
          }
        })
        .catch(() => showInfoMsg('error', 'Network error. Please try again.'))
        .finally(() => btn.restore());
    });
  };

  // Push the new name to the sidebar/header without requiring a full page
  // reload. c_sidebar.php / c_header.php render the ".sb-name" element
  // straight from $_SESSION on page load; since save_profile.php already
  // updates $_SESSION['user_name'] server-side, a fresh navigation always
  // shows the right value. This just makes the *current* page's sidebar
  // (already in the DOM) update immediately too, and lets other listeners
  // on the page react if needed.
  function broadcastProfileUpdate(detail) {
    const sbName = document.querySelector('.sidebar .sb-name');
    if (sbName && detail.name) sbName.textContent = detail.name;

    document.dispatchEvent(new CustomEvent('secreco:profile-updated', { detail }));
  }

  /* ── Change password ── */
  window.submitPasswordChange = function () {
    const current = document.getElementById('pwCurrent')?.value;
    const pwNew   = document.getElementById('pwNew')?.value;
    const confirm = document.getElementById('pwConfirm')?.value;

    if (!current || !pwNew || !confirm) return showPwMsg('error', 'Please fill in all fields.');
    if (pwNew.length < 8)               return showPwMsg('error', 'New password must be at least 8 characters.');
    if (pwNew !== confirm)              return showPwMsg('error', 'New passwords do not match.');

    withBtn('[onclick="submitPasswordChange()"]', 'Saving…', btn => {
      fetch(API_CHANGE_PW, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: current, new_password: pwNew }),
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showPwMsg('success', 'Password changed successfully.');
            ['pwCurrent','pwNew','pwConfirm'].forEach(id => setVal(id, ''));
            const s = document.getElementById('pwStrength');
            if (s) { s.textContent = ''; s.className = 'pw-strength'; }
            if (typeof toast === 'function') toast('✅ Password updated');
          } else {
            showPwMsg('error', data.message || 'Failed to change password.');
          }
        })
        .catch(() => showPwMsg('error', 'Network error. Please try again.'))
        .finally(() => btn.restore());
    });
  };

  /* ── Password strength ── */
  document.addEventListener('DOMContentLoaded', function () {
    const pwNew = document.getElementById('pwNew');
    const strEl = document.getElementById('pwStrength');
    if (!pwNew || !strEl) return;
    pwNew.addEventListener('input', function () {
      const v = pwNew.value;
      if (!v) { strEl.textContent = ''; strEl.className = 'pw-strength'; return; }
      const score = (v.length >= 8 ? 1 : 0) + (v.length >= 12 ? 1 : 0)
                  + (/[A-Z]/.test(v) ? 1 : 0) + (/\d/.test(v) ? 1 : 0)
                  + (/[^a-zA-Z0-9]/.test(v) ? 1 : 0);
      if (score <= 2)      { strEl.textContent = 'Weak';   strEl.className = 'pw-strength weak'; }
      else if (score <= 3) { strEl.textContent = 'Medium'; strEl.className = 'pw-strength medium'; }
      else                 { strEl.textContent = 'Strong'; strEl.className = 'pw-strength strong'; }
    });
  });

  /* ── Helpers ── */
  function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
  function setVal(id, val)  { const el = document.getElementById(id); if (el) el.value = val; }
  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
  function showInfoMsg(type, text) {
    const el = document.getElementById('profileInfoMsg');
    if (!el) return;
    el.innerHTML = `<div class="prof-msg ${type}">${escHtml(text)}</div>`;
    if (type === 'success') setTimeout(() => { el.innerHTML = ''; }, 5000);
  }
  function showPwMsg(type, text) {
    const el = document.getElementById('pwMsg');
    if (!el) return;
    el.innerHTML = `<div class="prof-msg ${type}">${escHtml(text)}</div>`;
    if (type === 'success') setTimeout(() => { el.innerHTML = ''; }, 5000);
  }
  function withBtn(selector, loadLabel, cb) {
    const btn = document.querySelector(selector);
    const orig = btn ? btn.textContent : null;
    if (btn) { btn.disabled = true; btn.textContent = loadLabel; }
    cb({ restore: () => { if (btn) { btn.disabled = false; btn.textContent = orig; } } });
  }

  document.addEventListener('DOMContentLoaded', loadProfile);
})();
