  /* ── CUSTOM ALERT MODAL (replaces browser alert()) ── */
  function showAlert(message, onOk) {
    const overlay = document.getElementById('customModalOverlay');
    const msgEl   = document.getElementById('customModalMessage');
    const okBtn   = document.getElementById('customModalOkBtn');

    if (!overlay || !msgEl || !okBtn) {
      // Fallback kung walang modal sa page
      window.alert(message);
      if (typeof onOk === 'function') onOk();
      return;
    }

    msgEl.textContent = message;
    overlay.classList.add('show');

    function handleOk() {
      overlay.classList.remove('show');
      okBtn.removeEventListener('click', handleOk);
      if (typeof onOk === 'function') onOk();
    }
    okBtn.addEventListener('click', handleOk);
  }

  /* ── TYPING ANIMATION ── */
  (function() {
    const text = 'CVAARRD';
    const el = document.getElementById('typedTitle');
    const cursor = document.getElementById('cursor');
    let i = 0;
    function type() {
      if (i <= text.length) {
        el.textContent = text.slice(0, i);
        i++;
        setTimeout(type, i === 1 ? 320 : 95);
      } else {
        setTimeout(() => {
          cursor.style.transition = 'opacity 0.5s';
          cursor.style.opacity = '0';
          setTimeout(() => cursor.remove(), 600);
        }, 1500);
      }
    }
    setTimeout(type, 600);
  })();

  /* ── RECAPTCHA CALLBACKS ── */
  function onRecaptchaSuccess(token) {
    const errBox = document.getElementById('loginError');
    if (errBox) errBox.classList.remove('show');
  }

  function onRecaptchaExpired() {
    if (typeof grecaptcha !== 'undefined') {
      grecaptcha.reset();
    }
  }

  /* ── LOGIN HANDLER ── */
  function handleLogin() {
    const email   = document.getElementById('loginEmail').value.trim();
    const pw      = document.getElementById('pw').value;
    const errBox  = document.getElementById('loginError');
    const errText = document.getElementById('loginErrorText');
    const btn     = document.querySelector('#loginView .btn-signin');

    if (!email.toLowerCase().endsWith('@gmail.com')) {
      errText.textContent = 'Only @gmail.com addresses are accepted.';
      errBox.classList.add('show');
      document.getElementById('loginEmail').classList.add('invalid');
      return;
    }
    if (!pw) {
      errText.textContent = 'Please enter your password.';
      errBox.classList.add('show');
      return;
    }

    let recaptchaToken = '';
    if (typeof grecaptcha !== 'undefined') {
      recaptchaToken = grecaptcha.getResponse();
    }

    if (!recaptchaToken) {
      errText.textContent = 'Please complete the reCAPTCHA challenge.';
      errBox.classList.add('show');
      return;
    }

    errBox.classList.remove('show');
    document.getElementById('loginEmail').classList.remove('invalid');

    const origBtnContent = btn ? btn.innerHTML : '';
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Signing in…';
    }

    const fd = new FormData();
    fd.append('email', email);
    fd.append('password', pw);
    fd.append('g_recaptcha_response', recaptchaToken);

    fetch('/api/auth/login', {
      method: 'POST',
      body: fd
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          window.location.href = res.redirect || '/login';
        } else {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = origBtnContent;
          }
          if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
          }
          errText.textContent = res.message || 'Invalid email or password.';
          errBox.classList.add('show');
        }
      })
      .catch(err => {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = origBtnContent;
        }
        if (typeof grecaptcha !== 'undefined') {
          grecaptcha.reset();
        }
        errText.textContent = 'Network error. Please try again.';
        errBox.classList.add('show');
      });
  }

  /* ── TOGGLE PASSWORD ── */
  function togglePw() {
    const f = document.getElementById('pw');
    const i = document.getElementById('eyeIco');
    if (f.type === 'password') {
      f.type = 'text';
      i.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
      f.type = 'password';
      i.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
  }

  function togglePw2(id, iconId) {
    const f   = document.getElementById(id);
    const ico = document.getElementById(iconId);
    if (f.type === 'password') {
      f.type = 'text';
      ico.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
      f.type = 'password';
      ico.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
  }

  /* ── GMAIL LIVE VALIDATION ── */
  function validateGmailLive(input) {
    const fb  = document.getElementById('emailFeedback');
    const val = input.value.trim().toLowerCase();
    if (!val) { fb.style.display = 'none'; input.classList.remove('invalid','valid'); return; }
    if (val.endsWith('@gmail.com')) {
      fb.style.display = 'block';
      fb.style.color   = '#2d6a30';
      fb.textContent   = '✓ Valid Gmail address';
      input.classList.remove('invalid'); input.classList.add('valid');
    } else {
      fb.style.display = 'block';
      fb.style.color   = '#c62828';
      fb.textContent   = '✕ Must be a @gmail.com address';
      input.classList.add('invalid'); input.classList.remove('valid');
    }
  }

  /* ── OCCUPIED INSTITUTIONS (1 CMI per institution rule) ── */
  let occupiedInstitutions = [];

  async function loadOccupiedInstitutions() {
    try {
      const res  = await fetch('/api/auth/occupied-institutions');
      const data = await res.json();
      if (data.success && Array.isArray(data.occupied)) {
        occupiedInstitutions = data.occupied;
        updateRegInstSelectOptions();
      }
    } catch(e) {
      console.error('Error loading occupied institutions:', e);
    }
  }

  function updateRegInstSelectOptions() {
    const select = document.getElementById('regInstSelect');
    if (!select) return;

    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      if (!opt.value) continue;

      if (!opt.dataset.origText) {
        opt.dataset.origText = opt.text;
      }

      const isOccupied = occupiedInstitutions.includes(opt.value);
      if (isOccupied) {
        opt.disabled = true;
        opt.text = opt.dataset.origText + ' (Already Registered)';
        opt.style.color = '#9ca3af';
      } else {
        opt.disabled = false;
        opt.text = opt.dataset.origText;
        opt.style.color = '';
      }
    }
  }

  /* ── VIEW TOGGLE ── */
  function showRegister() {
    document.getElementById('loginView').classList.add('hidden');
    document.getElementById('forgotView').classList.add('hidden');
    document.getElementById('registerView').classList.remove('hidden');
    regCurrentStep = 1;
    selectedRole   = null;
    otpSent        = false;
    const instField = document.getElementById('regInstField');
    if (instField) instField.style.display = 'none';
    loadOccupiedInstitutions();
    updateSteps();
  }
  function showLogin() {
    document.getElementById('registerView').classList.add('hidden');
    document.getElementById('forgotView').classList.add('hidden');
    document.getElementById('loginView').classList.remove('hidden');
  }
  function showForgot() {
    document.getElementById('loginView').classList.add('hidden');
    document.getElementById('registerView').classList.add('hidden');
    document.getElementById('forgotView').classList.remove('hidden');
    // Reset to step 1
    document.getElementById('fpStep1').style.display  = '';
    document.getElementById('fpStep2a').style.display = 'none';
    document.getElementById('fpStep2b').style.display = 'none';
    document.getElementById('fpEmail').value = '';
    _fpHideMsg('fpError');
    _fpHideMsg('fpSuccess');
  }

  /* ── REGISTER STATE ── */
  let regCurrentStep = 1;
  let selectedRole   = null;
  let otpTimer       = null;
  let otpSent        = false;

  /* ── STEP INDICATOR ── */
  function updateSteps() {
    for (let i = 1; i <= 4; i++) {
      const sc = document.getElementById('sc' + i);
      const sl = document.getElementById('sl' + i);
      sc.className = 'step-circle';
      sl.className = 'step-label';
      if (i < regCurrentStep)        { sc.classList.add('done');   sl.classList.add('done'); }
      else if (i === regCurrentStep) { sc.classList.add('active'); sl.classList.add('active'); }
    }
    for (let i = 1; i <= 3; i++) {
      const ln = document.getElementById('line' + i);
      ln.className = 'step-line';
      if (i < regCurrentStep) ln.classList.add('done');
    }
    document.getElementById('regStep1').style.display = regCurrentStep === 1 ? '' : 'none';
    document.getElementById('regStep2').style.display = regCurrentStep === 2 ? '' : 'none';
    document.getElementById('regStep3').style.display = regCurrentStep === 3 ? '' : 'none';
    document.getElementById('regStep4').style.display = regCurrentStep === 4 ? '' : 'none';
  }

  /* ── ROLE SELECT ──
    - PTA: walang institution, walang designation
    - CMI: may institution AT designation
    - Viewer: designation lang, walang institution */
  function selectRole(role) {
    selectedRole = role;
    const instField = document.getElementById('regInstField');
    if (!instField) return;

    if (role === 'pta') {
      // PTA: itago ang buong block, i-clear ang fields
      instField.style.display = 'none';
      document.getElementById('regInstSelect').value = '';
      document.getElementById('regDesignation').value = '';
    } else if (role === 'cmi' || role === 'viewer') {
      // CMI at Viewer: ipakita ang block
      instField.style.display = 'block';

      // Institution: visible lang para sa CMI
      const instSelectWrap = document.getElementById('regInstSelect').closest('.field');
      if (instSelectWrap) {
        instSelectWrap.style.display = role === 'cmi' ? '' : 'none';
      }
      if (role === 'cmi') {
        updateRegInstSelectOptions();
      } else {
        document.getElementById('regInstSelect').value = '';
      }
    } else {
      // Walang napiling role pa
      instField.style.display = 'none';
    }
  }

  /* ── STEP 1 → STEP 2 ── */
  function regNext() {
    const fn     = document.getElementById('regFirstName').value.trim();
    const ln     = document.getElementById('regLastName').value.trim();
    const roleEl = document.getElementById('regRoleSelect');

    if (roleEl) selectedRole = roleEl.value;

    if (!fn) {
      showAlert('Please enter your first name.', () => document.getElementById('regFirstName').focus());
      return;
    }
    if (!ln) {
      showAlert('Please enter your last name.', () => document.getElementById('regLastName').focus());
      return;
    }
    if (!selectedRole) {
      showAlert('Please select a role.', () => { if (roleEl) roleEl.focus(); });
      return;
    }
    if (selectedRole === 'cmi') {
      const inst = document.getElementById('regInstSelect').value;
      if (!inst) {
        showAlert('Please select your institution.', () => document.getElementById('regInstSelect').focus());
        return;
      }
      if (occupiedInstitutions.includes(inst)) {
        showAlert('This institution already has a CMI Representative account. Only 1 CMI account is allowed per institution.', () => document.getElementById('regInstSelect').focus());
        return;
      }
    }
    // Designation: required para sa CMI at Viewer lang, hindi para sa PTA
    if (selectedRole !== 'pta') {
      const desg = document.getElementById('regDesignation').value.trim();
      if (!desg) {
        showAlert('Please enter your designation / position.', () => document.getElementById('regDesignation').focus());
        return;
      }
    }

    regCurrentStep = 2;
    updateSteps();
  }

  /* ── STEP 2 → STEP 3 ── */
  function regStep2Next() {
    const email = document.getElementById('regEmail').value.trim();
    const pw    = document.getElementById('regPw').value;
    const pwc   = document.getElementById('regPwConfirm').value;

    if (!email.toLowerCase().endsWith('@gmail.com')) {
      showAlert('Only @gmail.com addresses are accepted.');
      return;
    }
    if (pw.length < 8) {
      showAlert('Password must be at least 8 characters.');
      return;
    }
    if (pw !== pwc) {
      showAlert('Passwords do not match.');
      return;
    }

    document.getElementById('otpEmailPreview').textContent = email;
    otpSent = false;

    const btn = document.getElementById('otpActionBtn');
    btn.innerHTML = `Send OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>`;

    document.querySelectorAll('.otp-box').forEach(b => { b.value = ''; b.classList.remove('filled'); });
    document.getElementById('otpPreMsg').style.display    = '';
    document.getElementById('otpSentMsg').style.display   = 'none';
    document.getElementById('otpTimerText').style.display = 'none';
    document.getElementById('otpResendBtn').style.display = 'none';

    regCurrentStep = 3;
    updateSteps();
  }

  /* ── STEP 3 ACTION (send OTP first, then verify) ── */
  function regOtpAction() {
    if (!otpSent) regSendOtp();
    else          regVerifyOtp();
  }

  /* ── BACK ── */
  function regBack() {
    if (regCurrentStep > 1) {
      regCurrentStep--;
      updateSteps();
      if (otpTimer && regCurrentStep < 3) { clearInterval(otpTimer); otpTimer = null; }
    } else {
      showLogin();
    }
  }

  /* ── SEND OTP ── */
  function regSendOtp() {
    const email = document.getElementById('regEmail').value.trim();
    const pw    = document.getElementById('regPw').value;
    const pwc   = document.getElementById('regPwConfirm').value;
    const btn   = document.getElementById('otpActionBtn');

    btn.disabled    = true;
    btn.textContent = 'Sending…';

    const data = new FormData();
    data.append('first_name',       document.getElementById('regFirstName').value.trim());
    data.append('last_name',        document.getElementById('regLastName').value.trim());
    data.append('email',            email);
    data.append('password',         pw);
    data.append('password_confirm', pwc);
    data.append('role',             selectedRole);
    data.append('institution',      selectedRole === 'cmi' ? document.getElementById('regInstSelect').value : '');
    data.append('designation',      selectedRole !== 'pta' ? document.getElementById('regDesignation').value.trim() : '');

    fetch('/api/auth/register', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        if (!res.success) {
          showAlert(res.message);
          btn.innerHTML = `Send OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>`;
          return;
        }
        otpSent = true;
        document.getElementById('otpEmailDisplay').textContent = email;
        document.getElementById('otpPreMsg').style.display     = 'none';
        document.getElementById('otpSentMsg').style.display    = '';
        startOtpTimer();
        setTimeout(() => document.querySelectorAll('.otp-box')[0].focus(), 100);
        btn.innerHTML = `Verify OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>`;
      })
      .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = `Send OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>`;
        showAlert('Network error. Please try again.');
      });
  }

  /* ── RESEND OTP ── */
  function resendOtp() {
    otpSent = false;
    document.querySelectorAll('.otp-box').forEach(b => { b.value = ''; b.classList.remove('filled'); });
    document.getElementById('otpResendBtn').style.display = 'none';
    regSendOtp();
  }

  /* ── OTP BOXES ── */
  function initOtpBoxes() {
    const boxes = document.querySelectorAll('.otp-box');
    boxes.forEach((box, idx) => {
      box.addEventListener('input', function() {
        const val = this.value.replace(/\D/g, '');
        this.value = val ? val[0] : '';
        if (val) {
          this.classList.add('filled');
          if (idx < boxes.length - 1) boxes[idx + 1].focus();
        } else {
          this.classList.remove('filled');
        }
      });
      box.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
          boxes[idx - 1].focus();
          boxes[idx - 1].value = '';
          boxes[idx - 1].classList.remove('filled');
        }
        if (e.key === 'Enter' && otpSent) regVerifyOtp();
      });
      box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        pasted.split('').slice(0, 6).forEach((ch, i) => {
          if (boxes[idx + i]) { boxes[idx + i].value = ch; boxes[idx + i].classList.add('filled'); }
        });
        const next = idx + pasted.length;
        if (boxes[next]) boxes[next].focus();
      });
    });
  }

  /* ── OTP COUNTDOWN ── */
  function startOtpTimer() {
    if (otpTimer) clearInterval(otpTimer);
    let secs = 60;
    const countEl   = document.getElementById('otpCountdown');
    const timerText = document.getElementById('otpTimerText');
    const resendBtn = document.getElementById('otpResendBtn');
    countEl.textContent     = secs;
    timerText.style.display = 'inline';
    resendBtn.style.display = 'none';
    resendBtn.disabled      = true;

    otpTimer = setInterval(() => {
      secs--;
      countEl.textContent = secs;
      if (secs <= 0) {
        clearInterval(otpTimer);
        timerText.style.display = 'none';
        resendBtn.style.display = 'inline';
        resendBtn.disabled      = false;
      }
    }, 1000);
  }

  /* ── VERIFY OTP ── */
  function regVerifyOtp() {
    const boxes = document.querySelectorAll('.otp-box');
    const code  = Array.from(boxes).map(b => b.value).join('');
    if (code.length < 6) { showAlert('Please enter all 6 digits of the OTP.'); return; }

    const email = document.getElementById('regEmail').value.trim();
    const btn   = document.getElementById('otpActionBtn');
    btn.disabled    = true;
    btn.textContent = 'Verifying…';

    const data = new FormData();
    data.append('action', 'verify');
    data.append('email',  email);
    data.append('otp',    code);

    fetch('/api/auth/verify-otp', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        btn.disabled  = false;
        btn.innerHTML = `Verify OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>`;
        if (!res.success) { showAlert(res.message); return; }
        if (otpTimer) { clearInterval(otpTimer); otpTimer = null; }
        buildReview();
        regCurrentStep = 4;
        updateSteps();
      })
      .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = `Verify OTP <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>`;
        showAlert('Network error. Please try again.');
      });
  }

  /* ── REVIEW ── */
  const ROLE_LABELS = { pta: 'Project Technical Assistant II', cmi: 'CMI Representative', viewer: 'Viewer' };

  function buildReview() {
    const fn    = document.getElementById('regFirstName').value.trim();
    const ln    = document.getElementById('regLastName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const desg  = selectedRole !== 'pta' ? document.getElementById('regDesignation').value.trim() : '';
    const inst  = selectedRole === 'cmi' ? document.getElementById('regInstSelect').value : '';

    document.getElementById('rv-name').textContent  = fn + ' ' + ln;
    document.getElementById('rv-email').textContent = email;
    document.getElementById('rv-role').textContent  = ROLE_LABELS[selectedRole] || selectedRole;

    // Designation row: ipakita para sa CMI at Viewer lang, itago para sa PTA
    const desgRow = document.getElementById('rv-desg').closest('.review-row');
    if (selectedRole === 'pta') {
      if (desgRow) desgRow.style.display = 'none';
    } else {
      if (desgRow) desgRow.style.display = '';
      document.getElementById('rv-desg').textContent = desg;
    }

    const instRow = document.getElementById('rv-inst-row');
    if (inst) {
      document.getElementById('rv-inst').textContent = inst;
      instRow.style.display = '';
    } else {
      instRow.style.display = 'none';
    }
  }

  /* ── SUBMIT REGISTRATION ── */
  function regSubmit() {
    if (!document.getElementById('termsCheck').checked) {
      showAlert('Please agree to the Terms of Use and Privacy Policy before continuing.');
      return;
    }

    const btn = document.querySelector('#regStep4 .btn-continue');
    btn.disabled    = true;
    btn.textContent = 'Creating account…';

    const data = new FormData();
    data.append('first_name',  document.getElementById('regFirstName').value.trim());
    data.append('last_name',   document.getElementById('regLastName').value.trim());
    data.append('email',       document.getElementById('regEmail').value.trim());
    data.append('password',    document.getElementById('regPw').value);
    data.append('role',        selectedRole);
    data.append('institution', selectedRole === 'cmi' ? document.getElementById('regInstSelect').value : '');
    data.append('designation', selectedRole !== 'pta' ? document.getElementById('regDesignation').value.trim() : '');

    fetch('/api/auth/finalize-register', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (!res.success) {
          btn.disabled    = false;
          btn.textContent = '✓ Create Account';
          showAlert(res.message);
          return;
        }
        showAlert(res.message, () => {
          window.location.href = res.redirect || '/login?registered=1';
        });
      })
      .catch(() => {
        btn.disabled    = false;
        btn.textContent = '✓ Create Account';
        showAlert('Network error. Please try again.');
      });
  }

  /* ── PASSWORD STRENGTH (shared by register + forgot password) ── */
  function checkPwStrength(val, prefix) {
    // Default IDs are pws1-4 / pwHint (registration form)
    // Forgot-password form passes prefix='fp' → fpPws1-4 / fpPwHint
    const p = prefix || '';
    const segs = [1,2,3,4].map(n => document.getElementById(p + 'pws' + n) || document.getElementById(p + 'Pws' + n));
    const hint = document.getElementById(p + 'pwHint') || document.getElementById(p + 'PwHint');
    segs.forEach(s => { if (s) s.className = 'pw-seg'; });
    let score = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = ['weak','fair','good','strong'];
    const labels = ['Weak','Fair — add uppercase & number','Good — add a symbol','Strong'];
    segs.forEach((s, i) => { if (s && i < score) s.classList.add(levels[Math.min(score - 1, 3)]); });
    if (hint) hint.textContent = score === 0 ? '8+ chars, uppercase, number, symbol' : labels[score - 1];
  }

  /* ── INIT ── */
  // ── Hash-based routing: open the right view based on URL hash ──
  // e.g. login.php#register -> Register view, login.php#forgot -> Forgot Password view
  function applyHashView() {
    if (window.location.hash === '#register') {
      showRegister();
    } else if (window.location.hash === '#forgot') {
      showForgot();
    } else if (window.location.hash === '' || window.location.hash === '#') {
      showLogin();
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    initOtpBoxes();
    applyHashView();
  });
  // Fallback if DOMContentLoaded already fired
  initOtpBoxes();
  applyHashView();

  // Re-apply when the hash changes without a full page reload
  // (e.g. clicking "Get Started" link to login.php#register from the same page)
  window.addEventListener('hashchange', applyHashView);
  /* ══════════════════════════════════════════════════════════════
    FORGOT PASSWORD FLOW  (Step 1 → Step 2a OTP verify → Step 2b new password)
    ══════════════════════════════════════════════════════════════ */

  /* ── Internal helpers ── */
  function _fpHideMsg(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  }
  function _fpShowMsg(id, text, isError) {
    const el = document.getElementById(id);
    if (!el) return;
    const span = el.querySelector('span');
    if (span && text) span.textContent = text;
    el.style.display     = 'flex';
    el.style.borderColor = isError ? '#fca5a5' : '#bbf7d0';
    el.style.background  = isError ? '#fef2f2' : '#f0fdf4';
    el.style.color       = isError ? '#b91c1c' : '#166534';
  }

  /* ── Shared state ── */
  let _fpEmail           = '';
  let _fpTimerInterval   = null;
  let _fpOtp             = '';   // keeps the verified OTP for reset-password.php

  /* ── Step 1: Send OTP ── */
  function fpSendOtp() {
    const email = (document.getElementById('fpEmail').value || '').trim();
    _fpHideMsg('fpError');
    _fpHideMsg('fpSuccess');

    if (!email) {
      _fpShowMsg('fpError', 'Please enter your email address.', true); return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      _fpShowMsg('fpError', 'Please enter a valid email address.', true); return;
    }

    const btn = document.getElementById('fpSendBtn');
    btn.disabled    = true;
    btn.textContent = 'Sending…';

    const fd = new FormData();
    fd.append('email', email);

    fetch('/api/auth/forgot-password', { method: 'POST', body: fd })
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(res => {
        btn.disabled  = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Reset Code`;
        if (res.success) {
          _fpEmail = email;
          _fpShowMsg('fpSuccess', 'OTP sent! Check your Gmail inbox.', false);
          setTimeout(() => {
            document.getElementById('fpStep1').style.display  = 'none';
            document.getElementById('fpStep2a').style.display = '';
            document.getElementById('fpEmailDisplay').textContent = email;
            fpInitOtpBoxes();
            fpStartTimer();
          }, 900);
        } else {
          _fpShowMsg('fpError', res.message || 'No account found with that email.', true);
        }
      })
      .catch(err => {
        btn.disabled  = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Reset Code`;
        _fpShowMsg('fpError', 'Cannot reach server. (' + err.message + ')', true);
      });
  }

  /* ── OTP boxes for forgot password ── */
  function fpInitOtpBoxes() {
    const boxes = document.querySelectorAll('#fpOtpWrap .otp-box');
    boxes.forEach((box, i) => {
      box.value = '';
      box.classList.remove('filled');
      box.oninput = function() {
        const val = this.value.replace(/\D/g, '');
        this.value = val ? val[0] : '';
        if (val) { this.classList.add('filled'); if (i < boxes.length - 1) boxes[i+1].focus(); }
        else { this.classList.remove('filled'); }
      };
      box.onkeydown = function(e) {
        if (e.key === 'Backspace' && !this.value && i > 0) {
          boxes[i-1].focus(); boxes[i-1].value = ''; boxes[i-1].classList.remove('filled');
        }
        // Enter on OTP step → verify
        if (e.key === 'Enter') fpVerifyOtp();
      };
      box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        pasted.split('').slice(0, 6).forEach((ch, j) => {
          if (boxes[i + j]) { boxes[i+j].value = ch; boxes[i+j].classList.add('filled'); }
        });
        const next = i + pasted.length;
        if (boxes[next]) boxes[next].focus();
      });
    });
    if (boxes[0]) boxes[0].focus();
  }

  /* ── Countdown timer ── */
  function fpStartTimer() {
    let secs = 60;
    const timerText = document.getElementById('fpTimerText');
    const resendBtn = document.getElementById('fpResendBtn');
    const countdown = document.getElementById('fpCountdown');
    if (timerText) { timerText.style.display = ''; countdown.textContent = secs; }
    if (resendBtn) { resendBtn.style.display = 'none'; resendBtn.disabled = true; }

    clearInterval(_fpTimerInterval);
    _fpTimerInterval = setInterval(() => {
      secs--;
      if (countdown) countdown.textContent = secs;
      if (secs <= 0) {
        clearInterval(_fpTimerInterval);
        if (timerText) timerText.style.display = 'none';
        if (resendBtn) { resendBtn.style.display = ''; resendBtn.disabled = false; }
      }
    }, 1000);
  }

  /* ── Resend OTP (stays on Step 2a) ── */
  function fpResendOtp() {
    const fd = new FormData();
    fd.append('email', _fpEmail);
    fetch('/api/auth/forgot-password', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) { fpInitOtpBoxes(); fpStartTimer(); _fpHideMsg('fpStep2aError'); }
        else _fpShowMsg('fpStep2aError', res.message || 'Could not resend OTP.', true);
      })
      .catch(() => _fpShowMsg('fpStep2aError', 'Network error. Please try again.', true));
  }

  /* ── Step 2a: Verify OTP (calls verify-otp-fp.php) ── */
  function fpVerifyOtp() {
    _fpHideMsg('fpStep2aError');

    const otp = Array.from(document.querySelectorAll('#fpOtpWrap .otp-box'))
                    .map(b => b.value).join('');
    if (otp.length < 6) {
      _fpShowMsg('fpStep2aError', 'Please enter the complete 6-digit OTP.', true); return;
    }

    const btn     = document.getElementById('fpVerifyBtn');
    btn.disabled  = true;
    btn.textContent = 'Verifying…';

    const fd = new FormData();
    fd.append('email', _fpEmail);
    fd.append('otp',   otp);

    fetch('/api/auth/verify-otp-fp', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        btn.disabled  = false;
        btn.innerHTML = `Verify Code <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>`;
        if (res.success) {
          _fpOtp = otp;   // stash for reset-password.php
          clearInterval(_fpTimerInterval);
          // Show password step
          document.getElementById('fpStep2a').style.display = 'none';
          document.getElementById('fpStep2b').style.display = '';
          document.getElementById('fpEmailDisplay2').textContent = _fpEmail;
          // Reset password fields
          const pwEl = document.getElementById('fpNewPw');
          const cpEl = document.getElementById('fpConfirmPw');
          if (pwEl) pwEl.value = '';
          if (cpEl) cpEl.value = '';
          checkPwStrength('', 'fp');
          if (pwEl) pwEl.focus();
        } else {
          _fpShowMsg('fpStep2aError', res.message || 'Invalid OTP. Please try again.', true);
        }
      })
      .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = `Verify Code <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>`;
        _fpShowMsg('fpStep2aError', 'Network error. Please try again.', true);
      });
  }

  /* ── Back: Step 2a → Step 1 ── */
  function fpGoBack() {
    clearInterval(_fpTimerInterval);
    document.getElementById('fpStep2a').style.display = 'none';
    document.getElementById('fpStep1').style.display  = '';
    _fpHideMsg('fpStep2aError');
  }

  /* ── Back: Step 2b → Step 2a ── */
  function fpGoBack2b() {
    document.getElementById('fpStep2b').style.display  = 'none';
    document.getElementById('fpStep2a').style.display  = '';
    _fpHideMsg('fpStep2bError');
    // Reinit boxes (already filled/verified, but give user a chance to re-enter if desired)
    fpInitOtpBoxes();
    fpStartTimer();
  }

  /* ── Step 2b: Reset password (calls reset-password.php) ── */
  function fpResetPassword() {
    _fpHideMsg('fpStep2bError');

    const newPw  = (document.getElementById('fpNewPw').value  || '').trim();
    const confPw = (document.getElementById('fpConfirmPw').value || '').trim();

    if (newPw.length < 8) { _fpShowMsg('fpStep2bError', 'Password must be at least 8 characters.', true); return; }
    if (newPw !== confPw)  { _fpShowMsg('fpStep2bError', 'Passwords do not match.', true); return; }

    const fd = new FormData();
    fd.append('email',    _fpEmail);
    fd.append('otp',      _fpOtp);
    fd.append('password', newPw);

    fetch('/api/auth/reset-password', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          clearInterval(_fpTimerInterval);
          showLogin();
          const sm = document.getElementById('regSuccessMsg');
          if (sm) {
            const span = sm.querySelector('span');
            if (span) span.textContent = 'Password reset! You can now sign in.';
            sm.style.display = 'flex';
          }
        } else {
          _fpShowMsg('fpStep2bError', res.message || 'Could not reset password. Please try again.', true);
        }
      })
      .catch(() => _fpShowMsg('fpStep2bError', 'Network error. Please try again.', true));
  }


