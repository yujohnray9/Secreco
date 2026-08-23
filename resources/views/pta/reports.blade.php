@extends('layouts.pta')

@section('styles')
<link rel="stylesheet" href="/assets/css/pta/reports.css"/>
<style>
/* ── Modern FreshCart adjustments for Reports ── */
.page-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.page-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.page-sub { font-size:13px; color:#6b7280; margin-top:3px; }

.rpt-filter-row { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:24px; }
.toggle-group { display:flex; background:#eef7ee; border-radius:8px; padding:3px; gap:3px; border:1px solid #c8e6c9; }
.toggle-opt { padding:7px 18px; border-radius:6px; font-size:12.5px; font-weight:600; cursor:pointer; color:#2e7d32; transition:all .2s; user-select:none; }
.toggle-opt.active { background:#2d6a30; color:#fff; shadow:0 2px 4px rgba(0,0,0,.1); }

.filter-select { border:1px solid #d1d5db; border-radius:8px; padding:7px 14px; font-size:13px; color:#374151; background:#fff; cursor:pointer; outline:none; }
.filter-select:focus { border-color:#2d6a30; box-shadow:0 0 0 3px rgba(45,106,48,.12); }

.card { background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 2px 12px rgba(0,0,0,.04); overflow:hidden; margin-bottom:24px; }
.card-hdr { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid #f3f4f6; }
.card-title { font-size:15px; font-weight:700; color:#111827; }
.tbl-wrap { overflow-x:auto; }

.btn { display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; transition:all .2s; }
.btn-sm { padding:6px 14px; font-size:12.5px; }
.btn-yellow { background:#f59e0b; color:#fff; }
.btn-yellow:hover { background:#d97706; }
.btn-primary { background:#2d6a30; color:#fff; }
.btn-primary:hover { background:#235425; }

.word-preview { background:#fff; border:1px solid #e0e0e0; border-radius:10px; padding:40px 48px; font-family:'Calibri',Arial,sans-serif; font-size:13px; color:#1a1a1a; max-width:780px; margin:0 auto; box-shadow:0 4px 24px rgba(0,0,0,.08); }
</style>
@endsection

@section('content')
<div class="page active" id="page-reports">

  <!-- PAGE HEADER -->
  <div class="page-hdr">
    <div>
      <div class="page-title">Consolidated Reports</div>
      <div class="page-sub" id="rptSubtitle">Consolidated Annual Accomplishment Report</div>
    </div>
  </div>

  <!-- FILTERS ROW -->
  <div class="rpt-filter-row">
    <div class="toggle-group">
      <div class="toggle-opt active" id="togPerTable" onclick="switchReportView('table')" style="display:flex;align-items:center;gap:6px">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Per Table
      </div>
      <div class="toggle-opt" id="togPerCMI" onclick="switchReportView('cmi')" style="display:flex;align-items:center;gap:6px">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M6 18V11"/><path d="M10 18V11"/><path d="M14 18V11"/><path d="M18 18V11"/><path d="M12 3L2 9h20L12 3z"/></svg>
        Per CMI
      </div>
    </div>

    <select class="filter-select" id="rptYearFilter">
      <option value="">Loading...</option>
    </select>

    <!-- Per Table filters -->
    <select class="filter-select" id="rptTableFilter">
      @php
      $tables = [
        'T1'  => 'Table 1 — AIHRs',
        'T2a' => 'Table 2a — RSRDH Summary',
        'T2b' => 'Table 2b — RSRDH Participants',
        'T3'  => 'Table 3 — Projects Monitored',
        'T4'  => 'Table 4 — Resources Shared',
        'T5'  => 'Table 5 — Resources Generated',
        'T6'  => 'Table 6 — Linkages',
        'T7a' => 'Table 7a — Databases',
        'T7b' => 'Table 7b — Info Systems',
        'T8a' => 'Table 8a — R&D Programs',
        'T8b' => 'Table 8b — Collaborative R&D',
        'T9'  => 'Table 9 — Technologies from R&D',
        'T10' => 'Table 10 — TT Programs',
        'T11' => 'Table 11 — Technologies Extended',
        'T12' => 'Table 12 — Commercialized',
        'T13' => 'Table 13 — Promotion Approaches',
        'T14' => 'Table 14 — Non-degree Trainings',
        'T15' => 'Table 15 — Equipment/Facilities',
        'T16' => 'Table 16 — Awards',
        'T17' => 'Table 17 — Regular Meetings',
        'T18' => 'Table 18 — CMI Contributions',
        'T19' => 'Table 19 — New Initiatives',
        'T20a'=> 'Table 20a — Policy Researches',
        'T20b'=> 'Table 20b — Policies',
      ];
      @endphp
      @foreach ($tables as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>

    <!-- Per CMI filter -->
    <select class="filter-select" id="rptCMIFilter" style="display:none">
      <option value="">Select Institution...</option>
    </select>

    <div class="card-actions" id="rptExportBtns" style="margin-left:auto;display:flex;gap:8px;"></div>
  </div>

  <!-- PER TABLE VIEW -->
  <div id="viewPerTable">
    <div class="card">
      <div class="card-hdr">
        <div class="card-title" id="tableCardTitle">Loading...</div>
      </div>
      <div class="tbl-wrap" id="tableContainer">
        <div class="loading-state" style="padding:40px;text-align:center;color:#9ca3af">Loading data...</div>
      </div>
      <div class="tbl-note-footer" style="padding:14px 20px;border-top:1px solid #f3f4f6;font-size:11.5px;color:#6b7280;font-style:italic;">
        Note: The Regional Consortium may prepare other tables for ease in data presentation.
      </div>
    </div>

    <!-- Word Preview Card -->
    <div class="card" id="wordPreviewCard" style="display:none">
      <div class="card-hdr">
        <div class="card-title">Export Preview — Word A4 Format</div>
        <button class="btn btn-sm" style="background:#f3f4f6;color:#374151" onclick="closeWordPreview()">Close Preview</button>
      </div>
      <div class="card-body" style="padding:24px;">
        <div class="word-preview" id="wordPreviewContent"></div>
      </div>
    </div>
  </div>

  <!-- PER CMI VIEW -->
  <div id="viewPerCMI" style="display:none">
    <div class="card">
      <div class="card-hdr">
        <div class="card-title" id="cmiCardTitle">Select a CMI</div>
      </div>
      <div class="tbl-wrap" id="cmiContainer">
        <div class="loading-state" style="padding:40px;text-align:center;color:#9ca3af">Select an institution to view their tables.</div>
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script src="/assets/js/pta/reports-helpers.js"></script>
<script src="/assets/js/pta/reports-docs-lightbox.js"></script>

<!-- ── Table renderers (must load before reports-tabledefs.js) ── -->
<script src="/assets/js/pta/renderers/render-t1.js"></script>
<script src="/assets/js/pta/renderers/render-t2a.js"></script>
<script src="/assets/js/pta/renderers/render-t2b.js"></script>
<script src="/assets/js/pta/renderers/render-t3.js"></script>
<script src="/assets/js/pta/renderers/render-t4.js"></script>
<script src="/assets/js/pta/renderers/render-t5.js"></script>
<script src="/assets/js/pta/renderers/render-t6.js"></script>
<script src="/assets/js/pta/renderers/render-t7a.js"></script>
<script src="/assets/js/pta/renderers/render-t7b.js"></script>
<script src="/assets/js/pta/renderers/render-t8a.js"></script>
<script src="/assets/js/pta/renderers/render-t8b.js"></script>
<script src="/assets/js/pta/renderers/render-t9.js"></script>
<script src="/assets/js/pta/renderers/render-t10.js"></script>
<script src="/assets/js/pta/renderers/render-t11.js"></script>
<script src="/assets/js/pta/renderers/render-t12.js"></script>
<script src="/assets/js/pta/renderers/render-t13.js"></script>
<script src="/assets/js/pta/renderers/render-t14.js"></script>
<script src="/assets/js/pta/renderers/render-t15.js"></script>
<script src="/assets/js/pta/renderers/render-t16.js"></script>
<script src="/assets/js/pta/renderers/render-t17.js"></script>
<script src="/assets/js/pta/renderers/render-t18.js"></script>
<script src="/assets/js/pta/renderers/render-t19.js"></script>
<script src="/assets/js/pta/renderers/render-t20a.js"></script>
<script src="/assets/js/pta/renderers/render-t20b.js"></script>
<script src="/assets/js/pta/renderers/render-generic.js"></script>
<!-- ── End renderers ── -->

<script src="/assets/js/pta/reports-renderers.js"></script>
<script src="/assets/js/pta/reports-tabledefs.js"></script>
<script src="/assets/js/pta/reports-api.js"></script>
<script src="/assets/js/pta/reports-view-table.js"></script>
<script src="/assets/js/pta/reports-view-cmi.js"></script>
<script src="/assets/js/pta/reports-export.js"></script>
<script src="/assets/js/pta/reports-boot.js"></script>
@endsection
