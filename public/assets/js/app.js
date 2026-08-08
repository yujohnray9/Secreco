function showScreen(id, navEl) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById('screen-' + id).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  navEl.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

function editFormat(name) {
  document.getElementById('formatName').textContent = name;
  document.getElementById('formatEditor').style.display = 'block';
  document.getElementById('formatEditor').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function saveFormat() {
  showToast('Template saved!');
  document.getElementById('formatEditor').style.display = 'none';
}

function cancelEdit() {
  document.getElementById('formatEditor').style.display = 'none';
}

function switchSettingsTab(el, tab) {
  document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  ['security', 'access', 'auth', 'audit'].forEach(id => {
    const e = document.getElementById('settings-' + id);
    if (e) e.style.display = id === tab ? 'block' : 'none';
  });
}

function toggleUserStatus(btn, name) {
  const row = btn.closest('tr');
  const badge = row.querySelectorAll('.badge')[1];
  if (btn.classList.contains('btn-deactivate')) {
    badge.className = 'badge badge-inactive';
    badge.textContent = 'Inactive';
    btn.textContent = 'Activate';
    btn.className = 'btn btn-activate';
    showToast(name + ' deactivated');
  } else {
    badge.className = 'badge badge-active';
    badge.textContent = 'Active';
    btn.textContent = 'Deactivate';
    btn.className = 'btn btn-deactivate';
    showToast(name + ' activated');
  }
}

// Institution filter
document.addEventListener('DOMContentLoaded', function () {
  const f = document.getElementById('institutionFilter');
  if (f) {
    f.addEventListener('change', function () {
      const sel = Array.from(this.selectedOptions);
      if (sel.some(o => o.value === 'all') && sel.length === 1) {
        Array.from(this.options).forEach(o => o.selected = true);
      } else {
        this.querySelector('option[value="all"]').selected = false;
      }
    });
  }
});
