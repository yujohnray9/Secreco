@extends('layouts.pta')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:24px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }

/* ── Inst Card Grid ── */
.inst-card-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; padding-top:8px; }
.inst-card { border:1px solid #f0f0f0; border-radius:14px; padding:18px 20px; transition:all .2s; background:#fff; }
.inst-card:hover { border-color:#a7f3d0; box-shadow:0 4px 16px rgba(16,185,129,.1); transform:translateY(-2px); }
.inst-card-head { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.inst-logo { width:42px; height:42px; border-radius:10px; object-fit:cover; background:#ecfdf5; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:800; color:#059669; border:2px solid #a7f3d0; flex-shrink:0; }
.inst-name { font-size:14px; font-weight:700; color:#111827; line-height:1.3; }
.inst-type { font-size:11.5px; color:#9ca3af; margin-top:2px; }
.inst-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.inst-encoder { font-size:12px; color:#6b7280; }
.inst-tables  { font-size:12px; font-weight:700; color:#374151; }
.inst-prog { margin-top:10px; }
.inst-prog-head { display:flex; justify-content:space-between; margin-bottom:5px; }
.inst-prog-label { font-size:11px; color:#9ca3af; }
.inst-prog-pct   { font-size:11px; font-weight:700; color:#10b981; }
.prog-track { height:6px; background:#f3f4f6; border-radius:20px; overflow:hidden; }
.prog-fill  { height:100%; border-radius:20px; background:linear-gradient(90deg,#10b981,#34d399); transition:width .8s ease; }

/* ── Table View ── */
.fc-table-wrap { overflow-x:auto; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }

/* ── Toggle ── */
.view-toggle { display:flex; gap:4px; background:#f3f4f6; border-radius:10px; padding:3px; }
.view-toggle-btn { border:none; background:transparent; border-radius:8px; padding:6px 12px; cursor:pointer; color:#6b7280; transition:all .15s; display:flex; align-items:center; gap:5px; font-size:12.5px; font-weight:600; }
.view-toggle-btn.active { background:#fff; color:#111827; box-shadow:0 1px 4px rgba(0,0,0,.1); }
</style>
@endsection

@section('content')
<div class="page active" id="page-institutions">

  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Member Institutions</div>
      <div class="pg-banner-sub">CVAARRD Region 2 Consortium Member Institutions</div>
    </div>
    <div class="view-toggle">
      <button class="view-toggle-btn active" id="btnCardView" onclick="setView('cards')">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Cards
      </button>
      <button class="view-toggle-btn" id="btnTableView" onclick="setView('table')">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Table
      </button>
    </div>
  </div>

  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Institutions Status Overview
      </div>
      <span class="badge badge-green" id="instCount">—</span>
    </div>
    <div class="fc-card-body">
      <!-- Card View -->
      <div id="viewCards" class="inst-card-grid"></div>
      <!-- Table View -->
      <div id="viewTable" style="display:none" class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr><th>Institution</th><th>Type</th><th>CMI Representative</th><th>Tables Done</th><th>Progress</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody id="instTbody"></tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script>
let instData = [];
let currentView = 'cards';

function setView(v) {
  currentView = v;
  document.getElementById('viewCards').style.display = v === 'cards' ? 'grid' : 'none';
  document.getElementById('viewTable').style.display = v === 'table' ? 'block' : 'none';
  document.getElementById('btnCardView').classList.toggle('active', v === 'cards');
  document.getElementById('btnTableView').classList.toggle('active', v === 'table');
}

function statusBadgeClass(s) {
  return s === 'Submitted' ? 'badge-green' : s === 'In Progress' ? 'badge badge-orange' : 'badge-gray';
}
function instInitials(name) {
  return name ? name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase() : '?';
}

function renderCards(data) {
  const wrap = document.getElementById('viewCards');
  if (!data.length) { wrap.innerHTML = '<div style="color:#9ca3af;font-size:13px;padding:24px">No institutions found.</div>'; return; }
  wrap.innerHTML = data.map(inst => {
    const pct = inst.total_tables > 0 ? Math.round((inst.tables_done / inst.total_tables) * 100) : 0;
    const badgeClass = inst.status === 'Submitted' ? 'badge-green' : inst.status === 'In Progress' ? 'badge-orange' : 'badge-gray';
    return `
      <div class="inst-card">
        <div class="inst-card-head">
          ${inst.logo_url
            ? `<img src="${inst.logo_url}" class="inst-logo" style="object-fit:cover" onerror="this.outerHTML='<div class=\\'inst-logo\\'>${instInitials(inst.name)}</div>'">`
            : `<div class="inst-logo">${instInitials(inst.name)}</div>`
          }
          <div>
            <div class="inst-name">${inst.name}</div>
            <div class="inst-type">${inst.type}</div>
          </div>
        </div>
        <div class="inst-meta">
          <span class="inst-encoder">${inst.encoder}</span>
          <span class="inst-tables">${inst.tables_done}/${inst.total_tables} tables</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <span class="badge ${badgeClass}">${inst.status}</span>
          <a href="/dashboard/pta/submissions" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#ecfdf5;color:#059669;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View
          </a>
        </div>
        <div class="inst-prog">
          <div class="inst-prog-head">
            <span class="inst-prog-label">Completion</span>
            <span class="inst-prog-pct">${pct}%</span>
          </div>
          <div class="prog-track"><div class="prog-fill" style="width:${pct}%"></div></div>
        </div>
      </div>
    `;
  }).join('');
}

function renderTable(data) {
  const tbody = document.getElementById('instTbody');
  if (!data.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">No institutions found.</td></tr>'; return; }
  tbody.innerHTML = data.map(inst => {
    const pct = inst.total_tables > 0 ? Math.round((inst.tables_done / inst.total_tables) * 100) : 0;
    const badgeClass = inst.status === 'Submitted' ? 'badge-green' : inst.status === 'In Progress' ? 'badge-orange' : 'badge-gray';
    return `
      <tr>
        <td><div style="display:flex;align-items:center;gap:10px">
          ${inst.logo_url ? `<img src="${inst.logo_url}" width="28" height="28" style="border-radius:6px" onerror="this.style.display='none'"/>` : ''}
          <strong>${inst.name}</strong>
        </div></td>
        <td style="color:#6b7280">${inst.type}</td>
        <td>${inst.encoder}</td>
        <td><strong>${inst.tables_done}/${inst.total_tables}</strong></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;min-width:120px">
            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden"><div style="height:100%;width:${pct}%;background:linear-gradient(90deg,#10b981,#34d399);border-radius:20px;transition:width .6s"></div></div>
            <span style="font-size:11px;font-weight:700;color:#10b981;white-space:nowrap">${pct}%</span>
          </div>
        </td>
        <td><span class="badge ${badgeClass}">${inst.status}</span></td>
        <td>
          <a href="/dashboard/pta/submissions" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#ecfdf5;color:#059669;text-decoration:none;border:1px solid #a7f3d0;">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View Submissions
          </a>
        </td>
      </tr>
    `;
  }).join('');
}


document.addEventListener('DOMContentLoaded', async function () {
  try {
    const year = new Date().getFullYear();
    const res  = await fetch(`/api/pta/institutions?year=${year}`);
    const json = await res.json();
    instData = json.institutions || [];
    document.getElementById('instCount').textContent = `${instData.length} institutions`;
    renderCards(instData);
    renderTable(instData);
  } catch(e) { console.error('Institutions load error:', e); }
});
</script>
@endsection
