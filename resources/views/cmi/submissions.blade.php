@extends('layouts.cmi')

@section('styles')
<link rel="stylesheet" href="/assets/css/cmi/submissions.css"/>
<style>
/* ── Toolbar & Modals styling for CMI Submissions ── */
.sub-toolbar { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
.search-input { flex:1; min-width:220px; border:1px solid #d1d5db; border-radius:8px; padding:8px 14px; font-size:13px; outline:none; }
.search-input:focus { border-color:#2d6a30; box-shadow:0 0 0 3px rgba(45,106,48,.12); }
.filter-select { border:1px solid #d1d5db; border-radius:8px; padding:8px 14px; font-size:13px; color:#374151; background:#fff; cursor:pointer; outline:none; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(20, 30, 24, .45); align-items:center; justify-content:center; z-index:1000; opacity:0; transition:opacity .18s ease; padding:16px; }
.modal-overlay.modal-visible { opacity:1; display:flex; }
.modal-box { background:#fff; border-radius:14px; width:100%; max-width:760px; max-height:88vh; display:flex; flex-direction:column; overflow:hidden; transform:translateY(14px) scale(.98); transition:transform .2s ease; box-shadow:0 16px 48px rgba(0,0,0,.22); }
.modal-overlay.modal-visible .modal-box { transform:translateY(0) scale(1); }

.modal-hdr { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:16px 22px; border-bottom:1px solid #e5ece6; }
.modal-title { font-weight:700; font-size:15px; color:#2d6a30; }
.modal-sub { font-size:12px; color:#7c8a82; margin-top:3px; }
.modal-close { border:none; background:none; font-size:24px; line-height:1; cursor:pointer; color:#7c8a82; padding:0 2px; flex-shrink:0; }
.modal-close:hover { color:#333; }
.modal-body { padding:18px 22px; overflow-y:auto; flex:1; }
.modal-ftr { display:flex; justify-content:flex-end; gap:8px; padding:14px 22px; border-top:1px solid #e5ece6; flex-wrap:wrap; }

.view-meta-row { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.view-meta-item { background:#f3f6f4; border-radius:8px; padding:8px 12px; min-width:140px; }
.view-meta-label { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#7c8a82; margin-bottom:2px; }
.view-meta-val { font-size:13px; font-weight:600; color:#2a2f2c; }
</style>
@endsection

@section('content')
<div class="page active" id="page-submissions">
  <div class="page-hdr">
    <div>
      <div class="page-title">My Submissions</div>
      <div class="page-sub">Your submitted tables for CY {{ date('Y') }}</div>
    </div>
  </div>

  <div class="sub-toolbar">
    <div id="submissions-summary"></div>
    <input class="search-input" id="subSearch" placeholder="Search table number or title..."/>
    <select class="filter-select" id="subYearFilter">
      @for($y = date('Y'); $y >= 2020; $y--)
        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>CY {{ $y }}</option>
      @endfor
    </select>
    <select class="filter-select" id="subSectionFilter">
      <option value="">All Sections</option>
    </select>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Submitted Tables — by Category</div>
    </div>
    <div class="tbl-wrap">
      <table class="dt" id="submissionsTable">
        <thead>
          <tr>
            <th>Table</th>
            <th>Title</th>
            <th>Status</th>
            <th>Date Submitted</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- Populated dynamically by JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- VIEW SUBMISSION MODAL -->
<div class="modal-overlay" id="modalViewTable">
  <div class="modal-box">
    <div class="modal-hdr">
      <div>
        <div class="modal-title">
          <span id="viewModalTableNo"></span> — <span id="viewModalTableTitle"></span>
        </div>
        <div class="modal-sub" id="viewModalDate"></div>
      </div>
      <button class="modal-close" id="btnCloseViewModal" type="button" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="viewModalBody"></div>
    <div class="modal-ftr">
      <button class="btn btn-outline" type="button" onclick="closeViewModal()">Close</button>
      <a href="#" id="viewModalEditBtn" class="btn btn-primary">Edit this table</a>
    </div>
  </div>
</div>

<!-- EDIT SUBMISSION MODAL -->
<div class="modal-overlay" id="modalEditTable">
  <div class="modal-box" style="max-width:900px">
    <div class="modal-hdr">
      <div>
        <div class="modal-title">
          ✏️ <span id="editModalTableNo"></span> — <span id="editModalTableTitle"></span>
        </div>
        <div class="modal-sub">Edit submitted data below, then click Save Changes.</div>
      </div>
      <button class="modal-close" id="btnCloseEditModal" type="button" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="editModalBody"></div>
    <div class="modal-ftr">
      <button class="btn btn-outline" type="button" onclick="closeEditModal()">Cancel</button>
      <button class="btn btn-primary" id="editModalSaveBtn" type="button">Save Changes</button>
    </div>
  </div>
</div>

<!-- LIGHTBOX MODAL -->
<div class="modal-overlay" id="modalLightbox">
  <div class="modal-box" style="max-width:min(90vw,720px);background:transparent;box-shadow:none">
    <div style="display:flex;justify-content:flex-end;margin-bottom:8px">
      <button class="modal-close" id="btnCloseLightbox" type="button" aria-label="Close"
        style="background:#fff;border-radius:50%;width:32px;height:32px">&times;</button>
    </div>
    <img id="lightboxImg" src="" alt=""
      style="display:block;max-width:100%;max-height:75vh;margin:0 auto;border-radius:10px;box-shadow:0 16px 48px rgba(0,0,0,.35)"/>
    <div id="lightboxCaption" style="text-align:center;color:#fff;font-size:13px;margin-top:10px"></div>
  </div>
</div>
@endsection

@section('scripts')
<script src="/assets/js/cmi/sections-data.js"></script>
<script src="/assets/js/cmi/tables-config.js"></script>
<script src="/assets/js/cmi/submissions-helpers.js"></script>
<script src="/assets/js/cmi/submissions-state.js"></script>
<script src="/assets/js/cmi/submissions-load.js"></script>
<script src="/assets/js/cmi/submissions-render.js"></script>
<script src="/assets/js/cmi/submissions-view.js"></script>
<script src="/assets/js/cmi/submissions-lightbox.js"></script>
<script src="/assets/js/cmi/submissions-edit-state.js"></script>
<script src="/assets/js/cmi/submissions-edit-builders.js"></script>
<script src="/assets/js/cmi/submissions-edit-events.js"></script>
<script src="/assets/js/cmi/submissions-edit-save.js"></script>
<script src="/assets/js/cmi/submissions-edit.js"></script>
<script src="/assets/js/cmi/submissions.js"></script>
@endsection
