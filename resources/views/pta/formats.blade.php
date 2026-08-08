@extends('layouts.pta')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }

/* ── Year Tabs ── */
.year-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.year-tab { display:inline-flex; align-items:center; gap:7px; padding:8px 18px; border-radius:40px; font-size:13px; font-weight:600; cursor:pointer; border:1.5px solid transparent; transition:all .2s; text-decoration:none; }
.year-tab.active-year, .year-tab.selected  { background:#ecfdf5; color:#059669; border-color:#a7f3d0; }
.year-tab.archived-year { background:#f3f4f6; color:#6b7280; border-color:#e5e7eb; }
.year-tab.draft-year    { background:#fff7ed; color:#d97706; border-color:#fed7aa; }

/* ── Alert Banners ── */
.alert { display:flex; align-items:center; gap:10px; padding:12px 18px; border-radius:12px; font-size:13.5px; font-weight:500; margin-bottom:20px; border:1px solid transparent; }
.alert-success { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
.alert-warning { background:#fff7ed; color:#92400e; border-color:#fed7aa; }
.alert-info    { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }

/* ── Card ── */
.fc-card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(16,185,129,.05); overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:18px 22px 14px; border-bottom:1px solid #f3f4f6; flex-wrap:wrap; gap:10px; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:0 22px 22px; }
.card-actions  { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

/* ── Buttons ── */
.btn-primary-fc { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(16,185,129,.3); transition:all .2s; }
.btn-primary-fc:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(16,185,129,.4); }
.btn-outline-fc { display:inline-flex; align-items:center; gap:6px; background:#fff; color:#374151; border:1.5px solid #e5e7eb; border-radius:10px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-outline-fc:hover { border-color:#10b981; color:#059669; }
.btn-sm-edit   { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; transition:all .15s; }
.btn-sm-edit:hover { background:#dbeafe; }
.btn-sm-del    { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; transition:all .15s; }
.btn-sm-del:hover { background:#fee2e2; }

/* ── Table ── */
.fc-table-wrap { overflow-x:auto; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:last-child { border-bottom:none; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#eff6ff; color:#2563eb; }
.badge-orange { background:#fff7ed; color:#d97706; }

/* ── Modal ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); z-index:9000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:18px; padding:28px 32px; width:100%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,.18); position:relative; }
.modal-title { font-size:18px; font-weight:800; color:#111827; margin-bottom:6px; display:flex; align-items:center; gap:9px; }
.modal-title svg { color:#10b981; }
.modal-desc { font-size:13px; color:#6b7280; margin-bottom:20px; }
.modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:16px; }
.form-label { font-size:12.5px; font-weight:600; color:#374151; }
.form-input, .form-select { border:1.5px solid #e5e7eb; border-radius:10px; padding:9px 14px; font-size:13.5px; color:#111827; background:#fafafa; outline:none; font-family:inherit; transition:all .15s; width:100%; box-sizing:border-box; }
.form-input:focus, .form-select:focus { border-color:#10b981; background:#fff; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.col-row { display:flex; gap:6px; align-items:center; margin-bottom:6px; }
.btn-cancel { background:#f3f4f6; color:#374151; border:none; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-cancel:hover { background:#e5e7eb; }
</style>
@endsection

@section('content')
<div class="page active" id="page-formats">

  <!-- Page Banner -->
  <div class="pg-banner">
    <div>
      <div class="pg-banner-title">Manage Formats</div>
      <div class="pg-banner-sub">Annual report table templates — control what CMIs fill up each year</div>
    </div>
    <button class="btn-primary-fc" onclick="openCloneModal()">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
      </svg>
      Prepare New Year
    </button>
  </div>

  <!-- Year Tabs -->
  <div class="year-tabs" id="yearTabs">
    <div style="color:#9ca3af;font-size:13px">Loading years...</div>
  </div>

  <!-- Status Alert -->
  <div id="statusAlert" style="display:none"></div>

  <!-- Format Table Card -->
  <div class="fc-card">
    <div class="fc-card-head">
      <div class="fc-card-title">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        </svg>
        <span id="cardTitle">Format Tables</span>
      </div>
      <div class="card-actions" id="cardActions">
        <!-- dynamic buttons inserted here -->
      </div>
    </div>
    <div class="fc-card-body">
      <div class="fc-table-wrap">
        <table class="fc-table">
          <thead>
            <tr>
              <th>#</th><th>Table No</th><th>Title</th><th>Section</th><th>Required</th>
              <th>Submissions</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="fmtTbody">
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">Loading format templates...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ═══ CLONE / PREPARE NEW YEAR MODAL ═══ -->
<div class="modal-overlay" id="modalClone">
  <div class="modal-box">
    <div class="modal-title">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
      Prepare New Year Format
    </div>
    <div class="modal-desc">Clone all tables from an existing year as a draft. You can edit before activating.</div>
    <div class="form-group">
      <label class="form-label">Clone from year</label>
      <select class="form-select" id="cloneFromYear"></select>
    </div>
    <div class="form-group">
      <label class="form-label">New year to create</label>
      <input class="form-input" type="number" id="cloneToYear" min="2020" max="2050" value="{{ date('Y') + 1 }}"/>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modalClone')">Cancel</button>
      <button class="btn-primary-fc" onclick="submitClone()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        Clone &amp; Prepare
      </button>
    </div>
  </div>
</div>

<!-- ═══ ADD TABLE MODAL ═══ -->
<div class="modal-overlay" id="modalAddTable">
  <div class="modal-box">
    <div class="modal-title">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Table
    </div>
    <input type="hidden" id="addTableYear"/>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Table No. <span style="color:#ef4444">*</span></label>
        <input class="form-input" id="addTableNo" placeholder="e.g. T1, T2a"/>
      </div>
      <div class="form-group">
        <label class="form-label">Section <span style="color:#ef4444">*</span></label>
        <select class="form-select" id="addTableSection">
          <option>R&D Mgt. &amp; Coord.</option>
          <option>Strategic R&D</option>
          <option>Results Utilization</option>
          <option>Capability &amp; Gov.</option>
          <option>Policy Analysis</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Title <span style="color:#ef4444">*</span></label>
      <input class="form-input" id="addTableTitle" placeholder="e.g. R&D Projects Funded / Implemented"/>
    </div>
    <div class="form-group">
      <label class="form-label" style="margin-bottom:8px">Table Columns <span style="font-size:11px;color:#9ca3af;font-weight:400">(fields CMI fills in per row)</span></label>
      <div id="addColList" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px"></div>
      <button type="button" class="btn-outline-fc" style="width:100%;justify-content:center;border-style:dashed" onclick="addAddCol()">+ Add Column</button>
    </div>
    <div class="form-group" style="flex-direction:row;align-items:center;gap:10px">
      <input type="checkbox" id="addTableRequired" style="width:16px;height:16px;accent-color:#10b981" checked/>
      <label for="addTableRequired" class="form-label" style="margin:0;cursor:pointer">Required field</label>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modalAddTable')">Cancel</button>
      <button class="btn-primary-fc" onclick="submitAddTable()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Table
      </button>
    </div>
  </div>
</div>

<!-- ═══ EDIT TABLE MODAL ═══ -->
<div class="modal-overlay" id="modalEditTable">
  <div class="modal-box">
    <div class="modal-title">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Table
    </div>
    <input type="hidden" id="editTableId"/>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Table No. <span style="color:#ef4444">*</span></label>
        <input class="form-input" id="editTableNo" placeholder="e.g. T1, T2a"/>
      </div>
      <div class="form-group">
        <label class="form-label">Section <span style="color:#ef4444">*</span></label>
        <select class="form-select" id="editTableSection">
          <option>R&D Mgt. &amp; Coord.</option>
          <option>Strategic R&D</option>
          <option>Results Utilization</option>
          <option>Capability &amp; Gov.</option>
          <option>Policy Analysis</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Title <span style="color:#ef4444">*</span></label>
      <input class="form-input" id="editTableTitle"/>
    </div>
    <div class="form-group">
      <label class="form-label" style="margin-bottom:8px">Table Columns</label>
      <div id="editColList" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px"></div>
      <button type="button" class="btn-outline-fc" style="width:100%;justify-content:center;border-style:dashed" onclick="addEditCol()">+ Add Column</button>
    </div>
    <div class="form-group" style="flex-direction:row;align-items:center;gap:10px">
      <input type="checkbox" id="editTableRequired" style="width:16px;height:16px;accent-color:#10b981"/>
      <label for="editTableRequired" class="form-label" style="margin:0;cursor:pointer">Required field</label>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modalEditTable')">Cancel</button>
      <button class="btn-primary-fc" onclick="submitEditTable()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Changes
      </button>
    </div>
  </div>
</div>

<!-- ═══ ACTIVATE MODAL ═══ -->
<div class="modal-overlay" id="modalActivate">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-title">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      Activate CY <span id="activateYearLabel"></span>
    </div>
    <div class="modal-desc">
      This will open CY <span id="activateYearLabel2"></span> for CMI submissions.
      The currently active year will be <strong>archived</strong> — all data is preserved.
      All CMI users will receive a notification to start filling up their report.
      <br><br><strong style="color:#ef4444">This action cannot be undone.</strong>
    </div>
    <input type="hidden" id="activateYearVal"/>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modalActivate')">Cancel</button>
      <button class="btn-primary-fc" onclick="submitActivate()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Confirm Activate
      </button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
let fmtCurrentYear = {{ date('Y') }};
let fmtYearStatus  = 'draft';
let allYears       = [];

const API = '/api/pta/formats/save';

// ── Open / Close Modals ───────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
// close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', function(e){ if(e.target===this) closeModal(this.id); }));

// ── Load Formats for a Year ───────────────────────────────────────
async function loadFormats(year) {
  fmtCurrentYear = year;
  const res  = await fetch(`/api/pta/formats?year=${year}`);
  const json = await res.json();
  const templates = json.templates || [];
  allYears = json.years || [year];

  // Update clone select options
  const sel = document.getElementById('cloneFromYear');
  sel.innerHTML = allYears.map(y => `<option value="${y}">${y}</option>`).join('');

  // Render year tabs
  renderYearTabs(allYears, year, templates);

  // Status
  fmtYearStatus = templates.length > 0 ? (templates[0].status || 'draft') : 'draft';

  // Alert
  const alertDiv = document.getElementById('statusAlert');
  if (fmtYearStatus === 'archived') {
    alertDiv.innerHTML = `<div class="alert alert-info"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2"/><path d="M5 5 L12 12 L5 19"/></svg> CY ${year} is <strong>archived</strong>. All data is permanently preserved and read-only.</div>`;
    alertDiv.style.display = 'block';
  } else if (fmtYearStatus === 'active') {
    alertDiv.innerHTML = `<div class="alert alert-success"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> CY ${year} is the <strong>active year</strong>. Tables with existing submissions are protected from deletion.</div>`;
    alertDiv.style.display = 'block';
  } else {
    alertDiv.innerHTML = `<div class="alert alert-warning"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> CY ${year} is a <strong>draft</strong>. Add, edit, or remove tables. Click "Activate" when ready for CMI submissions.</div>`;
    alertDiv.style.display = 'block';
  }

  // Card title
  document.getElementById('cardTitle').textContent = `CY ${year} — ${templates.length} Tables`;

  // Card action buttons
  const actions = document.getElementById('cardActions');
  let btns = '';
  if (fmtYearStatus !== 'archived') {
    btns += `<button class="btn-outline-fc" onclick="openAddModal(${year})">
      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>+ Add Table</button>`;
  }
  if (fmtYearStatus === 'draft') {
    btns += `<button class="btn-primary-fc" onclick="confirmActivate(${year})">
      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Activate CY ${year}</button>`;
  }
  actions.innerHTML = btns;

  // Table body
  const tbody = document.getElementById('fmtTbody');
  if (!templates.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:48px;color:#9ca3af">
      <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px">
        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
      </svg>No format templates defined for CY ${year}. Use "+ Prepare New Year" to clone from a previous year.
    </td></tr>`;
    return;
  }

  tbody.innerHTML = templates.map((t, idx) => {
    const pct   = t.total_cmi > 0 ? Math.round((t.submission_count / t.total_cmi) * 100) : 0;
    const pcBg  = pct >= 75 ? '#ecfdf5' : pct >= 40 ? '#fff7ed' : '#fef2f2';
    const pcTxt = pct >= 75 ? '#059669'  : pct >= 40 ? '#d97706'  : '#dc2626';
    const canDelete = fmtYearStatus === 'draft' || t.submission_count == 0;
    const isArchived = fmtYearStatus === 'archived';
    const colsJson = JSON.stringify(t.columns_json || []);
    return `
      <tr>
        <td style="color:#9ca3af;font-size:12px">${t.sort_order || idx+1}</td>
        <td><span class="badge badge-blue">${t.table_no}</span></td>
        <td><strong>${t.title}</strong></td>
        <td style="color:#6b7280;font-size:12.5px">${t.section}</td>
        <td><span class="badge ${t.is_required ? 'badge-green' : 'badge-gray'}">${t.is_required ? '✓ Required' : 'Optional'}</span></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;min-width:110px">
            <span style="font-size:12.5px;font-weight:700;color:#374151">${t.submission_count}/${t.total_cmi}</span>
            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden">
              <div style="height:100%;width:${pct}%;background:linear-gradient(90deg,#10b981,#34d399);border-radius:20px"></div>
            </div>
            <span style="font-size:11px;font-weight:700;color:${pcTxt}">${pct}%</span>
          </div>
        </td>
        <td>
          ${!isArchived ? `
            <div style="display:flex;gap:6px">
              <button class="btn-sm-edit" onclick='openEditModal(${t.id}, ${JSON.stringify(t.table_no)}, ${JSON.stringify(t.title)}, ${JSON.stringify(t.section)}, ${t.is_required}, ${JSON.stringify(colsJson)})'>
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
              </button>
              ${canDelete ? `<button class="btn-sm-del" onclick="deleteTable(${t.id},'${t.table_no}')">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>Remove
              </button>` : ''}
            </div>
          ` : '<span style="color:#9ca3af;font-size:12px">Archived</span>'}
        </td>
      </tr>
    `;
  }).join('');
}

function renderYearTabs(years, selectedYear, templates) {
  const tabs = document.getElementById('yearTabs');
  if (!years.length) { tabs.innerHTML = '<div style="color:#9ca3af;font-size:13px">No years found. Prepare a new year to start.</div>'; return; }
  tabs.innerHTML = years.map(y => {
    let yCls = 'draft-year', icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`, label = 'Draft';
    if (y === selectedYear && templates.length) {
      const st = templates[0].status || 'draft';
      if (st === 'active')    { yCls = 'active-year';   icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`; label = 'Active'; }
      if (st === 'archived')  { yCls = 'archived-year'; icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 8v13H3V8"/><rect x="1" y="3" width="22" height="5" rx="1"/></svg>`; label = 'Archived'; }
    }
    return `<a href="#" class="year-tab ${yCls} ${y==selectedYear?'selected':''}" onclick="loadFormats(${y});return false">${icon} CY ${y} — ${label}</a>`;
  }).join('');
}

// ── Add Table ─────────────────────────────────────────────────────
function openAddModal(year) {
  document.getElementById('addTableYear').value  = year;
  document.getElementById('addTableNo').value    = '';
  document.getElementById('addTableTitle').value = '';
  document.getElementById('addTableRequired').checked = true;
  document.getElementById('addColList').innerHTML = '';
  openModal('modalAddTable');
}
function addAddCol() {
  const row = document.createElement('div');
  row.className = 'col-row';
  row.innerHTML = `<input class="form-input add-col-name" placeholder="Column name e.g. Agency" style="flex:1;padding:7px 12px;font-size:13px"/>
    <select class="form-select add-col-type" style="width:100px;padding:7px 10px;font-size:12px"><option value="text">Text</option><option value="number">Number</option><option value="date">Date</option></select>
    <button type="button" onclick="this.closest('.col-row').remove()" style="background:none;border:none;color:#ef4444;font-size:20px;cursor:pointer;padding:0 4px">×</button>`;
  document.getElementById('addColList').appendChild(row);
  row.querySelector('.add-col-name').focus();
}
async function submitAddTable() {
  const cols = [...document.querySelectorAll('#addColList .col-row')].map(r => ({ name:r.querySelector('.add-col-name').value.trim(), type:r.querySelector('.add-col-type').value })).filter(c=>c.name);
  const payload = { action:'add', year:parseInt(document.getElementById('addTableYear').value), table_no:document.getElementById('addTableNo').value.trim(), title:document.getElementById('addTableTitle').value.trim(), section:document.getElementById('addTableSection').value, is_required:document.getElementById('addTableRequired').checked?1:0, columns_json:JSON.stringify(cols) };
  if (!payload.table_no) { showToast('Table No. is required.'); return; }
  if (!payload.title)    { showToast('Title is required.'); return; }
  const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
  const json = await res.json();
  if (json.ok) { closeModal('modalAddTable'); showToast(json.message); loadFormats(fmtCurrentYear); }
  else showToast(json.message);
}

// ── Edit Table ─────────────────────────────────────────────────────
function openEditModal(id, tableNo, title, section, required, colsJson) {
  document.getElementById('editTableId').value       = id;
  document.getElementById('editTableNo').value       = tableNo;
  document.getElementById('editTableTitle').value    = title;
  document.getElementById('editTableSection').value  = section;
  document.getElementById('editTableRequired').checked = !!required;
  let cols = [];
  try { cols = JSON.parse(colsJson||'[]'); } catch(e){}
  const list = document.getElementById('editColList');
  list.innerHTML = '';
  (Array.isArray(cols)?cols:[]).forEach(col => {
    const row = document.createElement('div');
    row.className = 'col-row';
    const name = typeof col === 'string' ? col : (col.name||'');
    const type = typeof col === 'string' ? 'text' : (col.type||'text');
    row.innerHTML = `<input class="form-input edit-col-name" value="${name}" placeholder="Column name" style="flex:1;padding:7px 12px;font-size:13px"/>
      <select class="form-select edit-col-type" style="width:100px;padding:7px 10px;font-size:12px"><option value="text" ${type==='text'?'selected':''}>Text</option><option value="number" ${type==='number'?'selected':''}>Number</option><option value="date" ${type==='date'?'selected':''}>Date</option></select>
      <button type="button" onclick="this.closest('.col-row').remove()" style="background:none;border:none;color:#ef4444;font-size:20px;cursor:pointer;padding:0 4px">×</button>`;
    list.appendChild(row);
  });
  openModal('modalEditTable');
}
function addEditCol() {
  const row = document.createElement('div');
  row.className = 'col-row';
  row.innerHTML = `<input class="form-input edit-col-name" placeholder="Column name e.g. Agency" style="flex:1;padding:7px 12px;font-size:13px"/>
    <select class="form-select edit-col-type" style="width:100px;padding:7px 10px;font-size:12px"><option value="text">Text</option><option value="number">Number</option><option value="date">Date</option></select>
    <button type="button" onclick="this.closest('.col-row').remove()" style="background:none;border:none;color:#ef4444;font-size:20px;cursor:pointer;padding:0 4px">×</button>`;
  document.getElementById('editColList').appendChild(row);
  row.querySelector('.edit-col-name').focus();
}
async function submitEditTable() {
  const cols = [...document.querySelectorAll('#editColList .col-row')].map(r => ({ name:r.querySelector('.edit-col-name').value.trim(), type:r.querySelector('.edit-col-type').value })).filter(c=>c.name);
  const payload = { action:'edit', id:parseInt(document.getElementById('editTableId').value), table_no:document.getElementById('editTableNo').value.trim(), title:document.getElementById('editTableTitle').value.trim(), section:document.getElementById('editTableSection').value, is_required:document.getElementById('editTableRequired').checked?1:0, columns_json:JSON.stringify(cols) };
  if (!payload.table_no) { showToast('Table No. is required.'); return; }
  if (!payload.title)    { showToast('Title is required.'); return; }
  const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
  const json = await res.json();
  if (json.ok) { closeModal('modalEditTable'); showToast(json.message); loadFormats(fmtCurrentYear); }
  else showToast(json.message);
}

// ── Delete Table ──────────────────────────────────────────────────
async function deleteTable(id, tableNo) {
  if (!confirm(`Remove ${tableNo} from this year's format? This cannot be undone.`)) return;
  const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'delete',id}) });
  const json = await res.json();
  showToast(json.message);
  if (json.ok) loadFormats(fmtCurrentYear);
}

// ── Clone Year ────────────────────────────────────────────────────
function openCloneModal() { openModal('modalClone'); }
async function submitClone() {
  const fromYear = parseInt(document.getElementById('cloneFromYear').value);
  const toYear   = parseInt(document.getElementById('cloneToYear').value);
  if (!toYear || toYear < 2020) { showToast('Enter a valid target year.'); return; }
  const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'clone', from_year:fromYear, to_year:toYear}) });
  const json = await res.json();
  if (json.ok) { closeModal('modalClone'); showToast(json.message); loadFormats(toYear); }
  else showToast(json.message);
}

// ── Activate Year ─────────────────────────────────────────────────
function confirmActivate(year) {
  document.getElementById('activateYearLabel').textContent  = year;
  document.getElementById('activateYearLabel2').textContent = year;
  document.getElementById('activateYearVal').value          = year;
  openModal('modalActivate');
}
async function submitActivate() {
  const year = parseInt(document.getElementById('activateYearVal').value);
  const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'activate', year}) });
  const json = await res.json();
  if (json.ok) { closeModal('modalActivate'); showToast(json.message); loadFormats(year); }
  else showToast(json.message);
}

document.addEventListener('DOMContentLoaded', () => loadFormats({{ date('Y') }}));
</script>
@endsection
