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
    .main-content.page-content { isolation: auto !important; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(5px); -webkit-backdrop-filter:blur(5px); z-index:99999 !important; overflow-y:auto; padding:32px 16px; box-sizing:border-box; }
    .modal-overlay.open { display:flex; justify-content:center; align-items:center; }
    .modal-box { background:#fff; border-radius:18px; padding:28px 32px; width:100%; max-width:500px; box-shadow:0 25px 70px rgba(0,0,0,.35); position:relative; margin:auto; }
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

    @media (max-width: 640px) {
      .pg-banner { flex-direction: column; align-items: stretch; gap: 12px; }
      .pg-banner .btn-primary-fc { align-self: flex-start; }
      .modal-box { padding: 20px 16px; }
      .form-row { grid-template-columns: 1fr; }
      .fc-table thead th, .fc-table td { padding: 10px 12px; }
      .fc-card-head { flex-direction: column; align-items: stretch; gap: 10px; }
    }

    /* ── Section Header Row ── */
    .section-header-row td {
      padding: 10px 16px !important;
      font-weight: 700 !important;
      font-size: 12px !important;
      text-transform: uppercase !important;
      letter-spacing: .06em !important;
      color: #059669 !important;
      background: #f3f6f4 !important;
      border-left: 4px solid #10b981 !important;
    }
    .section-header-row:hover td {
      background: #edf3ee !important;
    }
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
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <button class="btn-outline-fc" style="background:#fff;border:1.5px solid #d1d5db;color:#374151;display:inline-flex;align-items:center;gap:6px;font-weight:600;" onclick="openBlankYearModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Blank Year
          </button>
          <button class="btn-primary-fc" onclick="openCloneModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
            Prepare New Year (Clone)
          </button>
        </div>
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
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="text" class="form-input" id="fmtSearchInput" placeholder="Search table, title..." style="width:190px;padding:7px 12px;font-size:12.5px;border-radius:8px"/>
            <select class="form-select" id="fmtSectionFilter" style="width:auto;padding:7px 12px;font-size:12.5px;border-radius:8px">
              <option value="">All Sections</option>
              <option value="R&D Mgt. & Coord.">R&D Mgt. & Coord.</option>
              <option value="Strategic R&D">Strategic R&D</option>
              <option value="Results Utilization">Results Utilization</option>
              <option value="Capability & Governance">Capability & Governance</option>
              <option value="Policy Analysis">Policy Analysis</option>
            </select>
            <div class="card-actions" id="cardActions">
              <!-- dynamic buttons inserted here -->
            </div>
          </div>
        </div>
        <div class="fc-card-body">
          <div class="fc-table-wrap">
            <table class="fc-table">
              <thead>
                <tr>
                  <th>#</th><th>Table No</th><th>Title</th><th>Access</th><th>Required</th>
                  <th>Submissions</th><th>Actions</th>
                </tr>
              </thead>
              <tbody id="fmtTbody">
                <tr><td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">Loading format templates...</td></tr>
              </tbody>
            </table>
          </div>
          <div id="fmtPaginationWrap"></div>
        </div>
      </div>

    </div>

    <!-- ═══ CREATE BLANK YEAR MODAL ═══ -->
    <div class="modal-overlay" id="modalBlankYear">
      <div class="modal-box">
        <div class="modal-title">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
          </svg>
          Create Blank Reporting Year
        </div>
        <div class="modal-desc">Create a new annual reporting cycle without copying tables from previous years. The year will start completely empty (0 tables) so you can add formats manually.</div>
        <div class="form-group">
          <label class="form-label">Reporting Year (e.g. {{ date('Y') + 1 }})</label>
          <input class="form-input" type="number" id="blankYearInput" min="2020" max="2050" value="{{ date('Y') + 1 }}"/>
        </div>
        <div id="blankYearWarning" style="display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:14px;">
          <div id="blankYearWarnText" style="font-weight:600"></div>
          <label style="display:flex;align-items:center;gap:7px;margin-top:8px;cursor:pointer;font-weight:600;font-size:12px;">
            <input type="checkbox" id="blankYearClearConfirm" style="accent-color:#ef4444"/> Yes, clear existing tables and make this year empty
          </label>
        </div>
        <div class="modal-actions">
          <button class="btn-cancel" onclick="closeModal('modalBlankYear')">Cancel</button>
          <button class="btn-primary-fc" onclick="submitBlankYear()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Blank Year
          </button>
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
      <div class="modal-box" style="max-width:700px;width:100%;max-height:88vh;display:flex;flex-direction:column;padding:0;overflow:hidden;border-radius:18px">
        <!-- Header -->
        <div style="padding:22px 28px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0">
          <div class="modal-title" style="margin-bottom:4px">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Format Table(s)
          </div>
          <div class="modal-desc" style="margin-bottom:0">Add one or more table templates for CMI annual reporting.</div>
          <input type="hidden" id="addTableYear"/>
        </div>

        <!-- Scrollable content -->
        <div style="padding:20px 28px;overflow-y:auto;flex:1 1 0%;min-height:0">
          <!-- Dynamic list of table entries to add -->
          <div id="addTableRowsList"></div>
        </div>

        <!-- Pinned Actions -->
        <div class="modal-actions" style="margin-top:0;padding:14px 28px 20px;border-top:1px solid #f1f5f9;background:#fafafa;border-radius:0 0 18px 18px;flex-shrink:0">
          <button class="btn-cancel" onclick="closeModal('modalAddTable')">Cancel</button>
          <button class="btn-primary-fc" onclick="submitAddTable()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Save Table(s)
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ EDIT TABLE MODAL ═══ -->
    <div class="modal-overlay" id="modalEditTable">
      <div class="modal-box" style="max-width:580px;width:100%;max-height:88vh;display:flex;flex-direction:column;padding:0;overflow:hidden;border-radius:18px">
        <div style="padding:22px 28px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0">
          <div class="modal-title" style="margin-bottom:0">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Table
          </div>
        </div>
        <div style="padding:20px 28px;overflow-y:auto;flex:1 1 0%;min-height:0">
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
          <!-- Titles & Rows for this Table -->
          <div style="margin-bottom:14px">
            <div id="editTitlesContainer"></div>
            <button type="button" class="btn-outline-fc btn-sm" onclick="addEditTitleBlock()" style="border-style:dashed;padding:6px 14px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px;margin-top:2px">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Another Title
            </button>
          </div>
          <div class="form-row" style="margin-bottom:6px">
            <div class="form-group" style="flex-direction:row;align-items:center;gap:10px">
              <input type="checkbox" id="editTableRequired" style="width:16px;height:16px;accent-color:#10b981"/>
              <label for="editTableRequired" class="form-label" style="margin:0;cursor:pointer">Required field</label>
            </div>
            <div class="form-group" style="flex-direction:row;align-items:center;gap:10px">
              <input type="checkbox" id="editTableLocked" style="width:16px;height:16px;accent-color:#d97706"/>
              <label for="editTableLocked" class="form-label" style="margin:0;cursor:pointer;color:#92400e;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Lock for CMI (PTA Only)
              </label>
            </div>
          </div>
        </div>
        <div class="modal-actions" style="margin-top:0;padding:14px 28px 20px;border-top:1px solid #f1f5f9;background:#fafafa;border-radius:0 0 18px 18px;flex-shrink:0">
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
    function openModal(id) {
      const m = document.getElementById(id);
      if (!m) return;
      if (m.parentElement !== document.body) {
        document.body.appendChild(m);
      }
      m.classList.add('open');
    }
    function closeModal(id) {
      const m = document.getElementById(id);
      if (m) m.classList.remove('open');
    }
    // close on backdrop click
    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList && e.target.classList.contains('modal-overlay')) {
        closeModal(e.target.id);
      }
    });

    let cachedTemplates = [];

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

    function renderFormatsTable() {
      const tbody = document.getElementById('fmtTbody');
      const query = (document.getElementById('fmtSearchInput')?.value || '').toLowerCase().trim();
      const sectionFilter = document.getElementById('fmtSectionFilter')?.value || '';

      const filtered = cachedTemplates.filter(t => {
        const sec = normalizeSectionName(t.section);
        if (sectionFilter && sec !== normalizeSectionName(sectionFilter)) return false;
        if (!query) return true;
        return (t.table_no || '').toLowerCase().includes(query) ||
              (`table ${t.table_no}` || '').toLowerCase().includes(query) ||
              (t.title || '').toLowerCase().includes(query) ||
              (t.section || '').toLowerCase().includes(query);
      });

      if (!cachedTemplates.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:56px 20px;color:#9ca3af">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/>
          </svg>
          <strong style="display:block;font-size:14.5px;color:#374151;margin-bottom:6px">No format templates created for CY ${fmtCurrentYear} yet.</strong>
          <span style="font-size:12.5px;color:#6b7280">This reporting year is empty. Click <strong>+ Add Table</strong> above to start adding formats.</span>
        </td></tr>`;
        const wrap = document.getElementById('fmtPaginationWrap');
        if (wrap) wrap.innerHTML = `<div style="padding:14px 20px;color:#9ca3af;font-size:12.5px;text-align:center;">Showing 0 format tables across sections</div>`;
        return;
      }

      if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:48px;color:#9ca3af">
          <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px">
            <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>No format templates found matching your filter.
        </td></tr>`;
        return;
      }

      // Group by section
      const grouped = {};
      SECTION_ORDER.forEach(s => { grouped[s] = []; });

      filtered.forEach(t => {
        const sec = normalizeSectionName(t.section);
        if (!grouped[sec]) grouped[sec] = [];
        grouped[sec].push(t);
      });

      const allSectionKeys = [
        ...SECTION_ORDER,
        ...Object.keys(grouped).filter(k => !SECTION_ORDER.includes(k))
      ];

      const htmlParts = [];
      let rowIdx = 0;

      allSectionKeys.forEach(secName => {
        const secTemplates = grouped[secName] || [];
        if (!secTemplates.length) return;

        htmlParts.push(`
          <tr class="section-header-row" style="background:#f3f6f4 !important">
            <td colspan="7" style="font-weight:700 !important;font-size:12px !important;text-transform:uppercase !important;letter-spacing:.06em !important;color:#059669 !important;background:#f3f6f4 !important;border-left:4px solid #10b981 !important;padding:10px 16px !important">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#059669" stroke-width="2.5" style="display:inline-block;vertical-align:-1px;margin-right:6px"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              ${secName.toUpperCase()} <span style="font-size:11px;font-weight:500;color:#6b7280;text-transform:none;letter-spacing:normal;margin-left:6px">(${secTemplates.length} table${secTemplates.length > 1 ? 's' : ''})</span>
            </td>
          </tr>
        `);

        secTemplates.forEach(t => {
          rowIdx++;
          const pct   = t.total_cmi > 0 ? Math.round((t.submission_count / t.total_cmi) * 100) : 0;
          const pcTxt = pct >= 75 ? '#059669'  : pct >= 40 ? '#d97706'  : '#dc2626';
          const canDelete = t.submission_count == 0;
          const colsJson = JSON.stringify(t.columns_json || []);

          htmlParts.push(`
            <tr>
              <td style="color:#9ca3af;font-size:12px">${t.sort_order || rowIdx}</td>
              <td><span class="badge badge-blue">Table ${t.table_no}</span></td>
              <td>
                <strong>${t.title}</strong>
              </td>
              <td>
                <button type="button" onclick="toggleLock(${t.id})" style="border:none;background:transparent;padding:0;cursor:pointer;" title="${t.is_locked ? 'Table is locked for CMI. Only PTA can fill out. Click to unlock.' : 'Table is open for CMI encoding. Click to lock.'}">
                  ${t.is_locked
                    ? `<span class="badge badge-orange" style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-weight:700;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> PTA Only</span>`
                    : `<span class="badge badge-green" style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-weight:700;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg> CMI &amp; PTA</span>`
                  }
                </button>
              </td>
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
                <div style="display:flex;gap:6px">
                  <button class="btn-sm-edit" onclick="openEditModalById(${t.id})">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                  </button>
                  ${canDelete ? `<button class="btn-sm-del" onclick="deleteTable(${t.id},'${t.table_no}')">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>Remove
                  </button>` : ''}
                </div>
              </td>
            </tr>
          `);
        });
      });

      tbody.innerHTML = htmlParts.join('');

      const wrap = document.getElementById('fmtPaginationWrap');
      if (wrap) {
        wrap.innerHTML = `
          <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #f3f4f6;flex-wrap:wrap;gap:10px;font-size:12.5px;color:#6b7280">
            <div>Showing <strong>${rowIdx}</strong> format table${rowIdx !== 1 ? 's' : ''} across sections</div>
          </div>`;
      }
    }

    // ── Load Formats for a Year ───────────────────────────────────────
    let formatLoadSequence = 0;
    async function loadFormats(year) {
      const sequence = ++formatLoadSequence;
      const url = year ? `/api/pta/formats?year=${year}` : `/api/pta/formats`;
      const res  = await fetch(url);
      const json = await res.json();
      if (sequence !== formatLoadSequence) return;
      const templates = json.templates || [];
      cachedTemplates = templates;
      const actualYear = json.year || json.active_year || year || 2025;
      fmtCurrentYear = actualYear;
      try { localStorage.setItem('secreco_selected_year', String(actualYear)); } catch(e){}
      allYears = json.years || [actualYear];
      if (!allYears.includes(actualYear)) {
        allYears.push(actualYear);
        allYears.sort((a,b) => b - a);
      }

      // Update clone select options
      const sel = document.getElementById('cloneFromYear');
      if (sel) sel.innerHTML = allYears.map(y => `<option value="${y}">${y}</option>`).join('');

      // Render year tabs
      renderYearTabs(allYears, actualYear, templates, json.active_year);

      // Status
      fmtYearStatus = templates.length > 0 ? (templates[0].status || 'draft') : 'draft';

      // Alert
      const alertDiv = document.getElementById('statusAlert');
      if (fmtYearStatus === 'archived') {
        alertDiv.innerHTML = `<div class="alert alert-info"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2"/><path d="M5 5 L12 12 L5 19"/></svg> CY ${actualYear} is <strong>archived</strong>. All data is permanently preserved and read-only.</div>`;
        alertDiv.style.display = 'block';
      } else if (fmtYearStatus === 'active') {
        alertDiv.innerHTML = `<div class="alert alert-success"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> CY ${actualYear} is the <strong>active year</strong>. Tables with existing submissions are protected from deletion.</div>`;
        alertDiv.style.display = 'block';
      } else {
        alertDiv.innerHTML = `<div class="alert alert-warning"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> CY ${actualYear} is a <strong>draft</strong>. Add, edit, or remove tables. Click "Activate" when ready for CMI submissions.</div>`;
        alertDiv.style.display = 'block';
      }

      // Card title
      document.getElementById('cardTitle').textContent = `CY ${actualYear} — ${templates.length} Tables`;

      // Card action buttons
      const actions = document.getElementById('cardActions');
      let btns = `<button class="btn-outline-fc" onclick="openAddModal(${actualYear})">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>+ Add Table</button>`;
      if (fmtYearStatus === 'draft') {
        if (templates.length > 0) {
          btns += `<button class="btn-primary-fc" onclick="confirmActivate(${actualYear})">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Activate CY ${actualYear}</button>`;
        } else {
          btns += `<button class="btn-primary-fc" disabled style="opacity:0.45;cursor:not-allowed;" title="You must add at least one table before activating this year">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Activate CY ${actualYear}</button>`;
        }
      }
      actions.innerHTML = btns;

      renderFormatsTable();
    }

    function renderYearTabs(years, selectedYear, templates, activeYear) {
      const tabs = document.getElementById('yearTabs');
      if (!years.length) { tabs.innerHTML = '<div style="color:#9ca3af;font-size:13px">No years found. Prepare a new year to start.</div>'; return; }
      tabs.innerHTML = years.map(y => {
        let yCls = 'draft-year', icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`, label = 'Draft';
        if (y === activeYear) {
          yCls = 'active-year';
          icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
          label = 'Active';
        } else if (activeYear && y < activeYear) {
          yCls = 'archived-year';
          icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 8v13H3V8"/><rect x="1" y="3" width="22" height="5" rx="1"/></svg>`;
          label = 'Archived';
        } else if (y === selectedYear && templates.length) {
          const st = templates[0].status || 'draft';
          if (st === 'active') { yCls = 'active-year'; icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`; label = 'Active'; }
          if (st === 'archived') { yCls = 'archived-year'; icon = `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 8v13H3V8"/><rect x="1" y="3" width="22" height="5" rx="1"/></svg>`; label = 'Archived'; }
        }
        return `<a href="#" class="year-tab ${yCls} ${y == selectedYear ? 'selected' : ''}" onclick="loadFormats(${y});return false">${icon} CY ${y} — ${label}</a>`;
      }).join('');
    }

    // ── Dynamic Multi-Row Add Table ──────────────────────────────────
    let addTableRowCounter = 0;
    function addNewAddTableRow(initialData = {}) {
      addTableRowCounter++;
      const container = document.getElementById('addTableRowsList');
      if (!container) return;
      const rowId = `add-tbl-row-${Date.now()}-${addTableRowCounter}`;
      const item = document.createElement('div');
      item.className = 'add-table-item';
      item.id = rowId;
      item.style.cssText = 'background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:14px;position:relative;';

      item.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <span class="table-row-badge" style="font-size:11.5px;font-weight:700;color:#059669;background:#ecfdf5;padding:3px 10px;border-radius:20px;border:1px solid #a7f3d0">
            Table Item <span class="row-seq"></span>
          </span>
          <button type="button" onclick="removeAddTableRow('${rowId}')" class="btn-remove-tbl-row" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Remove Table
          </button>
        </div>
        <div class="form-row" style="margin-bottom:10px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Table No. <span style="color:#ef4444">*</span></label>
            <input class="form-input add-tbl-no" value="${initialData.table_no || ''}" placeholder="e.g. T21, T22"/>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Section <span style="color:#ef4444">*</span></label>
            <select class="form-select add-tbl-section">
              <option ${initialData.section==='R&D Mgt. & Coord.'?'selected':''}>R&D Mgt. &amp; Coord.</option>
              <option ${initialData.section==='Strategic R&D'?'selected':''}>Strategic R&D</option>
              <option ${initialData.section==='Results Utilization'?'selected':''}>Results Utilization</option>
              <option ${initialData.section==='Capability & Gov.'?'selected':''}>Capability &amp; Gov.</option>
              <option ${initialData.section==='Policy Analysis'?'selected':''}>Policy Analysis</option>
            </select>
          </div>
        </div>

        <!-- Titles & Rows for this Table -->
        <div style="margin-bottom:12px">
          <div class="tbl-titles-container"></div>
          <button type="button" class="btn-outline-fc btn-sm btn-add-another-title" onclick="addTitleBlockToTable('${rowId}')" style="border-style:dashed;padding:6px 12px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Another Title
          </button>
        </div>

        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;font-size:12.5px">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="checkbox" class="add-tbl-req" style="accent-color:#10b981" ${initialData.is_required !== false ? 'checked' : ''}/> Required field
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#92400e;font-weight:600">
            <input type="checkbox" class="add-tbl-locked" style="accent-color:#d97706" ${initialData.is_locked ? 'checked' : ''}/>
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Lock for CMI (PTA Only)
          </label>
        </div>
      `;
      container.appendChild(item);
      updateAddTableRowIndices();

      if (initialData.titles && Array.isArray(initialData.titles) && initialData.titles.length > 0) {
        initialData.titles.forEach(t => addTitleBlockToTable(rowId, t.title, t.columns));
      } else if (initialData.categories && Array.isArray(initialData.categories) && initialData.categories.length > 0) {
        initialData.categories.forEach(c => addTitleBlockToTable(rowId, c, initialData.columns));
      } else {
        addTitleBlockToTable(rowId, initialData.title || '', initialData.columns || null);
      }
    }

    function createTitleBlockElement(titleVal = '', cols = null) {
      const block = document.createElement('div');
      block.className = 'tbl-title-block';
      block.style.cssText = 'background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:12px;position:relative;box-shadow:0 1px 2px rgba(0,0,0,0.03)';

      block.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span class="title-seq-badge" style="font-size:11.5px;font-weight:700;color:#059669;background:#ecfdf5;padding:2px 8px;border-radius:6px;border:1px solid #a7f3d0">
            Title <span class="title-seq-num">#1</span>
          </span>
          <button type="button" class="btn-remove-title" onclick="removeTitleBlock(this)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;font-weight:600;display:none;align-items:center;gap:4px">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Remove Title
          </button>
        </div>
        <div class="form-group" style="margin-bottom:10px">
          <label class="form-label" style="font-size:12px;margin-bottom:3px;font-weight:600">Title <span style="color:#ef4444">*</span></label>
          <input class="form-input tbl-title-val" placeholder="e.g. Title Name" value="${titleVal || ''}" style="padding:7px 12px;font-size:13px"/>
        </div>
        <div style="background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:10px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px">
            <div>
              <label class="form-label" style="font-size:11.5px;margin-bottom:0;font-weight:700">Columns / Row Fields for this Title</label>
              <span style="font-size:10.5px;color:#9ca3af">Fields CMI fills in per row</span>
            </div>
            <button type="button" class="btn-outline-fc btn-sm" onclick="addColToTitleBlock(this)" style="border-style:dashed;padding:3px 8px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
              <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Row / Column
            </button>
          </div>
          <div class="title-col-list" style="display:flex;flex-direction:column;gap:6px"></div>
        </div>
      `;

      const colList = block.querySelector('.title-col-list');
      if (Array.isArray(cols) && cols.length > 0) {
        cols.forEach(c => {
          const name = typeof c === 'string' ? c : (c.name || '');
          const type = typeof c === 'string' ? 'text' : (c.type || 'text');
          const options = typeof c === 'object' ? (c.options || '') : '';
          appendColRow(colList, name, type, options);
        });
      } else {
        appendColRow(colList, '', 'text', '');
      }

      return block;
    }

    function esc(s) {
      return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function appendColRow(colList, colName = '', colType = 'text', colOptions = '') {
      const row = document.createElement('div');
      row.className = 'col-row';
      row.style.cssText = 'display:flex;flex-direction:column;margin-bottom:8px;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px;';
      const isDropdown = colType === 'dropdown';

      row.innerHTML = `
        <div style="display:flex;gap:6px;align-items:center;">
          <input class="form-input title-col-name" value="${esc(colName)}" placeholder="Column name (e.g. Agency, Project Title, Date, Amount)" style="flex:1;padding:5px 8px;font-size:12px"/>
          <select class="form-select title-col-type" onchange="toggleColOptions(this)" style="width:115px;padding:5px 6px;font-size:12px">
            <option value="text" ${colType === 'text' ? 'selected' : ''}>Text</option>
            <option value="number" ${colType === 'number' ? 'selected' : ''}>Number</option>
            <option value="amount" ${colType === 'amount' ? 'selected' : ''}>Amount</option>
            <option value="dropdown" ${colType === 'dropdown' ? 'selected' : ''}>Dropdown</option>
            <option value="date" ${colType === 'date' ? 'selected' : ''}>Date</option>
          </select>
          <button type="button" onclick="this.closest('.col-row').remove()" style="background:none;border:none;color:#ef4444;font-size:18px;cursor:pointer;padding:0 4px;line-height:1" title="Remove column">×</button>
        </div>
        <div class="col-options-wrap" style="display:${isDropdown ? 'block' : 'none'};margin-top:8px;padding:8px 10px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="font-size:11.5px;font-weight:700;color:#334155;">Dropdown Options:</span>
              <span class="dropdown-opts-count" style="font-size:10.5px;font-weight:700;color:#059669;background:#ecfdf5;padding:1px 6px;border-radius:10px;border:1px solid #a7f3d0">0 options</span>
            </div>
            <button type="button" class="btn-add-opt" onclick="addDropdownOptionRowFromBtn(this)" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
              <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              + Add Option
            </button>
          </div>
          <div class="dropdown-options-list" style="display:flex;flex-direction:column;gap:5px;margin-bottom:4px;"></div>
          <div style="font-size:10.5px;color:#94a3b8;margin-top:4px;">
            Add unlimited options. Press <kbd style="background:#e2e8f0;padding:1px 4px;border-radius:3px;font-family:inherit;font-size:10px;">Enter</kbd> to add next, or paste comma-separated list.
          </div>
        </div>
      `;
      colList.appendChild(row);

      const optsList = row.querySelector('.dropdown-options-list');
      let initialOpts = [];
      if (Array.isArray(colOptions)) {
        initialOpts = colOptions.map(s => String(s).trim()).filter(Boolean);
      } else if (typeof colOptions === 'string' && colOptions.trim()) {
        initialOpts = colOptions.split(',').map(s => s.trim()).filter(Boolean);
      }

      if (initialOpts.length > 0) {
        initialOpts.forEach(opt => addDropdownOptionRow(optsList, opt));
      } else if (isDropdown) {
        addDropdownOptionRow(optsList, '');
        addDropdownOptionRow(optsList, '');
      }

      updateDropdownOptionIndices(optsList);

      if (!colName) {
        row.querySelector('.title-col-name')?.focus();
      }
    }

    function addDropdownOptionRow(listEl, val = '') {
      if (!listEl) return null;
      const optItem = document.createElement('div');
      optItem.className = 'dropdown-opt-item';
      optItem.style.cssText = 'display:flex;align-items:center;gap:6px;';
      optItem.innerHTML = `
        <span class="opt-num" style="font-size:11px;font-weight:700;color:#64748b;min-width:20px;text-align:right;">#1</span>
        <input type="text" class="form-input dropdown-opt-val" value="${esc(val)}" placeholder="Option choice (e.g. Active, Completed, Pending)" style="flex:1;padding:4px 8px;font-size:12px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;" onkeydown="handleOptKeydown(event, this)" onpaste="handleOptPaste(event, this)"/>
        <button type="button" onclick="removeDropdownOptionRow(this)" style="background:none;border:none;color:#ef4444;font-size:16px;cursor:pointer;padding:0 4px;line-height:1;border-radius:4px;" title="Remove option">×</button>
      `;
      listEl.appendChild(optItem);
      updateDropdownOptionIndices(listEl);
      return optItem;
    }

    function addDropdownOptionRowFromBtn(btn) {
      const wrap = btn.closest('.col-options-wrap');
      const listEl = wrap?.querySelector('.dropdown-options-list');
      if (listEl) {
        const item = addDropdownOptionRow(listEl, '');
        item?.querySelector('.dropdown-opt-val')?.focus();
      }
    }

    function removeDropdownOptionRow(btn) {
      const row = btn.closest('.dropdown-opt-item');
      const listEl = row?.closest('.dropdown-options-list');
      if (row) row.remove();
      if (listEl) updateDropdownOptionIndices(listEl);
    }

    function updateDropdownOptionIndices(listEl) {
      if (!listEl) return;
      const items = listEl.querySelectorAll('.dropdown-opt-item');
      items.forEach((item, idx) => {
        const numEl = item.querySelector('.opt-num');
        if (numEl) numEl.textContent = `#${idx + 1}`;
      });
      const wrap = listEl.closest('.col-options-wrap');
      const countBadge = wrap?.querySelector('.dropdown-opts-count');
      if (countBadge) {
        countBadge.textContent = `${items.length} option${items.length !== 1 ? 's' : ''}`;
      }
    }

    function handleOptKeydown(e, input) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const listEl = input.closest('.dropdown-options-list');
        if (listEl) {
          const item = addDropdownOptionRow(listEl, '');
          item?.querySelector('.dropdown-opt-val')?.focus();
        }
      }
    }

    function handleOptPaste(e, input) {
      const pasteText = (e.clipboardData || window.clipboardData)?.getData('text');
      if (pasteText && pasteText.includes(',')) {
        e.preventDefault();
        const parts = pasteText.split(',').map(s => s.trim()).filter(Boolean);
        if (parts.length > 0) {
          input.value = parts[0];
          const listEl = input.closest('.dropdown-options-list');
          for (let i = 1; i < parts.length; i++) {
            addDropdownOptionRow(listEl, parts[i]);
          }
          updateDropdownOptionIndices(listEl);
        }
      }
    }

    function toggleColOptions(sel) {
      const wrap = sel.closest('.col-row')?.querySelector('.col-options-wrap');
      if (wrap) {
        const isDropdown = sel.value === 'dropdown';
        wrap.style.display = isDropdown ? 'block' : 'none';
        if (isDropdown) {
          const listEl = wrap.querySelector('.dropdown-options-list');
          if (listEl && listEl.querySelectorAll('.dropdown-opt-item').length === 0) {
            addDropdownOptionRow(listEl, '');
            addDropdownOptionRow(listEl, '');
          }
          listEl?.querySelector('.dropdown-opt-val')?.focus();
        }
      }
    }

    function addColToTitleBlock(btn) {
      const block = btn.closest('.tbl-title-block');
      if (!block) return;
      const colList = block.querySelector('.title-col-list');
      if (colList) appendColRow(colList, '', 'text', '');
    }

    function removeTitleBlock(btn) {
      const block = btn.closest('.tbl-title-block');
      const container = block?.parentElement;
      if (block) block.remove();
      if (container) updateTitleIndices(container);
    }

    function updateTitleIndices(container) {
      if (!container) return;
      const blocks = container.querySelectorAll('.tbl-title-block');
      blocks.forEach((b, idx) => {
        const numEl = b.querySelector('.title-seq-num');
        if (numEl) numEl.textContent = `#${idx + 1}`;
        const delBtn = b.querySelector('.btn-remove-title');
        if (delBtn) delBtn.style.display = blocks.length > 1 ? 'inline-flex' : 'none';
      });
    }

    function addTitleBlockToTable(rowId, titleVal = '', cols = null) {
      const item = document.getElementById(rowId);
      if (!item) return;
      const container = item.querySelector('.tbl-titles-container');
      if (!container) return;

      const block = createTitleBlockElement(titleVal, cols);
      container.appendChild(block);
      updateTitleIndices(container);
      if (!titleVal) {
        block.querySelector('.tbl-title-val')?.focus();
      }
    }

    function addEditTitleBlock(titleVal = '', cols = null) {
      const container = document.getElementById('editTitlesContainer');
      if (!container) return;

      const block = createTitleBlockElement(titleVal, cols);
      container.appendChild(block);
      updateTitleIndices(container);
      if (!titleVal) {
        block.querySelector('.tbl-title-val')?.focus();
      }
    }

    function removeAddTableRow(rowId) {
      const el = document.getElementById(rowId);
      if (el) el.remove();
      updateAddTableRowIndices();
    }

    function updateAddTableRowIndices() {
      const rows = document.querySelectorAll('#addTableRowsList .add-table-item');
      rows.forEach((r, idx) => {
        const seq = r.querySelector('.row-seq');
        if (seq) seq.textContent = `#${idx + 1}`;
        const delBtn = r.querySelector('.btn-remove-tbl-row');
        if (delBtn) delBtn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
      });
    }

    function openAddModal(year) {
      document.getElementById('addTableYear').value = year;
      const container = document.getElementById('addTableRowsList');
      if (container) container.innerHTML = '';
      addNewAddTableRow();
      openModal('modalAddTable');
    }

    async function submitAddTable() {
      const items = document.querySelectorAll('#addTableRowsList .add-table-item');
      const tables = [];
      let hasError = false;

      items.forEach((item, idx) => {
        const no  = (item.querySelector('.add-tbl-no')?.value || '').trim();
        const sec = (item.querySelector('.add-tbl-section')?.value || '').trim();
        const req = item.querySelector('.add-tbl-req')?.checked ? 1 : 0;
        const lck = item.querySelector('.add-tbl-locked')?.checked ? 1 : 0;

        if (!no) {
          showToast(`Table #${idx + 1}: Table No. is required.`);
          hasError = true;
          return;
        }

        const titleBlocks = item.querySelectorAll('.tbl-titles-container .tbl-title-block');
        const titlesData = [];

        titleBlocks.forEach(b => {
          const tName = (b.querySelector('.tbl-title-val')?.value || '').trim();
          const colRows = b.querySelectorAll('.title-col-list .col-row');
          const cols = [...colRows].map(r => {
            const name = (r.querySelector('.title-col-name')?.value || '').trim();
            const type = r.querySelector('.title-col-type')?.value || 'text';
            const colObj = { name, type };
            if (type === 'dropdown') {
              const optInputs = r.querySelectorAll('.dropdown-options-list .dropdown-opt-val');
              let opts = [...optInputs].map(i => (i.value || '').trim()).filter(Boolean);
              if (!opts.length) {
                const rawOpts = (r.querySelector('.title-col-options')?.value || '').trim();
                if (rawOpts) opts = rawOpts.split(',').map(s => s.trim()).filter(Boolean);
              }
              colObj.options = opts;
            }
            return colObj;
          }).filter(c => c.name);

          if (tName) {
            titlesData.push({ title: tName, columns: cols });
          }
        });

        if (titlesData.length === 0) {
          showToast(`Table #${idx + 1}: At least one Title is required.`);
          hasError = true;
          return;
        }

        let mainTitle = '';
        let colsData = null;

        if (titlesData.length === 1) {
          mainTitle = titlesData[0].title;
          colsData = JSON.stringify({
            titles: titlesData,
            categories: [titlesData[0].title],
            columns: titlesData[0].columns
          });
        } else {
          mainTitle = titlesData.map(t => t.title).join(' / ');
          colsData = JSON.stringify({
            titles: titlesData,
            categories: titlesData.map(t => t.title),
            columns: titlesData[0].columns
          });
        }

        tables.push({
          table_no: no,
          section: sec,
          title: mainTitle,
          is_required: req,
          is_locked: lck,
          columns_json: colsData
        });
      });

      if (hasError || tables.length === 0) return;

      const payload = {
        action: 'add',
        year: parseInt(document.getElementById('addTableYear').value),
        tables: tables
      };

      const res  = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.ok) {
        closeModal('modalAddTable');
        showToast(json.message);
        loadFormats(fmtCurrentYear);
      } else {
        showToast(json.message);
      }
    }

    // ── Edit Table ─────────────────────────────────────────────────────
    function openEditModalById(id) {
      const t = (cachedTemplates || []).find(x => x.id === id);
      if (!t) return;
      openEditModal(t.id, t.table_no, t.title, t.section, t.is_required, t.is_locked, t.columns_json);
    }

    let originalEditFormat = null;
    const editFormatSnapshot = () => JSON.stringify([...document.querySelectorAll('#editTitlesContainer input, #editTitlesContainer select')].map(el => [el.className, el.value]));
    function openEditModal(id, tableNo, title, section, required, locked, colsJson) {
      document.getElementById('editTableId').value         = id;
      document.getElementById('editTableNo').value         = tableNo;
      document.getElementById('editTableSection').value    = section;
      document.getElementById('editTableRequired').checked = !!required;
      document.getElementById('editTableLocked').checked   = !!locked;

      const container = document.getElementById('editTitlesContainer');
      if (container) container.innerHTML = '';

      let parsed = colsJson;
      if (typeof parsed === 'string') {
        try { parsed = JSON.parse(parsed); } catch(e){}
      }
      if (typeof parsed === 'string') {
        try { parsed = JSON.parse(parsed); } catch(e){}
      }

      if (parsed && typeof parsed === 'object' && Array.isArray(parsed.titles) && parsed.titles.length > 0) {
        parsed.titles.forEach(t => addEditTitleBlock(t.title, t.columns));
      } else if (parsed && typeof parsed === 'object' && Array.isArray(parsed.categories) && parsed.categories.length > 0) {
        parsed.categories.forEach(cat => addEditTitleBlock(cat, parsed.columns || []));
      } else if (Array.isArray(parsed) && parsed.length > 0) {
        if (parsed[0] && typeof parsed[0] === 'object' && parsed[0].title) {
          parsed.forEach(t => addEditTitleBlock(t.title, t.columns));
        } else if (title && title.includes(' / ')) {
          const parts = title.split(' / ');
          parts.forEach(p => addEditTitleBlock(p.trim(), parsed));
        } else {
          addEditTitleBlock(title, parsed);
        }
      } else {
        if (title && title.includes(' / ')) {
          const parts = title.split(' / ');
          parts.forEach(p => addEditTitleBlock(p.trim(), []));
        } else {
          addEditTitleBlock(title, []);
        }
      }

      originalEditFormat = { title, columns: colsJson, snapshot: editFormatSnapshot(), year: fmtCurrentYear };
      openModal('modalEditTable');
    }

    async function submitEditTable() {
      const tableNo = (document.getElementById('editTableNo').value || '').trim();
      const section = document.getElementById('editTableSection').value;
      const req = document.getElementById('editTableRequired').checked ? 1 : 0;
      const lck = document.getElementById('editTableLocked').checked ? 1 : 0;

      if (!tableNo) {
        showToast('Table No. is required.');
        return;
      }

      const container = document.getElementById('editTitlesContainer');
      const titleBlocks = container.querySelectorAll('.tbl-title-block');
      const titlesData = [];

      titleBlocks.forEach(b => {
        const tName = (b.querySelector('.tbl-title-val')?.value || '').trim();
        const colRows = b.querySelectorAll('.title-col-list .col-row');
        const cols = [...colRows].map(r => {
          const name = (r.querySelector('.title-col-name')?.value || '').trim();
          const type = r.querySelector('.title-col-type')?.value || 'text';
          const colObj = { name, type };
          if (type === 'dropdown') {
            const optInputs = r.querySelectorAll('.dropdown-options-list .dropdown-opt-val');
            let opts = [...optInputs].map(i => (i.value || '').trim()).filter(Boolean);
            if (!opts.length) {
              const rawOpts = (r.querySelector('.title-col-options')?.value || '').trim();
              if (rawOpts) opts = rawOpts.split(',').map(s => s.trim()).filter(Boolean);
            }
            colObj.options = opts;
          }
          return colObj;
        }).filter(c => c.name);

        if (tName) {
          titlesData.push({ title: tName, columns: cols });
        }
      });

      if (titlesData.length === 0) {
        showToast('At least one Title is required.');
        return;
      }

      let mainTitle = '';
      let colsData = null;

      if (titlesData.length === 1) {
        mainTitle = titlesData[0].title;
        colsData = JSON.stringify({
          titles: titlesData,
          categories: [titlesData[0].title],
          columns: titlesData[0].columns
        });
      } else {
        mainTitle = titlesData.map(t => t.title).join(' / ');
        colsData = JSON.stringify({
          titles: titlesData,
          categories: titlesData.map(t => t.title),
          columns: titlesData[0].columns
        });
      }

      if (originalEditFormat && originalEditFormat.snapshot === editFormatSnapshot()) {
        mainTitle = originalEditFormat.title;
        colsData = originalEditFormat.columns;
      } else if (!titlesData.some(t => t.columns.length)) {
        colsData = null;
      }
      const payload = {
        action: 'edit',
        year: originalEditFormat?.year || fmtCurrentYear,
        id: parseInt(document.getElementById('editTableId').value),
        table_no: tableNo,
        title: mainTitle,
        section: section,
        is_required: req,
        is_locked: lck,
        columns_json: colsData
      };

      const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
      const json = await res.json();
      if (json.ok) { closeModal('modalEditTable'); showToast(json.message); loadFormats(fmtCurrentYear); }
      else showToast(json.message);
    }

    // ── Toggle Lock ───────────────────────────────────────────────────
    async function toggleLock(id) {
      try {
        const res  = await fetch(API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'toggle_lock', id, year: fmtCurrentYear })
        });
        const json = await res.json();
        if (json.ok) {
          showToast(json.message);
          loadFormats(fmtCurrentYear);
        } else {
          showToast(json.message || 'Could not update lock.');
        }
      } catch (e) {
        showToast('Failed to update lock.');
      }
    }

    // ── Delete Table ──────────────────────────────────────────────────
    async function deleteTable(id, tableNo) {
      if (!await AppDialog.confirm(`Remove ${tableNo} from this year's format? This cannot be undone.`, { title: 'Remove table', confirmText: 'Remove', destructive: true })) return;
      const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'delete',id}) });
      const json = await res.json();
      showToast(json.message);
      if (json.ok) loadFormats(fmtCurrentYear);
    }

    // ── Blank Year ────────────────────────────────────────────────────
    function openBlankYearModal() {
      document.getElementById('blankYearWarning').style.display = 'none';
      document.getElementById('blankYearClearConfirm').checked = false;
      openModal('modalBlankYear');
    }
    async function submitBlankYear() {
      const year = parseInt(document.getElementById('blankYearInput').value);
      if (!year || year < 2020) { showToast('Please enter a valid reporting year.'); return; }
      const clearExisting = document.getElementById('blankYearClearConfirm').checked;

      const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create_blank_year', year, clear_existing: clearExisting })
      });
      const json = await res.json();
      if (json.ok) {
        closeModal('modalBlankYear');
        showToast(json.message);
        loadFormats(year);
      } else if (json.exists) {
        document.getElementById('blankYearWarnText').textContent = json.message;
        document.getElementById('blankYearWarning').style.display = 'block';
      } else {
        showToast(json.message || 'Failed to create year.');
      }
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
      if (!cachedTemplates || cachedTemplates.length === 0) {
        showToast(`Cannot activate CY ${year} because it has 0 tables. Add at least one table template first.`);
        return;
      }
      document.getElementById('activateYearLabel').textContent  = year;
      document.getElementById('activateYearLabel2').textContent = year;
      document.getElementById('activateYearVal').value          = year;
      openModal('modalActivate');
    }
    async function submitActivate() {
      const year = parseInt(document.getElementById('activateYearVal').value);
      if (!cachedTemplates || cachedTemplates.length === 0) {
        showToast(`Cannot activate CY ${year} because it has 0 tables.`);
        return;
      }
      const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'activate', year}) });
      const json = await res.json();
      if (json.ok) { closeModal('modalActivate'); showToast(json.message); loadFormats(year); }
      else showToast(json.message);
    }

    const searchFmt = document.getElementById('fmtSearchInput');
    if (searchFmt) searchFmt.addEventListener('input', renderFormatsTable);
    const secFmt = document.getElementById('fmtSectionFilter');
    if (secFmt) secFmt.addEventListener('change', renderFormatsTable);

    document.addEventListener('DOMContentLoaded', () => {
      const urlYear = parseInt(new URLSearchParams(window.location.search).get('year'));
      const storedYear = parseInt(localStorage.getItem('secreco_selected_year'));
      const initialYear = urlYear || storedYear || null;
      loadFormats(initialYear);
    });
    </script>
    @endsection
