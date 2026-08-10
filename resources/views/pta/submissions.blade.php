@extends('layouts.pta')

@section('styles')
<style>
/* ── Page Header ── */
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }

/* ── FC Card ── */
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #f3f4f6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:0 24px 24px; }

/* ── Filter Bar ── */
.filter-select { border:1px solid #e5e7eb; border-radius:8px; padding:7px 12px; font-size:13px; color:#374151; background:#f9fafb; cursor:pointer; outline:none; }
.filter-select:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.12); }

/* ── Table ── */
.fc-table-wrap { overflow-x:auto; margin-top:4px; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:last-child { border-bottom:none; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }

/* ── Badges ── */
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-orange { background:#fff7ed; color:#d97706; }
.badge-red    { background:#fef2f2; color:#dc2626; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#eff6ff; color:#2563eb; }
.badge-purple { background:#f5f3ff; color:#7c3aed; }

/* ── Action Buttons ── */
.btn-sm-fc { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.btn-sm-view    { background:#ecfdf5; color:#059669; }
.btn-sm-view:hover { background:#d1fae5; }
.btn-sm-accept  { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
.btn-sm-accept:hover { background:#d1fae5; }
.btn-sm-return  { background:#fff7ed; color:#d97706; border:1px solid #fed7aa; }
.btn-sm-return:hover { background:#ffedd5; }

/* ── Modal Box for View Data ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(17,24,39,0.55); backdrop-filter:blur(6px); z-index:9990; align-items:center; justify-content:center; padding:16px; }
.modal-overlay.open { display:flex; }
.modal-box-lg { background:#fff; border-radius:20px; padding:28px; width:100%; max-width:760px; box-shadow:0 24px 60px rgba(0,0,0,.18); max-height:90vh; overflow-y:auto; }
.modal-title-lg { font-size:18px; font-weight:800; color:#111827; margin-bottom:4px; display:flex; align-items:center; gap:8px; }
.modal-sub-lg { font-size:13px; color:#6b7280; margin-bottom:20px; }
</style>
@endsection

@section('content')
<div class="page active" id="page-submissions">

  <!-- Page Banner -->
  <div class="pg-banner">
    <div class="pg-banner-left">
      <div class="pg-banner-title">Submissions</div>
      <div class="pg-banner-sub">CY {{ date('Y') }} CMI submissions — review, accept, or request corrections</div>
    </div>
  </div>

  <!-- Card -->
  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
          <polyline points="9 15 11 17 15 13"/>
        </svg>
        All Submissions
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input type="text" class="filter-select" id="subSearchInput" placeholder="Search institution, encoder, table..." style="width:240px"/>
        <select class="filter-select" id="subYearSel" style="width:auto">
          @for($y = date('Y'); $y >= 2020; $y--)
            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>CY {{ $y }}</option>
          @endfor
        </select>
        <select class="filter-select" id="subStatusSel" style="width:auto">
          <option value="">All Statuses</option>
          <option value="done">Done / Complete</option>
          <option value="draft">Draft</option>
        </select>
      </div>
    </div>
    <div class="fc-card-body">
      <div class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr>
              <th>Institution</th>
              <th>Encoder</th>
              <th>Table</th>
              <th>Table Status</th>
              <th>Updated At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="subsTbody">
            <tr><td colspan="6" style="text-align:center;padding:32px;color:#9ca3af">
              <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 8px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Loading submissions...
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ══ VIEW DATA MODAL ══ -->
<div class="modal-overlay" id="modalViewData">
  <div class="modal-box-lg">
    <div class="modal-title-lg">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#10b981" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span id="vdInst">Submission Data</span>
    </div>
    <div class="modal-sub-lg" id="vdEncoder">Submission Details</div>
    
    <div id="vdTableWrap" style="margin-bottom:20px; overflow-x:auto;"></div>

    <div style="display:flex; justify-content:flex-end;">
      <button class="btn-sm-fc btn-sm-view" onclick="closeModal('modalViewData')" style="padding:8px 20px; font-size:13px;">Close</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let cachedSubRows = [];

const statusBadge = s => {
  const map = { done:'badge-green', draft:'badge-orange', accepted:'badge-green', returned:'badge-purple' };
  const label = s === 'done' ? 'Done (Submitted)' : (s === 'draft' ? 'Draft' : s);
  return `<span class="badge ${map[s] || 'badge-gray'}">${label}</span>`;
};

function renderSubsTable() {
  const query = (document.getElementById('subSearchInput')?.value || '').toLowerCase().trim();
  const tbody = document.getElementById('subsTbody');
  
  const filtered = cachedSubRows.filter(r => {
    if (!query) return true;
    return (r.institution || '').toLowerCase().includes(query) ||
           (r.encoder || '').toLowerCase().includes(query) ||
           (`table ${r.table_no}` || '').toLowerCase().includes(query);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af">
      No submissions found matching "${query}".
    </td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((r, idx) => `
    <tr>
      <td><strong>${r.institution}</strong></td>
      <td>${r.encoder}</td>
      <td><span class="badge badge-blue">Table ${r.table_no}</span></td>
      <td>${statusBadge(r.table_status || 'done')}</td>
      <td style="color:#6b7280;font-size:12.5px">${r.updated_at ? r.updated_at.substring(0,16).replace('T',' ') : '—'}</td>
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <button class="btn-sm-fc btn-sm-view" onclick="viewDataModal(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Data
        </button>
        <button class="btn-sm-fc btn-sm-accept" onclick="acceptSub(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Accept
        </button>
        <button class="btn-sm-fc btn-sm-return" onclick="returnSub(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.73"/></svg>
          Return
        </button>
      </td>
    </tr>
  `).join('');
}

async function loadSubs() {
  const year   = document.getElementById('subYearSel').value;
  const status = document.getElementById('subStatusSel').value;
  const tbody  = document.getElementById('subsTbody');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:#9ca3af">Loading...</td></tr>';
  try {
    let url = `/api/pta/submissions?year=${year}`;
    if (status) url += `&status=${status}`;
    const res  = await fetch(url);
    const json = await res.json();
    cachedSubRows = json.rows || [];
    renderSubsTable();
  } catch(e) { console.error('PTA Submissions load error:', e); }
}

window.viewDataModal = function(idx) {
  const r = cachedSubRows[idx];
  if (!r) return;
  document.getElementById('vdInst').textContent    = r.institution;
  document.getElementById('vdEncoder').textContent = `Encoder: ${r.encoder} • Table ${r.table_no}`;

  const wrap = document.getElementById('vdTableWrap');
  if (r.rows && r.rows.length > 0) {
    const keys = Object.keys(r.rows[0]);
    let html = '<table class="fc-table" style="font-size:13px"><thead><tr>' + keys.map(k=>`<th>${k}</th>`).join('') + '</tr></thead><tbody>';
    html += r.rows.map(row => '<tr>' + keys.map(k=>`<td>${row[k]||'—'}</td>`).join('') + '</tr>').join('');
    html += '</tbody></table>';
    wrap.innerHTML = html;
  } else {
    wrap.innerHTML = '<div style="padding:32px;text-align:center;color:#9ca3af;font-size:13.5px;">No row data entered for this table yet.</div>';
  }
  openModal('modalViewData');
};

window.acceptSub = function(idx) {
  const r = cachedSubRows[idx];
  const year = document.getElementById('subYearSel').value;
  showConfirmModal({
    title: 'Accept Submission?',
    message: `Are you sure you want to accept Table ${r.table_no} submission from ${r.institution}?`,
    confirmText: 'Accept Submission',
    type: 'green',
    onConfirm: async function() {
      const res  = await fetch('/api/pta/submissions/accept', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ cmi_user_id: r.cmi_user_id, table_no: r.table_no, year: year })
      });
      const json = await res.json();
      showToast(json.message || 'Submission accepted successfully!');
      loadSubs();
    }
  });
};

window.returnSub = function(idx) {
  const r = cachedSubRows[idx];
  const year = document.getElementById('subYearSel').value;
  showPromptModal({
    title: 'Request Correction',
    message: `State reason for returning Table ${r.table_no} to ${r.institution}:`,
    placeholder: 'e.g. Please update budget column and re-upload proof document...',
    confirmText: 'Send Request',
    onConfirm: async function(reason) {
      const res  = await fetch('/api/pta/submissions/request-correction', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ cmi_user_id: r.cmi_user_id, table_no: r.table_no, year: year, reason: reason })
      });
      const json = await res.json();
      showToast(json.message || 'Correction request sent!');
      loadSubs();
    }
  });
};

document.getElementById('subYearSel').addEventListener('change', loadSubs);
document.getElementById('subStatusSel').addEventListener('change', loadSubs);
document.addEventListener('DOMContentLoaded', loadSubs);
</script>
@endsection
