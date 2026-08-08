// ═══ INSTITUTIONS PAGE ═══
'use strict';

let _allInstitutions = [];

document.addEventListener('DOMContentLoaded', loadInstitutions);

// ── LOAD ──────────────────────────────────────────────────────
async function loadInstitutions() {
    const year = new Date().getFullYear();
    const grid = document.getElementById('instGrid');

    try {
        const res  = await fetch(`/api/pta/institutions/get_institutions.php?year=${year}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.error ?? 'API error');

        _allInstitutions = json.institutions ?? [];
        renderSummary(json.summary);
        renderGrid(_allInstitutions);
    } catch (e) {
        console.error('[institutions] Failed:', e);
        grid.innerHTML = '<div class="inst-loading">⚠️ Could not load institutions.</div>';
    }
}

// ── SUMMARY STRIP ─────────────────────────────────────────────
function renderSummary(s) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('sumSubmitted',  s.submitted);
    set('sumInProgress', s.in_progress);
    set('sumNotStarted', s.not_started);
    set('sumNoCmi',      s.no_cmi);
    const row = document.getElementById('instSummary');
    if (row) row.style.display = '';
}

// ── RENDER GRID ───────────────────────────────────────────────
function renderGrid(items) {
    const grid  = document.getElementById('instGrid');
    const empty = document.getElementById('instEmpty');

    if (!items.length) {
        grid.innerHTML = '';
        if (empty) empty.style.display = 'block';
        return;
    }
    if (empty) empty.style.display = 'none';

    grid.innerHTML = items.map(inst => instCard(inst)).join('');
}

function instCard(i) {
    const pct       = Math.round((i.tables_done / i.total_tables) * 100);
    const statusCls = statusClass(i.status);
    const logoText  = i.short.substring(0, 3);
    const hasCmi    = i.has_cmi;

    return `
    <div class="inst-card"
         data-name="${esc((i.name + ' ' + i.short).toLowerCase())}"
         data-type="${esc(i.type)}"
         data-status="${esc(i.status)}">

      <div class="inst-logo" style="background:transparent; padding:0; overflow:hidden;">
        ${i.logo_url 
          ? `<img src="${i.logo_url}" alt="${esc(i.short)}" style="width:100%;height:100%;object-fit:contain" onerror="this.style.display='none';this.parentElement.innerHTML='<div style=\\'background:var(--gray-200);width:100%;height:100%;display:flex;align-items:center;justify-content:center\\'>${esc(logoText)}</div>'"/>`
          : `<div style="background:var(--gray-200);width:100%;height:100%;display:flex;align-items:center;justify-content:center">${esc(logoText)}</div>`
        }
      </div>
      <div class="inst-name">${esc(i.name)}</div>
      <div class="inst-meta">${esc(i.type)}</div>

      <div class="inst-status-line ${statusCls}">
        ${i.tables_done}/${i.total_tables} tables &middot;
        <span class="inst-badge ${statusCls}">${esc(i.status)}</span>
      </div>

      <div class="inst-prog">
        <div class="inst-prog-fill ${statusCls}" style="width:${pct}%"></div>
      </div>

      <div class="inst-encoder">
        ${hasCmi
            ? `CMI Representative: <strong>${esc(i.encoder)}</strong>`
            : `<span class="inst-no-cmi">⚠️ No CMI representative assigned</span>`}
      </div>

      <div class="inst-card-actions">
        <button class="btn btn-xs" onclick="navTo('submissions')">View</button>
      </div>
    </div>`;
}

// ── FILTER ────────────────────────────────────────────────────
function filterInst() {
    const search = (document.getElementById('instSearch')?.value ?? '').toLowerCase().trim();
    const type   = document.getElementById('instTypeFilter')?.value ?? '';
    const status = document.getElementById('instStatusFilter')?.value ?? '';

    const filtered = _allInstitutions.filter(i => {
        const nameMatch   = !search || (i.name + ' ' + i.short).toLowerCase().includes(search);
        const typeMatch   = !type   || i.type   === type;
        const statusMatch = !status || i.status === status;
        return nameMatch && typeMatch && statusMatch;
    });

    renderGrid(filtered);
}

// ── HELPERS ───────────────────────────────────────────────────
function statusClass(status) {
    return {
        'Submitted'   : 'status-submitted',
        'In Progress' : 'status-progress',
        'Not Started' : 'status-none',
        'Returned'    : 'status-returned',
    }[status] ?? 'status-none';
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
