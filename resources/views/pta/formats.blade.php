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
              <label for="editTableLocked" class="form-label" style="margin:0;cursor:pointer;color:#92400e;font-weight:700">🔒 Lock for CMI (PTA Only)</label>
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
                  <button class="btn-sm-edit" onclick='openEditModal(${t.id}, ${JSON.stringify(t.table_no)}, ${JSON.stringify(t.title)}, ${JSON.stringify(t.section)}, ${t.is_required}, ${t.is_locked ? 1 : 0}, ${JSON.stringify(colsJson)})'>
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
    async function loadFormats(year) {
      fmtCurrentYear = year;
      const res  = await fetch(`/api/pta/formats?year=${year}`);
      const json = await res.json();
      const templates = json.templates || [];
      cachedTemplates = templates;
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
      let btns = `<button class="btn-outline-fc" onclick="openAddModal(${year})">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>+ Add Table</button>`;
      if (fmtYearStatus === 'draft') {
        btns += `<button class="btn-primary-fc" onclick="confirmActivate(${year})">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Activate CY ${year}</button>`;
      }
      actions.innerHTML = btns;

      renderFormatsTable();
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
            <input type="checkbox" class="add-tbl-locked" style="accent-color:#d97706" ${initialData.is_locked ? 'checked' : ''}/> 🔒 Lock for CMI (PTA Only)
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
          appendColRow(colList, name, type);
        });
      } else {
        appendColRow(colList, '', 'text');
      }

      return block;
    }

    function appendColRow(colList, colName = '', colType = 'text') {
      const row = document.createElement('div');
      row.className = 'col-row';
      row.style.cssText = 'display:flex;gap:6px;align-items:center;margin-bottom:2px';
      row.innerHTML = `
        <input class="form-input title-col-name" value="${colName}" placeholder="Column name (e.g. Agency, Project Title, Date)" style="flex:1;padding:6px 10px;font-size:12.5px"/>
        <select class="form-select title-col-type" style="width:100px;padding:6px 8px;font-size:12px">
          <option value="text" ${colType === 'text' ? 'selected' : ''}>Text</option>
          <option value="number" ${colType === 'number' ? 'selected' : ''}>Number</option>
          <option value="date" ${colType === 'date' ? 'selected' : ''}>Date</option>
        </select>
        <button type="button" onclick="this.closest('.col-row').remove()" style="background:none;border:none;color:#ef4444;font-size:20px;cursor:pointer;padding:0 4px;line-height:1">×</button>
      `;
      colList.appendChild(row);
      if (!colName) {
        row.querySelector('.title-col-name')?.focus();
      }
    }

    function addColToTitleBlock(btn) {
      const block = btn.closest('.tbl-title-block');
      if (!block) return;
      const colList = block.querySelector('.title-col-list');
      if (colList) appendColRow(colList, '', 'text');
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
          const cols = [...colRows].map(r => ({
            name: (r.querySelector('.title-col-name')?.value || '').trim(),
            type: r.querySelector('.title-col-type')?.value || 'text'
          })).filter(c => c.name);

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
          colsData = titlesData[0].columns.length > 0 ? JSON.stringify(titlesData[0].columns) : null;
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
    function openEditModal(id, tableNo, title, section, required, locked, colsJson) {
      document.getElementById('editTableId').value         = id;
      document.getElementById('editTableNo').value         = tableNo;
      document.getElementById('editTableSection').value    = section;
      document.getElementById('editTableRequired').checked = !!required;
      document.getElementById('editTableLocked').checked   = !!locked;

      const container = document.getElementById('editTitlesContainer');
      if (container) container.innerHTML = '';

      let parsed = null;
      try {
        parsed = (typeof colsJson === 'string') ? JSON.parse(colsJson) : colsJson;
        if (typeof parsed === 'string') parsed = JSON.parse(parsed);
      } catch(e){}

      if (parsed && typeof parsed === 'object' && Array.isArray(parsed.titles) && parsed.titles.length > 0) {
        parsed.titles.forEach(t => addEditTitleBlock(t.title, t.columns));
      } else if (parsed && typeof parsed === 'object' && Array.isArray(parsed.categories) && parsed.categories.length > 0) {
        parsed.categories.forEach(cat => addEditTitleBlock(cat, parsed.columns || []));
      } else if (Array.isArray(parsed)) {
        addEditTitleBlock(title, parsed);
      } else {
        addEditTitleBlock(title, []);
      }

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
        const cols = [...colRows].map(r => ({
          name: (r.querySelector('.title-col-name')?.value || '').trim(),
          type: r.querySelector('.title-col-type')?.value || 'text'
        })).filter(c => c.name);

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
        colsData = titlesData[0].columns.length > 0 ? JSON.stringify(titlesData[0].columns) : null;
      } else {
        mainTitle = titlesData.map(t => t.title).join(' / ');
        colsData = JSON.stringify({
          titles: titlesData,
          categories: titlesData.map(t => t.title),
          columns: titlesData[0].columns
        });
      }

      const payload = {
        action: 'edit',
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
          body: JSON.stringify({ action: 'toggle_lock', id })
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

    const searchFmt = document.getElementById('fmtSearchInput');
    if (searchFmt) searchFmt.addEventListener('input', renderFormatsTable);
    const secFmt = document.getElementById('fmtSectionFilter');
    if (secFmt) secFmt.addEventListener('change', renderFormatsTable);

    document.addEventListener('DOMContentLoaded', () => loadFormats({{ date('Y') }}));
    </script>
    @endsection
