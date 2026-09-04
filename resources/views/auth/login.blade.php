<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CVAARRD SecReCo — Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/login.css?v=2"/>
  <!-- Google reCAPTCHA v2 -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    /* ── reCAPTCHA Container ── */
    .recaptcha-wrap {
      display: flex;
      justify-content: center;
      margin: 12px 0 16px;
      min-height: 78px;
    }
    .recaptcha-wrap .g-recaptcha {
      display: inline-block;
    }
    /* ── Custom Alert Modal (replaces browser alert()) ── */
    .custom-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(10, 25, 15, 0.6);
      backdrop-filter: blur(4px);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      animation: cmFadeIn 0.15s ease-out;
    }
    .custom-modal-overlay.show {
      display: flex;
    }
    .custom-modal {
      background: #ffffff;
      border-radius: 16px;
      padding: 28px 26px 22px;
      max-width: 380px;
      width: 90%;
      box-shadow: 0 16px 40px rgba(0,0,0,0.25);
      text-align: center;
      animation: cmPopIn 0.18s ease-out;
    }
    .custom-modal-icon {
      width: 46px;
      height: 46px;
      margin: 0 auto 14px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #eaf4eb;
      color: #0e3d26;
    }
    .custom-modal-icon svg {
      width: 24px;
      height: 24px;
    }
    .custom-modal p {
      margin: 0 0 20px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      line-height: 1.5;
      color: #374151;
    }
    .custom-modal-ok {
      background: #0e3d26;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 32px;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .custom-modal-ok:hover {
      background: #092819;
    }
    @keyframes cmFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes cmPopIn { from { transform: scale(0.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  </style>
</head>
<body>

  <!-- ══════════ CUSTOM ALERT MODAL ══════════ -->
  <div class="custom-modal-overlay" id="customModalOverlay">
    <div class="custom-modal">
      <div class="custom-modal-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12.5"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/>
        </svg>
      </div>
      <p id="customModalMessage"></p>
      <button class="custom-modal-ok" id="customModalOkBtn" type="button">OK</button>
    </div>
  </div>

  <div class="login-page">

    <!-- ══════════ COLLAGE BACKGROUND ══════════ -->
    <div class="collage-bg">
      <!-- Column 1: Citrus (Left panel full-height) -->
      <div class="collage-col col-left">
        <img src="/assets/login/commodities/citrus.jpg" alt="Citrus" class="collage-img" />
        <div class="collage-overlay left-tint"></div>
      </div>

      <!-- Column 2: Mungbean & Small Ruminants (Center) -->
      <div class="collage-col col-grid">
        <div class="collage-cell">
          <img src="/assets/login/commodities/mungbean.jpg" alt="Mungbean" class="collage-img" />
          <div class="collage-overlay"></div>
        </div>
        <div class="collage-cell">
          <img src="/assets/login/commodities/small-ruminants.jpg" alt="Small Ruminants" class="collage-img" />
          <div class="collage-overlay"></div>
        </div>
      </div>

      <!-- Column 3: Red Tilapia & Timber (Right) -->
      <div class="collage-col col-grid">
        <div class="collage-cell">
          <img src="/assets/login/commodities/red-tilapia.jpg" alt="Red Tilapia" class="collage-img" />
          <div class="collage-overlay"></div>
        </div>
        <div class="collage-cell">
          <img src="/assets/login/commodities/timber.jpg" alt="Timber" class="collage-img" />
          <div class="collage-overlay"></div>
        </div>
      </div>
    </div>

    <!-- ══════════ MAIN CONTENT LAYOUT ══════════ -->
    <div class="main-layout">

      <!-- LEFT PANEL BRANDING -->
      <div class="left-panel">
        <div class="left-content">
          <div class="logo-badge">
            <img src="/assets/img/logo26.png" alt="SecReCo Logo"
                 onerror="this.src='/assets/logo/cvaarrd.jpeg'"/>
          </div>
          <p class="hero-label">WELCOME TO</p>
          <h1 class="hero-brand">SecReCo<span class="amber-bar"></span></h1>
          <p class="hero-tagline">
            A Secure Reporting and Consolidation System<br/>
            for CVAARRD Consortium
          </p>
          <div class="hero-amber-line"></div>
        </div>
      </div>

      <!-- RIGHT PANEL (FLOATING CARD) -->
      <div class="right-panel">

        <!-- ══════════ LOGIN VIEW ══════════ -->
        <div class="login-view" id="loginView">
          <div class="card">

            <div class="avatar-wrap">
              <div class="avatar">
                <img src="/assets/logo/cvaarrd.jpeg" alt="CVAARRD Logo"
                     onerror="this.src='/assets/img/cvaarrd.png'"/>
              </div>
            </div>

            <div class="card-head">
              <h3>Sign in to your account</h3>
              <p>Access your dashboard and manage reports securely.</p>
            </div>

            <!-- Registration / password-reset success message -->
            <div class="success-msg" id="regSuccessMsg" @if(!request('registered')) style="display:none;" @endif>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
              <span>{{ request('registered') == '1' ? 'Account created successfully. Please wait for approval from the PTA Admin before you can log in.' : '' }}</span>
            </div>

            @if(request('timeout'))
            <div class="error-msg show" style="background:#fff7ed;border-color:#fed7aa;color:#9a3412;display:flex;margin-bottom:14px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="#c2410c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;width:18px;height:18px;">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              <span>Your session has expired due to 30 minutes of inactivity. Please sign in again.</span>
            </div>
            @endif

            <!-- Error message -->
            @if(request()->has('error'))
            <div class="error-msg show" id="loginError">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span id="loginErrorText">{{ request('error') }}</span>
            </div>
            @else
            <div class="error-msg" id="loginError">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span id="loginErrorText">Invalid email or password.</span>
            </div>
            @endif

            <!-- Email field -->
            <div class="field">
              <label>EMAIL ADDRESS</label>
              <div class="inp-wrap">
                <span class="inp-ico">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                </span>
                <input type="email" id="loginEmail" placeholder="yourname@gmail.com" autocomplete="email"
                      oninput="validateGmailLive(this)"/>
              </div>
              <div id="emailFeedback" style="font-size:11px; margin-top:5px; display:none;"></div>
            </div>

            <!-- Password field -->
            <div class="field">
              <label>PASSWORD</label>
              <div class="inp-wrap">
                <span class="inp-ico">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                </span>
                <input type="password" id="pw" placeholder="Enter your password" autocomplete="current-password"
                      onkeydown="if(event.key==='Enter') handleLogin()"/>
                <button class="show-pw" type="button" onclick="togglePw()">
                  <svg id="eyeIco" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
              <div class="forgot-row"><a href="#" onclick="showForgot(); return false;">Forgot Password?</a></div>
            </div>

            <!-- reCAPTCHA Widget -->
            <div class="recaptcha-wrap">
              <div class="g-recaptcha"
                  data-sitekey="{{ config('secreco.recaptcha_site_key') }}"
                  data-callback="onRecaptchaSuccess"
                  data-expired-callback="onRecaptchaExpired">
              </div>
            </div>

            <button class="btn-signin" type="button" onclick="handleLogin()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              Sign In
            </button>

            <div class="reg-row">
              Don't have an account?&nbsp;<a href="#" onclick="showRegister(); return false;">Register here</a>
            </div>

          </div>
        </div><!-- end login-view -->

        <!-- ══════════ FORGOT PASSWORD VIEW ══════════ -->
        <div class="login-view hidden" id="forgotView">
          <div class="card">

            <div class="avatar-wrap">
              <div class="avatar">
                <img src="/assets/logo/cvaarrd.jpeg" alt="CVAARRD Logo"
                     onerror="this.src='/assets/img/cvaarrd.png'"/>
              </div>
            </div>

            <!-- STEP 1: Enter Email -->
            <div id="fpStep1">
              <div class="card-head">
                <h3>Forgot your password?</h3>
                <p>Enter your registered Gmail address and we'll send a reset code.</p>
              </div>

              <div class="error-msg" id="fpError" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="fpErrorText">Email not found.</span>
              </div>
              <div class="success-msg" id="fpSuccess" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span id="fpSuccessText">OTP sent! Check your inbox.</span>
              </div>

              <div class="field">
                <label>EMAIL ADDRESS</label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                  </span>
                  <input type="email" id="fpEmail" placeholder="yourname@gmail.com" autocomplete="email"
                        onkeydown="if(event.key==='Enter') fpSendOtp()"/>
                </div>
              </div>

              <button class="btn-signin" type="button" onclick="fpSendOtp()" id="fpSendBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Send Reset Code
              </button>

              <div class="reg-row" style="margin-top:14px;">
                Remembered it? <a href="#" onclick="showLogin(); return false;">Back to Sign In</a>
              </div>
            </div><!-- end fpStep1 -->

            <!-- STEP 2a: Enter & verify OTP -->
            <div id="fpStep2a" style="display:none;">
              <div class="card-head">
                <h3>Check your email</h3>
                <p>Enter the 6-digit code sent to <strong id="fpEmailDisplay">your email</strong>.</p>
              </div>

              <div class="error-msg" id="fpStep2aError" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="fpStep2aErrorText">Invalid OTP.</span>
              </div>

              <div class="field">
                <label style="justify-content:center; text-align:center;">ENTER OTP CODE</label>
                <div class="otp-wrap" id="fpOtpWrap">
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                </div>
                <div class="otp-timer">
                  <span id="fpTimerText">Resend OTP in <span class="otp-countdown" id="fpCountdown">60</span>s</span>
                  <button class="resend-btn" id="fpResendBtn" style="display:none;" onclick="fpResendOtp()">Resend OTP</button>
                </div>
              </div>

              <div class="reg-btn-row">
                <button class="btn-back" type="button" onclick="fpGoBack()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Back
                </button>
                <button class="btn-continue" type="button" id="fpVerifyBtn" onclick="fpVerifyOtp()">
                  Verify Code
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </button>
              </div>

              <div class="reg-row" style="margin-top:10px;">
                Remembered it? <a href="#" onclick="showLogin(); return false;">Back to Sign In</a>
              </div>
            </div><!-- end fpStep2a -->

            <!-- STEP 2b: Set new password -->
            <div id="fpStep2b" style="display:none;">
              <div class="card-head">
                <h3>Create new password</h3>
                <p>OTP verified! Choose a secure new password for <strong id="fpEmailDisplay2">your email</strong>.</p>
              </div>

              <div class="error-msg" id="fpStep2bError" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="fpStep2bErrorText">Something went wrong.</span>
              </div>

              <div class="field">
                <label>NEW PASSWORD</label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2"/>
                      <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                  </span>
                  <input type="password" id="fpNewPw" placeholder="Minimum 8 characters"
                        oninput="checkPwStrength(this.value, 'fp')" autocomplete="new-password"/>
                  <button class="show-pw" type="button" onclick="togglePw2('fpNewPw','fpEye1')">
                    <svg id="fpEye1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
                <div class="pw-strength-bar">
                  <div class="pw-seg" id="fpPws1"></div>
                  <div class="pw-seg" id="fpPws2"></div>
                  <div class="pw-seg" id="fpPws3"></div>
                  <div class="pw-seg" id="fpPws4"></div>
                </div>
                <div class="pw-hint" id="fpPwHint">8+ chars, uppercase, number, symbol</div>
              </div>

              <div class="field">
                <label>CONFIRM NEW PASSWORD</label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2"/>
                      <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                  </span>
                  <input type="password" id="fpConfirmPw" placeholder="Re-enter new password" autocomplete="new-password"/>
                  <button class="show-pw" type="button" onclick="togglePw2('fpConfirmPw','fpEye2')">
                    <svg id="fpEye2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="reg-btn-row">
                <button class="btn-back" type="button" onclick="fpGoBack2b()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Back
                </button>
                <button class="btn-continue" type="button" onclick="fpResetPassword()">
                  Reset Password
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>
              </div>

              <div class="reg-row" style="margin-top:10px;">
                Remembered it? <a href="#" onclick="showLogin(); return false;">Back to Sign In</a>
              </div>
            </div><!-- end fpStep2b -->

          </div>
        </div><!-- end forgotView -->

        <!-- ══════════ REGISTER VIEW ══════════ -->
        <div class="register-view hidden" id="registerView">
          <div class="reg-card active" id="regCard">

            <div class="reg-card-head">
              <h3>Create your Account</h3>
              <p>Register to submit and manage reports for your institution.</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator" id="stepIndicator">
              <div class="step-item">
                <div class="step-circle active" id="sc1">1</div>
                <span class="step-label active" id="sl1">INFO</span>
              </div>
              <div class="step-line" id="line1"></div>
              <div class="step-item">
                <div class="step-circle" id="sc2">2</div>
                <span class="step-label" id="sl2">ACCOUNT</span>
              </div>
              <div class="step-line" id="line2"></div>
              <div class="step-item">
                <div class="step-circle" id="sc3">3</div>
                <span class="step-label" id="sl3">VERIFY</span>
              </div>
              <div class="step-line" id="line3"></div>
              <div class="step-item">
                <div class="step-circle" id="sc4">4</div>
                <span class="step-label" id="sl4">REVIEW</span>
              </div>
            </div>

            <!-- ── STEP 1: INFO ── -->
            <div id="regStep1">
              <div class="field-row">
                <div class="field">
                  <label>FIRST NAME <span style="color:#ef4444">*</span></label>
                  <div class="inp-wrap">
                    <input type="text" id="regFirstName" placeholder="Enter first name" autocomplete="given-name"/>
                  </div>
                </div>
                <div class="field">
                  <label>LAST NAME <span style="color:#ef4444">*</span></label>
                  <div class="inp-wrap">
                    <input type="text" id="regLastName" placeholder="Enter last name" autocomplete="family-name"/>
                  </div>
                </div>
              </div>

              <div class="field">
                <label>ROLE <span style="color:#ef4444">*</span></label>
                @php
                  $ptaExists = \App\Models\User::where('role', 'pta')->exists();
                @endphp
                <select class="inst-select" id="regRoleSelect" onchange="selectRole(this.value)">
                  <option value="">— Select your role —</option>
                  <option value="pta" id="optRolePta" {{ $ptaExists ? 'disabled style=color:#9ca3af;' : '' }}>Project Technical Assistant II{{ $ptaExists ? ' (Already Registered)' : '' }}</option>
                  <option value="cmi">CMI Representative</option>
                  <option value="viewer">Guest</option>
                </select>
              </div>

              <!-- Institution + Designation -->
              <div id="regInstField" style="display:none; margin-top:10px;">
                <div class="field" id="instSelectWrap">
                  <label>INSTITUTION <span style="color:#ef4444">*</span></label>
                  <select class="inst-select" id="regInstSelect">
                    <option value="">— Select your institution —</option>
                    <option value="Isabela State University - Echague">Isabela State University - Echague</option>
                    <option value="Isabela State University - Cabagan">Isabela State University - Cabagan</option>
                    <option value="Batanes State College">Batanes State College</option>
                    <option value="Cagayan State University">Cagayan State University</option>
                    <option value="Nueva Vizcaya State University">Nueva Vizcaya State University</option>
                    <option value="Quirino State University">Quirino State University</option>
                    <option value="University of La Salette">University of La Salette</option>
                    <option value="DA - Agricultural Training Institute Region II">DA - Agricultural Training Institute Region II</option>
                    <option value="DA - Regional Field Office 2">DA - Regional Field Office 2</option>
                    <option value="Bureau of Fisheries &amp; Aquatic Resources - R2">Bureau of Fisheries &amp; Aquatic Resources - R2</option>
                    <option value="Department of Environment and Natural Resources - Region II">Department of Environment and Natural Resources - Region II</option>
                    <option value="Department of Science and Technology - Region II">Department of Science and Technology - Region II</option>
                    <option value="Department of Trade and Industry - Region II">Department of Trade and Industry - Region II</option>
                    <option value="Department of Economy, Planning and Development - Region II">Department of Economy, Planning and Development - Region II</option>
                    <option value="National Tobacco Administration">National Tobacco Administration</option>
                    <option value="DA - Philippine Rice Research Institute - Isabela">DA - Philippine Rice Research Institute - Isabela</option>
                    <option value="Philippine Council for Agriculture, Aquatic and Natural Resources Research and Development">Philippine Council for Agriculture, Aquatic and Natural Resources Research and Development</option>
                    <option value="DA - Bureau of Agricultural Research">DA - Bureau of Agricultural Research</option>
                    <option value="Watershed &amp; Water Resources Research Development and Extension Center">Watershed &amp; Water Resources Research Development and Extension Center</option>
                    <option value="Mabuwaya Foundation Inc.">Mabuwaya Foundation Inc.</option>
                    <option value="Government City of Santiago">Government City of Santiago</option>
                    <option value="Commission on Higher Education - Regional Office 2">Commission on Higher Education - Regional Office 2</option>
                  </select>
                </div>

                <div class="field" style="margin-top:10px;">
                  <label>DESIGNATION / POSITION <span style="color:#ef4444">*</span></label>
                  <div class="inp-wrap">
                    <input type="text" id="regDesignation" placeholder="e.g. Researcher, Project Leader, Faculty"/>
                  </div>
                </div>
              </div>

              <div class="reg-btn-row" style="margin-top:16px">
                <button class="btn-continue" type="button" onclick="regNext()">
                  Continue
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>
              </div>

              <div class="reg-bottom-row">
                Already have an account? <a href="#" onclick="showLogin(); return false;">Sign in</a>
              </div>
            </div><!-- end step1 -->

            <!-- ── STEP 2: ACCOUNT ── -->
            <div id="regStep2" style="display:none">
              <div class="field">
                <label>EMAIL ADDRESS <span style="color:#ef4444">*</span></label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                  </span>
                  <input type="email" id="regEmail" placeholder="yourname@gmail.com" autocomplete="email"
                        oninput="validateGmailLive(this)"/>
                </div>
                <div id="emailFeedback" style="font-size:11px; margin-top:5px; display:none;"></div>
              </div>

              <div class="field">
                <label>PASSWORD <span style="color:#ef4444">*</span></label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2"/>
                      <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                  </span>
                  <input type="password" id="regPw" placeholder="Minimum 8 characters"
                        oninput="checkPwStrength(this.value)" autocomplete="new-password"/>
                  <button class="show-pw" type="button" onclick="togglePw2('regPw','eyeIco2')">
                    <svg id="eyeIco2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
                <div class="pw-strength-bar">
                  <div class="pw-seg" id="pws1"></div>
                  <div class="pw-seg" id="pws2"></div>
                  <div class="pw-seg" id="pws3"></div>
                  <div class="pw-seg" id="pws4"></div>
                </div>
                <div class="pw-hint" id="pwHint">8+ chars, uppercase, number, symbol</div>
              </div>

              <div class="field">
                <label>CONFIRM PASSWORD <span style="color:#ef4444">*</span></label>
                <div class="inp-wrap">
                  <span class="inp-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2"/>
                      <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                  </span>
                  <input type="password" id="regPwConfirm" placeholder="Re-enter password" autocomplete="new-password"/>
                  <button class="show-pw" type="button" onclick="togglePw2('regPwConfirm','eyeIco3')">
                    <svg id="eyeIco3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="reg-btn-row">
                <button class="btn-back" type="button" onclick="regBack()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Back
                </button>
                <button class="btn-continue" type="button" onclick="regStep2Next()">
                  Continue
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>
              </div>

              <div class="reg-bottom-row">
                Already have an account? <a href="#" onclick="showLogin(); return false;">Sign in</a>
              </div>
            </div><!-- end step2 -->

            <!-- ── STEP 3: VERIFY OTP ── -->
            <div id="regStep3" style="display:none">
              <p class="otp-sent-to" id="otpSentMsg" style="display:none">
                A 6-digit code was sent to<br/>
                <strong id="otpEmailDisplay">yourname@gmail.com</strong>
              </p>
              <p class="otp-sent-to" id="otpPreMsg">
                We'll send a 6-digit verification code to<br/>
                <strong id="otpEmailPreview">yourname@gmail.com</strong>
              </p>

              <div class="field">
                <label style="justify-content:center; text-align:center;">ENTER OTP CODE</label>
                <div class="otp-wrap" id="otpWrap">
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                  <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
                </div>
                <div class="otp-timer">
                  <span id="otpTimerText" style="display:none">Resend OTP in <span class="otp-countdown" id="otpCountdown">60</span>s</span>
                  <button class="resend-btn" id="otpResendBtn" style="display:none" onclick="resendOtp()">Resend OTP</button>
                </div>
              </div>

              <div class="reg-btn-row" style="margin-top:16px">
                <button class="btn-back" type="button" onclick="regBack()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Back
                </button>
                <button class="btn-continue" type="button" id="otpActionBtn" onclick="regOtpAction()">
                  Send OTP
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                  </svg>
                </button>
              </div>

              <div class="reg-bottom-row">
                Already have an account? <a href="#" onclick="showLogin(); return false;">Sign in</a>
              </div>
            </div><!-- end step3 -->

            <!-- ── STEP 4: REVIEW ── -->
            <div id="regStep4" style="display:none">
              <div class="review-card" id="reviewCard">
                <div class="review-row">
                  <span class="review-label">Full Name</span>
                  <span class="review-value" id="rv-name">—</span>
                </div>
                <div class="review-row">
                  <span class="review-label">Gmail</span>
                  <span class="review-value" id="rv-email">—</span>
                </div>
                <div class="review-row">
                  <span class="review-label">Role</span>
                  <span class="review-value" id="rv-role">—</span>
                </div>
                <div class="review-row">
                  <span class="review-label">Designation</span>
                  <span class="review-value" id="rv-desg">—</span>
                </div>
                <div class="review-row" id="rv-inst-row" style="display:none">
                  <span class="review-label">Institution</span>
                  <span class="review-value" id="rv-inst">—</span>
                </div>
              </div>

              <div class="terms-row">
                <input type="checkbox" id="termsCheck"/>
                <label for="termsCheck">
                  I agree to the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a> of the CVAARRD SecReCo system.
                </label>
              </div>

              <div class="reg-btn-row">
                <button class="btn-back" type="button" onclick="regBack()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Back
                </button>
                <button class="btn-continue" type="button" onclick="regSubmit()">
                  ✓ Create Account
                </button>
              </div>

              <div class="reg-bottom-row">
                Already have an account? <a href="#" onclick="showLogin(); return false;">Sign in</a>
              </div>
            </div><!-- end step4 -->

          </div><!-- end reg-card -->
        </div><!-- end register-view -->

      </div><!-- end right-panel -->

    </div><!-- end main-layout -->

  </div><!-- end login-page -->

  <script src="/assets/js/login.js?v=4"></script>
</body>
</html>
