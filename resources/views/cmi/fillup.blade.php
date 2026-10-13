  @extends(Auth::user()?->role === 'pta' ? 'layouts.pta' : 'layouts.cmi')

  @section('styles')
  <link rel="stylesheet" href="/assets/css/cmi/fillup.css?v=3"/>
  <link rel="stylesheet" href="/assets/css/cmi/fillup-additions.css?v=6"/>
  <style>
  /* ── Modern Fillup Layout Fixes ── */
  .fill-layout { display: flex; gap: 24px; margin-top: 20px; align-items: flex-start; }
  @media (max-width: 900px) { .fill-layout { flex-direction: column; } }
  .fill-nav-wrapper {
    width: 320px;
    flex-shrink: 0;
    position: sticky;
    top: 20px;
  }
  @media (max-width: 900px) {
    .fill-nav-wrapper {
      width: 100%;
      position: relative;
      top: 0;
    }
  }
  .fill-nav {
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 12px rgba(16,185,129,.05);
    padding: 18px 18px 64px;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
    scroll-behavior: smooth;
    box-sizing: border-box;
  }
  .fill-body { flex: 1; min-width: 0; background: #ffffff; border-radius: 16px; border: 1px solid #f0f0f0; box-shadow: 0 2px 12px rgba(16,185,129,.05); padding: 24px; max-height: calc(100vh - 160px); overflow-y: auto; }
  .btn-submit-report { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; border-radius: 12px; padding: 10px 22px; font-size: 13.5px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,.35); transition: all .2s; }
  .btn-submit-report:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.45); }

  /* ── Sidebar Scroll-To-Top Arrow Button ── */
  .fill-nav-scroll-top {
    position: absolute;
    bottom: 14px;
    right: 14px;
    z-index: 40;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.45);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateY(8px) scale(0.85);
    transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s, box-shadow 0.2s;
    outline: none;
  }
  .fill-nav-scroll-top.visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(0) scale(1);
  }
  .fill-nav-scroll-top:hover {
    background: linear-gradient(135deg, #059669, #047857);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.6);
    transform: translateY(-2px) scale(1.08);
  }
  .fill-nav-scroll-top:active {
    transform: translateY(0) scale(0.94);
  }

  /* ── Table List Collapse Arrow ── */
  .fill-nav-wrapper { transition: width .22s cubic-bezier(.23,1,.32,1); }
  .fill-nav-toggle {
    position: absolute;
    top: 16px;
    right: -14px;
    z-index: 41;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    color: #059669;
    border: 1.5px solid #a7f3d0;
    box-shadow: 0 3px 10px rgba(16,185,129,.22);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background .15s, color .15s, transform .15s;
  }
  .fill-nav-toggle:hover { background: #10b981; color: #ffffff; transform: scale(1.08); }
  .fill-nav-toggle svg { transition: transform .22s cubic-bezier(.23,1,.32,1); }

  .fill-layout.fn-collapsed .fill-nav-wrapper { width: 92px; }
  .fill-layout.fn-collapsed .fill-nav { padding: 12px 8px; }
  .fill-layout.fn-collapsed .fill-nav-toggle svg { transform: rotate(180deg); }
  .fill-layout.fn-collapsed .fn-title,
  .fill-layout.fn-collapsed .fn-sec-label { display: none; }
  .fill-layout.fn-collapsed .fill-nav-item {
    justify-content: center;
    padding: 6px 0;
    margin-top: 10px;
  }
  .fill-layout.fn-collapsed .fill-nav-item svg { margin-right: 0 !important; }
  .fill-layout.fn-collapsed .fill-nav-sub {
    justify-content: center;
    padding: 7px 4px;
  }
  .fill-layout.fn-collapsed .fn-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 700;
  }
  .fill-layout.fn-collapsed .fill-nav-sub > span:not(.fn-label) { display: none !important; } /* lock badge */

  @media (max-width: 900px) {
    .fill-nav-toggle { display: none; }
    .fill-layout.fn-collapsed .fill-nav-wrapper { width: 100%; }
  }
  </style>
  @endsection

  @section('content')
  <div class="page active" id="page-fillup">

    <div class="page-hdr" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <div>
        <div class="page-title">Fill Out Report</div>
        <div class="page-sub" id="fillupSubtitle">CY {{ date('Y') }} Annual Accomplishment Report &amp; All Sections &amp; Tables</div>
      </div>
      <div class="page-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px">
          <label style="font-size:13px;font-weight:600;color:#374151">Year:</label>
          <select id="fillupYearSel" style="border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;font-size:13px;color:#374151;background:#fff;cursor:pointer;outline:none;">
            <option value="">Loading...</option>
          </select>
        </div>
        <button class="btn-submit-report" id="btn-save-draft" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
          Save Draft
        </button>
        <button class="btn-submit-report" id="btn-submit">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Submit Reports
        </button>
      </div>
    </div>

    <div class="fill-layout fn-collapsed">
      <div class="fill-nav-wrapper">
        <button type="button" id="fillNavToggle" class="fill-nav-toggle" title="Collapse table list" aria-label="Collapse table list">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <div class="fill-nav" id="fillNav"></div>
        <button type="button" id="fillNavScrollTop" class="fill-nav-scroll-top" title="Scroll to top" aria-label="Scroll tables to top">
          <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
          </svg>
        </button>
      </div>
      <div class="fill-body" id="fillBody"></div>
    </div>

  </div>

  <!-- ═══ CONFIRM SUBMIT MODAL ═══ -->
  <div class="modal-overlay" id="modalConfirmSubmit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:9000;align-items:center;justify-content:center;">
    <div class="modal-box" style="background:#fff;border-radius:18px;padding:28px 32px;width:100%;max-width:460px;box-shadow:0 20px 60px rgba(0,0,0,.18);">
      <div class="modal-title" style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;display:flex;align-items:center;gap:10px;">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        Submit Annual Accomplishment Report?
      </div>
      <div class="modal-desc" style="font-size:13.5px;color:#4b5563;line-height:1.5;margin-bottom:24px;">
        Are you sure you want to submit your <strong id="confirmSubmitYearText">CY {{ date('Y') }}</strong> annual accomplishment report?
        <br><br>
        Once submitted, your report will be sent to the PTA for review and editing will be locked for this year.
      </div>
      <div class="modal-actions" style="display:flex;justify-content:flex-end;gap:12px;">
        <button class="btn-cancel" onclick="closeSubmitModal()" style="background:#f3f4f6;color:#374151;border:none;border-radius:10px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
        <button class="btn-submit-report" onclick="confirmAndExecuteSubmit()" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(16,185,129,.3);">
          Yes, Submit Report
        </button>
      </div>
    </div>
  </div>
  @endsection

  @section('scripts')
  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const paramYear = urlParams.has('year') ? parseInt(urlParams.get('year')) : null;
    const paramCmiUser = parseInt(urlParams.get('cmi_user_id')) || {{ $targetUserId ?? 0 }};

    window.CMI_AGENCY_NAME = @json($userInst ?? session('user_inst') ?? '');
    window.CMI_REPORTING_YEAR = paramYear;
    window.CMI_TARGET_USER_ID = paramCmiUser;
  </script>

  <script>
  // Year selector logic — runs after fillup-core.js loads
  document.addEventListener('DOMContentLoaded', function() {
    const yearSel  = document.getElementById('fillupYearSel');
    const subtitle = document.getElementById('fillupSubtitle');
    const saveDraftBtn = document.getElementById('btn-save-draft');
    const params   = new URLSearchParams(window.location.search);

    function normalizeTableKey(str) {
      if (!str) return 'T1';
      const s = String(str).trim();
      const m = s.match(/^(T\d+)([a-zA-Z])?$/i);
      if (m) {
        return m[1].toUpperCase() + (m[2] ? m[2].toLowerCase() : '');
      }
      return s;
    }
    const rawReqTable = params.get('table') || params.get('t') || '';
    const requestedTable = rawReqTable ? normalizeTableKey(rawReqTable) : '';

    if (yearSel) {
      // Load format years from Manage Formats
      fetch('/api/formats')
        .then(r => r.json())
        .then(data => {
          if (data && data.years && Array.isArray(data.years) && data.years.length > 0) {
            const urlHasYear = params.has('year');
            const activeYr = (urlHasYear && params.get('year'))
              ? parseInt(params.get('year'))
              : (data.active_year || data.years[0] || new Date().getFullYear());

            window.CMI_REPORTING_YEAR = activeYr;
            yearSel.innerHTML = data.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
            yearSel.value = activeYr;
            if (subtitle) subtitle.textContent = 'CY ' + activeYr + ' Annual Accomplishment Report — All Sections & Tables';

            // Fetch format templates and statuses sequentially for active year before rendering table
            fetch('/api/formats?year=' + activeYr)
              .then(r => r.json())
              .then(fmtData => {
                if (fmtData && Array.isArray(fmtData.templates) && typeof CMI !== 'undefined') {
                  CMI.setFormatTemplates(fmtData.templates);
                }
                const firstTemplateNo = (fmtData && Array.isArray(fmtData.templates) && fmtData.templates.length > 0 && fmtData.templates[0].table_no)
                  ? fmtData.templates[0].table_no
                  : 'T1';
                const targetTable = requestedTable || firstTemplateNo;

                const cmiUserId = params.get('cmi_user_id') || '';
                const cmiQuery = cmiUserId ? '&cmi_user_id=' + encodeURIComponent(cmiUserId) : '';
                return fetch('/api/cmi/tables/statuses?year=' + activeYr + cmiQuery)
                  .then(r => r.json())
                  .then(stData => ({ fmtData, stData, targetTable }));
              })
              .then(({ fmtData, stData, targetTable }) => {
                if (stData && typeof CMI !== 'undefined') {
                  CMI.setStatuses(stData.statuses || {}, stData);
                  CMI.showTable(targetTable);
                }
              }).catch(() => {
                if (typeof CMI !== 'undefined' && typeof CMI.showTable === 'function') {
                  CMI.showTable(requestedTable || 'T1');
                }
              });
          }
        }).catch(() => {});

      yearSel.addEventListener('change', function() {
        const selectedYear = parseInt(this.value);
        window.CMI_REPORTING_YEAR = selectedYear;
        if (subtitle) subtitle.textContent = 'CY ' + selectedYear + ' Annual Accomplishment Report — All Sections & Tables';
        
        const body = document.getElementById('fillBody');
        if (body) body.innerHTML = '<div style="padding:32px;text-align:center;color:#9ca3af">Loading CY ' + selectedYear + ' data...</div>';

        // Reload format templates and statuses for selected year then re-show appropriate table
        fetch('/api/formats?year=' + selectedYear)
          .then(r => r.json())
          .then(fmtData => {
            if (fmtData && Array.isArray(fmtData.templates) && typeof CMI !== 'undefined') {
              CMI.setFormatTemplates(fmtData.templates);
            }
            const cmiUserId = params.get('cmi_user_id') || '';
            const cmiQuery = cmiUserId ? '&cmi_user_id=' + encodeURIComponent(cmiUserId) : '';
            return fetch('/api/cmi/tables/statuses?year=' + selectedYear + cmiQuery)
              .then(r => r.json())
              .then(stData => ({ fmtData, stData }));
          })
          .then(({ fmtData, stData }) => {
            if (typeof CMI !== 'undefined') {
              CMI.setStatuses(stData ? stData.statuses : {}, stData);
              const tmpls = (fmtData && Array.isArray(fmtData.templates)) ? fmtData.templates : [];
              const curActive = window._cmiActiveTable;
              let target = curActive;
              const existsInNewYear = tmpls.some(t => 
                String(t.table_no).toLowerCase() === String(curActive).toLowerCase() ||
                normalizeTableKey(t.table_no).toLowerCase() === normalizeTableKey(curActive).toLowerCase()
              );
              if (!existsInNewYear && tmpls.length > 0) {
                target = tmpls[0].table_no;
              }
              target = target || (tmpls[0] ? tmpls[0].table_no : 'T1');
              if (typeof CMI.showTable === 'function') CMI.showTable(target);
            }
          })
          .catch(() => {});
      });
    }

    // Save Draft button — saves the current table with status = draft
    if (saveDraftBtn) {
      saveDraftBtn.addEventListener('click', function() {
        if (typeof CMI !== 'undefined' && typeof CMI.saveDraft === 'function') {
          CMI.saveDraft();
        } else {
          // Trigger save via the active table's save button if exposed
          const activeBtn = document.querySelector('#fillBody button[data-action="save"]');
          if (activeBtn) {
            activeBtn.click();
          } else {
            showToast('Use the Save button inside the table to save your draft.');
          }
        }
      });
    }
  });
  </script>

  <script>
  // Sidebar scroll-to-top arrow button logic
  document.addEventListener('DOMContentLoaded', function() {
    const fillNav = document.getElementById('fillNav');
    const scrollBtn = document.getElementById('fillNavScrollTop');
    if (!fillNav || !scrollBtn) return;

    function handleNavScroll() {
      if (fillNav.scrollTop > 45) {
        scrollBtn.classList.add('visible');
      } else {
        scrollBtn.classList.remove('visible');
      }
    }

    fillNav.addEventListener('scroll', handleNavScroll, { passive: true });

    scrollBtn.addEventListener('click', function(e) {
      e.preventDefault();
      fillNav.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // Table list collapse arrow (remembers state)
  document.addEventListener('DOMContentLoaded', function() {
    const layout = document.querySelector('.fill-layout');
    const toggle = document.getElementById('fillNavToggle');
    if (!layout || !toggle) return;

    function apply(collapsed) {
      layout.classList.toggle('fn-collapsed', collapsed);
      const label = collapsed ? 'Expand table list' : 'Collapse table list';
      toggle.title = label;
      toggle.setAttribute('aria-label', label);
      toggle.setAttribute('aria-expanded', String(!collapsed));
    }

    // Always start collapsed on Fill Out (more room for the tables);
    // the arrow expands it for the current visit only.
    apply(true);

    toggle.addEventListener('click', function() {
      apply(!layout.classList.contains('fn-collapsed'));
    });
  });

  // Remove the empty delete (🗑) column from the layout.
  // The delete button then floats at the row's right edge on hover (see fillup-additions.css).
  document.addEventListener('DOMContentLoaded', function() {
    const body = document.getElementById('fillBody');
    if (!body) return;

    const span = (c) => parseInt(c.getAttribute('colspan') || '1', 10) || 1;
    const hasControls = (el) => !!el.querySelector('input, textarea, select');

    function compact(table) {
      const headRow = table.querySelector('thead tr');
      if (!headRow) return;
      const lastTh = headRow.lastElementChild;
      if (!lastTh || lastTh.tagName !== 'TH' || span(lastTh) !== 1 || lastTh.textContent.trim() !== '') return;

      lastTh.classList.add('rm-col');
      const totalCols = [...headRow.children].reduce((n, c) => n + span(c), 0);
      const visibleCols = totalCols - 1;

      table.querySelectorAll('tbody tr, tfoot tr').forEach((tr) => {
        const last = tr.lastElementChild;
        if (!last) return;
        const isRm = last.tagName === 'TD' && span(last) === 1 && !hasControls(last) &&
          (last.querySelector('.row-remove-btn') || last.textContent.trim() === '');
        const cells = [...tr.children];
        const rowSpan = cells.reduce((n, c) => n + span(c), 0);

        if (isRm && rowSpan === totalCols) {
          last.classList.add('rm-cell');
        } else if (!isRm && rowSpan > visibleCols) {
          // e.g. section headings with colspan = all columns incl. the delete column
          const excess = rowSpan - visibleCols;
          last.setAttribute('colspan', String(Math.max(1, span(last) - excess)));
        }
      });
    }

    function run() { body.querySelectorAll('table.merged').forEach(compact); }

    let pending = false;
    new MutationObserver(() => {
      if (pending) return;
      pending = true;
      requestAnimationFrame(() => { pending = false; run(); });
    }).observe(body, { childList: true, subtree: true });

    run();
  });
  </script>

  <script>
    window.IS_PTA_USER = {{ Auth::user()?->role === 'pta' ? 'true' : 'false' }};
    window.CURRENT_USER_ROLE = '{{ Auth::user()?->role }}';
    window.TARGET_CMI_USER_ID = {{ (int) request('cmi_user_id', 0) }};
  </script>

  <script src="/assets/js/cmi/category-actions.js?v=1"></script>
  <!-- Core engine first, then per-table scripts -->
  <script src="/assets/js/xlsx.full.min.js"></script>
  <script src="/assets/js/cmi/table-utils.js?v=10"></script>
  <script src="/assets/js/cmi/cmi-excel.js?v=20261012"></script>
  <script src="/assets/js/cmi/fillup-core.js?v=20261012"></script>
  <script src="/assets/js/cmi/fillup-docs-modal.js?v=20261012"></script>

  <!-- Section 1: R&D Mgt. & Coord. -->
  <script src="/assets/js/cmi/tables/t1_aihrs.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t2a_rsrdh_summary.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t2b_rsrdh_participants.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t3_projects_monitored.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t4_resources_shared.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t5_resources_generated.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t6_linkages.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t7a_databases.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t7b_infosystems.js?v=20261012"></script>

  <!-- Section 2: Strategic R&D -->
  <script src="/assets/js/cmi/tables/t8a_rd_projects.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t8b_collaborative_rd.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t9_technologies_generated.js?v=20261012"></script>

  <!-- Section 3: Results Utilization -->
  <script src="/assets/js/cmi/tables/t10_tt_projects.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t11_technologies_extended.js?v=20261012b"></script>
  <script src="/assets/js/cmi/tables/t12_commercialized.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t13_tech_promotion.js?v=20261012"></script>

  <!-- Section 4: Capability & Gov. -->
  <script src="/assets/js/cmi/tables/t14_trainings.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t15_equipment_facilities.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t16_awards.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t17_meetings.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t18_cmi_contributions.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t19_governance.js?v=20261012"></script>

  <!-- Section 5: Policy Analysis -->
  <script src="/assets/js/cmi/tables/t20a_policy_researches.js?v=20261012"></script>
  <script src="/assets/js/cmi/tables/t20b_policies.js?v=20261012"></script>
  @endsection
