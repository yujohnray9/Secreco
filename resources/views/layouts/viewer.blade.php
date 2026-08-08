<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SecReCo — Viewer Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="/assets/css/pta/base.css?v=2"/>
<link rel="stylesheet" href="/assets/css/pta/header.css?v=2"/>
<link rel="stylesheet" href="/assets/css/pta/sidebar.css?v=2"/>
<link rel="stylesheet" href="/assets/css/pta/modals.css?v=2"/>
@yield('styles')
</head>
<body>

<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- MODAL: Generic Confirm -->
<div class="modal-overlay" id="modalConfirm">
  <div class="modal" style="width:360px">
    <div class="modal-title" id="confirmTitle">Are you sure?</div>
    <div class="modal-desc" id="confirmMsg">Are you sure you want to proceed?</div>
    <div class="modal-actions">
      <button class="btn" onclick="closeModal('modalConfirm')">Cancel</button>
      <button class="btn btn-danger" id="confirmOkBtn">OK</button>
    </div>
  </div>
</div>

<!-- ═══ HEADER ═══ -->
<header class="app-header">
  <div class="hdr-left">
    <div class="logo-circle" style="background:#2d5a2f;overflow:hidden;padding:0">
      <img src="/assets/img/cvaarrd.png" alt="CVAARRD" style="width:100%;height:100%;object-fit:cover"/>
    </div>
    <div>
      <div class="brand-name">SecReCo · CVAARRD Consortium</div>
      <div class="brand-sub">Secure Reporting and Consolidation System (Viewer)</div>
    </div>
  </div>

  <div class="hdr-center">
    <div class="hdr-meta">
      <span class="hdr-cy">CY {{ date('Y') }}</span>
      <span style="color:var(--border);margin:0 4px">|</span>
      Annual Accomplishment Report
    </div>
  </div>

  <div class="hdr-right">
    <button class="notif-btn" onclick="window.location.href='/dashboard/viewer/notifications'" title="Notifications">
      🔔<span class="notif-dot"></span>
    </button>
    <button class="signout-btn" onclick="confirmSignOut()">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </button>
  </div>
</header>

<script src="/assets/js/pta/core.js?v=2"></script>
<script>
function confirmSignOut() {
  document.getElementById('confirmTitle').textContent = 'Sign Out?';
  document.getElementById('confirmMsg').textContent   = 'Are you sure you want to sign out?';
  const okBtn = document.getElementById('confirmOkBtn');
  okBtn.textContent = 'Sign Out';
  okBtn.onclick = function () {
    fetch('/api/auth/logout', { method: 'POST' }).then(() => {
      window.location.href = '/login';
    });
  };
  openModal('modalConfirm');
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
    <div class="sidebar-user">
      <div class="sb-user-center">
        <div class="sb-av-lg">
          <img id="sidebarAvatarImg"
               src="{{ $userPhoto ? '/' . e($userPhoto) : '/assets/img/default-avatar.svg' }}"
               alt="avatar"/>
        </div>
        <div class="sb-name" style="margin-top:6px">{{ $userName }}</div>
        <div class="sb-role">Viewer</div>
      </div>
    </div>

    <div class="nav-section">
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
