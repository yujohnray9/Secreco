// ════════════════════════════════════════════
// CORE — shared utilities loaded on every page
// ════════════════════════════════════════════

// ── PAGE NAVIGATION ──
// Page-to-URL map — add entries here whenever a new separate PHP page is added.
const PAGE_URLS = {
  dashboard     : '/dashboards/pta/dashboard.php',
  submissions   : '/dashboards/pta/submissions.php',
  reports       : '/dashboards/pta/reports.php',
  users         : '/dashboards/pta/users.php',
  institutions  : '/dashboards/pta/institutions.php',
  formats       : '/dashboards/pta/formats.php',
  settings      : '/dashboards/pta/settings.php',
  notifications : '/dashboards/pta/notifications.php',
};

function navTo(pg) {
  // Try in-page section first (single-file multi-section layout)
  const target = document.getElementById('page-' + pg);
  if (target) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    target.classList.add('active');
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const navItem = document.getElementById('nav-' + pg);
    if (navItem) navItem.classList.add('active');
    const mc = document.getElementById('mainContent');
    if (mc) mc.scrollTop = 0;
    return;
  }
  // Fall back to separate PHP page redirect
  const url = PAGE_URLS[pg];
  if (url) { window.location.href = url; return; }
  console.warn('[navTo] Unknown page:', pg);
}

// ── MODALS ──
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', function (e) {
  if (e.target === this) this.classList.remove('open');
}));

// ── TOAST ──
function toast(msg) {
  const c = document.getElementById('toastWrap');
  if (!c) return;
  const t = document.createElement('div');
  t.className = 'toast';
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}
window.toast = toast;
window.showToast = toast;

