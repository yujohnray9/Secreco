
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CVAARRD SecReCo — Verify</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/login.css"/>

  <!-- Cloudflare Turnstile -->
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>
    .verify-badge {
      width: 68px; height: 68px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--green-light), #d4e8d4);
      border: 2px solid rgba(45,106,48,0.18);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 10px;
      box-shadow: 0 4px 16px rgba(45,106,48,0.15);
    }
    .verify-badge svg { width: 32px; height: 32px; color: var(--green); }

    .verify-email-tag {
      display: inline-block;
      background: var(--green-pale);
      border: 1px solid rgba(45,106,48,0.2);
      border-radius: 20px;
      padding: 5px 14px;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--green-dark);
      margin: 10px 0 18px;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Turnstile widget centering */
    .turnstile-wrap {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
      min-height: 65px;
    }

    /* Rate-limit lockout banner */
    .rate-notice {
      display: none;
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      color: #856404;
      margin-bottom: 14px;
      text-align: center;
    }
    .rate-notice.show { display: block; }
  </style>
</head>
<body>

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
        A Secure Reporting and Consolidation System<br/>
        for CVAARRD Consortium.
      </p>
      <div class="hero-underline"></div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="login-view" id="loginView">
    <div class="card">

      <!-- Shield badge -->
      <div class="verify-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <polyline points="9 12 11 14 15 10"/>
        </svg>
      </div>

      <div class="card-head">
        <h3>Security Verification</h3>
        <p>Complete the challenge before signing in.</p>
      </div>

      <!-- Email tag -->
      <div style="text-align:center;">
        <span class="verify-email-tag" id="displayEmail">—</span>
      </div>

      <!-- Rate-limit lockout notice -->
      <div class="rate-notice" id="rateNotice">
        Too many failed attempts. Please wait <strong id="rateCooldown">15</strong> minute(s) before trying again.
      </div>

      <!-- Error message -->
      <div class="error-msg" id="verifyError">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="verifyErrorText">Verification failed. Try again.</span>
      </div>

      <!-- CLOUDFLARE TURNSTILE WIDGET -->
      <div class="turnstile-wrap">
        <div class="cf-turnstile"
             data-sitekey="{{ in_array(request()->getHost(), ['localhost', '127.0.0.1']) 
    ? '1x00000000000000000000AA' 
    : env('CF_TURNSTILE_SITE_KEY', '0x4AAAAAADm44d5meCp5GCFg') }}"
             data-theme="light"
             data-callback="onTurnstileSuccess"
             data-expired-callback="onTurnstileExpired"
             data-error-callback="onTurnstileError">
        </div>
      </div>

      <button class="btn-signin" type="button" id="verifyBtn" onclick="handleVerify()" disabled>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Verify &amp; Sign In
      </button>

      <div class="reg-row" style="margin-top:14px;">
        <a href="/login" onclick="sessionStorage.removeItem('_pendingEmail'); sessionStorage.removeItem('_pendingPw');">
          ← Back to Login
        </a>
      </div>

    </div>
    </div>
  </div><!-- end right-panel -->

</div><!-- end main -->

<!-- FOOTER -->
<footer>
  <span>© 2026 CVAARRD Consortium. All rights reserved.</span>
  <div class="foot-links">
    <a href="#">Privacy Policy</a>
    <span class="foot-sep">|</span>
    <a href="#">Terms of Use</a>
    <span class="foot-sep">|</span>
    <a href="#">Help &amp; Support</a>
  </div>
</footer>

<script>
const AUTH_LOGIN_URL = '/api/auth/login';
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

/* ── REDIRECT IF NO PENDING CREDENTIALS ── */
const pendingEmail = sessionStorage.getItem('_pendingEmail');
const pendingPw    = sessionStorage.getItem('_pendingPw');

if (!pendingEmail || !pendingPw) {
  window.location.href = '/login';
}

document.getElementById('displayEmail').textContent = pendingEmail || '—';

/* ── CLIENT-SIDE RATE LIMIT CHECK ON LOAD ── */
(function() {
  const MAX = 5, WINDOW_MS = 15 * 60 * 1000;
  const attempts = parseInt(sessionStorage.getItem('_rl_attempts') || '0');
  const firstTs  = parseInt(sessionStorage.getItem('_rl_ts')       || '0');
  const now      = Date.now();

  if (attempts >= MAX && (now - firstTs) < WINDOW_MS) {
    const remaining = Math.ceil((WINDOW_MS - (now - firstTs)) / 60000);
    document.getElementById('rateCooldown').textContent = remaining;
    document.getElementById('rateNotice').classList.add('show');
    document.getElementById('verifyBtn').disabled = true;
    const tw = document.querySelector('.turnstile-wrap');
    tw.style.opacity = '0.4';
    tw.style.pointerEvents = 'none';
  } else if ((now - firstTs) >= WINDOW_MS) {
    sessionStorage.removeItem('_rl_attempts');
    sessionStorage.removeItem('_rl_ts');
  }
})();

/* ── TURNSTILE CALLBACKS ── */
let turnstileToken = null;

function onTurnstileSuccess(token) {
  turnstileToken = token;
  document.getElementById('verifyBtn').disabled = false;
  document.getElementById('verifyError').classList.remove('show');
}

function onTurnstileExpired() {
  turnstileToken = null;
  document.getElementById('verifyBtn').disabled = true;
}

function onTurnstileError() {
  turnstileToken = null;
  document.getElementById('verifyBtn').disabled = true;
  showError('Turnstile challenge failed. Please refresh the page and try again.');
}

/* ── HELPERS ── */
function showError(msg) {
  const errBox  = document.getElementById('verifyError');
  const errText = document.getElementById('verifyErrorText');
  errText.textContent = msg;
  errBox.classList.add('show');
}

function resetTurnstile() {
  if (window.turnstile) turnstile.reset();
  turnstileToken = null;
  document.getElementById('verifyBtn').disabled = true;
}

function incrementRateLimit() {
  const now = Date.now();
  let attempts = parseInt(sessionStorage.getItem('_rl_attempts') || '0');
  if (attempts === 0) sessionStorage.setItem('_rl_ts', now);
  attempts++;
  sessionStorage.setItem('_rl_attempts', attempts);
  return attempts;
}

/* ── VERIFY HANDLER ── */
function handleVerify() {
  document.getElementById('verifyError').classList.remove('show');

  if (!turnstileToken) {
    showError('Please complete the security challenge first.');
    return;
  }

  // Client-side lockout check before sending
  const MAX = 5, WINDOW_MS = 15 * 60 * 1000;
  const attempts = parseInt(sessionStorage.getItem('_rl_attempts') || '0');
  const firstTs  = parseInt(sessionStorage.getItem('_rl_ts')       || '0');
  if (attempts >= MAX && (Date.now() - firstTs) < WINDOW_MS) {
    const remaining = Math.ceil((WINDOW_MS - (Date.now() - firstTs)) / 60000);
    document.getElementById('rateCooldown').textContent = remaining;
    document.getElementById('rateNotice').classList.add('show');
    return;
  }

  const btn = document.getElementById('verifyBtn');
  btn.disabled = true;
  btn.textContent = 'Signing in…';

  const fd = new FormData();
  fd.append('email',                 pendingEmail);
  fd.append('password',              pendingPw);
  fd.append('cf_turnstile_response', turnstileToken);

  fetch(AUTH_LOGIN_URL, { method: 'POST', body: fd })
    .then(r => {
      if (!r.ok) {
        return r.text().then(txt => {
          console.error('Server error ' + r.status + ':', txt);
          throw new Error('HTTP ' + r.status);
        });
      }
      return r.text().then(txt => {
        try {
          return JSON.parse(txt);
        } catch(e) {
          console.error('Non-JSON response:', txt);
          throw new Error('Server returned invalid response. Check console for details.');
        }
      });
    })
    .then(res => {
      if (!res.success) {
        const count = incrementRateLimit();
        // Restore credentials so the user can retry
        sessionStorage.setItem('_pendingEmail', pendingEmail);
        sessionStorage.setItem('_pendingPw', pendingPw);
        if (count >= MAX) {
          document.getElementById('rateCooldown').textContent = 15;
          document.getElementById('rateNotice').classList.add('show');
          btn.disabled = true;
        } else {
          showError(res.message || 'Verification failed. Try again.');
          btn.disabled = false;
          btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Verify &amp; Sign In`;
          resetTurnstile();
        }
        return;
      }
      // Success — clear credentials AND rate limit counters
      sessionStorage.removeItem('_pendingEmail');
      sessionStorage.removeItem('_pendingPw');
      sessionStorage.removeItem('_rl_attempts');
      sessionStorage.removeItem('_rl_ts');
      window.location.href = res.redirect;
    })
    .catch((err) => {
      console.error('Fetch error:', err);
      showError('Error: ' + (err.message || 'Network error. Please check your connection and try again.'));
      btn.disabled = false;
      btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Verify &amp; Sign In`;
      // Restore credentials so the user can retry without going back to login
      sessionStorage.setItem('_pendingEmail', pendingEmail);
      sessionStorage.setItem('_pendingPw', pendingPw);
      resetTurnstile();
    });
}
</script>
</body>
</html>
