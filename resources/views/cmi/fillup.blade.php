@extends('layouts.cmi')

@section('styles')
<link rel="stylesheet" href="/assets/css/cmi/fillup.css?v=3"/>
<link rel="stylesheet" href="/assets/css/cmi/fillup-additions.css?v=3"/>
<style>
/* ── Modern Fillup Layout Fixes ── */
.fill-layout { display: flex; gap: 24px; margin-top: 20px; align-items: flex-start; }
@media (max-width: 900px) { .fill-layout { flex-direction: column; } }
.fill-nav { width: 320px; flex-shrink: 0; background: #ffffff; border-radius: 16px; border: 1px solid #f0f0f0; box-shadow: 0 2px 12px rgba(16,185,129,.05); padding: 18px; max-height: calc(100vh - 140px); overflow-y: auto; }
.fill-body { flex: 1; min-width: 0; }
.btn-submit-report { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; border-radius: 12px; padding: 10px 22px; font-size: 13.5px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,.35); transition: all .2s; }
.btn-submit-report:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.45); }
</style>
@endsection

@section('content')
<div class="page active" id="page-fillup">

  <div class="page-hdr" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
      <div class="page-title">Fill Up Report</div>
      <div class="page-sub" id="fillupSubtitle">CY {{ date('Y') }} Annual Accomplishment Report &amp; All Sections &amp; Tables</div>
    </div>
    <div class="page-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:8px">
        <label style="font-size:13px;font-weight:600;color:#374151">Year:</label>
        <select id="fillupYearSel" style="border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;font-size:13px;color:#374151;background:#fff;cursor:pointer;outline:none;">
          @for($y = date('Y'); $y >= 2020; $y--)
            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>CY {{ $y }}</option>
          @endfor
        </select>
      </div>
      <button class="btn-submit-report" id="btn-save-draft" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
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

  <div class="fill-layout">
    <div class="fill-nav" id="fillNav"></div>
    <div class="fill-body" id="fillBody"></div>
  </div>

</div>
@endsection

@section('scripts')
<script>
  window.CMI_AGENCY_NAME = @json(Auth::user()?->institution ?? session('user_inst') ?? '');
  window.CMI_REPORTING_YEAR = {{ date('Y') }};
</script>

<script>
// Year selector logic — runs after fillup-core.js loads
document.addEventListener('DOMContentLoaded', function() {
  const yearSel  = document.getElementById('fillupYearSel');
  const subtitle = document.getElementById('fillupSubtitle');
  const saveDraftBtn = document.getElementById('btn-save-draft');

  if (yearSel) {
    yearSel.addEventListener('change', function() {
      window.CMI_REPORTING_YEAR = parseInt(this.value);
      if (subtitle) subtitle.textContent = 'CY ' + this.value + ' Annual Accomplishment Report — All Sections & Tables';
      // Reload statuses for selected year then re-show current table
      fetch('/api/cmi/tables/statuses?year=' + this.value)
        .then(r => r.json())
        .then(data => {
          if (data && data.statuses) CMI.setStatuses(data.statuses);
          const active = window._cmiActiveTable || 'T1';
          CMI.showTable(active);
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

<!-- Core engine first, then per-table scripts -->
<script src="/assets/js/cmi/table-utils.js?v=3"></script>
<script src="/assets/js/cmi/fillup-core.js?v=3"></script>
<script src="/assets/js/cmi/fillup-docs-modal.js?v=3"></script>

<!-- Section 1: R&D Mgt. & Coord. -->
<script src="/assets/js/cmi/tables/t1_aihrs.js?v=3"></script>
<script src="/assets/js/cmi/tables/t2a_rsrdh_summary.js?v=3"></script>
<script src="/assets/js/cmi/tables/t2b_rsrdh_participants.js?v=3"></script>
<script src="/assets/js/cmi/tables/t3_projects_monitored.js?v=3"></script>
<script src="/assets/js/cmi/tables/t4_resources_shared.js?v=3"></script>
<script src="/assets/js/cmi/tables/t5_resources_generated.js?v=3"></script>
<script src="/assets/js/cmi/tables/t6_linkages.js?v=3"></script>
<script src="/assets/js/cmi/tables/t7a_databases.js?v=3"></script>
<script src="/assets/js/cmi/tables/t7b_infosystems.js?v=3"></script>

<!-- Section 2: Strategic R&D -->
<script src="/assets/js/cmi/tables/t8a_rd_projects.js?v=3"></script>
<script src="/assets/js/cmi/tables/t8b_collaborative_rd.js?v=3"></script>
<script src="/assets/js/cmi/tables/t9_technologies_generated.js?v=3"></script>

<!-- Section 3: Results Utilization -->
<script src="/assets/js/cmi/tables/t10_tt_projects.js?v=3"></script>
<script src="/assets/js/cmi/tables/t11_technologies_extended.js?v=3"></script>
<script src="/assets/js/cmi/tables/t12_commercialized.js?v=3"></script>
<script src="/assets/js/cmi/tables/t13_tech_promotion.js?v=3"></script>

<!-- Section 4: Capability & Gov. -->
<script src="/assets/js/cmi/tables/t14_trainings.js?v=3"></script>
<script src="/assets/js/cmi/tables/t15_equipment_facilities.js?v=3"></script>
<script src="/assets/js/cmi/tables/t16_awards.js?v=3"></script>
<script src="/assets/js/cmi/tables/t17_meetings.js?v=3"></script>
<script src="/assets/js/cmi/tables/t18_cmi_contributions.js?v=3"></script>
<script src="/assets/js/cmi/tables/t19_governance.js?v=3"></script>

<!-- Section 5: Policy Analysis -->
<script src="/assets/js/cmi/tables/t20a_policy_researches.js?v=3"></script>
<script src="/assets/js/cmi/tables/t20b_policies.js?v=3"></script>
@endsection
