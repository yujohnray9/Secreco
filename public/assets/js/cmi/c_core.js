/* ═══════════════════════════════════════════
   C_CORE — shared helpers for header, sidebar
   and footer (modals, toast, navigation)
════════════════════════════════════════════ */

// ── MODAL HELPERS ──
function openModal(id){
  const el = document.getElementById(id);
  if(el) el.classList.add('open');
}

function closeModal(id){
  const el = document.getElementById(id);
  if(el) el.classList.remove('open');
}

// ── GENERIC CONFIRM MODAL ──
// Usage: showConfirm('Title', 'Message', () => { ...on confirm... })
function showConfirm(title, message, onConfirm){
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMsg').textContent = message;

  const okBtn = document.getElementById('confirmOkBtn');
  // Replace handler to avoid stacking listeners
  const newBtn = okBtn.cloneNode(true);
  okBtn.parentNode.replaceChild(newBtn, okBtn);

  newBtn.addEventListener('click', () => {
    closeModal('modalConfirm');
    if(typeof onConfirm === 'function') onConfirm();
  });

  openModal('modalConfirm');
}

// ── TOAST ──
function toast(message, duration = 3000){
  const wrap = document.getElementById('toastWrap');
  if(!wrap) return;

  const el = document.createElement('div');
  el.className = 'toast';
  el.textContent = message;
  wrap.appendChild(el);

  setTimeout(() => el.remove(), duration);
}

// ── NAVIGATION ──
// Redirects to the given CMI dashboard page.
function navTo(page){
  window.location.href = '/dashboards/cmi/' + page + '.php';
}

// ── CLOSE MODAL ON OVERLAY CLICK ──
document.addEventListener('click', function(e){
  if(e.target.classList && e.target.classList.contains('modal-overlay') && e.target.classList.contains('open')){
    e.target.classList.remove('open');
  }
});
