<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SecReCo — Viewer Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="/assets/css/pta/base.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/header.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/sidebar.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/modals.css?v=3"/>
@yield('styles')
</head>
<body>

<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- ═══ HEADER ═══ -->
<header class="app-header">
  <div class="hdr-left" style="display:flex;align-items:center;gap:10px">
    <img src="/assets/img/cvaarrd.png" alt="CVAARRD" style="height:36px;width:auto;object-fit:contain;margin-right:4px"/>
    <div>
      <div class="hdr-page-title" style="font-size:16px;font-weight:700;color:#111827">SecReCo Dashboard</div>
      <div class="hdr-page-sub" style="font-size:12px;color:#6b7280">CVAARRD Consortium System (Viewer)</div>
    </div>
  </div>

  <div class="hdr-right-actions">
    <button class="notif-btn" onclick="window.location.href='/dashboard/viewer/notifications'" title="Notifications">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-dot"></span>
    </button>

    <div class="hdr-user-profile">
      <img class="hdr-user-avatar" src="{{ $userPhoto ? '/' . e($userPhoto) : '/assets/img/default-avatar.svg' }}" alt="avatar"/>
      <div class="hdr-user-details">
        <span class="hdr-user-name">{{ $userName }}</span>
        <span class="hdr-user-email">Viewer</span>
      </div>
    </div>

    <button class="signout-btn" onclick="confirmSignOut()" title="Sign Out">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </button>
  </div>
<!-- GLOBAL CONFIRM MODAL -->
<div class="modal-overlay" id="globalConfirmModal">
  <div class="modal-fc-box">
    <div class="modal-fc-icon-wrap type-green" id="gModalIconWrap">
      <svg id="gModalIcon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div class="modal-fc-title" id="gModalTitle">Confirmation</div>
    <div class="modal-fc-desc" id="gModalDesc">Are you sure you want to proceed?</div>
    <input type="text" class="modal-fc-input" id="gModalInput" style="display:none" placeholder="Enter reason or details..."/>
    <div class="modal-fc-actions">
      <button class="modal-fc-btn modal-fc-btn-cancel" onclick="closeGlobalModal()">Cancel</button>
      <button class="modal-fc-btn modal-fc-btn-green" id="gModalConfirmBtn">Confirm</button>
    </div>
  </div>
</div>

</header>

<script src="/assets/js/pta/core.js?v=3"></script>
<script>
window.showToast = window.toast = window.toast || function(msg) {
  const c = document.getElementById('toastWrap');
  if (!c) { alert(msg); return; }
  const t = document.createElement('div');
  t.className = 'toast';
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => {
    t.classList.remove('show');
    setTimeout(() => t.remove(), 300);
  }, 3500);
};

let globalConfirmCallback = null;

function closeGlobalModal() {
  const m = document.getElementById('globalConfirmModal');
  if (m) m.classList.remove('open');
  globalConfirmCallback = null;
}

function showConfirmModal(opts) {
  const title       = opts.title || 'Confirmation';
  const desc        = opts.message || 'Are you sure you want to proceed?';
  const confirmText = opts.confirmText || 'Confirm';
  const type        = opts.type || 'green';
  const onConfirm   = opts.onConfirm || null;

  document.getElementById('gModalTitle').textContent = title;
  document.getElementById('gModalDesc').textContent  = desc;
  document.getElementById('gModalInput').style.display = 'none';

  const iconWrap = document.getElementById('gModalIconWrap');
  iconWrap.className = 'modal-fc-icon-wrap type-' + type;

  const btn = document.getElementById('gModalConfirmBtn');
  btn.className = 'modal-fc-btn modal-fc-btn-' + type;
  btn.textContent = confirmText;

  const iconSvg = document.getElementById('gModalIcon');
  if (type === 'green') {
    iconSvg.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
  } else if (type === 'red') {
    iconSvg.innerHTML = '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>';
  } else if (type === 'orange') {
    iconSvg.innerHTML = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
  } else {
    iconSvg.innerHTML = '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>';
  }

  btn.onclick = async function() {
    btn.disabled = true;
    const oldText = confirmText;
    btn.innerHTML = '<span class="btn-spinner"></span> Processing...';
    try {
      if (onConfirm) await onConfirm();
    } catch(e) {
      console.error(e);
    } finally {
      btn.disabled = false;
      btn.textContent = oldText;
      closeGlobalModal();
    }
  };

  document.getElementById('globalConfirmModal').classList.add('open');
}

function confirmSignOut() {
  showConfirmModal({
    title: 'Sign Out Account?',
    message: 'Are you sure you want to sign out of your Viewer session?',
    confirmText: 'Sign Out',
    type: 'red',
    onConfirm: function() {
      fetch('/api/auth/logout', { method: 'POST' }).then(() => {
        window.location.href = '/login';
      });
    }
  });
}

(async function loadBellBadge() {
  try {
    const year = new Date().getFullYear();
    const res  = await fetch(`/api/notifications?year=${year}`);
    const json = await res.json();
    if (!json.ok) return;
    const dot = document.querySelector('.notif-dot');
    if (!dot) return;
    dot.style.display = json.unread_count > 0 ? 'block' : 'none';
  } catch (e) {
    console.warn('[bell] Could not fetch notification count:', e);
  }
})();
</script>

<!-- ═══ BODY ═══ -->
<div class="app-body">
  <aside class="sidebar">
    <div class="sb-brand">
      <img src="/assets/img/cvaarrd.png" alt="CVAARRD Logo" style="height:36px;width:auto;object-fit:contain;margin-right:10px;"/>
      <div>
        <div class="sb-brand-title">SecReCo</div>
        <div class="sb-brand-sub">CVAARRD Consortium</div>
      </div>
    </div>

    <div class="nav-section">
      <div class="nav-sec-label">Main</div>
      <a href="/dashboard/viewer/dashboard" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
        Dashboard
      </a>
      <a href="/dashboard/viewer/submissions" class="nav-item {{ $currentPage === 'submissions' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M12 19V5m-7 7 7-7 7 7"/><path d="M5 19h14"/></svg></span>
        Submissions
      </a>
      <a href="/dashboard/viewer/reports" class="nav-item {{ $currentPage === 'reports' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        Reports
      </a>
      <a href="/dashboard/viewer/institutions" class="nav-item {{ $currentPage === 'institutions' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg></span>
        Institutions
      </a>
    </div>
  </aside>

  <div class="main-content" id="mainContent">
    @yield('content')
  </div>
</div>

@yield('scripts')
</body>
</html>
