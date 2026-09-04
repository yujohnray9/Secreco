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
.section-header-row td {
  padding: 10px 16px !important;
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #059669;
  background: #f3f6f4 !important;
  border-left: 4px solid #10b981;
}
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
        <input type="text" class="filter-select" id="subSearchInput" placeholder="Search institution, encoder, table..." style="width:230px"/>
        <select class="filter-select" id="subSectionFilter" style="width:auto">
          <option value="">All Sections</option>
          <option value="R&D Mgt. & Coord.">R&D Mgt. & Coord.</option>
          <option value="Strategic R&D">Strategic R&D</option>
          <option value="Results Utilization">Results Utilization</option>
          <option value="Capability & Governance">Capability & Governance</option>
          <option value="Policy Analysis">Policy Analysis</option>
        </select>
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
  const norm = (s || '').toLowerCase();
  const map = {
    submitted   : 'badge-green',
    done        : 'badge-green',
    accepted    : 'badge-teal',
    returned    : 'badge-purple',
    deleted     : 'badge-red',
    draft       : 'badge-orange',
    'not-started': 'badge-gray',
  };
  const label = {
    submitted   : 'Submitted',
    done        : 'Submitted',
    accepted    : 'Accepted',
    returned    : 'Returned',
    deleted     : 'Deleted',
    draft       : 'Draft',
    'not-started': 'Not Started',
  };
  return `<span class="badge ${map[norm] || 'badge-gray'}">${label[norm] || (norm.charAt(0).toUpperCase() + norm.slice(1))}</span>`;
};

const SECTION_ORDER = [
  'R&D Mgt. & Coord.',
  'Strategic R&D',
  'Results Utilization',
  'Capability & Governance',
  'Policy Analysis'
];

function normalizeSectionName(sec) {
  if (!sec) return 'R&D Mgt. & Coord.';
  const s = String(sec).trim();
  if (/^capability/i.test(s)) return 'Capability & Governance';
  if (/^r\s*&\s*d/i.test(s)) return 'R&D Mgt. & Coord.';
  if (/^strategic/i.test(s)) return 'Strategic R&D';
  if (/^results/i.test(s)) return 'Results Utilization';
  if (/^policy/i.test(s)) return 'Policy Analysis';
  return s;
}

let currentSubPage = 1;
let subPageSize = 10;

function renderSubsTable() {
  const query = (document.getElementById('subSearchInput')?.value || '').toLowerCase().trim();
  const sectionFilter = document.getElementById('subSectionFilter')?.value || '';
  const tbody = document.getElementById('subsTbody');
  
  const filtered = cachedSubRows.filter(r => {
    const sec = normalizeSectionName(r.section);
    if (sectionFilter && sec !== normalizeSectionName(sectionFilter)) {
      return false;
    }
    if (!query) return true;
    return (r.institution || '').toLowerCase().includes(query) ||
           (r.encoder || '').toLowerCase().includes(query) ||
           (`table ${r.table_no}` || '').toLowerCase().includes(query) ||
           (r.title || '').toLowerCase().includes(query) ||
           (r.section || '').toLowerCase().includes(query);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">
      No submissions found matching current filters.
    </td></tr>`;
    const wrap = document.getElementById('subsPaginationWrap');
    if (wrap) wrap.innerHTML = '';
    return;
  }

  // Group filtered rows by Section (preserving standard section order)
  const groupedBySection = {};
  SECTION_ORDER.forEach(sec => { groupedBySection[sec] = []; });

  filtered.forEach(r => {
    const sec = normalizeSectionName(r.section);
    if (!groupedBySection[sec]) {
      groupedBySection[sec] = [];
    }
    groupedBySection[sec].push(r);
  });

  const htmlParts = [];
  let renderedCount = 0;

  const allSectionKeys = [
    ...SECTION_ORDER,
    ...Object.keys(groupedBySection).filter(k => !SECTION_ORDER.includes(k))
  ];

  allSectionKeys.forEach(secName => {
    const sectionRows = groupedBySection[secName] || [];
    if (sectionRows.length === 0) return;

    htmlParts.push(`
      <tr class="section-header-row" style="background:#f3f6f4 !important">
        <td colspan="7" style="font-weight:700 !important;font-size:12px !important;text-transform:uppercase !important;letter-spacing:.06em !important;color:#059669 !important;background:#f3f6f4 !important;border-left:4px solid #10b981 !important;padding:10px 16px !important">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#059669" stroke-width="2.5" style="display:inline-block;vertical-align:-1px;margin-right:6px"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          ${secName.toUpperCase()} <span style="font-size:11px;font-weight:500;color:#6b7280;text-transform:none;letter-spacing:normal;margin-left:6px">(${sectionRows.length} table${sectionRows.length > 1 ? 's' : ''})</span>
        </td>
      </tr>
    `);

    sectionRows.forEach(r => {
      renderedCount++;
      const subDate = formatPHDate(r.submitted_at);
      const ts = formatPHDate(r.updated_at);
      const actionTs = r.action_at ? `<span class="ts-label">${formatPHDate(r.action_at)}</span>` : '';
      const isActioned = r.table_status === 'accepted' || r.table_status === 'returned' || r.table_status === 'deleted';
      const origIdx = cachedSubRows.indexOf(r);

      htmlParts.push(`
        <tr>
          <td><strong>${r.institution}</strong></td>
          <td>${r.encoder}</td>
          <td><span class="badge badge-blue">Table ${r.table_no}</span></td>
          <td>${statusBadge(r.table_status || 'done')}${actionTs}</td>
          <td style="color:#059669;font-weight:600;font-size:12.5px">${subDate}</td>
          <td style="color:#6b7280;font-size:12.5px">${ts}</td>
          <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
            <button class="btn-sm-fc btn-sm-view" onclick="viewDataModal(${origIdx})" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
              Edit/View Data
            </button>
            ${!isActioned ? `
            <button class="btn-sm-fc btn-sm-accept" onclick="acceptSub(${origIdx})">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Accept
            </button>
            <button class="btn-sm-fc btn-sm-return" onclick="returnSub(${origIdx})">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.73"/></svg>
              Return
            </button>
            <button class="btn-sm-fc btn-sm-delete" onclick="deleteSub(${origIdx})">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
              Delete
            </button>` : statusBadge(r.table_status)}
          </td>
        </tr>
      `);
    });
  });

  tbody.innerHTML = htmlParts.join('');

  const wrap = document.getElementById('subsPaginationWrap');
  if (wrap) {
    wrap.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #f3f4f6;flex-wrap:wrap;gap:10px;font-size:12.5px;color:#6b7280">
        <div>Showing <strong>${renderedCount}</strong> submission${renderedCount !== 1 ? 's' : ''} across sections</div>
      </div>`;
  }
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

  const DEFAULT_TABLE_KEYS = {
    'T1':  ['date', 'agency', 'new_', 'ongoing', 'completed', 'terminated'],
    'T2A': ['title', 'agency', 'researcher', 'recommendations', 'winners'],
    'T2B': ['category', 'agency', 'count', 'remarks'],
    'T3':  ['project', 'status', 'duration', 'fund'],
    'T4':  ['donor', 'activity', 'amount', 'remarks'],
    'T5':  ['donor', 'activity', 'amount', 'remarks'],
    'T6':  ['agency', 'address', 'year', 'nature'],
    'T7A': ['type', 'date', 'purpose'],
    'T7B': ['type', 'date', 'purpose'],
    'T8A': ['title', 'researcher', 'agency', 'duration', 'funds', 'commodity'],
    'T8B': ['program', 'project', 'agency', 'duration', 'budget', 'source', 'role'],
    'T9':  ['title', 'source', 'agency', 'researcher', 'impact'],
    'T10': ['title', 'agency', 'priority', 'duration', 'budget', 'fund'],
    'T11': ['tech', 'project', 'agency'],
    'T12': ['tech', 'owner', 'precomm', 'licensor', 'startup', 'spinoff'],
    'T13': ['remarks'],
    'T14': ['title', 'venue', 'participants', 'expenditures', 'funds'],
    'T15': ['item', 'location', 'expense', 'funds'],
    'T16': ['award', 'recipient', 'sponsor', 'event', 'venue', 'date'],
    'T17': ['type', 'venue', 'host'],
    'T18': ['cmi', 'amount'],
    'T19': ['initiative', 'date'],
    'T20A': ['project', 'agency', 'author', 'description', 'findings'],
    'T20B': ['agency', 'description'],
  };
  window._DEFAULT_TABLE_KEYS = DEFAULT_TABLE_KEYS;

  const COLUMN_LABELS = {
    category: 'Category (GO, NGO, Private, LGU)',
    date: 'Date', agency: 'Agency', new_: 'New', ongoing: 'Ongoing', completed: 'Completed', terminated: 'Terminated',
    title: 'Title', researcher: 'Researcher', recommendations: 'Recommendations', winners: 'Winners',
    count: 'Count', remarks: 'Remarks', project: 'Project', status: 'Status', duration: 'Duration', fund: 'Funding',
    donor: 'Donor', activity: 'Activity', amount: 'Amount', nature: 'Nature', address: 'Address', year: 'Year',
    type: 'Type', purpose: 'Purpose', funds: 'Funds', commodity: 'Commodity', program: 'Program', budget: 'Budget',
    source: 'Source', role: 'Role', tech: 'Technology', owner: 'Owner', precomm: 'Pre-commercialization',
    licensor: 'Licensor', startup: 'Startup', spinoff: 'Spinoff', venue: 'Venue', participants: 'Participants',
    expenditures: 'Expenditures', item: 'Item', location: 'Location', expense: 'Expense', award: 'Award',
    recipient: 'Recipient', sponsor: 'Sponsor', event: 'Event', host: 'Host', cmi: 'CMI', initiative: 'Initiative',
    author: 'Author', description: 'Description', findings: 'Findings'
  };

  function getFieldInputType(k) {
    const norm = (k || '').toLowerCase();
    if (norm === 'category') {
      return 'category';
    }
    if (norm === 'date' || norm.endsWith('_date') || norm.startsWith('date_') || norm === 'published_date') {
      return 'date';
    }
    const numberFields = new Set([
      'new_', 'new', 'ongoing', 'completed', 'terminated',
      'count', 'amount', 'budget', 'expense', 'expenditures',
      'participants', 'precomm', 'licensor', 'startup', 'spinoff',
      'total', 'funds_amount', 'qty', 'quantity'
    ]);
    if (numberFields.has(norm)) {
      return 'number';
    }
    return 'text';
  }

  function formatForDateInput(val) {
    if (!val) return '';
    const str = String(val).trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
    const dmy = str.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
    if (dmy) {
      const d = dmy[1].padStart(2, '0');
      const m = dmy[2].padStart(2, '0');
      const y = dmy[3];
      return `${y}-${m}-${d}`;
    }
    const d = new Date(str);
    if (!isNaN(d.getTime())) {
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    }
    return str;
  }

  function renderCellInput(key, rawVal) {
    const type = getFieldInputType(key);
    let val = rawVal ?? '';
    if (type === 'category') {
      const cat = String(val || 'go').toLowerCase().trim();
      return `<select class="pta-cell-inp" data-key="category" style="width:100%;min-width:110px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;background:#fff;">
        <option value="go" ${cat==='go'?'selected':''}>GO (Government)</option>
        <option value="ngo" ${cat==='ngo'?'selected':''}>NGO (Non-Government)</option>
        <option value="private" ${cat==='private'?'selected':''}>Private Sector</option>
        <option value="lgu" ${cat==='lgu'?'selected':''}>LGU (Local Govt Unit)</option>
      </select>`;
    }
    if (type === 'date') {
      const dateVal = formatForDateInput(val);
      return `<input type="date" class="pta-cell-inp" data-key="${key}" value="${String(dateVal).replace(/"/g, '&quot;')}" style="width:100%;min-width:130px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`;
    }
    if (type === 'number') {
      const numVal = (val === '' || val === null || val === undefined) ? '' : val;
      return `<input type="number" min="0" step="any" class="pta-cell-inp" data-key="${key}" value="${String(numVal).replace(/"/g, '&quot;')}" placeholder="0" style="width:100%;min-width:65px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`;
    }
    return `<input type="text" class="pta-cell-inp" data-key="${key}" value="${String(val).replace(/"/g, '&quot;')}" placeholder="${COLUMN_LABELS[key] || key}" style="width:100%;min-width:90px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`;
  }
  window._renderCellInput = renderCellInput;

window.viewDataModal = function(idx) {
  currentEditSubIdx = idx;
  const r = cachedSubRows[idx];
  if (!r) return;
  document.getElementById('vdInst').textContent    = `${r.institution} — Table ${r.table_no}`;
  document.getElementById('vdEncoder').textContent = `Encoder: ${r.encoder} • Status: ${r.table_status}`;

  const wrap = document.getElementById('vdTableWrap');
  let html = '';

  let rows = (r.rows && r.rows.length > 0) ? JSON.parse(JSON.stringify(r.rows)) : [{}];
  const tNoUpper = (r.table_no || '').toUpperCase();
  const stdKeys = DEFAULT_TABLE_KEYS[tNoUpper] || ['field1', 'field2', 'field3', 'field4'];
  const keyAliases = {
    'new': 'new_',
    'new_projects': 'new_',
  };
  const customKeys = [];
  rows.forEach(row => {
    if (row && typeof row === 'object') {
      Object.keys(row).forEach(k => {
        const canonical = keyAliases[k] || k;
        if (k && !stdKeys.includes(canonical) && !stdKeys.includes(k) && !customKeys.includes(k) && !k.startsWith('_') && k !== 'field1') {
          customKeys.push(k);
        }
      });
    }
  });
  let keys = [...stdKeys, ...customKeys];

  html += `<div style="font-size:12.5px;color:#374151;margin-bottom:12px;background:#ecfdf5;padding:10px 14px;border-radius:8px;border:1px solid #a7f3d0;">
    <strong>PTA Admin Access:</strong> You can edit cell values or add missing rows below, then click <strong>Save / Submit Updates</strong> to update this submission.
  </div>`;

  html += `<table class="fc-table" id="ptaEditGrid" style="font-size:12.5px">
    <thead>
      <tr>
        <th style="width:36px">#</th>
        ${keys.map(k=>`<th>${COLUMN_LABELS[k] || k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g, ' ')}</th>`).join('')}
        <th style="width:40px">Del</th>
      </tr>
    </thead>
    <tbody id="ptaEditTbody">`;

  rows.forEach((row, rIdx) => {
    html += `<tr>
      <td style="text-align:center;font-weight:600;color:#6b7280">${rIdx + 1}</td>
      ${keys.map(k=>{
        let cellVal = row[k];
        if (cellVal === undefined || cellVal === null || cellVal === '') {
          if (k === 'new_' || k === 'new') {
            cellVal = row['new_'] ?? row['new'] ?? row['new_projects'] ?? '';
          }
        }
        return `<td>${renderCellInput(k, cellVal)}</td>`;
      }).join('')}
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
          const dId = d.id || d.doc_id;
          return `
            <div id="pta-doc-card-${dId}" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative">
              <button type="button" onclick="deletePtaModalDoc(${dId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
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
      showToast('Attachment uploaded successfully!');
      const gallery = document.getElementById('ptaModalDocsGallery');
      const noDocsMsg = document.getElementById('ptaNoDocsMsg');
      if (noDocsMsg) noDocsMsg.remove();

      r.docs = r.docs || [];
      json.files.forEach(f => {
        r.docs.push(f);
        const src = '/' + (f.file_path || '').replace(/^\//, '');
        const fId = f.id || f.doc_id;
        const card = document.createElement('div');
        card.id = `pta-doc-card-${fId}`;
        card.style.cssText = 'border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative';
        card.innerHTML = `
          <button type="button" onclick="deletePtaModalDoc(${fId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
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
      showToast('Attachment deleted.');
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
  let keys = [];
  if (firstRow) {
    keys = [...firstRow.querySelectorAll('.pta-cell-inp')].map(inp => inp.dataset.key);
  }
  if ((!keys || !keys.length) && currentEditSubIdx >= 0) {
    const r = cachedSubRows[currentEditSubIdx];
    const tNo = (r?.table_no || '').toUpperCase();
    const map = window._DEFAULT_TABLE_KEYS || {};
    keys = map[tNo] || ['field1', 'field2', 'field3', 'field4'];
  }
  const rowCount = tbody.rows.length + 1;
  const tr = document.createElement('tr');
  const renderInp = window._renderCellInput || ((k, v) => `<input type="text" class="pta-cell-inp" data-key="${k}" value="${v}" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`);
  tr.innerHTML = `<td style="text-align:center;font-weight:600;color:#6b7280">${rowCount}</td>
    ${keys.map(k=>`<td>${renderInp(k, '')}</td>`).join('')}
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
        const key = inp.dataset.key;
        let v = inp.value;
        if (getFieldInputType(key) === 'number' && v !== '') {
          v = isNaN(Number(v)) ? v : Number(v);
        }
        rowObj[key] = v;
        if (key === 'new_') {
          rowObj['new'] = v;
        }
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
        status: r.table_status || 'submitted'
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
const secFilterSel = document.getElementById('subSectionFilter');
if (secFilterSel) secFilterSel.addEventListener('change', renderSubsTable);
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
