
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CVAARRD SecReCo — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/login.css"/>
  <style>
    /* ── Custom Alert Modal (replaces browser alert()) ── */
    .custom-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(20, 40, 20, 0.45);
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
      border-radius: 14px;
      padding: 28px 26px 22px;
      max-width: 380px;
      width: 90%;
      box-shadow: 0 12px 40px rgba(0,0,0,0.18);
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
      background: var(--green-light, #e8f3e8);
      color: var(--green-dark, #2d6a30);
    }
    .custom-modal-icon svg {
      width: 24px;
      height: 24px;
    }
    .custom-modal p {
      margin: 0 0 20px;
      font-family: 'Inter', sans-serif;
      font-size: 14.5px;
      line-height: 1.5;
      color: #2c2c2c;
    }
    .custom-modal-ok {
      background: var(--green-dark, #2d6a30);
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
      background: #245025;
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

<div class="main">

  <!-- DECORATIVE LEAVES & ACCENTS -->
  <div class="deco-leaves">
    <svg width="100%" height="100%" viewBox="0 0 1440 680" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g style="transform-origin:120px 620px; animation: sway 7s ease-in-out infinite;">
        <path d="M20 680 C60 580 140 540 180 560 C120 600 80 650 20 680Z" fill="#2d6a30" opacity="0.22"/>
        <path d="M20 680 C80 600 160 580 200 600 C140 630 70 660 20 680Z" fill="#3d7a3f" opacity="0.16"/>
        <path d="M0 660 C50 580 130 550 160 570 C100 610 50 640 0 660Z" fill="#2d6a30" opacity="0.18"/>
        <path d="M20 680 C60 640 110 600 150 570" stroke="#2d6a30" stroke-width="1.5" opacity="0.25" fill="none" stroke-linecap="round"/>
      </g>
      <g style="transform-origin:60px 680px; animation: swayR 9s ease-in-out infinite 1s;">
        <path d="M-20 700 C30 620 100 590 130 608 C70 645 20 675 -20 700Z" fill="#4a8f4d" opacity="0.14"/>
        <path d="M10 700 C70 630 150 600 175 618 C110 652 50 680 10 700Z" fill="#2d6a30" opacity="0.13"/>
      </g>
      <g style="transform-origin:80px 60px; animation: sway 8s ease-in-out infinite 0.5s;">
        <path d="M0 0 C40 40 80 90 60 130 C30 90 10 45 0 0Z" fill="#2d6a30" opacity="0.15"/>
        <path d="M0 0 C60 30 100 80 85 120 C55 80 25 38 0 0Z" fill="#3d7a3f" opacity="0.10"/>
        <path d="M0 10 C30 55 55 100 40 135 C15 95 5 50 0 10Z" fill="#2d6a30" opacity="0.12"/>
      </g>
      <g style="transform-origin:200px 380px; animation: swayR 10s ease-in-out infinite 2s;">
        <path d="M140 420 C170 370 220 355 240 368 C210 385 175 405 140 420Z" fill="#2d6a30" opacity="0.13"/>
        <path d="M145 430 C185 375 240 360 260 375 C225 395 180 418 145 430Z" fill="#3d7a3f" opacity="0.10"/>
      </g>
      <circle cx="320" cy="80" r="38" fill="#2d6a30" opacity="0.06"/>
      <circle cx="310" cy="72" r="22" fill="#3d7a3f" opacity="0.09"/>
      <circle cx="298" cy="90" r="10" fill="#2d6a30" opacity="0.13"/>
      <circle cx="600" cy="618" r="30" fill="#2d6a30" opacity="0.06"/>
      <circle cx="588" cy="628" r="15" fill="#3d7a3f" opacity="0.10"/>
      <circle cx="980" cy="118" r="16" fill="#2d6a30" opacity="0.08"/>
      <circle cx="998" cy="106" r="9"  fill="#3d7a3f" opacity="0.12"/>
      <circle cx="967" cy="106" r="5"  fill="#2d6a30" opacity="0.10"/>
      <g style="transform-origin:1380px 80px; animation: swayR 9s ease-in-out infinite 1.5s;">
        <path d="M1440 0 C1400 50 1360 100 1380 140 C1410 100 1435 50 1440 0Z" fill="#2d6a30" opacity="0.13"/>
        <path d="M1440 0 C1395 40 1350 90 1365 128 C1400 88 1428 42 1440 0Z" fill="#3d7a3f" opacity="0.09"/>
      </g>
      <g style="transform-origin:1350px 600px; animation: sway 8s ease-in-out infinite 3s;">
        <path d="M1440 680 C1390 600 1320 570 1300 590 C1360 615 1410 650 1440 680Z" fill="#2d6a30" opacity="0.18"/>
        <path d="M1440 680 C1380 610 1310 585 1295 605 C1355 628 1408 658 1440 680Z" fill="#3d7a3f" opacity="0.13"/>
      </g>
      <circle cx="450" cy="160" r="4" fill="#2d6a30" opacity="0.12"/>
      <circle cx="700" cy="90"  r="3" fill="#2d6a30" opacity="0.10"/>
      <circle cx="850" cy="560" r="5" fill="#2d6a30" opacity="0.10"/>
      <circle cx="380" cy="520" r="3" fill="#2d6a30" opacity="0.12"/>
      <circle cx="260" cy="200" r="4" fill="#2d6a30" opacity="0.12"/>
      <circle cx="1100" cy="400" r="4" fill="#2d6a30" opacity="0.10"/>
      <path d="M0 640 C120 590 250 560 380 578 C510 596 600 548 720 534 C840 520 950 558 1100 542 C1250 526 1360 490 1440 468 L1440 680 L0 680 Z" fill="#2d6a30" opacity="0.08"/>
      <path d="M0 660 C150 636 280 610 420 624 C560 638 640 600 780 588 C920 576 1040 608 1200 594 C1320 583 1400 558 1440 544 L1440 680 L0 680 Z" fill="#2d6a30" opacity="0.06"/>
    </svg>
  </div>

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="left-content">
      <div class="logo-wrap">
        <div class="logo-circle">
          <img src="/assets/img/logo26.png" alt="CVAARRD Logo"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"/>
          <div class="logo-svg" style="display:none; width:100%; height:100%; align-items:center; justify-content:center; background:var(--green-light); flex-direction:column; gap:2px;">
            <svg viewBox="0 0 48 36" width="52" height="40" fill="none">
              <path d="M24 32 C24 20 14 16 17 8 C21 3 31 8 31 18 C31 26 27 30 24 32Z" fill="#2d6a30" opacity="0.85"/>
              <path d="M24 32 C24 22 30 15 36 18" stroke="#2d6a30" stroke-width="1.8" stroke-linecap="round" fill="none"/>
              <path d="M24 32 C24 23 18 17 12 20" stroke="#3d7a3f" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.7"/>
            </svg>
            <span style="font-family:'Poppins',sans-serif; font-size:8px; font-weight:800; color:var(--green-dark); letter-spacing:0.05em; line-height:1;">CVAARRD</span>
          </div>
        </div>
      </div>
      <p class="hero-label">Welcome to</p>
      <h1 class="hero-title"><span id="typedTitle"></span><span class="cursor" id="cursor"></span></h1>
      <p class="hero-tagline">
        The Secure Reporting and Consolidation System<br/>
        for CVAARRD and Partner Institutions in Region II.
      </p>
      <div class="hero-underline"></div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">

    <!-- ══════════ LOGIN VIEW ══════════ -->
    <div class="login-view" id="loginView">
    <div class="card">

      <div class="avatar-wrap">
        <div class="avatar">
          <img src="/assets/img/cvaarrd.png" alt="CVAARRD Logo"
               onerror="this.outerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.8\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2\'/><circle cx=\'12\' cy=\'7\' r=\'4\'/></svg>'"/>
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

      <!-- Error message (from login.php POST) -->
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
          <img src="/assets/img/cvaarrd.png" alt="CVAARRD Logo"
               onerror="this.outerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.8\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect x=\'3\' y=\'11\' width=\'18\' height=\'11\' rx=\'2\'/><path d=\'M7 11V7a5 5 0 0110 0v4\'/><circle cx=\'12\' cy=\'16\' r=\'1\' fill=\'currentColor\'/></svg>'"/>
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
          <label style="justify-content:center;">ENTER OTP CODE</label>
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

      <!-- STEP 2b: Set new password (shown only after OTP verified) -->
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
        <h3>Create your <span style="color:var(--green)">Account.</span></h3>
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
            <label>FIRST NAME <span style="color:#e57373">*</span></label>
            <div class="inp-wrap">
              <input type="text" id="regFirstName" placeholder="Enter first name" autocomplete="given-name"/>
            </div>
          </div>
          <div class="field">
            <label>LAST NAME <span style="color:#e57373">*</span></label>
            <div class="inp-wrap">
              <input type="text" id="regLastName" placeholder="Enter last name" autocomplete="family-name"/>
            </div>
          </div>
        </div>

        <div class="field">
          <label>ROLE <span style="color:#e57373">*</span></label>
          <select class="inst-select" id="regRoleSelect" onchange="selectRole(this.value)">
            <option value="">— Select your role —</option>
            <option value="pta">Project Technical Assistant II</option>
            <option value="cmi">CMI Representative</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>

        <!-- Institution + Designation — lumalabas pagka-select ng role -->
        <div id="regInstField" style="display:none; margin-top:10px;">
          <div class="field" id="instSelectWrap">
            <label>INSTITUTION <span style="color:#e57373">*</span></label>
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

          <!-- Designation / Position — baba ng institution, visible sa lahat ng roles -->
          <div class="field" style="margin-top:10px;">
            <label>DESIGNATION / POSITION <span style="color:#e57373">*</span></label>
            <div class="inp-wrap">
              <input type="text" id="regDesignation" placeholder="e.g. Researcher, Project Leader, Faculty"/>
            </div>
          </div>
        </div>

        <div class="reg-btn-row" style="margin-top:20px">
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
          <label>EMAIL ADDRESS <span style="color:#e57373">*</span></label>
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
          <label>PASSWORD <span style="color:#e57373">*</span></label>
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
          <label>CONFIRM PASSWORD <span style="color:#e57373">*</span></label>
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
          <label style="justify-content:center;">ENTER OTP CODE</label>
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
          <!-- ★ BAGONG ROW: Designation -->
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

</div><!-- end main -->

<!-- FOOTER -->
<footer>
  <span>© 2026 CVAARRD Consortium Office. All rights reserved.</span>
  <div class="foot-links">
    <a href="privacy-policy.php">Privacy Policy</a>
    <span class="foot-sep">|</span>
    <a href="terms.php">Terms of Use</a>
    <span class="foot-sep">|</span>
    <a href="help.php">Help &amp; Support</a>
  </div>
</footer>

<script src="/assets/js/login.js?v=2"></script>
</body>
</html>
