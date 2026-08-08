// ═══ SETTINGS PAGE ═══

function previewProfilePhoto(input){
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e){
    const img = document.getElementById('profilePhotoImg');
    const fallback = img.nextElementSibling;
    img.src = e.target.result;
    img.style.display = 'block';
    if (fallback) fallback.style.display = 'none';
  };
  reader.readAsDataURL(file);
  if (typeof toast === 'function') {
    toast('📷 Photo selected — click "Save Changes" to apply');
  }
}

function switchTab(el, target){
  const card = el.closest('.card');
  card.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
  card.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
  document.getElementById(target).classList.add('active');
}

function filterAuditLogs(){
  const search = (document.getElementById('auditSearch')?.value || '').toLowerCase().trim();
  const action = document.getElementById('auditActionFilter')?.value || '';

  const rows = document.querySelectorAll('#auditTable tbody tr');
  let visibleCount = 0;

  rows.forEach(row => {
    const rowSearch = row.dataset.search || '';
    const rowAction = row.dataset.action || '';

    const matchesSearch = !search || rowSearch.includes(search);
    const matchesAction = !action || rowAction === action;

    const visible = matchesSearch && matchesAction;
    row.style.display = visible ? '' : 'none';
    if (visible) visibleCount++;
  });

  const empty = document.getElementById('auditEmpty');
  if (empty) empty.style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', filterAuditLogs);
