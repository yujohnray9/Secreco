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
.badge-teal   { background:#ecfdf5; color:#0d9488; }

/* ── Action Buttons ── */
.btn-sm-fc { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.btn-sm-view    { background:#ecfdf5; color:#059669; }
.btn-sm-view:hover { background:#d1fae5; }
.btn-sm-accept  { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
.btn-sm-accept:hover { background:#d1fae5; }
.btn-sm-return  { background:#fff7ed; color:#d97706; border:1px solid #fed7aa; }
.btn-sm-return:hover { background:#ffedd5; }
.btn-sm-delete  { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.btn-sm-delete:hover { background:#fee2e2; }
.ts-label { display:block; font-size:10.5px; color:#9ca3af; margin-top:2px; }
.pg-btn { border:1px solid #d1d5db; background:#fff; color:#374151; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all .15s; }
.pg-btn:hover:not(:disabled) { border-color:#10b981; color:#059669; background:#ecfdf5; }
.pg-btn.active { background:#10b981; color:#fff; border-color:#10b981; }

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
          <option value="">Loading...</option>
        </select>
        <select class="filter-select" id="subStatusSel" style="width:auto">
          <option value="">All Statuses</option>
          <option value="done">Done / Complete</option>
          <option value="draft">Draft</option>
          <option value="not-started">Not Started</option>
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
              <th>Date Submitted</th>
              <th>Updated At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="subsTbody">
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">
              <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 8px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Loading submissions...
            </td></tr>
          </tbody>
        </table>
      </div>
      <div id="subsPaginationWrap"></div>
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

    <div style="display:flex; justify-content:flex-end; gap:10px;">
      <button class="btn-sm-fc" onclick="closeModal('modalViewData')" style="background:#f3f4f6;color:#374151;padding:8px 20px; font-size:13px;">Close</button>
      <button class="btn-sm-fc" onclick="savePtaModalEdit()" style="background:#10b981;color:#fff;border:none;padding:8px 22px; font-size:13px;font-weight:700;">Save / Submit Updates</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let cachedSubRows = [];
let currentEditSubIdx = -1;

function formatPHDate(dt) {
  if (!dt) return '—';
  let str = String(dt).trim();
  if (!/Z$|[+-]\d{2}:?\d{2}$/.test(str)) str = str.replace('T', ' ') + ' GMT+0800';
  const d = new Date(str);
  if (isNaN(d.getTime())) return dt;
  return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' }) + ' ' + d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Manila' });
}

const statusBadge = s => {
  const map = {
    done      : 'badge-green',
    accepted  : 'badge-teal',
    returned  : 'badge-purple',
    deleted   : 'badge-red',
    draft     : 'badge-orange',
  };
  const label = {
    done      : 'Submitted',
    accepted  : 'Accepted',
    returned  : 'Returned',
    deleted   : 'Deleted',
    draft     : 'Draft',
  };
  return `<span class="badge ${map[s] || 'badge-gray'}">${label[s] || s}</span>`;
};

let currentSubPage = 1;
let subPageSize = 10;

function renderSubsTable() {
  const query = (document.getElementById('subSearchInput')?.value || '').toLowerCase().trim();
  const year  = document.getElementById('subYearSel')?.value || new Date().getFullYear();
  const tbody = document.getElementById('subsTbody');
  
  const filtered = cachedSubRows.filter(r => {
    if (!query) return true;
    return (r.institution || '').toLowerCase().includes(query) ||
           (r.encoder || '').toLowerCase().includes(query) ||
           (`table ${r.table_no}` || '').toLowerCase().includes(query);
  });

  const totalItems = filtered.length;
  const totalPages = Math.ceil(totalItems / subPageSize) || 1;
  if (currentSubPage > totalPages) currentSubPage = totalPages;
  if (currentSubPage < 1) currentSubPage = 1;

  const startIdx = (currentSubPage - 1) * subPageSize;
  const endIdx   = Math.min(startIdx + subPageSize, totalItems);
  const pageRows = filtered.slice(startIdx, endIdx);

  if (pageRows.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">
      No submissions found matching "${query}".
    </td></tr>`;
    renderSubsPagination(0, 0, 0, 1);
    return;
  }

  tbody.innerHTML = pageRows.map((r) => {
    const subDate = formatPHDate(r.submitted_at);
    const ts = formatPHDate(r.updated_at);
    const actionTs = r.action_at ? `<span class="ts-label">${formatPHDate(r.action_at)}</span>` : '';
    const isActioned = r.table_status === 'accepted' || r.table_status === 'returned' || r.table_status === 'deleted';
    return `
    <tr>
      <td><strong>${r.institution}</strong></td>
      <td>${r.encoder}</td>
      <td><span class="badge badge-blue">Table ${r.table_no}</span></td>
      <td>${statusBadge(r.table_status || 'done')}${actionTs}</td>
      <td style="color:#059669;font-weight:600;font-size:12.5px">${subDate}</td>
      <td style="color:#6b7280;font-size:12.5px">${ts}</td>
      <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
        <button class="btn-sm-fc btn-sm-view" onclick="viewDataModal(${cachedSubRows.indexOf(r)})" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
          Edit Data
        </button>
        ${!isActioned ? `
        <button class="btn-sm-fc btn-sm-accept" onclick="acceptSub(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Accept
        </button>
        <button class="btn-sm-fc btn-sm-return" onclick="returnSub(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.73"/></svg>
          Return
        </button>
        <button class="btn-sm-fc btn-sm-delete" onclick="deleteSub(${cachedSubRows.indexOf(r)})">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
          Delete
        </button>` : statusBadge(r.table_status)}
      </td>
    </tr>`;
  }).join('');

  renderSubsPagination(startIdx + 1, endIdx, totalItems, totalPages);
}

function renderSubsPagination(start, end, total, totalPages) {
  let wrap = document.getElementById('subsPaginationWrap');
  if (!wrap) return;

  if (total === 0) {
    wrap.innerHTML = '';
    return;
  }

  let pageBtns = '';
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentSubPage - 1 && i <= currentSubPage + 1)) {
      pageBtns += `<button class="pg-btn ${i === currentSubPage ? 'active' : ''}" onclick="goToSubPage(${i})">${i}</button>`;
    } else if (i === currentSubPage - 2 || i === currentSubPage + 2) {
      pageBtns += `<span style="padding:0 4px;color:#9ca3af">…</span>`;
    }
  }

  wrap.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #f3f4f6;flex-wrap:wrap;gap:10px;font-size:12.5px;color:#6b7280">
      <div>Showing <strong>${start}</strong> to <strong>${end}</strong> of <strong>${total}</strong> submissions</div>
      <div style="display:flex;align-items:center;gap:6px">
        <button class="pg-btn" ${currentSubPage === 1 ? 'disabled style="opacity:0.5;cursor:not-allowed"' : ''} onclick="goToSubPage(${currentSubPage - 1})">‹ Prev</button>
        ${pageBtns}
        <button class="pg-btn" ${currentSubPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed"' : ''} onclick="goToSubPage(${currentSubPage + 1})">Next ›</button>
      </div>
    </div>`;
}

window.goToSubPage = function(page) {
  currentSubPage = page;
  renderSubsTable();
};

async function loadSubs() {
  const year   = document.getElementById('subYearSel').value;
  const status = document.getElementById('subStatusSel').value;
  const tbody  = document.getElementById('subsTbody');
  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">Loading...</td></tr>';
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
  currentEditSubIdx = idx;
  const r = cachedSubRows[idx];
  if (!r) return;
  document.getElementById('vdInst').textContent    = `${r.institution} — Table ${r.table_no}`;
  document.getElementById('vdEncoder').textContent = `Encoder: ${r.encoder} • Status: ${r.table_status}`;

  const wrap = document.getElementById('vdTableWrap');
  let html = '';

  let rows = (r.rows && r.rows.length > 0) ? JSON.parse(JSON.stringify(r.rows)) : [{}];
  let keys = rows.length > 0 && Object.keys(rows[0]).length > 0 ? Object.keys(rows[0]) : ['Date', 'Agency', 'New', 'Ongoing', 'Completed', 'Terminated'];

  html += `<div style="font-size:12.5px;color:#374151;margin-bottom:12px;background:#ecfdf5;padding:10px 14px;border-radius:8px;border:1px solid #a7f3d0;">
    <strong>PTA Admin Access:</strong> You can edit cell values or add missing rows below, then click <strong>Save / Submit Updates</strong> to update this submission.
  </div>`;

  html += `<table class="fc-table" id="ptaEditGrid" style="font-size:12.5px">
    <thead>
      <tr>
        <th style="width:36px">#</th>
        ${keys.map(k=>`<th>${k}</th>`).join('')}
        <th style="width:40px">Del</th>
      </tr>
    </thead>
    <tbody id="ptaEditTbody">`;

  rows.forEach((row, rIdx) => {
    html += `<tr>
      <td style="text-align:center;font-weight:600;color:#6b7280">${rIdx + 1}</td>
      ${keys.map(k=>`<td><input type="text" class="pta-cell-inp" data-key="${k}" value="${String(row[k] ?? '').replace(/"/g, '&quot;')}" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" /></td>`).join('')}
      <td style="text-align:center"><button type="button" onclick="this.closest('tr').remove()" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:3px 8px;cursor:pointer;font-weight:bold;">×</button></td>
    </tr>`;
  });

  html += `</tbody></table>
  <div style="margin-top:12px;display:flex;gap:10px;align-items:center;">
    <button type="button" class="btn-sm-fc" onclick="addPtaEditRow()" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;padding:6px 14px;">+ Add Row</button>
  </div>`;

  // Always render Documentation section in the modal so PTA Admin can upload/manage documents
  const docsList = r.docs || [];
  html += `
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0">
      <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
          Attached Documentation (<span id="ptaDocCount">${docsList.length}</span>)
        </div>
        <label class="btn-sm-fc" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;padding:6px 14px;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Attach / Upload Photo or File
          <input type="file" id="ptaModalDocFileInput" multiple accept="image/*,.pdf,.doc,.docx" style="display:none" onchange="uploadPtaModalDoc(this)" />
        </label>
      </div>

      <div id="ptaModalDocsGallery" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:12px">
        ${docsList.map(d => {
          const src = '/' + (d.file_path || '').replace(/^\//, '');
          return `
            <div id="pta-doc-card-${d.id}" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative">
              <button type="button" onclick="deletePtaModalDoc(${d.id})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
              <img src="${src}" style="width:130px;height:90px;object-fit:cover;cursor:pointer;display:block" onclick="window.open('${src}','_blank')" title="${d.caption||'View photo'}"/>
              <div style="padding:6px;font-size:11px;color:#6b7280;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${d.caption || 'Attached photo'}</div>
            </div>`;
        }).join('')}
        ${docsList.length === 0 ? '<div id="ptaNoDocsMsg" style="font-size:12.5px;color:#9ca3af;font-style:italic">No attachments uploaded yet. Use the button above to add document photos or files.</div>' : ''}
      </div>
    </div>`;

  wrap.innerHTML = html;
  openModal('modalViewData');
};

window.uploadPtaModalDoc = async function(input) {
  if (!input.files || input.files.length === 0) return;
  if (currentEditSubIdx < 0) return;
  const r = cachedSubRows[currentEditSubIdx];
  const year = document.getElementById('subYearSel')?.value || new Date().getFullYear();

  const formData = new FormData();
  for (let i = 0; i < input.files.length; i++) {
    formData.append('images[]', input.files[i]);
  }
  formData.append('table_no', r.table_no);
  formData.append('year', year);
  formData.append('cmi_user_id', r.cmi_user_id);

  try {
    showToast('Uploading attachment(s)...');
    const res = await fetch('/api/cmi/tables/upload-doc', {
      method: 'POST',
      body: formData
    });
    const json = await res.json();
    if (json.success && json.files) {
      showToast('✅ Attachment uploaded successfully!');
      const gallery = document.getElementById('ptaModalDocsGallery');
      const noDocsMsg = document.getElementById('ptaNoDocsMsg');
      if (noDocsMsg) noDocsMsg.remove();

      r.docs = r.docs || [];
      json.files.forEach(f => {
        r.docs.push(f);
        const src = '/' + (f.file_path || '').replace(/^\//, '');
        const card = document.createElement('div');
        card.id = `pta-doc-card-${f.id}`;
        card.style.cssText = 'border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative';
        card.innerHTML = `
          <button type="button" onclick="deletePtaModalDoc(${f.id})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
          <img src="${src}" style="width:130px;height:90px;object-fit:cover;cursor:pointer;display:block" onclick="window.open('${src}','_blank')" title="${f.caption||'View photo'}"/>
          <div style="padding:6px;font-size:11px;color:#6b7280;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${f.caption || 'Attached photo'}</div>`;
        gallery.appendChild(card);
      });

      const countEl = document.getElementById('ptaDocCount');
      if (countEl) countEl.textContent = r.docs.length;
    } else {
      showToast('❌ Upload failed: ' + (json.error || 'Unknown error'));
    }
  } catch(e) {
    showToast('❌ Upload failed.');
  } finally {
    input.value = '';
  }
};

window.deletePtaModalDoc = async function(docId) {
  if (!confirm('Are you sure you want to delete this attachment?')) return;
  if (currentEditSubIdx < 0) return;
  const r = cachedSubRows[currentEditSubIdx];

  try {
    const res = await fetch('/api/cmi/tables/delete-doc', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: docId, doc_id: docId, cmi_user_id: r.cmi_user_id })
    });
    const json = await res.json();
    if (json.success) {
      showToast('✅ Attachment deleted.');
      const card = document.getElementById(`pta-doc-card-${docId}`);
      if (card) card.remove();

      r.docs = (r.docs || []).filter(d => d.id !== docId);
      const countEl = document.getElementById('ptaDocCount');
      if (countEl) countEl.textContent = r.docs.length;

      const gallery = document.getElementById('ptaModalDocsGallery');
      if (gallery && gallery.children.length === 0) {
        gallery.innerHTML = '<div id="ptaNoDocsMsg" style="font-size:12.5px;color:#9ca3af;font-style:italic">No attachments uploaded yet. Use the button above to add document photos or files.</div>';
      }
    } else {
      showToast('❌ Delete failed: ' + (json.error || 'Unknown error'));
    }
  } catch(e) {
    showToast('❌ Delete failed.');
  }
};

window.addPtaEditRow = function() {
  const tbody = document.getElementById('ptaEditTbody');
  if (!tbody) return;
  const firstRow = tbody.querySelector('tr');
  let keys = ['field1', 'field2', 'field3', 'field4'];
  if (firstRow) {
    keys = [...firstRow.querySelectorAll('.pta-cell-inp')].map(inp => inp.dataset.key);
  }
  const rowCount = tbody.rows.length + 1;
  const tr = document.createElement('tr');
  tr.innerHTML = `<td style="text-align:center;font-weight:600;color:#6b7280">${rowCount}</td>
    ${keys.map(k=>`<td><input type="text" class="pta-cell-inp" data-key="${k}" value="" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" /></td>`).join('')}
    <td style="text-align:center"><button type="button" onclick="this.closest('tr').remove()" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:3px 8px;cursor:pointer;font-weight:bold;">×</button></td>`;
  tbody.appendChild(tr);
};

window.savePtaModalEdit = async function() {
  if (currentEditSubIdx < 0) return;
  const r = cachedSubRows[currentEditSubIdx];
  const year = document.getElementById('subYearSel').value;
  const tbody = document.getElementById('ptaEditTbody');
  
  const updatedRows = [];
  if (tbody) {
    tbody.querySelectorAll('tr').forEach(tr => {
      const inputs = tr.querySelectorAll('.pta-cell-inp');
      const rowObj = {};
      inputs.forEach(inp => {
        rowObj[inp.dataset.key] = inp.value;
      });
      updatedRows.push(rowObj);
    });
  }

  try {
    const res = await fetch('/api/pta/submissions/update-table', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        cmi_user_id: r.cmi_user_id,
        table_no: r.table_no,
        year: year,
        rows: updatedRows,
        meta: r.meta || {},
        status: 'done'
      })
    });
    const json = await res.json();
    if (json.ok) {
      showToast(json.message || 'Table updated and saved successfully!');
      closeModal('modalViewData');
      loadSubs();
    } else {
      showToast('Error saving update: ' + (json.error || 'Unknown error'));
    }
  } catch(e) {
    showToast('Failed to save update.');
  }
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

window.deleteSub = function(idx) {
  const r = cachedSubRows[idx];
  const year = document.getElementById('subYearSel').value;
  showConfirmModal({
    title: 'Delete Submission?',
    message: `Are you sure you want to delete Table ${r.table_no} submission from ${r.institution}? This action marks the record as deleted.`,
    confirmText: 'Delete Submission',
    type: 'red',
    onConfirm: async function() {
      const res  = await fetch('/api/pta/submissions/delete', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ cmi_user_id: r.cmi_user_id, table_no: r.table_no, year: year })
      });
      const json = await res.json();
      showToast(json.message || 'Submission deleted.');
      loadSubs();
    }
  });
};

document.getElementById('subYearSel').addEventListener('change', loadSubs);
document.getElementById('subStatusSel').addEventListener('change', loadSubs);
const searchInp = document.getElementById('subSearchInput');
if (searchInp) searchInp.addEventListener('input', renderSubsTable);
document.addEventListener('DOMContentLoaded', function() {
  fetch('/api/formats')
    .then(r => r.json())
    .then(data => {
      if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
        const yearSel = document.getElementById('subYearSel');
        const activeYr = data.active_year || new Date().getFullYear();
        if (yearSel) {
          yearSel.innerHTML = data.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
        }
      }
    }).catch(() => {}).finally(() => loadSubs());
});
</script>
@endsection
