<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SecReCo — Executive Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="/assets/css/pta/base.css?v=20260903"/>
<link rel="stylesheet" href="/assets/css/pta/header.css?v=20260903"/>
<link rel="stylesheet" href="/assets/css/pta/sidebar.css?v=20261008b"/>
<script src="/assets/js/sidebar-collapse.js?v=2"></script>
<link rel="stylesheet" href="/assets/css/pta/modals.css?v=20260903"/>
<link rel="stylesheet" href="/assets/css/pta/upload_photo.css?v=20260903"/>
@yield('styles')
</head>
<body>

<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>

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

<script src="/assets/js/pta/core.js?v=3"></script>
<script src="/assets/js/pta/upload_photo.js?v=3"></script>
<script>
function toggleMobileSidebar() {
  const sb = document.querySelector('.sidebar');
  const bd = document.getElementById('sidebarBackdrop');
  if (sb) sb.classList.toggle('mobile-open');
  if (bd) bd.classList.toggle('active');
}

function closeMobileSidebar() {
  const sb = document.querySelector('.sidebar');
  const bd = document.getElementById('sidebarBackdrop');
  if (sb) sb.classList.remove('mobile-open');
  if (bd) bd.classList.remove('active');
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeMobileSidebar();
});

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
    message: 'Are you sure you want to sign out of your Executive session?',
    confirmText: 'Sign Out',
    type: 'red',
    onConfirm: function() {
      fetch('/api/auth/logout', { method: 'POST' }).then(() => {
        window.location.href = '/login';
      });
    }
  });
}
</script>

<!-- APP SHELL -->
<div class="app-shell">
  <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

  <aside class="sidebar">
    <div class="sb-header-wrap">
      <div class="sidebar-brand" onclick="window.location.href='/dashboard/executive/dashboard'" style="cursor:pointer">
        <div class="brand-mark">
          <img src="/assets/img/cvaarrd.png" alt="CVAARRD Logo" onerror="this.src='/assets/img/cvaarrd.png'"/>
        </div>
        <div>
          <strong>SecReCo</strong>
          <span>CVAARRD Consortium (Executive)</span>
        </div>
      </div>
      <button class="sb-close-btn" onclick="closeMobileSidebar()" aria-label="Close Sidebar">&times;</button>
    </div>

    <div class="sidebar-rule"></div>

    <div class="nav-section">
      <div class="nav-sec-label">Main</div>
      <a href="/dashboard/executive/dashboard" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
        Dashboard
      </a>
      <a href="/dashboard/executive/institutions" class="nav-item {{ in_array($currentPage, ['institutions', 'institution']) ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        Institutions
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-sec-label">Account</div>
      <a href="/dashboard/executive/profile" class="nav-item {{ $currentPage === 'profile' ? 'active' : '' }}">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
        Profile
      </a>
      <a href="#" onclick="confirmSignOut(); return false;" class="nav-item logout">
        <span class="nav-ic"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
        Logout
      </a>
    </div>
  </aside>

  <div class="main-area">
    <header class="app-header topbar">
      <div class="hdr-left-group">
        <button class="hdr-menu-btn" id="mobileMenuBtn" onclick="toggleMobileSidebar()" aria-label="Toggle Menu" title="Menu">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>

        <div class="hdr-title-group">
          <div class="hdr-page-title">SecReCo Executive Portal</div>
          <div class="hdr-page-sub">CVAARRD Consortium System (Executive Director)</div>
        </div>
      </div>

      <div class="hdr-right-actions">
        <div class="profile-divider"></div>

        @php
          $rawPhoto = Auth::user()?->profile_picture ?? ($userPhoto ?? null);
          $photoUrl = '/assets/img/default-avatar.svg';
          if (!empty($rawPhoto)) {
              $trimmed = ltrim((string) $rawPhoto, '/');
              if (!str_starts_with($trimmed, 'storage/') && !str_starts_with($trimmed, 'assets/')) {
                  $trimmed = 'storage/' . $trimmed;
              }
              $photoUrl = '/' . $trimmed;
          }
        @endphp

        <div class="hdr-user-profile" onclick="window.location.href='/dashboard/executive/profile'" style="cursor:pointer">
          <div class="hdr-user-avatar">
            <img src="{{ $photoUrl }}" alt="avatar" onerror="this.src='/assets/img/default-avatar.svg'"/>
          </div>
          <div class="hdr-user-details">
            <span class="hdr-user-name">{{ $userName }}</span>
            <span class="hdr-user-email">Executive Director</span>
          </div>
        </div>

        <button class="signout-btn" onclick="confirmSignOut()" title="Sign Out">
          <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </div>
    </header>

    <main class="main-content page-content" id="mainContent">
      @yield('content')
    </main>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  });
});
</script>
<script src="/assets/js/session-timeout.js?v=3"></script>
@yield('scripts')
</body>
</html>
