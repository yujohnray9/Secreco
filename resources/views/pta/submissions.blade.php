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
        <div class="pg-banner-sub" id="subYearHeading">CY {{ date('Y') }} CMI submissions — review, accept, or request corrections</div>
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
            <option value="submitted">Submitted</option>
            <option value="accepted">Accepted</option>
            <option value="returned">Returned</option>
            <option value="not-started">Not Started</option>
          </select>
        </div>
      </div>
      <div class="fc-card-body">
        <div class="fc-table-wrap">
          <table class="fc-table">
            <thead id="subsThead">
              <tr>
                <th>Institution</th>
                <th>Encoder</th>
                <th>Table</th>
                <th>Submitted At</th>
                <th style="text-align:center">Action</th>
              </tr>
            </thead>
            <tbody id="subsTbody">
              <tr><td colspan="5" style="text-align:center;padding:32px;color:#9ca3af">
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

      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid #f0f0f0;">
        <div style="display:flex; gap:8px;" id="modalDecisionBtns">
          <button type="button" class="btn-sm-fc btn-sm-accept" id="modalAcceptBtn" onclick="acceptModalSub()" style="padding:8px 18px; font-size:13px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Accept
          </button>
          <button type="button" class="btn-sm-fc btn-sm-return" id="modalReturnBtn" onclick="returnModalSub()" style="padding:8px 18px; font-size:13px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.73"/></svg>
            Return
          </button>
        </div>
        <div style="display:flex; gap:10px;">
          <button class="btn-sm-fc" onclick="closeModal('modalViewData')" style="background:#f3f4f6;color:#374151;padding:8px 20px; font-size:13px;">Close</button>
          <button class="btn-sm-fc" id="modalSaveUpdatesBtn" onclick="savePtaModalEdit()" style="background:#10b981;color:#fff;border:none;padding:8px 22px; font-size:13px;font-weight:700;">Save / Submit Updates</button>
        </div>
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
    const statusFilter = (document.getElementById('subStatusSel')?.value || '').toLowerCase();
    const currentYear = document.getElementById('subYearSel')?.value || new Date().getFullYear();
    const thead = document.getElementById('subsThead');
    const tbody = document.getElementById('subsTbody');

    const isAcceptedFilter  = (statusFilter === 'accepted');
    const isReturnedFilter  = (statusFilter === 'returned');
    const isSubmittedFilter = (statusFilter === 'submitted');
    const isNotStartedFilter = (statusFilter === 'not-started');

    // Configure table headers based on active status filter
    if (thead) {
      if (isNotStartedFilter) {
        thead.innerHTML = `
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Progress</th>
            <th style="text-align:center">Actions</th>
          </tr>`;
      } else if (isReturnedFilter) {
        thead.innerHTML = `
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Table</th>
            <th>Date Returned</th>
            <th style="text-align:center">Action</th>
          </tr>`;
      } else if (isAcceptedFilter || isSubmittedFilter) {
        thead.innerHTML = `
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Table</th>
            <th>Date Submitted</th>
            <th style="text-align:center">Action</th>
          </tr>`;
      } else {
        // All Statuses
        thead.innerHTML = `
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Table</th>
            <th>Submitted At</th>
            <th style="text-align:center">Action</th>
          </tr>`;
      }
    }
    
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

    const colSpan = isNotStartedFilter ? 4 : 5;
    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center;padding:40px;color:#9ca3af">
        No submissions found matching current filters.
      </td></tr>`;
      const wrap = document.getElementById('subsPaginationWrap');
      if (wrap) wrap.innerHTML = '';
      return;
    }

    const htmlParts = [];
    let renderedCount = 0;

    if (isNotStartedFilter) {
      filtered.forEach(r => {
        renderedCount++;
        const origIdx = cachedSubRows.indexOf(r);
        const prog = r.progress || '0/24';
        const instEsc = (r.institution || '').replace(/'/g, "\\'");
        htmlParts.push(`
          <tr>
            <td><strong>${r.institution}</strong></td>
            <td>${r.encoder}</td>
            <td><span class="badge badge-gray" style="font-weight:700;font-size:12px">${prog}</span></td>
            <td style="text-align:center">
              <button type="button" class="btn-sm-fc" onclick="sendPtaReminder(${origIdx}, '${instEsc}')" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:700;padding:6px 14px">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:-1px"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Send Reminder
              </button>
            </td>
          </tr>
        `);
      });
    } else {
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

      const allSectionKeys = [
        ...SECTION_ORDER,
        ...Object.keys(groupedBySection).filter(k => !SECTION_ORDER.includes(k))
      ];

      allSectionKeys.forEach(secName => {
        const sectionRows = groupedBySection[secName] || [];
        if (sectionRows.length === 0) return;

        htmlParts.push(`
          <tr class="section-header-row" style="background:#f3f6f4 !important">
            <td colspan="${colSpan}" style="font-weight:700 !important;font-size:12px !important;text-transform:uppercase !important;letter-spacing:.06em !important;color:#059669 !important;background:#f3f6f4 !important;border-left:4px solid #10b981 !important;padding:10px 16px !important">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#059669" stroke-width="2.5" style="display:inline-block;vertical-align:-1px;margin-right:6px"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              ${secName.toUpperCase()} <span style="font-size:11px;font-weight:500;color:#6b7280;text-transform:none;letter-spacing:normal;margin-left:6px">(${sectionRows.length} table${sectionRows.length > 1 ? 's' : ''})</span>
            </td>
          </tr>
        `);

        sectionRows.forEach(r => {
          renderedCount++;
          const origIdx = cachedSubRows.indexOf(r);
          const rawDate = isReturnedFilter ? (r.date_returned || r.action_at || r.updated_at) : (r.submitted_at || r.updated_at);
          const formattedDate = formatPHDate(rawDate);

          let actionBtnHtml = '';
          if (isAcceptedFilter || isReturnedFilter) {
            actionBtnHtml = `
              <button class="btn-sm-fc btn-sm-view" onclick="viewDataModal(${origIdx}, true)" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                View
              </button>
            `;
          } else {
            actionBtnHtml = `
              <button class="btn-sm-fc btn-sm-view" onclick="viewDataModal(${origIdx})" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit/View Data
              </button>
            `;
          }

          let dateCellHtml = '';
          if (isReturnedFilter) {
            dateCellHtml = `<div style="color:#dc2626;font-weight:600;font-size:12.5px">${formattedDate}</div>`;
          } else if (r.resubmitted_at && r.resubmitted_at !== r.submitted_at) {
            dateCellHtml = `
              <div style="color:#059669;font-weight:600;font-size:12.5px">${formattedDate}</div>
              <div style="font-size:11px;color:#0369a1;margin-top:2px;font-weight:600">
                <span style="background:#e0f2fe;padding:2px 7px;border-radius:4px;display:inline-flex;align-items:center;gap:4px">
                  <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                  Revised: ${formatPHDate(r.resubmitted_at)}
                </span>
              </div>
            `;
          } else {
            dateCellHtml = `<div style="color:#059669;font-weight:600;font-size:12.5px">${formattedDate}</div>`;
          }

          htmlParts.push(`
            <tr>
              <td><strong>${r.institution}</strong></td>
              <td>${r.encoder}</td>
              <td><span class="badge badge-blue">Table ${r.table_no}</span></td>
              <td>${dateCellHtml}</td>
              <td style="text-align:center">
                ${actionBtnHtml}
              </td>
            </tr>
          `);
        });
      });
    }

    tbody.innerHTML = htmlParts.join('');

    const wrap = document.getElementById('subsPaginationWrap');
    if (wrap) {
      wrap.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #f3f4f6;flex-wrap:wrap;gap:10px;font-size:12.5px;color:#6b7280">
          <div>Showing <strong>${renderedCount}</strong> submission${renderedCount !== 1 ? 's' : ''}</div>
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
    const selectedYear = document.getElementById('subYearSel').value;
    document.getElementById('subYearHeading').textContent = `CY ${selectedYear} CMI submissions — review, accept, or request corrections`;
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
      'T6':  ['scope', 'agency', 'address', 'year', 'nature'],
      'T7A': ['type', 'date', 'purpose'],
      'T7B': ['type', 'date', 'purpose'],
      'T8A': ['title', 'researcher', 'agency', 'duration', 'funds', 'commodity'],
      'T8B': ['program', 'project', 'agency', 'duration', 'budget', 'source', 'role'],
      'T9':  ['title', 'source', 'agency', 'researcher', 'impact'],
      'T10': ['title', 'agency', 'priority', 'duration', 'budget', 'fund'],
      'T11': ['tech', 'project', 'stcbf', 'stc4id', 'safe', 'food_value_chain', 'others'],
      'T12': ['tech', 'owner', 'precomm', 'licensor', 'startup', 'spinoff'],
      'T13': ['remarks'],
      'T14': ['title', 'venue', 'participants', 'expenditures', 'funds'],
      'T15': ['item', 'location', 'expense', 'funds'],
      'T16': ['award', 'recipient', 'sponsor', 'event', 'venue', 'date'],
      'T17': ['type', 'venue', 'host'],
      'T18': ['cmi', 'amount'],
      'T19': ['initiative', 'date'],
      'T20A': ['project', 'agency', 'author', 'description', 'findings'],
      'T20B': ['policy', 'agency', 'description'],
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
      author: 'Author', description: 'Description', findings: 'Findings', scope: 'Scope / Level', policy: 'List of Policies'
    };

    function formatColumnHeader(k) {
      if (!k) return 'Column';
      if (COLUMN_LABELS[k]) return COLUMN_LABELS[k];
      const cleaned = String(k).replace(/^col[_\s]*/i, '').replace(/_/g, ' ').trim();
      if (!cleaned) return 'Column';
      return cleaned.replace(/\b\w/g, l => l.toUpperCase());
    }
    window.formatColumnHeader = formatColumnHeader;

    function getFieldInputType(k, tNo = '') {
      const norm = (k || '').toLowerCase();
      const tNoUpper = String(tNo || '').toUpperCase().trim();
      if (tNoUpper === 'T11' && ['stcbf', 'stc4id', 'safe', 'food_value_chain'].includes(norm)) return 'checkbox';
      if (norm === 'category') {
        return 'category';
      }
      if ((tNoUpper === 'T19' || tNoUpper === 'T16') && (norm === 'date' || norm.endsWith('_date') || norm.startsWith('date_'))) {
        return 'text';
      }
      if (norm === 'date' || norm.endsWith('_date') || norm.startsWith('date_') || norm === 'published_date') {
        return 'date';
      }
      if (norm === 'amount' || norm === 'expenditures' || norm === 'expenditure' || norm === 'expense' || norm === 'budget' || norm === 'cost' || norm === 'funds_amount') {
        return 'text';
      }
      const numberFields = new Set([
        'new_', 'new', 'ongoing', 'completed', 'terminated',
        'count',
        'total', 'qty', 'quantity'
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

    window._currentColOptionsMap = {};
    window._currentColDefs = [];
    window._currentEditKeys = [];

    function renderCellInput(key, rawVal, isViewOnly = false, colDef = null, tNo = '') {
      const type = colDef?.type || getFieldInputType(key, tNo);
      const opts = colDef?.options || window._currentColOptionsMap[key] || null;
      let val = rawVal ?? '';
      const disAttr = isViewOnly ? 'disabled style="background:#f9fafb;cursor:not-allowed;' : 'style="';

      if (type === 'dropdown' && Array.isArray(opts) && opts.length > 0) {
        const valStr = String(val).trim().toLowerCase();
        const optHtml = ['<option value="">-- Select --</option>'].concat(
          opts.map(o => {
            const sVal = String(o).trim();
            const sValLower = sVal.toLowerCase();
            const isSel = (valStr === sValLower ||
                           (valStr && sValLower.endsWith(valStr)) ||
                           (valStr && valStr.endsWith(sValLower)) ||
                           (valStr && sValLower.replace(/[^a-z0-9]/g, '') === valStr.replace(/[^a-z0-9]/g, ''))) ? 'selected' : '';
            return `<option value="${sVal.replace(/"/g, '&quot;')}" ${isSel}>${sVal}</option>`;
          })
        ).join('');
        return `<select class="pta-cell-inp" data-key="${key}" ${disAttr}width:100%;min-width:140px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;">${optHtml}</select>`;
      }

      if (type === 'category') {
        const cat = String(val || 'go').toLowerCase().trim();
        return `<select class="pta-cell-inp" data-key="category" ${disAttr}width:100%;min-width:110px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;">
          <option value="go" ${cat==='go'?'selected':''}>GO (Government)</option>
          <option value="ngo" ${cat==='ngo'?'selected':''}>NGO (Non-Government)</option>
          <option value="private" ${cat==='private'?'selected':''}>Private Sector</option>
          <option value="lgu" ${cat==='lgu'?'selected':''}>LGU (Local Govt Unit)</option>
        </select>`;
      }

      if (type === 'checkbox') {
        const checked = val === true || ['1', 'true', 'yes', '✓', 'x'].includes(String(val).toLowerCase());
        return `<input type="checkbox" class="pta-cell-inp" data-key="${key}" ${checked ? 'checked' : ''} ${isViewOnly ? 'disabled' : ''}>`;
      }
      if (type === 'date') {
        const dateVal = formatForDateInput(val);
        return `<input type="date" class="pta-cell-inp" data-key="${key}" value="${String(dateVal).replace(/"/g, '&quot;')}" ${disAttr}width:100%;min-width:130px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`;
      }

      if (type === 'number') {
        const numVal = (val === '' || val === null || val === undefined) ? '' : val;
        return `<input type="number" min="0" step="any" class="pta-cell-inp" data-key="${key}" value="${String(numVal).replace(/"/g, '&quot;')}" placeholder="0" ${disAttr}width:100%;min-width:65px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;" />`;
      }

      const ph = colDef?.label || COLUMN_LABELS[key] || formatColumnHeader(key);
      return `<textarea rows="2" class="pta-cell-inp" data-key="${key}" ${disAttr}width:100%;min-width:130px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;font-size:12.5px;outline:none;">${String(val).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>`;
    }
    window._renderCellInput = renderCellInput;

  function resolveTableColumns(r) {
    if (!r) return [];
    const tNoUpper = (r.table_no || '').toUpperCase();

    // 1. Check if format template columns were passed in r.columns
    let rawCols = r.columns;
    if (typeof rawCols === 'string') {
      try { rawCols = JSON.parse(rawCols); } catch (e) { rawCols = null; }
    }
    let templateCols = [];
    if (rawCols) {
      if (Array.isArray(rawCols)) {
        templateCols = rawCols;
      } else if (typeof rawCols === 'object') {
        if (Array.isArray(rawCols.titles) && rawCols.titles.length > 0 && Array.isArray(rawCols.titles[0]?.columns)) {
          templateCols = rawCols.titles[0].columns;
        } else if (Array.isArray(rawCols.columns)) {
          templateCols = rawCols.columns;
        }
      }
    }

    if (templateCols && templateCols.length > 0) {
      return templateCols.map(c => {
        if (typeof c === 'string') {
          return { key: c, label: c, type: (tNoUpper === 'T19' && (c === 'date' || c.endsWith('_date'))) ? 'text' : getFieldInputType(c, tNoUpper), options: [] };
        }
        const key = c.key || c.name || 'col';
        const label = c.label || c.name || c.title || formatColumnHeader(key);
        let type = (tNoUpper === 'T19' && (key === 'date' || key.endsWith('_date'))) ? 'text' : (c.type || getFieldInputType(key, tNoUpper));
        let options = c.options || [];
        if (typeof options === 'string') {
          options = options.split(',').map(s => s.trim()).filter(Boolean);
        }
        return { key, label, type, options };
      });
    }

    const builtIn = window.REPORT_SCHEMAS?.[tNoUpper];
    if (builtIn) return builtIn.columns.map(c => ({
      key: c.key, label: c.header,
      type: tNoUpper === 'T10' && c.key === 'status' ? 'dropdown' : (c.key === 'category' && tNoUpper !== 'T2B' ? 'dropdown' : getFieldInputType(c.key, tNoUpper)),
      options: tNoUpper === 'T10' && c.key === 'status' ? ['Approved', 'For Revision', 'Disapproved'] : (c.key === 'category' || c.key === 'scope' ? (builtIn.categories || []) : [])
    }));

    // 2. Standard table keys
    if (DEFAULT_TABLE_KEYS[tNoUpper] && DEFAULT_TABLE_KEYS[tNoUpper].length > 0) {
      return DEFAULT_TABLE_KEYS[tNoUpper].map(k => {
        let lbl = COLUMN_LABELS[k] || formatColumnHeader(k);
        if (tNoUpper === 'T16') {
          if (k === 'date') lbl = 'Date of Award';
          if (k === 'venue') lbl = 'Place of Award';
          if (k === 'award') lbl = 'Title of Award';
          if (k === 'recipient') lbl = 'Recipient / Agency';
          if (k === 'event') lbl = 'Event / Activity';
        }
        if (tNoUpper === 'T6' && k === 'scope') {
          return {
            key: 'scope',
            label: 'Scope / Level',
            type: 'dropdown',
            options: [
              'Developed / New - Local',
              'Developed / New - Regional',
              'Developed / New - National',
              'Developed / New - International',
              'Maintained / Sustained - Local',
              'Maintained / Sustained - Regional',
              'Maintained / Sustained - National',
              'Maintained / Sustained - International',
              'Local',
              'Regional',
              'National',
              'International'
            ]
          };
        }
        return {
          key: k,
          label: lbl,
          type: getFieldInputType(k, tNoUpper),
          options: []
        };
      });
    }

    // 3. Fallback: check actual keys in r.rows
    if (r.rows && Array.isArray(r.rows) && r.rows.length > 0) {
      const foundKeys = [];
      r.rows.forEach(row => {
        if (row && typeof row === 'object') {
          Object.keys(row).forEach(k => {
            if (k && !k.startsWith('_') && !k.startsWith('field') && !foundKeys.includes(k)) {
              foundKeys.push(k);
            }
          });
        }
      });
      if (foundKeys.length > 0) {
        let cols = foundKeys.map(k => ({
          key: k,
          label: COLUMN_LABELS[k] || formatColumnHeader(k),
          type: getFieldInputType(k, tNoUpper),
          options: []
        }));
        const isInternalCat = ['T1', 'T2A', 'T2', 'T7A', 'T7', 'T7B', 'T8A', 'T8', 'T10', 'T15', 'T16', 'T20B', 'T20b'].includes(tNoUpper);
        if (isInternalCat) {
          cols = cols.filter(c => c.key !== 'category');
        }
        return cols;
      }
    }

    // 4. Fallback business columns
    let fallback = [
      { key: 'description', label: 'Item Description', type: 'text', options: [] },
      { key: 'agency', label: 'Agency / Institution', type: 'text', options: [] },
      { key: 'amount', label: 'Amount / Budget', type: 'text', options: [] },
      { key: 'remarks', label: 'Remarks', type: 'text', options: [] }
    ];
    const isInternalCat = ['T1', 'T2A', 'T2', 'T7A', 'T7', 'T7B', 'T8A', 'T8', 'T10', 'T15', 'T16', 'T20B', 'T20b'].includes(tNoUpper);
    if (isInternalCat) {
      fallback = fallback.filter(c => c.key !== 'category');
    }
    return fallback;
  }

  window.viewDataModal = function(idx, isViewOnly = false) {
    currentEditSubIdx = idx;
    const r = cachedSubRows[idx];
    if (r.table_status === 'accepted' || r.table_status === 'returned') isViewOnly = true;

    document.getElementById('vdInst').textContent    = `${r.institution} — Table ${r.table_no}`;
    document.getElementById('vdEncoder').textContent = `Encoder: ${r.encoder} • Status: ${r.table_status}`;

    const wrap = document.getElementById('vdTableWrap');
    let html = '';

    let rows = (r.rows && r.rows.length > 0) ? JSON.parse(JSON.stringify(r.rows)) : [{}];
    let colDefs = resolveTableColumns(r);
    const tNoUpper = String(r.table_no || '').toUpperCase().trim();
    const isInternalCat = ['T1', 'T2A', 'T2', 'T7A', 'T7', 'T7B', 'T8A', 'T8', 'T10', 'T15', 'T16', 'T20B', 'T20b'].includes(tNoUpper);
    const schema = window.REPORT_SCHEMAS?.[tNoUpper];
    const groupKey = tNoUpper === 'T6' ? 'scope' : 'category';
    const grouped = Boolean(schema?.categories?.length);
    if (grouped) colDefs = colDefs.filter(c => c.key !== groupKey);
    const escapeCell = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const groupLabel = value => {
      const raw = String(value || schema?.categories?.[0] || '');
      const aliases = { go: 'GO', ngo: 'NGO', private: 'Private Sector', lgu: 'LGU', packaged: 'Proposals Packaged', implemented: 'Projects Approved and Implemented' };
      if (aliases[raw]) return aliases[raw];
      if (tNoUpper === 'T6' && /^(developed|maintained)_/.test(raw)) {
        const [kind, level] = raw.split('_');
        return (kind === 'developed' ? 'Developed / New - ' : 'Maintained / Sustained - ') + level[0].toUpperCase() + level.slice(1);
      }
      return raw;
    };
    window._ptaGrouping = { grouped, key: groupKey };

    const colOptionsMap = {};
    const colDefMap = {};
    colDefs.forEach(cd => {
      colDefMap[cd.key] = cd;
      if (cd.options && cd.options.length > 0) {
        colOptionsMap[cd.key] = cd.options;
      }
    });

    const keyAliases = {
      'new': 'new_',
      'new_projects': 'new_',
    };

    // If rows have extra keys not defined in colDefs, append them
    const existingKeys = colDefs.map(c => c.key);
    rows.forEach(row => {
      if (!DEFAULT_TABLE_KEYS[(r.table_no || '').toUpperCase()] && row && typeof row === 'object') {
        Object.keys(row).forEach(k => {
          if (isInternalCat && k.toLowerCase() === 'category') return;
          const canonical = keyAliases[k] || k;
          if (k && !existingKeys.includes(canonical) && !existingKeys.includes(k) && !k.startsWith('_') && !k.startsWith('field')) {
            existingKeys.push(k);
            const extraDef = {
              key: k,
              label: COLUMN_LABELS[k] || formatColumnHeader(k),
              type: getFieldInputType(k, r.table_no),
              options: []
            };
            colDefs.push(extraDef);
            colDefMap[k] = extraDef;
          }
        });
      }
    });

    const keys = colDefs.map(c => c.key);
    window._currentColOptionsMap = colOptionsMap;
    window._currentColDefs = colDefs;
    window._currentEditKeys = keys;

    if (isViewOnly) {
      const isReturned = r.table_status === 'returned';
      html += `<div style="font-size:12.5px;color:${isReturned ? '#991b1b' : '#065f46'};margin-bottom:12px;background:${isReturned ? '#fef2f2' : '#ecfdf5'};padding:10px 14px;border-radius:8px;border:1px solid ${isReturned ? '#fecaca' : '#a7f3d0'};">
        <strong>${isReturned ? 'Returned Table:' : 'Accepted Submission:'}</strong> This table has been ${isReturned ? 'returned for revision' : 'accepted'} and is in view-only mode.
      </div>`;
    } else {
      html += `<div style="font-size:12.5px;color:#374151;margin-bottom:12px;background:#ecfdf5;padding:10px 14px;border-radius:8px;border:1px solid #a7f3d0;">
        <strong>PTA Admin Access:</strong> You can edit cell values or add missing rows below, then click <strong>Save / Submit Updates</strong> to update this submission.
      </div>`;
    }

    if (r.resubmitted_at && r.resubmitted_at !== r.submitted_at) {
      html += `<div style="font-size:12px;color:#0369a1;margin-bottom:12px;background:#f0f9ff;padding:8px 14px;border-radius:8px;border:1px solid #bae6fd;display:flex;gap:16px;flex-wrap:wrap">
        <div><strong>Originally Submitted:</strong> ${formatPHDate(r.submitted_at)}</div>
        <div><strong>Revised / Resubmitted:</strong> ${formatPHDate(r.resubmitted_at)}</div>
      </div>`;
    }

    html += `<table class="fc-table" id="ptaEditGrid" style="font-size:12.5px">
      <thead>
        <tr>
          <th style="width:36px">#</th>
          ${colDefs.map(cd => `<th>${cd.label}</th>`).join('')}
          ${!isViewOnly ? '<th style="width:40px">Del</th>' : ''}
        </tr>
      </thead>
      <tbody id="ptaEditTbody">`;

    const PTA_ALIAS_MAP = {
      expenditures: ['expenditure', 'expense', 'amount', 'cost', 'budget'],
      expenditure: ['expenditures', 'expense', 'amount', 'cost', 'budget'],
      expense: ['expenditures', 'expenditure', 'amount', 'cost', 'budget'],
      budget: ['amount', 'cost', 'expenditures', 'expense'],
      funds: ['fundsource', 'source', 'fund', 'fund_source'],
      fundsource: ['funds', 'source', 'fund', 'fund_source'],
      title: ['activity', 'award', 'project', 'training', 'policy', 'name', 'item'],
      policy: ['title', 'policy_title', 'list_of_policies', 'ordinance', 'resolution'],
      award: ['title', 'activity', 'award_title'],
      event: ['activity', 'title', 'event_activity'],
      activity: ['title', 'event', 'project'],
      venue: ['date_venue', 'place', 'location', 'date'],
      date: ['date_awarded', 'published_date', 'award_date', 'venue'],
      author: ['researcher', 'lead'],
      description: ['desc', 'details', 'findings'],
      findings: ['finding', 'results'],
      new_: ['new', 'new_projects'],
      new: ['new_', 'new_projects'],
    };

    function getPtaCellVal(row, key) {
      if (!row || typeof row !== 'object') return '';
      let v = row[key] ?? row.modalities?.[key] ?? row[key?.toLowerCase()] ?? row[key?.toUpperCase()];
      if (v !== undefined && v !== null && String(v).trim() !== '') return String(v);

      const alts = PTA_ALIAS_MAP[key?.toLowerCase()] || [];
      for (const a of alts) {
        let av = row[a] ?? row[a?.toLowerCase()] ?? row[a?.toUpperCase()];
        if (av !== undefined && av !== null && String(av).trim() !== '') return String(av);
      }
      return '';
    }

    const groups = [];
    rows.forEach((row, rIdx) => {
      const value = grouped ? (row[groupKey] || schema.categories[0]) : '';
      let group = groups.find(g => groupLabel(g.value) === groupLabel(value));
      if (!group) { group = { value, rows: [] }; groups.push(group); }
      group.rows.push({ row, rIdx });
    });
    if (grouped) schema.categories.forEach(value => {
      if (!groups.some(g => groupLabel(g.value).toLowerCase() === value.toLowerCase())) groups.push({value, rows: []});
    });
    groups.forEach(group => {
      if (grouped) html += `<tr class="category-heading"><td colspan="${colDefs.length + (isViewOnly ? 1 : 2)}">${escapeCell(groupLabel(group.value))}</td></tr>`;
      group.rows.forEach(({row, rIdx}) => {
      html += `<tr data-row-index="${rIdx}" data-group="${escapeCell(group.value)}">
        <td style="text-align:center;font-weight:600;color:#6b7280">${rIdx + 1}</td>
        ${colDefs.map(cd => {
          const k = cd.key;
          let cellVal = getPtaCellVal(row, k);
          return `<td>${renderCellInput(k, cellVal, isViewOnly, cd, r.table_no)}</td>`;
        }).join('')}
        ${!isViewOnly ? `<td style="text-align:center"><button type="button" onclick="this.closest('tr').remove()" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:3px 8px;cursor:pointer;font-weight:bold;">×</button></td>` : ''}
      </tr>`;
    });

      if (grouped && !isViewOnly) html += `<tr class="category-actions"><td colspan="${colDefs.length + 2}"><button type="button" class="btn-sm-fc" data-group="${escapeCell(group.value)}" onclick="addPtaEditRow(this.dataset.group, this.closest('tr'))">+ Add Row</button></td></tr>`;
    });

    html += `</tbody></table>`;
    if (!isViewOnly && !grouped) {
      html += `<div style="margin-top:12px;display:flex;gap:10px;align-items:center;">
        <button type="button" class="btn-sm-fc" onclick="addPtaEditRow()" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;padding:6px 14px;">+ Add Row</button>
      </div>`;
    }

    // Always render Documentation section in the modal so PTA Admin can view/manage documents
    const docsList = r.docs || [];
    html += `
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0">
        <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
          <div style="display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            Attached Documentation (<span id="ptaDocCount">${docsList.length}</span>)
          </div>
          ${!isViewOnly ? `
          <label class="btn-sm-fc" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;padding:6px 14px;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Attach / Upload Photo or File
            <input type="file" id="ptaModalDocFileInput" multiple accept="image/*,.pdf,.doc,.docx" style="display:none" onchange="uploadPtaModalDoc(this)" />
          </label>` : ''}
        </div>

        <div id="ptaModalDocsGallery" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:12px">
          ${docsList.map(d => {
            const src = '/' + (d.file_path || '').replace(/^\//, '');
            const dId = d.id || d.doc_id;
            const isPdf = /\.pdf$/i.test(src);
            if (isPdf) {
              return `
                <div id="pta-doc-card-${dId}" style="border:1px solid #fecaca;border-radius:10px;overflow:hidden;background:#fff5f5;width:130px;height:120px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8px;box-sizing:border-box;text-align:center">
                  ${!isViewOnly ? `<button type="button" onclick="deletePtaModalDoc(${dId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>` : ''}
                  <a data-attachment="true" href="${src}" target="_blank" rel="noopener noreferrer" style="cursor:pointer;display:flex;flex-direction:column;align-items:center;width:100%;text-decoration:none">
                    <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                    <span style="font-size:11px;font-weight:700;color:#dc2626;margin-top:4px">PDF FILE</span>
                    <div style="font-size:10px;color:#6b7280;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:115px;margin-top:2px">${d.caption || 'Document'}</div>
                  </a>
                </div>`;
            }
            return `
              <div id="pta-doc-card-${dId}" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative">
                ${!isViewOnly ? `<button type="button" onclick="deletePtaModalDoc(${dId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>` : ''}
                <img src="${src}" style="width:130px;height:90px;object-fit:cover;cursor:pointer;display:block" data-attachment="true" onclick="window.open('${src}','_blank')" title="${d.caption||'View photo'}"/>
                <div style="padding:6px;font-size:11px;color:#6b7280;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${d.caption || 'Attached photo'}</div>
              </div>`;
          }).join('')}
          ${docsList.length === 0 ? '<div id="ptaNoDocsMsg" style="font-size:12.5px;color:#9ca3af;font-style:italic">No attachments uploaded yet.</div>' : ''}
        </div>
      </div>`;

    wrap.innerHTML = html;

    const isActioned = isViewOnly || r.table_status === 'accepted' || r.table_status === 'returned' || r.table_status === 'deleted';
    const decBtns = document.getElementById('modalDecisionBtns');
    if (decBtns) {
      decBtns.style.display = isActioned ? 'none' : 'flex';
    }

    const saveBtn = document.getElementById('modalSaveUpdatesBtn');
    if (saveBtn) {
      saveBtn.style.display = isViewOnly ? 'none' : 'inline-flex';
    }

    openModal('modalViewData');
  };

  window.uploadPtaModalDoc = async function(input) {
    if (!input.files || input.files.length === 0) return;
    if (currentEditSubIdx < 0) return;
    const r = cachedSubRows[currentEditSubIdx];
    const year = document.getElementById('subYearSel')?.value || new Date().getFullYear();

    if (!await AppDialog.confirm(`Upload ${input.files.length} attachment(s)?`, { title: 'Upload documentation', confirmText: 'Upload' })) { input.value = ''; return; }
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
          const fId = f.id || f.doc_id;
          if (document.getElementById(`pta-doc-card-${fId}`)) return;
          if (!r.docs.some(d => (d.id || d.doc_id) == fId)) {
            r.docs.push(f);
          }
          const src = '/' + (f.file_path || '').replace(/^\//, '');
          const isPdf = /\.pdf$/i.test(src);
          const card = document.createElement('div');
          card.id = `pta-doc-card-${fId}`;
          if (isPdf) {
            card.style.cssText = 'border:1px solid #fecaca;border-radius:10px;overflow:hidden;background:#fff5f5;width:130px;height:120px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8px;box-sizing:border-box;text-align:center';
            card.innerHTML = `
              <button type="button" onclick="deletePtaModalDoc(${fId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
              <div data-attachment="true" onclick="window.open('${src}','_blank')" style="cursor:pointer;display:flex;flex-direction:column;align-items:center;width:100%">
                <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                <span style="font-size:11px;font-weight:700;color:#dc2626;margin-top:4px">PDF FILE</span>
                <div style="font-size:10px;color:#6b7280;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:115px;margin-top:2px">${f.caption || 'Document'}</div>
              </div>`;
          } else {
            card.style.cssText = 'border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fafafa;width:130px;position:relative';
            card.innerHTML = `
              <button type="button" onclick="deletePtaModalDoc(${fId})" style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center" title="Delete Attachment">×</button>
              <img src="${src}" style="width:130px;height:90px;object-fit:cover;cursor:pointer;display:block" data-attachment="true" onclick="window.open('${src}','_blank')" title="${f.caption||'View photo'}"/>
              <div style="padding:6px;font-size:11px;color:#6b7280;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${f.caption || 'Attached photo'}</div>`;
          }
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
    if (!await AppDialog.confirm('Delete this attachment?', { title: 'Delete attachment', confirmText: 'Delete', destructive: true })) return;
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

  window.addPtaEditRow = function(category = '', beforeRow = null) {
    const tbody = document.getElementById('ptaEditTbody');
    if (!tbody) return;
    let colDefs = (window._currentColDefs && window._currentColDefs.length > 0) ? window._currentColDefs : [];
    if (!colDefs.length && currentEditSubIdx >= 0) {
      const r = cachedSubRows[currentEditSubIdx];
      colDefs = resolveTableColumns(r);
    }
    if (!colDefs.length) {
      const firstRow = tbody.querySelector('tr');
      if (firstRow) {
        const keys = [...firstRow.querySelectorAll('.pta-cell-inp')].map(inp => inp.dataset.key);
        colDefs = keys.map(k => ({ key: k, label: formatColumnHeader(k), type: getFieldInputType(k), options: [] }));
      }
    }
    if (!colDefs.length) {
      colDefs = [
        { key: 'description', label: 'Item Description', type: 'text', options: [] },
        { key: 'agency', label: 'Agency / Institution', type: 'text', options: [] },
        { key: 'amount', label: 'Amount / Budget', type: 'text', options: [] },
        { key: 'remarks', label: 'Remarks', type: 'text', options: [] }
      ];
    }
    const rowCount = tbody.rows.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `<td style="text-align:center;font-weight:600;color:#6b7280">${rowCount}</td>
      ${colDefs.map(cd => `<td>${renderCellInput(cd.key, '', false, cd, (currentEditSubIdx >= 0 && cachedSubRows[currentEditSubIdx]) ? cachedSubRows[currentEditSubIdx].table_no : '')}</td>`).join('')}
      <td style="text-align:center"><button type="button" onclick="this.closest('tr').remove()" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:3px 8px;cursor:pointer;font-weight:bold;">×</button></td>`;
    if (window._ptaGrouping?.grouped) tr.dataset.group = category;
    if (beforeRow) tbody.insertBefore(tr, beforeRow); else tbody.appendChild(tr);
  };

  window.savePtaModalEdit = async function() {
    if (currentEditSubIdx < 0) return;
    const r = cachedSubRows[currentEditSubIdx];
    const year = document.getElementById('subYearSel').value;
    const tbody = document.getElementById('ptaEditTbody');
    
    if (!await AppDialog.confirm('Save the changes to this submission?', { title: 'Save submission', confirmText: 'Save' })) return;
    const updatedRows = [];
    const isT2a = (r.table_no === 'T2A' || r.table_no === 'T2a' || r.table_no === 'T2');
    if (tbody) {
      tbody.querySelectorAll('tr').forEach((tr, rIdx) => {
        const inputs = tr.querySelectorAll('.pta-cell-inp');
        if (!inputs.length) return;
        const originalIndex = tr.dataset.rowIndex;
        const rowObj = originalIndex !== undefined ? { ...(r.rows?.[Number(originalIndex)] || {}) } : {};
        if (window._ptaGrouping?.grouped) rowObj[window._ptaGrouping.key] = tr.dataset.group;
        inputs.forEach(inp => {
          const key = inp.dataset.key;
          let v = inp.type === 'checkbox' ? inp.checked : inp.value;
          if (getFieldInputType(key, r.table_no) === 'number' && v !== '') {
            v = isNaN(Number(v)) ? v : Number(v);
          }
          rowObj[key] = v;
          if (key === 'new_') {
            rowObj['new'] = v;
          }
          if (key === 'expenditures') {
            rowObj['expenditure'] = v;
          } else if (key === 'expenditure') {
            rowObj['expenditures'] = v;
          }
        });

        if (String(r.table_no).toUpperCase() === 'T10') {
          rowObj.approved = rowObj.status === 'Approved';
          rowObj.for_revision = rowObj.status === 'For Revision';
          rowObj.disapproved = rowObj.status === 'Disapproved';
        }
        if (String(r.table_no).toUpperCase() === 'T11') {
          rowObj.modalities = { ...(rowObj.modalities || {}) };
          ['stcbf','stc4id','safe','food_value_chain','others'].forEach(key => { rowObj.modalities[key] = rowObj[key]; });
        }
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

  window.acceptModalSub = function() {
    if (currentEditSubIdx < 0) return;
    const idx = currentEditSubIdx;
    closeModal('modalViewData');
    acceptSub(idx);
  };

  window.returnModalSub = function() {
    if (currentEditSubIdx < 0) return;
    const idx = currentEditSubIdx;
    closeModal('modalViewData');
    returnSub(idx);
  };

  window.sendPtaReminder = async function(idx, instName) {
    const r = cachedSubRows[idx];
    const year = document.getElementById('subYearSel').value;
    const targetInst = instName || r?.institution || 'Institution';
    
    showConfirmModal({
      title: 'Send Reminder?',
      message: `Send an official notification reminder to ${targetInst} to encode and submit their CY ${year} tables?`,
      confirmText: 'Send Reminder',
      type: 'green',
      onConfirm: async function() {
        try {
          const res = await fetch('/api/pta/submissions/send-reminder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              cmi_user_id: r?.cmi_user_id || null,
              institution: targetInst,
              year: year
            })
          });
          const json = await res.json();
          if (json.ok) {
            showToast(`🔔 Reminder sent to ${targetInst}!`);
          } else {
            showToast('Failed to send reminder: ' + (json.error || 'Unknown error'));
          }
        } catch(e) {
          showToast('Failed to send reminder.');
        }
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
    // Auto-apply status filter from URL param (set by dashboard KPI card links)
    const urlParams = new URLSearchParams(window.location.search);
    const autoStatus = urlParams.get('autoStatus');
    const autoYear   = urlParams.get('year');

    fetch('/api/formats')
      .then(r => r.json())
      .then(data => {
        if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
          const yearSel = document.getElementById('subYearSel');
          const activeYr = autoYear ? parseInt(autoYear) : (data.active_year || new Date().getFullYear());
          if (yearSel) {
            yearSel.innerHTML = data.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
          }
        }

        // Apply status filter after year is set
        if (autoStatus) {
          const statusSel = document.getElementById('subStatusSel');
          if (statusSel) {
            // Map incoming status values to dropdown option values
            const statusMap = {
              'submitted'   : 'submitted',
              'in-progress' : 'draft',
              'not-started' : 'not-started',
            };
            const mapped = statusMap[autoStatus] || autoStatus;
            // Check if an option exists for that value; if not, leave as is
            const optExists = Array.from(statusSel.options).some(o => o.value === mapped);
            if (optExists) statusSel.value = mapped;
          }
        }
      }).catch(() => {}).finally(() => loadSubs());
  });
  </script>
  @endsection
