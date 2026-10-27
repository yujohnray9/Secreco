@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-submissions">
  <div class="page-hdr">
    <div>
      <div class="page-title">Submissions (View Only)</div>
      <div class="page-sub">CY <span id="vSubsYearSub">{{ date('Y') }}</span> CMI report submissions</div>
    </div>
  </div>

  <div class="pg-filter-row" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <label style="font-size:13px;font-weight:600;color:#374151">Year:</label>
    <select class="filter-select" id="vSubsYearSel" style="border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;font-size:13px;color:#374151;background:#fff;cursor:pointer;outline:none;">
      <option value="">Loading...</option>
    </select>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">All Submissions</div>
    </div>
    <div class="tbl-wrap">
      <table class="dt">
        <thead>
          <tr>
            <th>Institution</th>
            <th>Encoder</th>
            <th>Table</th>
            <th>Updated At</th>
          </tr>
        </thead>
        <tbody id="vSubsTbody">
          <tr><td colspan="4" style="text-align:center;padding:16px">Loading submissions...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let currentYear = {{ date('Y') }};

async function loadSubmissions(year) {
  const tbody = document.getElementById('vSubsTbody');
  const subYearSpan = document.getElementById('vSubsYearSub');
  if (subYearSpan) subYearSpan.textContent = year;
  tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;color:#9ca3af">Loading submissions...</td></tr>';
  try {
    const res = await fetch(`/api/pta/submissions?year=${year}`);
    const json = await res.json();
    if (json.rows) {
      if (json.rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:24px;color:#6b7280">No submissions found for CY ${year}.</td></tr>`;
      } else {
        tbody.innerHTML = json.rows.map(r => `
          <tr>
            <td><strong>${r.institution}</strong></td>
            <td>${r.encoder}</td>
            <td>Table ${r.table_no}</td>
            <td>${r.updated_at ? r.updated_at.substring(0, 16).replace('T', ' ') : '—'}</td>
          </tr>
        `).join('');
      }
    }
  } catch (e) {
    console.error('Viewer Submissions load error:', e);
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;color:#ef4444">Failed to load submissions.</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', async function () {
  const urlYear = parseInt(new URLSearchParams(window.location.search).get('year'), 10);
  let storedYear = null;
  try {
    const raw = localStorage.getItem('secreco_selected_year');
    if (raw) storedYear = parseInt(raw, 10);
  } catch(e){}

  const yrFilter = document.getElementById('vSubsYearSel');

  try {
    const fRes = await fetch('/api/formats?include_drafts=1');
    const fData = await fRes.json();
    const backendYears = [
      ...(Array.isArray(fData.all_years) ? fData.all_years : []),
      ...(Array.isArray(fData.years) ? fData.years : []),
      fData.active_year
    ].map(y => parseInt(y, 10)).filter(y => !isNaN(y) && y >= 2000);

    let targetYr = null;
    if (urlYear && !isNaN(urlYear) && urlYear >= 2000) {
      targetYr = urlYear;
    } else if (storedYear && !isNaN(storedYear) && storedYear >= 2000) {
      targetYr = storedYear;
    } else if (fData.active_year && !isNaN(parseInt(fData.active_year, 10))) {
      targetYr = parseInt(fData.active_year, 10);
    } else if (backendYears.length > 0) {
      targetYr = Math.max(...backendYears);
    } else {
      targetYr = new Date().getFullYear();
    }

    const availableYears = [...new Set([...backendYears, targetYr])].sort((a,b) => b - a);
    currentYear = targetYr;

    if (yrFilter) {
      yrFilter.innerHTML = availableYears.map(y => `<option value="${y}" ${y === targetYr ? 'selected' : ''}>CY ${y}</option>`).join('');
      yrFilter.value = String(targetYr);
    }

    try {
      localStorage.setItem('secreco_selected_year', String(targetYr));
      const u = new URL(window.location);
      u.searchParams.set('year', targetYr);
      window.history.replaceState({}, '', u);
    } catch(e){}
  } catch(e) {}

  if (yrFilter) {
    yrFilter.addEventListener('change', function() {
      const yr = parseInt(this.value, 10) || new Date().getFullYear();
      try {
        localStorage.setItem('secreco_selected_year', String(yr));
        const u = new URL(window.location);
        u.searchParams.set('year', yr);
        window.history.replaceState({}, '', u);
      } catch(err){}
      currentYear = yr;
      loadSubmissions(yr);
    });
  }

  loadSubmissions(currentYear);
});
</script>
@endsection
