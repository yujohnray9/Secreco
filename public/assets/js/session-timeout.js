/**
 * session-timeout.js — Inactivity detector & session timeout warning for SecReCo
 * Total timeout: 30 minutes (1,800,000 ms)
 * Warning modal triggers at: 28 minutes (120 seconds countdown)
 */
(function () {
  'use strict';

  // Configuration (in milliseconds)
  const TOTAL_TIMEOUT_MS = 30 * 60 * 1000; // 30 minutes
  const WARNING_DURATION_SEC = 120; // 2 minutes warning countdown
  const WARNING_THRESHOLD_MS = TOTAL_TIMEOUT_MS - (WARNING_DURATION_SEC * 1000); // 28 minutes

  let lastActivity = Date.now();
  let warningActive = false;
  let countdownTimer = null;
  let checkInterval = null;
  let remainingSeconds = WARNING_DURATION_SEC;

  // Track user interaction (throttled to avoid CPU overhead)
  let lastThrottle = 0;
  function registerActivity() {
    // If the warning modal is already showing, user must explicitly click "Stay Logged In"
    if (warningActive) return;

    const now = Date.now();
    if (now - lastThrottle > 10000) { // check every 10s
      lastActivity = now;
      lastThrottle = now;
    }
  }

  const events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
  events.forEach(evt => window.addEventListener(evt, registerActivity, { passive: true }));

  // Create warning modal in DOM
  function ensureModal() {
    if (document.getElementById('sessionTimeoutOverlay')) return;

    const style = document.createElement('style');
    style.innerHTML = `
      #sessionTimeoutOverlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 25, 18, 0.65);
        backdrop-filter: blur(5px);
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s ease-out;
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      }
      #sessionTimeoutOverlay.active {
        display: flex;
        opacity: 1;
      }
      .session-timeout-modal {
        background: #ffffff;
        border-radius: 16px;
        padding: 32px 28px;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        text-align: center;
        transform: scale(0.95);
        transition: transform 0.2s ease-out;
      }
      #sessionTimeoutOverlay.active .session-timeout-modal {
        transform: scale(1);
      }
      .session-timeout-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: #fef3c7;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulseWarning 2s infinite ease-in-out;
      }
      @keyframes pulseWarning {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.3); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(217, 119, 6, 0); }
      }
      .session-timeout-modal h3 {
        margin: 0 0 8px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
      }
      .session-timeout-modal p {
        margin: 0 0 24px;
        font-size: 0.92rem;
        color: #4b5563;
        line-height: 1.5;
      }
      .session-countdown-badge {
        display: inline-block;
        font-weight: 800;
        font-size: 1.15rem;
        color: #d97706;
        background: #fffbeb;
        padding: 2px 10px;
        border-radius: 8px;
        border: 1px solid #fde68a;
      }
      .session-timeout-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
      }
      .session-timeout-btn {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 0.92rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all 0.15s ease;
      }
      .btn-timeout-stay {
        background: #075b42;
        color: #ffffff;
        flex: 1;
      }
      .btn-timeout-stay:hover {
        background: #097253;
      }
      .btn-timeout-logout {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
      }
      .btn-timeout-logout:hover {
        background: #e5e7eb;
        color: #111827;
      }
    `;
    document.head.appendChild(style);

    const overlay = document.createElement('div');
    overlay.id = 'sessionTimeoutOverlay';
    overlay.innerHTML = `
      <div class="session-timeout-modal" role="dialog" aria-modal="true">
        <div class="session-timeout-icon">
          <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <h3>Session Expiring Soon</h3>
        <p>
          You have been inactive. For your security, you will be automatically logged out in
          <br><span class="session-countdown-badge" id="sessionCountdownSeconds">120s</span>
        </p>
        <div class="session-timeout-actions">
          <button type="button" class="session-timeout-btn btn-timeout-logout" id="btnSessionLogout">Log Out</button>
          <button type="button" class="session-timeout-btn btn-timeout-stay" id="btnSessionStay">Stay Logged In</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);

    document.getElementById('btnSessionStay')?.addEventListener('click', extendSession);
    document.getElementById('btnSessionLogout')?.addEventListener('click', forceLogout);
  }

  // Show warning modal and begin countdown
  function showWarning() {
    if (warningActive) return;
    warningActive = true;
    ensureModal();

    const overlay = document.getElementById('sessionTimeoutOverlay');
    if (overlay) overlay.classList.add('active');

    remainingSeconds = WARNING_DURATION_SEC;
    updateCountdownUI();

    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
      remainingSeconds--;
      updateCountdownUI();
      if (remainingSeconds <= 0) {
        clearInterval(countdownTimer);
        forceLogout();
      }
    }, 1000);
  }

  function updateCountdownUI() {
    const el = document.getElementById('sessionCountdownSeconds');
    if (el) {
      el.textContent = `${remainingSeconds}s`;
    }
  }

  // Keep session alive
  function extendSession() {
    if (countdownTimer) clearInterval(countdownTimer);
    warningActive = false;
    lastActivity = Date.now();

    const overlay = document.getElementById('sessionTimeoutOverlay');
    if (overlay) overlay.classList.remove('active');

    // Ping server to reset Laravel session lifetime
    fetch('/api/session-keepalive', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .catch(() => {});

    if (typeof window.showToast === 'function') {
      window.showToast('Session refreshed! You remain logged in.');
    }
  }

  // Terminate session
  function forceLogout() {
    if (countdownTimer) clearInterval(countdownTimer);
    if (checkInterval) clearInterval(checkInterval);

    fetch('/api/auth/logout', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .finally(() => {
        window.location.href = '/login?timeout=1';
      });
  }

  // Periodic checker every 5 seconds
  checkInterval = setInterval(() => {
    const idleTime = Date.now() - lastActivity;

    if (idleTime >= TOTAL_TIMEOUT_MS) {
      forceLogout();
    } else if (idleTime >= WARNING_THRESHOLD_MS && !warningActive) {
      showWarning();
    }
  }, 5000);

  // Initialize modal after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureModal);
  } else {
    ensureModal();
  }
})();
