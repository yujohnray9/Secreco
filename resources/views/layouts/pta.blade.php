<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SecReCo — PTA Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="/assets/css/pta/base.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/header.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/sidebar.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/modals.css?v=3"/>
<link rel="stylesheet" href="/assets/css/pta/upload_photo.css?v=3"/>
@yield('styles')
</head>
<body>

<!-- TOAST CONTAINER -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- FRESHCART GLOBAL CUSTOM CONFIRM / PROMPT MODAL -->
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

<!-- ═══ HEADER ═══ -->
<header class="app-header">
  <!-- Title & Subtitle -->
  <div class="hdr-title-group">
    <div class="hdr-page-title">SecReCo Dashboard</div>
    <div class="hdr-page-sub">Analyze consortium submissions, metrics, and institutional accomplishments.</div>
  </div>



  <!-- Right Actions -->
  <div class="hdr-right-actions">
    <!-- Notification Bell -->
    <button class="notif-btn" onclick="window.location.href='/dashboard/pta/notifications'" title="Notifications">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-dot"></span>
    </button>

    @php
      $photoUrl = Auth::user()?->profile_picture ?? $userPhoto;
      if ($photoUrl) {
          $photoUrl = ltrim($photoUrl, '/');
          if (!str_starts_with($photoUrl, 'storage/') && !str_starts_with($photoUrl, 'assets/')) {
              $photoUrl = 'storage/' . $photoUrl;
          }
          $photoUrl = '/' . $photoUrl;
      } else {
          $photoUrl = '/assets/img/default-avatar.svg';
      }
    @endphp
    <!-- User Profile Badge -->
    <div class="hdr-user-profile">
      <img class="hdr-user-avatar" src="{{ $photoUrl }}" alt="avatar"/>
      <div class="hdr-user-details">
        <span class="hdr-user-name">{{ $userName }}</span>
        <span class="hdr-user-email">PTA Admin</span>
      </div>
    </div>

    <!-- Sign Out Button -->
    <button class="signout-btn" onclick="confirmSignOut()" title="Sign Out">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </button>
  </div>
</header>

<script src="/assets/js/pta/core.js?v=3"></script>
<script src="/assets/js/pta/upload_photo.js?v=3"></script>
<script>
window.showToast = window.toast = window.toast || function(msg) {
  const c = document.getElementById('toastWrap');
  if (!c) { alert(msg); return; }
  const t = document.createElement('div');
  t.className = 'toast';
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3000);
};

let globalConfirmCallback = null;


function closeGlobalModal() {
  document.getElementById('globalConfirmModal').classList.remove('open');
  globalConfirmCallback = null;
}

function showConfirmModal(opts) {
  const title       = opts.title || 'Confirmation';
  const desc        = opts.message || 'Are you sure you want to proceed?';
  const confirmText = opts.confirmText || 'Confirm';
  const type        = opts.type || 'green'; // green, red, orange, blue
  const onConfirm   = opts.onConfirm || null;

  document.getElementById('gModalTitle').textContent = title;
  document.getElementById('gModalDesc').textContent  = desc;
  document.getElementById('gModalInput').style.display = 'none';

  const iconWrap = document.getElementById('gModalIconWrap');
  iconWrap.className = 'modal-fc-icon-wrap type-' + type;

  const btn = document.getElementById('gModalConfirmBtn');
  btn.className = 'modal-fc-btn modal-fc-btn-' + type;
  btn.textContent = confirmText;

  // Set SVG Icon
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

  btn.onclick = function() {
    closeGlobalModal();
    if (onConfirm) onConfirm();
  };

  document.getElementById('globalConfirmModal').classList.add('open');
}

function showPromptModal(opts) {
  const title       = opts.title || 'Input Required';
  const desc        = opts.message || 'Please enter details:';
  const placeholder = opts.placeholder || '';
  const confirmText = opts.confirmText || 'Submit';
  const onConfirm   = opts.onConfirm || null;

  document.getElementById('gModalTitle').textContent = title;
  document.getElementById('gModalDesc').textContent  = desc;

  const inp = document.getElementById('gModalInput');
  inp.style.display = 'block';
  inp.value = '';
  inp.placeholder = placeholder;

  const iconWrap = document.getElementById('gModalIconWrap');
  iconWrap.className = 'modal-fc-icon-wrap type-orange';

  const btn = document.getElementById('gModalConfirmBtn');
  btn.className = 'modal-fc-btn modal-fc-btn-orange';
  btn.textContent = confirmText;

  document.getElementById('gModalIcon').innerHTML = '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>';

  btn.onclick = function() {
    const val = inp.value.trim();
    if (!val) { showToast('Field cannot be empty.'); return; }
    closeGlobalModal();
    if (onConfirm) onConfirm(val);
  };

  document.getElementById('globalConfirmModal').classList.add('open');
  setTimeout(() => inp.focus(), 150);
}

function confirmSignOut() {
  showConfirmModal({
    title: 'Sign Out Account?',
    message: 'Are you sure you want to sign out of your SecReCo PTA account session?',
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
    const res  = await fetch(`/api/pta/notifications?year=${year}`);
    const json = await res.json();
    if (!json.ok) return;
    const dot = document.querySelector('.notif-dot');
    if (!dot) return;
    if (json.unread_count > 0) {
      dot.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
      dot.style.display = 'flex';
    } else {
      dot.style.display = 'none';
      dot.textContent = '';
    }
  } catch (e) {
    console.warn('[bell] Could not fetch notification count:', e);
  }
})();
</script>

<!-- ═══ BODY ═══ -->
<div class="app-body">
  <aside class="sidebar">
    <!-- Brand Header -->
    <div class="sb-brand">
      <img src="/assets/img/cvaarrd.png" alt="CVAARRD Logo" style="height:36px;width:auto;object-fit:contain;margin-right:10px;"/>
      <div>
        <div class="sb-brand-title">SecReCo</div>
        <div class="sb-brand-sub">CVAARRD Consortium</div>
      </div>
    </div>

    <!-- MAIN NAV -->
    <div class="nav-section">
      <div class="nav-sec-label">Main</div>
      <a href="/dashboard/pta/dashboard" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
        Dashboard
      </a>
      <a href="/dashboard/pta/submissions" class="nav-item {{ $currentPage === 'submissions' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M12 19V5m-7 7 7-7 7 7"/><path d="M5 19h14"/></svg></span>
        Submissions
      </a>
      <a href="/dashboard/pta/reports" class="nav-item {{ $currentPage === 'reports' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        Reports & Analytics
      </a>
      <a href="/dashboard/pta/institutions" class="nav-item {{ $currentPage === 'institutions' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
        Institutions
      </a>
    </div>

    <!-- OTHER NAV -->
    <div class="nav-section">
      <div class="nav-sec-label">Other</div>
      <a href="/dashboard/pta/users" class="nav-item {{ $currentPage === 'users' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg></span>
        User Management
      </a>
      <a href="/dashboard/pta/formats" class="nav-item {{ $currentPage === 'formats' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
        Manage Formats
      </a>
      <a href="/dashboard/pta/settings" class="nav-item {{ $currentPage === 'settings' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
        Settings
      </a>
      <a href="#" onclick="confirmSignOut(); return false;" class="nav-item">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
        Logout
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
