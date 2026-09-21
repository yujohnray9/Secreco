@extends('layouts.executive')

@section('styles')
<style>
.pg-banner { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:16px; }
.pg-banner-title { font-size:22px; font-weight:700; color:#111827; letter-spacing:-.4px; }
.pg-banner-sub   { font-size:13px; color:#6b7280; margin-top:3px; }
.fc-card { background:#fff; border-radius:16px; border:1px solid #eae3d6; box-shadow:0 2px 12px rgba(0,0,0,.03); margin-bottom:24px; overflow:hidden; }
.fc-card-head { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 14px; border-bottom:1px solid #eae3d6; }
.fc-card-title { font-size:15px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
.fc-card-title svg { color:#10b981; }
.fc-card-body  { padding:24px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.badge-green  { background:#ecfdf5; color:#059669; }
.badge-orange { background:#fffbeb; color:#d97706; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }

/* ── Inst Card Grid ── */
.inst-card-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; padding-top:8px; }
.inst-card { border:1px solid #eae3d6; border-radius:14px; padding:18px 20px; transition:all .2s; background:#fff; cursor:pointer; }
.inst-card:hover { border-color:#10b981; box-shadow:0 4px 16px rgba(0,0,0,.06); transform:translateY(-2px); }
.inst-card-head { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.inst-logo { width:44px; height:44px; border-radius:50%; object-fit:contain; background:#ffffff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#111827; border:1px solid #eae3d6; padding:2px; flex-shrink:0; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
.inst-name { font-size:14px; font-weight:700; color:#111827; line-height:1.3; }
.inst-type { font-size:11.5px; color:#9ca3af; margin-top:2px; }
.inst-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
.inst-encoder { font-size:12px; color:#6b7280; }
.inst-tables  { font-size:12px; font-weight:700; color:#374151; }
.inst-prog { margin-top:10px; }
.inst-prog-head { display:flex; justify-content:space-between; margin-bottom:5px; }
.inst-prog-label { font-size:11px; color:#9ca3af; }
.inst-prog-pct   { font-size:11px; font-weight:700; color:#10b981; }
.prog-track { height:6px; background:#f3f4f6; border-radius:20px; overflow:hidden; }
.prog-fill  { height:100%; border-radius:20px; background:linear-gradient(90deg,#10b981,#34d399); transition:width .8s ease; }

/* ── Table View ── */
.fc-table-wrap { overflow-x:auto; }
.fc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.fc-table thead tr { background:#f9fafb; }
.fc-table thead th { padding:11px 16px; text-align:left; font-size:11.5px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #f0f0f0; }
.fc-table tbody tr { border-bottom:1px solid #f9fafb; transition:background .15s; }
.fc-table tbody tr:hover { background:#f9fafe; }
.fc-table td { padding:13px 16px; color:#374151; vertical-align:middle; }
.fc-table td strong { color:#111827; font-weight:600; }

/* ── Controls ── */
.header-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.year-pill { display:inline-flex; align-items:center; gap:8px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:5px 12px; font-size:12.5px; font-weight:600; color:#374151; }
.year-pill select { border:none; background:transparent; font-size:12.5px; font-weight:700; color:#111827; cursor:pointer; outline:none; }
.view-toggle { display:flex; gap:4px; background:#f3f4f6; border-radius:10px; padding:3px; }
.view-toggle-btn { border:none; background:transparent; border-radius:8px; padding:6px 12px; cursor:pointer; color:#6b7280; transition:all .15s; display:flex; align-items:center; gap:5px; font-size:12.5px; font-weight:600; }
.view-toggle-btn.active { background:#fff; color:#111827; box-shadow:0 1px 4px rgba(0,0,0,.1); }

/* ── Detail Page Responsive ── */
.detail-stat-card { flex: 1; min-width: 105px; border-radius: 12px; padding: 12px 18px; box-sizing: border-box; }

@media (max-width: 860px) {
  .detail-header-flex { flex-direction: column; align-items: flex-start !important; }
  .detail-header-flex > div { width: 100%; max-width: 100% !important; }
}

@media (max-width: 640px) {
  .pg-banner { flex-direction: column; align-items: stretch; gap: 12px; }
  .header-actions { justify-content: space-between; }
  .fc-card-head { flex-direction: column; align-items: stretch; gap: 10px; padding: 14px 16px; }
  .fc-card-body { padding: 16px 14px; }
}
</style>
@endsection

@section('content')
<div class="page active" id="page-institutions">

  <!-- Main Institutions Overview -->
  <div id="viewInstMain">
    <div class="pg-banner">
      <div>
        <div class="pg-banner-title">Member Institutions</div>
        <div class="pg-banner-sub">CVAARRD Region 2 Consortium Member Institutions</div>
      </div>
      <div class="header-actions">
        <div class="year-pill">
          <span>Reporting Year:</span>
          <select id="instYearFilter">
            <option value="2026">CY 2026</option>
          </select>
        </div>
        <div class="view-toggle">
          <button class="view-toggle-btn active" id="btnCardView" onclick="setView('cards')">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Cards
          </button>
          <button class="view-toggle-btn" id="btnTableView" onclick="setView('table')">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Table
          </button>
        </div>
      </div>
    </div>

    <div class="fc-card">
      <div class="fc-card-head">
        <div class="fc-card-title">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Institutions Status Overview
        </div>
        <span class="badge badge-green" id="instCount">—</span>
      </div>
      <div class="fc-card-body">
        <!-- Card View -->
        <div id="viewCards" class="inst-card-grid">
          <div style="grid-column:1/-1;text-align:center;padding:36px;color:#9ca3af">Loading institutions...</div>
        </div>
        <!-- Table View -->
        <div id="viewTable" style="display:none" class="fc-table-wrap">
          <table class="fc-table">
            <thead>
              <tr>
                <th>Institution</th>
                <th>Type</th>
                <th>CMI Representative</th>
                <th>Tables Done</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="instTbody">
              <tr><td colspan="7" style="text-align:center;padding:36px;color:#9ca3af">Loading institutions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Institution View Container -->
  <div id="viewInstDetail" style="display:none">
    
    <!-- ← Back to Institutions -->
    <div style="margin-bottom:20px;">
      <button type="button" onclick="backToInstList()" style="display:inline-flex;align-items:center;gap:6px;background:none;border:none;color:#047857;font-weight:700;font-size:13.5px;cursor:pointer;padding:4px 0;transition:opacity .15s" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Institutions
      </button>
    </div>

    <!-- Profile & KPI Stats Card -->
    <div class="fc-card" style="padding:22px 26px;margin-bottom:24px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
      <div class="detail-header-flex" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;">
        
        <!-- Institution Identity (Logo, Name, Type, Location) -->
        <div style="display:flex;align-items:center;gap:16px;min-width:260px;">
          <img id="detailInstLogo" src="/assets/img/logo1.jpg" alt="Logo" style="width:58px;height:58px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;box-shadow:0 2px 5px rgba(0,0,0,0.06);flex-shrink:0;">
          <div>
            <div id="detailInstName" style="font-size:18px;font-weight:800;color:#111827;line-height:1.25;">Institution Name</div>
            <div id="detailInstType" style="font-size:13px;color:#6b7280;font-weight:500;margin-top:2px;">State University</div>
            <div style="display:flex;align-items:center;gap:4px;font-size:12px;color:#6b7280;margin-top:4px;">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span id="detailInstLocation">Region 2, Philippines</span>
            </div>
          </div>
        </div>

        <!-- Overall Progress Track -->
        <div style="min-width:220px;max-width:280px;flex:1;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:700;color:#111827;">Overall Progress</span>
            <span id="detailInstPct" style="font-size:12px;font-weight:700;color:#111827;">0%</span>
          </div>
          <div style="width:100%;height:8px;background:#e5e7eb;border-radius:9999px;overflow:hidden;">
            <div id="detailInstBar" style="width:0%;height:100%;background:#10b981;border-radius:9999px;transition:width 0.6s ease;"></div>
          </div>
          <div id="detailInstCompletedText" style="font-size:12px;color:#6b7280;margin-top:6px;">0 / 24 tables completed</div>
        </div>

        <!-- 3 Status KPI Cards -->
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <!-- Submitted -->
          <div class="detail-stat-card" style="background:#f0fdf4;border:1px solid #bbf7d0;">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#15803d;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/></svg>
              Submitted
            </div>
            <div id="detailStatSubmitted" style="font-size:22px;font-weight:800;color:#111827;line-height:1;margin-top:6px;">0</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">tables</div>
          </div>

          <!-- In Progress -->
          <div class="detail-stat-card" style="background:#eff6ff;border:1px solid #bfdbfe;">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#1d4ed8;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 15 14"/></svg>
              In Progress
            </div>
            <div id="detailStatInProgress" style="font-size:22px;font-weight:800;color:#111827;line-height:1;margin-top:6px;">0</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">tables</div>
          </div>

          <!-- Not Started -->
          <div class="detail-stat-card" style="background:#f9fafb;border:1px solid #e5e7eb;">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#4b5563;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
              Not Started
            </div>
            <div id="detailStatNotStarted" style="font-size:22px;font-weight:800;color:#111827;line-height:1;margin-top:6px;">24</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">tables</div>
          </div>
        </div>

      </div>
    </div>

    <!-- All Tables (24) Card -->
    <div class="fc-card" style="border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f0f0f0;background:#fff;">
        <div style="display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700;color:#111827;">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#10b981" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          All Tables (<span id="detailTablesCount">24</span>)
        </div>
        <div style="display:inline-flex;align-items:center;gap:6px;border:1px solid #d1d5db;border-radius:8px;padding:5px 12px;background:#fff;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#6b7280" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <select id="detailYearSel" onchange="onDetailYearChange()" style="border:none;background:transparent;outline:none;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">
            <option value="2026">CY 2026</option>
          </select>
        </div>
      </div>
      
      <div class="fc-table-wrap">
        <table class="fc-table" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#00875a;color:#ffffff;">
              <th style="padding:13px 20px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fff;width:12%;border:none;">TABLE</th>
              <th style="padding:13px 20px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fff;width:35%;border:none;">TITLE</th>
              <th style="padding:13px 20px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fff;width:23%;border:none;">STATUS</th>
              <th style="padding:13px 20px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fff;width:12%;border:none;">ROWS</th>
              <th style="padding:13px 20px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fff;width:18%;border:none;">UPDATED</th>
            </tr>
          </thead>
          <tbody id="detailTablesTbody">
            <!-- Populated by JS -->
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>
@endsection

@section('scripts')
<script>
let currentYear = {{ date('Y') }};
let institutionsData = [];
let currentDetailInstName = '';

function setView(type) {
  const cards = document.getElementById('viewCards');
  const table = document.getElementById('viewTable');
  const btnC  = document.getElementById('btnCardView');
  const btnT  = document.getElementById('btnTableView');

  if (type === 'cards') {
    cards.style.display = 'grid';
    table.style.display = 'none';
    btnC.classList.add('active');
    btnT.classList.remove('active');
  } else {
    cards.style.display = 'none';
    table.style.display = 'block';
    btnT.classList.add('active');
    btnC.classList.remove('active');
  }
}

async function loadInstitutions(year) {
  currentYear = year || currentYear;
  const countEl = document.getElementById('instCount');
  const cardsWrap = document.getElementById('viewCards');
  const tbody = document.getElementById('instTbody');

  cardsWrap.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:36px;color:#9ca3af">Loading institutions...</div>';
  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:36px;color:#9ca3af">Loading institutions...</td></tr>';

  try {
    const res = await fetch(`/api/pta/institutions?year=${currentYear}`);
    const json = await res.json();
    institutionsData = json.institutions || [];

    if (countEl) countEl.textContent = `${institutionsData.length} Institutions`;

    renderCards(institutionsData);
    renderTable(institutionsData);

    // If URL has ?institution=... open it
    const urlParams = new URLSearchParams(window.location.search);
    const targetInst = urlParams.get('institution') || urlParams.get('inst');
    if (targetInst) {
      openInstDetail(targetInst);
    }
  } catch (e) {
    console.error('Error loading executive institutions:', e);
    cardsWrap.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:36px;color:#ef4444">Failed to load institutions. Please try again.</div>';
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:36px;color:#ef4444">Failed to load institutions.</td></tr>';
  }
}

function renderCards(data) {
  const container = document.getElementById('viewCards');
  if (!data.length) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af">No institutions found for this year.</div>';
    return;
  }
  container.innerHTML = data.map(inst => {
    const pct = inst.total_tables > 0 ? Math.round((inst.tables_done / inst.total_tables) * 100) : 0;
    const badgeClass = inst.status === 'Submitted' ? 'badge-green' : (inst.status === 'In Progress' ? 'badge-orange' : 'badge-gray');
    const instEsc = (inst.name || '').replace(/'/g, "\\'");
    return `
      <div class="inst-card" onclick="openInstDetail('${instEsc}')">
        <div class="inst-card-head">
          ${inst.logo_url ? `<img src="${inst.logo_url}" class="inst-logo" alt="${inst.name}" onerror="this.style.display='none'"/>` : ''}
          <div style="min-width:0;flex:1">
            <div class="inst-name" title="${inst.name}">${inst.name}</div>
            <div class="inst-type">${inst.type}</div>
          </div>
        </div>
        <div class="inst-meta">
          <span class="inst-encoder">${inst.encoder || 'No CMI Assigned'}</span>
          <span class="inst-tables">${inst.tables_done}/${inst.total_tables} tables</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:6px">
          <span class="badge ${badgeClass}">${inst.status}</span>
          <button type="button" onclick="event.stopPropagation(); openInstDetail('${instEsc}')" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:600;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View
          </button>
        </div>
        <div class="inst-prog">
          <div class="inst-prog-head">
            <span class="inst-prog-label">Completion</span>
            <span class="inst-prog-pct">${pct}%</span>
          </div>
          <div class="prog-track"><div class="prog-fill" style="width:${pct}%"></div></div>
        </div>
      </div>
    `;
  }).join('');
}

function renderTable(data) {
  const tbody = document.getElementById('instTbody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">No institutions found for this year.</td></tr>';
    return;
  }
  tbody.innerHTML = data.map(inst => {
    const pct = inst.total_tables > 0 ? Math.round((inst.tables_done / inst.total_tables) * 100) : 0;
    const badgeClass = inst.status === 'Submitted' ? 'badge-green' : (inst.status === 'In Progress' ? 'badge-orange' : 'badge-gray');
    const instEsc = (inst.name || '').replace(/'/g, "\\'");
    return `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            ${inst.logo_url ? `<img src="${inst.logo_url}" width="30" height="30" style="border-radius:50%;object-fit:contain;background:#fff;border:1px solid #eae3d6;padding:1px" onerror="this.style.display='none'"/>` : ''}
            <strong>${inst.name}</strong>
          </div>
        </td>
        <td style="color:#6b7280">${inst.type}</td>
        <td>${inst.encoder || '—'}</td>
        <td><strong>${inst.tables_done}/${inst.total_tables}</strong></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;min-width:120px">
            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden">
              <div style="height:100%;width:${pct}%;background:linear-gradient(90deg,#10b981,#34d399);border-radius:20px;transition:width .6s"></div>
            </div>
            <span style="font-size:11px;font-weight:700;color:#10b981;white-space:nowrap">${pct}%</span>
          </div>
        </td>
        <td><span class="badge ${badgeClass}">${inst.status}</span></td>
        <td>
          <button type="button" onclick="openInstDetail('${instEsc}')" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

window.backToInstList = function() {
  const detail = document.getElementById('viewInstDetail');
  const main = document.getElementById('viewInstMain');
  if (detail) detail.style.display = 'none';
  if (main) main.style.display = 'block';
  if (history.pushState) {
    history.pushState(null, null, window.location.pathname);
  }
};

window.openInstDetail = async function(instName, selectedYear) {
  if (!instName) return;
  currentDetailInstName = instName;
  const yearSel = document.getElementById('detailYearSel');
  const year = selectedYear || (yearSel ? yearSel.value : currentYear);

  const main = document.getElementById('viewInstMain');
  const detail = document.getElementById('viewInstDetail');
  if (main) main.style.display = 'none';
  if (detail) detail.style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });

  const tbody = document.getElementById('detailTablesTbody');
  if (tbody) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:36px;color:#9ca3af">Loading institution details...</td></tr>';
  }

  try {
    const res = await fetch(`/api/pta/institutions/details?institution=${encodeURIComponent(instName)}&year=${year}`);
    const json = await res.json();
    if (!json.ok) {
      if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:36px;color:#ef4444">Failed to load institution details.</td></tr>';
      return;
    }

    const inst = json.institution || {};
    const nameEl = document.getElementById('detailInstName');
    const typeEl = document.getElementById('detailInstType');
    const locEl = document.getElementById('detailInstLocation');
    const pctEl = document.getElementById('detailInstPct');
    const barEl = document.getElementById('detailInstBar');
    const compEl = document.getElementById('detailInstCompletedText');
    const subEl = document.getElementById('detailStatSubmitted');
    const progEl = document.getElementById('detailStatInProgress');
    const notEl = document.getElementById('detailStatNotStarted');
    const countEl = document.getElementById('detailTablesCount');
    const logoEl = document.getElementById('detailInstLogo');

    if (nameEl) nameEl.textContent = inst.name || instName;
    if (typeEl) typeEl.textContent = inst.type || 'State University';
    if (locEl) locEl.textContent = inst.location || 'Region 2, Philippines';
    if (pctEl) pctEl.textContent = `${inst.progress_pct || 0}%`;
    if (barEl) barEl.style.width = `${inst.progress_pct || 0}%`;
    if (compEl) compEl.textContent = `${inst.completed || 0} / ${inst.total || 24} tables completed`;
    if (subEl) subEl.textContent = inst.submitted || 0;
    if (progEl) progEl.textContent = inst.in_progress || 0;
    if (notEl) notEl.textContent = inst.not_started !== undefined ? inst.not_started : 24;
    if (countEl) countEl.textContent = (json.tables || []).length;

    if (logoEl && inst.logo_url) {
      logoEl.src = inst.logo_url;
      logoEl.style.display = 'block';
    }

    if (tbody) {
      const tables = json.tables || [];
      tbody.innerHTML = tables.map(t => {
        let statusPill = '';
        if (t.status === 'Submitted') {
          statusPill = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#ecfdf5;color:#059669;font-size:12px;font-weight:600;"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/></svg> Submitted</span>`;
        } else if (t.status === 'In Progress') {
          statusPill = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 15 14"/></svg> In Progress</span>`;
        } else {
          statusPill = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#f3f4f6;color:#4b5563;font-size:12px;font-weight:600;"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg> Not Started</span>`;
        }

        return `
          <tr style="border-bottom:1px solid #f3f4f6;">
            <td style="padding:13px 20px;color:#111827;font-weight:700;">${t.table_no}</td>
            <td style="padding:13px 20px;color:#374151;font-weight:500;">${t.title}</td>
            <td style="padding:13px 20px;">${statusPill}</td>
            <td style="padding:13px 20px;color:#4b5563;">${t.rows}</td>
            <td style="padding:13px 20px;color:#4b5563;">${t.updated}</td>
          </tr>
        `;
      }).join('');
    }

  } catch(e) {
    console.error('Error fetching inst details:', e);
    if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:36px;color:#ef4444">Error loading details.</td></tr>';
  }
};

window.onDetailYearChange = function() {
  if (currentDetailInstName) {
    const yr = document.getElementById('detailYearSel')?.value;
    openInstDetail(currentDetailInstName, yr);
  }
};

document.addEventListener('DOMContentLoaded', async function () {
  const yrFilter = document.getElementById('instYearFilter');
  const detailYearSel = document.getElementById('detailYearSel');
  try {
    const fRes = await fetch('/api/formats');
    const fData = await fRes.json();
    if (fData && fData.years && Array.isArray(fData.years) && fData.years.length > 0) {
      const activeYr = fData.active_year || new Date().getFullYear();
      if (yrFilter) {
        yrFilter.innerHTML = fData.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
        currentYear = yrFilter.value;
      }
      if (detailYearSel) {
        detailYearSel.innerHTML = fData.years.map(y => `<option value="${y}" ${y === activeYr ? 'selected' : ''}>CY ${y}</option>`).join('');
      }
    }
  } catch (err) {}

  if (yrFilter) {
    yrFilter.addEventListener('change', function () {
      loadInstitutions(this.value);
    });
  }

  loadInstitutions(currentYear);
});
</script>
@endsection
