/**
 * fillup-core.js — CMI Fill Up Core Engine  (v2)
 * Handles: sidebar nav render, table switching, Submit Report.
 * Each table registers itself via CMI.registerTable(def).
 *
 * Depends on: c_core.js (toast)
 * Loaded by:  dashboards/cmi/fillup.php
 */

(function () {
    "use strict";

    /* ─────────────────────────────────────────
     SECTION / TABLE REGISTRY
  ───────────────────────────────────────── */
    const SECTIONS = [
        {
            label: "R&D Mgt. & Coord.",
            tables: ["T1", "T2a", "T2b", "T3", "T4", "T5", "T6", "T7a", "T7b"],
        },
        { label: "Strategic R&D", tables: ["T8a", "T8b", "T9"] },
        { label: "Results Utilization", tables: ["T10", "T11", "T12", "T13"] },
        {
            label: "Capability & Gov.",
            tables: ["T14", "T15", "T16", "T17", "T18", "T19"],
        },
        { label: "Policy Analysis", tables: ["T20a", "T20b"] },
    ];

    const TABLE_TITLES = {
        T1: "AIHRs",
        T2a: "RSRDH Summary",
        T2b: "RSRDH Participants",
        T3: "Projects Monitored",
        T4: "Resources Shared",
        T5: "Resources Generated",
        T6: "Linkages",
        T7a: "Databases",
        T7b: "Info Systems",
        T8a: "R&D Programs",
        T8b: "Collaborative R&D",
        T9: "Technologies from R&D",
        T10: "TT Programs",
        T11: "Technologies Extended",
        T12: "Commercialized",
        T13: "Promotion Approaches",
        T14: "Non-degree Trainings",
        T15: "Equipment/Facilities",
        T16: "Awards",
        T17: "Regular Meetings",
        T18: "CMI Contributions",
        T19: "New Initiatives",
        T20a: "Policy Research",
        T20b: "Policies",
    };

    const CANONICAL_TABLES = {
        t1: 'T1', t2a: 'T2a', t2b: 'T2b', t3: 'T3', t4: 'T4', t5: 'T5', t6: 'T6',
        t7a: 'T7a', t7b: 'T7b', t8a: 'T8a', t8b: 'T8b', t9: 'T9', t10: 'T10',
        t11: 'T11', t12: 'T12', t13: 'T13', t14: 'T14', t15: 'T15', t16: 'T16',
        t17: 'T17', t18: 'T18', t19: 'T19', t20a: 'T20a', t20b: 'T20b',
    };

    function normalizeTableNo(str) {
        if (!str) return 'T1';
        const s = String(str).trim();
        const lower = s.toLowerCase();
        if (CANONICAL_TABLES[lower]) {
            return CANONICAL_TABLES[lower];
        }
        const m = s.match(/^(T\d+)([a-zA-Z])?$/i);
        if (m) {
            return m[1].toUpperCase() + (m[2] ? m[2].toLowerCase() : '');
        }
        return s;
    }

    const STATUS_ICON = {
        done: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>',
        submitted: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>',
        accepted: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>',
        draft: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>',
        error: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        "not-started":
            '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="9"/></svg>',
    };

    const SUBMIT_ICONS = {
        done: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        draft: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e65100" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        blank: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/></svg>',
    };

    const API_SAVE = "/api/cmi/tables/save";
    const API_SUBMIT = "/api/cmi/report/submit";

    /* registry: tableNo → { render } */
    const _registry = {};
    /* module cache: tableNo → { save: function } */
    const _tableModules = {};

    function getTableModule(no) {
        if (!no) return null;
        const str = String(no).trim();
        const norm = normalizeTableNo(str);
        const clean = str.replace(/^T/i, '');
        const withT = 'T' + clean;
        const candidates = [str, norm, clean, withT, str.toUpperCase(), str.toLowerCase(), norm.toUpperCase(), norm.toLowerCase()];
        for (const c of candidates) {
            if (_tableModules[c]) return _tableModules[c];
        }
        for (const c of candidates) {
            if (isNaN(Number(c)) && window[c] && typeof window[c].save === 'function') return window[c];
        }
        return null;
    }

    /* status cache: tableNo → status string */
    const _status = {};
    /* submission state */
    let _isSubmitted = false;
    let _submittedAt = null;
    let _submittedTables = [];

    /* ─────────────────────────────────────────
     PUBLIC API
  ───────────────────────────────────────── */
    window.CMI = window.CMI || {};

    CMI.registerTable = function (def) {
        if (!def || !def.no) return;
        const canon = normalizeTableNo(def.no);
        _registry[def.no] = def;
        _registry[canon] = def;
        _registry[canon.toUpperCase()] = def;
        _registry[canon.toLowerCase()] = def;
    };

    CMI.registerModule = function (tableNo, mod) {
        if (!tableNo || !mod) return;
        const str = String(tableNo).trim();
        const norm = normalizeTableNo(str);
        const clean = str.replace(/^T/i, '');
        const withT = 'T' + clean;
        const candidates = [str, norm, clean, withT, str.toUpperCase(), str.toLowerCase(), norm.toUpperCase(), norm.toLowerCase()];
        candidates.forEach(c => { _tableModules[c] = mod; });
        if (isNaN(Number(tableNo))) {
            try {
                window[tableNo] = mod;
                window[tableNo.toUpperCase()] = mod;
                window[tableNo.toLowerCase()] = mod;
            } catch (e) {}
        }
    };

    let _activeSections = SECTIONS;
    let _activeTitles = Object.assign({}, TABLE_TITLES);
    let _templateDefs = {};

    function getActiveTableNos() {
        const list = [];
        if (Array.isArray(_activeSections)) {
            _activeSections.forEach(sec => {
                if (Array.isArray(sec.tables)) {
                    sec.tables.forEach(t => {
                        if (t && !list.includes(t)) list.push(t);
                    });
                }
            });
        }
        if (list.length === 0) {
            return Object.keys(TABLE_TITLES);
        }
        return list;
    }

    CMI.setFormatTemplates = function (templates) {
        _templateDefs = {};

        if (Array.isArray(templates) && templates.length > 0) {
            const sections = [];
            const titles = {};

            templates.forEach(function (t) {
                if (!t || !t.table_no) return;
                const tblNo = String(t.table_no).trim();
                const canonicalNo = normalizeTableNo(tblNo);
                const cleanNo = tblNo.replace(/^T/i, '');
                const withT = 'T' + cleanNo;

                // Find section name from template or from default SECTIONS
                let secName = t.section;
                if (!secName) {
                    for (const s of SECTIONS) {
                        if (s.tables.some(tbl => normalizeTableNo(tbl) === canonicalNo || String(tbl).replace(/^T/i, '') === cleanNo)) {
                            secName = s.label;
                            break;
                        }
                    }
                }
                if (!secName) secName = "General";

                let secObj = sections.find(s => s.label.toLowerCase() === secName.toLowerCase());
                if (!secObj) {
                    secObj = { label: secName, tables: [] };
                    sections.push(secObj);
                }
                if (!secObj.tables.includes(tblNo)) {
                    secObj.tables.push(tblNo);
                }

                // Title resolution
                let shortTitle = t.title || tblNo;
                if (shortTitle.match(/^Table\s+[A-Za-z0-9.]+\s*[–-]?\s*/i)) {
                    shortTitle = shortTitle.replace(/^Table\s+[A-Za-z0-9.]+\s*[–-]?\s*/i, "").trim();
                }
                const resolvedTitle = shortTitle || TABLE_TITLES[canonicalNo] || t.title || tblNo;
                titles[tblNo] = resolvedTitle;
                titles[canonicalNo] = resolvedTitle;
                titles[cleanNo] = resolvedTitle;
                titles[withT] = resolvedTitle;

                _templateDefs[tblNo] = t;
                _templateDefs[tblNo.toUpperCase()] = t;
                _templateDefs[tblNo.toLowerCase()] = t;
                _templateDefs[canonicalNo] = t;
                _templateDefs[canonicalNo.toUpperCase()] = t;
                _templateDefs[canonicalNo.toLowerCase()] = t;
                _templateDefs[cleanNo] = t;
                _templateDefs[withT] = t;
            });

            _activeSections = sections;
            _activeTitles = titles;
            renderFillNav();
            return;
        }

        // Fallback when no templates configured for this year: use default 24 tables
        const sections = SECTIONS.map(s => ({
            label: s.label,
            tables: [...s.tables]
        }));
        _activeSections = sections;
        _activeTitles = Object.assign({}, TABLE_TITLES);
        renderFillNav();
    };

    CMI.setStatuses = function (statuses, meta = null) {
        for (let k in _status) delete _status[k];
        if (statuses) {
            for (let k in statuses) {
                const canon = normalizeTableNo(k);
                _status[k] = statuses[k];
                _status[canon] = statuses[k];
                _status[canon.toUpperCase()] = statuses[k];
                _status[canon.toLowerCase()] = statuses[k];
            }
        }

        if (meta && Array.isArray(meta.templates)) {
            CMI.setFormatTemplates(meta.templates);
        }

        if (meta && meta.submitted && Array.isArray(meta.submitted_tables) && meta.submitted_tables.length > 0) {
            _isSubmitted = true;
            _submittedAt = meta.submitted_at || null;
            _submittedTables = meta.submitted_tables || [];
        } else {
            _isSubmitted = false;
            _submittedAt = null;
            _submittedTables = [];
            const banner = document.getElementById("cmi-submitted-banner");
            if (banner) banner.remove();
        }

        renderFillNav();
    };

    CMI.updateStatus = function (no, status) {
        if (!no) return;
        _status[no] = status;
        _status[no.toUpperCase()] = status;
        _status[no.toLowerCase()] = status;
        renderFillNav();
    };

    /* ─────────────────────────────────────────
     NAV RENDER
  ───────────────────────────────────────── */
    function renderFillNav() {
        const nav = document.getElementById("fillNav");
        if (!nav) return;

        const VALID = new Set(["done", "submitted", "accepted", "draft", "error", "not-started"]);
        const sections = _activeSections && _activeSections.length > 0 ? _activeSections : SECTIONS;

        nav.innerHTML = sections.map((s) => {
            const items = s.tables
                .map((no) => {
                    const raw = _status[no] || _status[no.toUpperCase()] || _status[no.toLowerCase()];
                    const st = VALID.has(raw) ? raw : "not-started";
                    const ttl = _activeTitles[no] || TABLE_TITLES[no] || no;
                    const active =
                        (window._cmiActiveTable && String(window._cmiActiveTable).toLowerCase() === String(no).toLowerCase()) ? " active" : "";
                    const tDef = _templateDefs[no] || _templateDefs[no.toUpperCase()] || _templateDefs[no.toLowerCase()] || _templateDefs[normalizeTableNo(no)];
                    const isLocked = tDef && !!tDef.is_locked;
                    const lockBadge = isLocked
                        ? '<span style="margin-left:auto;display:inline-flex;align-items:center;color:#d97706;padding-left:4px" title="Locked: Only PTA can fill out">' +
                          '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' +
                          '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>' +
                          '</span>'
                        : '';
                    return (
                        `<div class="fill-nav-sub ${st}${active}" onclick="CMI.showTable('${no}')" style="display:flex;align-items:center;gap:4px">` +
                        `<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${STATUS_ICON[st] ?? STATUS_ICON["not-started"]} ${no} — ${ttl}</span>${lockBadge}</div>`
                    );
                })
                .join("");

            return `<div class="fill-nav-item">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
        </svg>${s.label}</div>${items}`;
        }).join("");
    }

    /* ─────────────────────────────────────────
     TABLE SWITCHING
  ───────────────────────────────────────── */
    CMI.showTable = function (no) {
        if (!no) return;
        const body = document.getElementById("fillBody");
        if (!body) return;

        const sNo = String(no).trim();
        const canon = normalizeTableNo(sNo);
        const cleanNo = sNo.replace(/^T/i, '');
        const withT = 'T' + cleanNo;
        const candidates = [sNo, canon, cleanNo, withT, sNo.toUpperCase(), sNo.toLowerCase(), canon.toUpperCase(), canon.toLowerCase()];

        // Find template definition if available
        let tDef = null;
        for (const c of candidates) {
            if (_templateDefs[c]) { tDef = _templateDefs[c]; break; }
        }
        if (!tDef) {
            const matchKey = Object.keys(_templateDefs).find(k => 
                k.toLowerCase() === sNo.toLowerCase() || 
                normalizeTableNo(k).toLowerCase() === canon.toLowerCase() ||
                k.replace(/^T/i, '').toLowerCase() === cleanNo.toLowerCase()
            );
            if (matchKey) tDef = _templateDefs[matchKey];
        }

        // Determine if template has real custom columns (from Manage Formats)
        let hasCustomCols = false;
        if (tDef && tDef.columns_json) {
            let parsedCols = tDef.columns_json;
            if (typeof parsedCols === 'string') {
                try { parsedCols = JSON.parse(parsedCols); } catch (e) { parsedCols = null; }
            }
            if (parsedCols && typeof parsedCols === 'object') {
                if (Array.isArray(parsedCols.titles) && parsedCols.titles.length > 0) {
                    hasCustomCols = true;
                } else if (Array.isArray(parsedCols.columns) && parsedCols.columns.length > 0) {
                    hasCustomCols = true;
                } else if (Array.isArray(parsedCols) && parsedCols.length > 0) {
                    // Ignore legacy seeder placeholder ['Item Description', 'Agency', 'Remarks']
                    const isLegacyPlaceholder = parsedCols.length === 3 &&
                        parsedCols[0] === 'Item Description' &&
                        parsedCols[1] === 'Agency' &&
                        parsedCols[2] === 'Remarks';
                    if (!isLegacyPlaceholder) {
                        hasCustomCols = true;
                    }
                }
            }
        }

        // Find built-in registered renderer
        let def = null;
        for (const c of candidates) {
            if (_registry[c]) { def = _registry[c]; break; }
        }
        if (!def) {
            const matchKey = Object.keys(_registry).find(k => 
                k.toLowerCase() === sNo.toLowerCase() || 
                normalizeTableNo(k).toLowerCase() === canon.toLowerCase()
            );
            if (matchKey) def = _registry[matchKey];
        }

        // Precedence:
        // 1. If template definition has custom columns OR if no built-in renderer exists, but template exists:
        //    Render dynamic table!
        if (tDef && (hasCustomCols || !def)) {
            const canonicalNo = tDef.table_no || canon || sNo;
            window._cmiActiveTable = canonicalNo;
            renderFillNav();
            renderDynamicTable(canonicalNo, tDef, body);
            setTimeout(() => applyLock(canonicalNo), 100);
            setTimeout(() => applyLock(canonicalNo), 550);
            return;
        }

        // 2. Otherwise if built-in registry renderer exists:
        if (def) {
            const canonicalNo = def.no || canon || sNo;
            window._cmiActiveTable = canonicalNo;
            renderFillNav();
            def.render(body);
            setTimeout(() => applyLock(canonicalNo), 100);
            setTimeout(() => applyLock(canonicalNo), 550);
            return;
        }

        // 3. Neither template nor built-in renderer found
        body.innerHTML =
            `<div style="padding:32px;color:var(--text-muted);font-size:14px">` +
            `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right:6px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>` +
            `Table <strong>${no}</strong> is not defined for this year.</div>`;
        window._cmiActiveTable = no;
        renderFillNav();
    };

    /* ─────────────────────────────────────────
     DYNAMIC TABLE RENDERER FOR FORMAT TEMPLATES
  ───────────────────────────────────────── */
    function renderDynamicTable(tableNo, tDef, container) {
        const rawTitle = (tDef.title || '').trim();
        let displayTitle = rawTitle;
        if (!/^Table\s+/i.test(displayTitle)) {
            const cleanNo = String(tableNo || '').trim().replace(/^T/i, '');
            displayTitle = rawTitle ? `Table ${cleanNo}. ${rawTitle}` : `Table ${cleanNo}`;
        }
        let cols = [];
        let categories = [];
        let parsed = tDef.columns_json;
        if (typeof parsed === "string") {
            try { parsed = JSON.parse(parsed); } catch (e) { parsed = null; }
        }
        if (parsed) {
            if (Array.isArray(parsed)) {
                cols = parsed;
            } else if (typeof parsed === "object") {
                if (Array.isArray(parsed.titles) && parsed.titles.length > 0) {
                    categories = parsed.titles.map(t => String(t.title || '').trim()).filter(Boolean);
                    if (Array.isArray(parsed.titles[0]?.columns) && parsed.titles[0].columns.length > 0) {
                        cols = parsed.titles[0].columns;
                    }
                }
                if (Array.isArray(parsed.categories) && categories.length === 0) {
                    categories = parsed.categories.map(c => String(c).trim()).filter(Boolean);
                }
                if (Array.isArray(parsed.columns) && cols.length === 0) {
                    cols = parsed.columns;
                }
            }
        }

        if (!cols || cols.length === 0) {
            cols = [
                { name: "Title / Description", type: "text" },
                { name: "Agency / Institution", type: "text" },
                { name: "Amount / Value", type: "number" },
                { name: "Remarks", type: "text" }
            ];
        }

        function escHtml(s) {
            return String(s || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        const isPtaLocked = !!(tDef && tDef.is_locked);
        const isLockedForCmi = isPtaLocked && !window.IS_PTA_USER;

        const ths = cols.map(c => {
            const name = typeof c === "string" ? c : (c.name || c.label || "Column");
            return `<th class="group">${escHtml(name)}</th>`;
        }).join("") + `<th class="group" style="width:40px;text-align:center"></th>`;

        const hasCategories = categories.length > 0;
        let addButtonsHTML = "";
        if (!isLockedForCmi) {
            if (hasCategories) {
                addButtonsHTML = categories.map(cat =>
                    `<button class="btn btn-sm dyn-add-cat-btn" data-cat="${escHtml(cat)}" type="button" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600">+ Add ${escHtml(cat)}</button>`
                ).join(" ");
            } else {
                addButtonsHTML = `<button class="btn btn-sm" id="dyn_${tableNo}_add_btn" type="button" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;font-weight:600">+ Add Row</button>`;
            }
        }

        container.innerHTML = `
        <div class="t-page" id="dyn_${tableNo}_wrap">
          <div class="t-hdr">
            <div class="t-title">${escHtml(displayTitle)}</div>
          </div>
          <div class="tbl-wrap" style="margin:14px 0">
            <table class="merged" style="width:100%;min-width:560px">
              <thead>
                <tr>
                  <th class="group" style="width:40px;text-align:center">#</th>
                  ${ths}
                </tr>
              </thead>
              <tbody id="dyn_${tableNo}_rows"></tbody>
            </table>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px" id="dyn_${tableNo}_cat_btns">
            ${addButtonsHTML}
          </div>
          <div class="t-footer" style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:8px">
              <button class="btn btn-sm" id="dyn_${tableNo}_save_btn" type="button" data-action="save" style="background:#2e7d32;color:#fff;border:none;padding:6px 16px;font-weight:600;cursor:pointer">Save</button>
              <button class="btn btn-sm" id="dyn_${tableNo}_draft_btn" type="button" data-action="draft" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:6px 16px;font-weight:600;cursor:pointer">Save Draft</button>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
              <button class="btn t-docs-btn" id="dyn_${tableNo}_docs_btn" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-2px;margin-right:4px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Documentation <span id="dyn_${tableNo}_docs_count" class="t-docs-badge" style="display:none">0</span>
              </button>
              <span id="dyn_${tableNo}_status_badge" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;display:none"></span>
              <span id="dyn_${tableNo}_status_msg" style="font-size:12px;color:var(--text-muted)"></span>
            </div>
          </div>
        </div>`;

        function makeCatHeaderHTML(catName) {
            const colspan = cols.length + 2;
            return `<tr class="dyn-cat-row" data-cat="${escHtml(catName)}"><td colspan="${colspan}" style="font-weight:700;background:var(--bg-soft,#f3f6f4);padding:9px 12px;color:var(--forest,#075b42);border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb">${escHtml(catName)}</td></tr>`;
        }

        function makeRowHTML(dataObj, idx, catKey) {
            const tds = cols.map(c => {
                const colKey = typeof c === "string" ? c : (c.key || c.name || "col");
                const colType = typeof c === "string" ? "text" : (c.type || "text");
                const val = escHtml(dataObj ? (dataObj[colKey] ?? dataObj[c.name] ?? "") : "");
                const disAttr = isLockedForCmi
                    ? ' disabled style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;color:#9ca3af;cursor:not-allowed;"'
                    : ' style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:6px"';
                if (colType === "number") {
                    return `<td><input type="number" class="dyn-col-val" data-key="${escHtml(colKey)}" step="any" placeholder="0" value="${val}"${disAttr}/></td>`;
                } else if (colType === "amount") {
                    return `<td><input type="number" class="dyn-col-val" data-key="${escHtml(colKey)}" step="0.01" min="0" placeholder="0.00" value="${val}"${disAttr}/></td>`;
                } else if (colType === "dropdown") {
                    const rawOptions = c.options || [];
                    const optsArr = Array.isArray(rawOptions) ? rawOptions : String(rawOptions).split(",").map(s => s.trim()).filter(Boolean);
                    const optHtml = ['<option value="">-- Select --</option>'].concat(
                        optsArr.map(o => `<option value="${escHtml(o)}" ${String(val) === String(o) ? 'selected' : ''}>${escHtml(o)}</option>`)
                    ).join("");
                    return `<td><select class="dyn-col-val" data-key="${escHtml(colKey)}"${disAttr}>${optHtml}</select></td>`;
                } else if (colType === "date") {
                    return `<td><input type="date" class="dyn-col-val" data-key="${escHtml(colKey)}" value="${val}"${disAttr}/></td>`;
                } else {
                    return `<td><input type="text" class="dyn-col-val" data-key="${escHtml(colKey)}" placeholder="${escHtml(c.name || colKey)}" value="${val}"${disAttr}/></td>`;
                }
            }).join("");

            const catAttr = catKey ? ` data-cat="${escHtml(catKey)}"` : "";
            const numText = (idx != null) ? `${idx + 1}.` : "1.";
            const catParam = catKey ? `'${escHtml(catKey)}'` : "null";
            const delTd = isLockedForCmi
                ? `<td style="text-align:center"></td>`
                : `<td style="text-align:center">
                    <button type="button" class="row-remove-btn" onclick="this.closest('tr').remove(); CMI.renumberDynRows('${tableNo}', ${catParam});">
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                  </td>`;

            return `
            <tr class="dyn-data-row"${catAttr}>
              <td class="dyn-row-no" style="text-align:center;font-weight:600;font-size:13px">${numText}</td>
              ${tds}
              ${delTd}
            </tr>`;
        }

        const tbody = document.getElementById(`dyn_${tableNo}_rows`);
        const saveBtn = document.getElementById(`dyn_${tableNo}_save_btn`);
        const docsBtn = document.getElementById(`dyn_${tableNo}_docs_btn`);
        const msgEl = document.getElementById(`dyn_${tableNo}_status_msg`);

        if (hasCategories) {
            container.querySelectorAll(".dyn-add-cat-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const cat = this.getAttribute("data-cat");
                    const existingCatRows = tbody.querySelectorAll(`tr.dyn-data-row[data-cat="${cat}"]`);
                    const count = existingCatRows.length;
                    const newRowHTML = makeRowHTML({}, count, cat);
                    if (count > 0) {
                        existingCatRows[count - 1].insertAdjacentHTML("afterend", newRowHTML);
                    } else {
                        const header = tbody.querySelector(`tr.dyn-cat-row[data-cat="${cat}"]`);
                        if (header) {
                            header.insertAdjacentHTML("afterend", newRowHTML);
                        } else {
                            tbody.insertAdjacentHTML("beforeend", newRowHTML);
                        }
                    }
                    CMI.renumberDynRows(tableNo, cat);
                });
            });
        } else {
            const addBtn = document.getElementById(`dyn_${tableNo}_add_btn`);
            if (addBtn) {
                addBtn.addEventListener("click", function () {
                    const count = tbody.querySelectorAll("tr.dyn-data-row").length;
                    tbody.insertAdjacentHTML("beforeend", makeRowHTML({}, count, null));
                    CMI.renumberDynRows(tableNo, null);
                });
            }
        }

        let _dynImages = [];

        function updateDynDocsBadge() {
            const badge = document.getElementById(`dyn_${tableNo}_docs_count`);
            if (!badge) return;
            if (_dynImages.length > 0) {
                badge.textContent = _dynImages.length;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }

        docsBtn.addEventListener("click", function () {
            if (typeof DocsModal !== "undefined" && typeof DocsModal.open === "function") {
                DocsModal.open(tableNo, _dynImages, function (saved) {
                    _dynImages = saved;
                    updateDynDocsBadge();
                });
            }
        });

        function performDynamicSave(requestedStatus) {
            const trs = tbody.querySelectorAll("tr.dyn-data-row");
            const rows = [];
            trs.forEach(tr => {
                const r = {};
                if (tr.dataset.cat) r._cat = tr.dataset.cat;
                tr.querySelectorAll(".dyn-col-val").forEach(inp => {
                    const k = inp.getAttribute("data-key");
                    if (k) r[k] = inp.value;
                });
                rows.push(r);
            });

            let hasData = false;
            rows.forEach(r => {
                for (let k in r) {
                    if (k !== '_cat' && String(r[k] || '').trim() !== '') { hasData = true; break; }
                }
            });
            const computedStatus = (requestedStatus === 'draft') ? 'draft' : (hasData ? 'done' : 'not-started');

            if (msgEl) msgEl.textContent = "Saving...";
            const payload = {
                table_no: tableNo,
                year: window.CMI_REPORTING_YEAR || new Date().getFullYear(),
                cmi_user_id: window.CMI_TARGET_USER_ID || 0,
                status: computedStatus,
                meta: { images: _dynImages },
                rows: rows
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const headers = { "Content-Type": "application/json" };
            if (csrfToken) headers["X-CSRF-TOKEN"] = csrfToken;

            return fetch(API_SAVE, {
                method: "POST",
                headers: headers,
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                const toastFn = (typeof window.showToast === "function") ? window.showToast : ((typeof showToast === "function") ? showToast : null);
                if (res.ok || res.success) {
                    if (msgEl) msgEl.textContent = computedStatus === 'draft' ? "Saved draft" : "Saved (Complete)";
                    CMI.updateStatus(tableNo, computedStatus);
                    if (toastFn) toastFn(computedStatus === 'draft' ? "Table draft saved successfully." : "Table data saved successfully.");
                } else {
                    if (msgEl) msgEl.textContent = "Error saving";
                    if (toastFn) toastFn(res.message || "Failed to save data.");
                }
                return res;
            })
            .catch((err) => {
                if (msgEl) msgEl.textContent = "Error saving";
                const toastFn = (typeof window.showToast === "function") ? window.showToast : ((typeof showToast === "function") ? showToast : null);
                if (toastFn) toastFn("Error saving data.");
                throw err;
            });
        }

        const dynModule = {
            save: function (status) {
                return performDynamicSave(status || 'done');
            }
        };

        const modCandidates = [
            tableNo,
            normalizeTableNo(tableNo),
            String(tableNo).replace(/^T/i, ''),
            'T' + String(tableNo).replace(/^T/i, ''),
            String(tableNo).toUpperCase(),
            String(tableNo).toLowerCase()
        ];
        modCandidates.forEach(c => {
            _tableModules[c] = dynModule;
        });

        if (isNaN(Number(tableNo))) {
            try {
                window[tableNo] = dynModule;
                window[tableNo.toUpperCase()] = dynModule;
                window[tableNo.toLowerCase()] = dynModule;
            } catch (e) {}
        }

        if (saveBtn) {
            if (isLockedForCmi) saveBtn.style.display = "none";
            saveBtn.onclick = function () {
                performDynamicSave('done');
            };
        }

        const draftBtn = document.getElementById(`dyn_${tableNo}_draft_btn`);
        if (draftBtn) {
            if (isLockedForCmi) draftBtn.style.display = "none";
            draftBtn.onclick = function () {
                performDynamicSave('draft');
            };
        }

        // Load existing row data
        const yr = window.CMI_REPORTING_YEAR || new Date().getFullYear();
        const targetUser = window.CMI_TARGET_USER_ID ? `&cmi_user_id=${window.CMI_TARGET_USER_ID}` : "";
        fetch(`/api/cmi/tables/load?table_no=${tableNo}&year=${yr}${targetUser}`)
            .then(r => r.json())
            .then(res => {
                const savedRows = (res && Array.isArray(res.rows)) ? res.rows : [];
                if (hasCategories) {
                    let html = "";
                    categories.forEach(cat => {
                        html += makeCatHeaderHTML(cat);
                        const catRows = savedRows.filter(r => r._cat === cat);
                        if (catRows.length > 0) {
                            catRows.forEach((r, i) => {
                                html += makeRowHTML(r, i, cat);
                            });
                        } else {
                            html += makeRowHTML({}, 0, cat);
                        }
                    });
                    tbody.innerHTML = html;
                } else {
                    const rowsData = savedRows.length > 0 ? savedRows : [{}];
                    tbody.innerHTML = rowsData.map((r, i) => makeRowHTML(r, i, null)).join("");
                }
                if (res && res.status) {
                    CMI.updateStatus(tableNo, res.status);
                }
                _dynImages = (res && Array.isArray(res.docs)) ? res.docs : ((res && res.meta && res.meta.images) ? res.meta.images : []);
                updateDynDocsBadge();
                applyLock(tableNo);
            })
            .catch(() => {
                if (hasCategories) {
                    let html = "";
                    categories.forEach(cat => {
                        html += makeCatHeaderHTML(cat);
                        html += makeRowHTML({}, 0, cat);
                    });
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = makeRowHTML({}, 0, null);
                }
                _dynImages = [];
                updateDynDocsBadge();
                applyLock(tableNo);
            });
    }

    CMI.renumberDynRows = function (tableNo, catKey) {
        const tbody = document.getElementById(`dyn_${tableNo}_rows`);
        if (!tbody) return;
        if (catKey) {
            tbody.querySelectorAll(`tr.dyn-data-row[data-cat="${catKey}"]`).forEach((tr, i) => {
                const numCell = tr.querySelector(".dyn-row-no");
                if (numCell) numCell.textContent = `${i + 1}.`;
            });
        } else {
            tbody.querySelectorAll("tr.dyn-data-row").forEach((tr, i) => {
                const numCell = tr.querySelector(".dyn-row-no");
                if (numCell) numCell.textContent = `${i + 1}.`;
            });
        }
    };

    /* ─────────────────────────────────────────
     HELPER: ACTIVE TABLE NOS
  ───────────────────────────────────────── */
    function getActiveTableNos() {
        const sections = _activeSections && _activeSections.length > 0 ? _activeSections : SECTIONS;
        const allNos = [];
        sections.forEach(function (s) {
            if (Array.isArray(s.tables)) {
                s.tables.forEach(function (n) {
                    if (!allNos.includes(n)) allNos.push(n);
                });
            }
        });
        if (allNos.length === 0) {
            const titlesObj = _activeTitles && Object.keys(_activeTitles).length > 0 ? _activeTitles : TABLE_TITLES;
            allNos.push(...Object.keys(titlesObj));
        }
        return allNos;
    }

    /* ─────────────────────────────────────────
     LOCK — per table, only if in submitted snapshot
  ───────────────────────────────────────── */
    let _isApplyingLock = false;

    function applyLock(tableNo) {
        if (_isApplyingLock) return;
        _isApplyingLock = true;

        try {
            const body = document.getElementById("fillBody");
            if (!body) return;

            const canonNo = normalizeTableNo(tableNo);
            const tDef = _templateDefs[tableNo] || _templateDefs[canonNo] || _templateDefs[String(tableNo).toUpperCase()] || _templateDefs[String(tableNo).toLowerCase()];
            const isPtaLocked = tDef && !!tDef.is_locked;
            const isTableSubmitted = (_isSubmitted && Array.isArray(_submittedTables) && _submittedTables.some(t => {
                const tu = String(t).toUpperCase();
                return tu === String(tableNo).toUpperCase() || tu === String(canonNo).toUpperCase() || normalizeTableNo(t) === canonNo;
            })) || (_status[tableNo] === 'submitted' || _status[tableNo] === 'accepted' || _status[canonNo] === 'submitted' || _status[canonNo] === 'accepted');

            // Clean up mismatching banners cleanly without thrashing DOM
            if (!isPtaLocked || window.IS_PTA_USER) {
                body.querySelector("#cmi-locked-cmi-banner")?.remove();
            }
            if (!isPtaLocked || !window.IS_PTA_USER) {
                body.querySelector("#cmi-pta-admin-banner")?.remove();
            }
            if (!isTableSubmitted) {
                body.querySelector("#cmi-submitted-banner")?.remove();
            }

            // 1. Check if table is locked for CMI (PTA-only table)
            if (isPtaLocked) {
                if (window.IS_PTA_USER) {
                    // PTA Administrator Mode: Authorized to edit
                    if (!body.querySelector("#cmi-pta-admin-banner")) {
                        const banner = document.createElement("div");
                        banner.id = "cmi-pta-admin-banner";
                        banner.style.cssText = "display:flex;align-items:center;gap:12px;background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:10px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:#065f46;";
                        banner.innerHTML = `
                          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                          <div>
                            <strong>PTA Administrator Mode</strong><br>
                            <span style="font-size:12px;color:#047857">Table ${tableNo} is locked for CMI encoding. As a PTA administrator, you are authorized to fill out, edit, and save records here.</span>
                          </div>`;
                        body.insertBefore(banner, body.firstChild);
                    }

                    body.querySelectorAll("input, select, textarea").forEach((el) => {
                        el.disabled = false;
                        el.style.background = "";
                        el.style.color = "";
                        el.style.cursor = "";
                    });
                    body.querySelectorAll("button").forEach((el) => {
                        if (el.style.display === "none") el.style.display = "";
                    });
                    return;
                } else {
                    // CMI User Mode: Restricted
                    if (!body.querySelector("#cmi-locked-cmi-banner")) {
                        const banner = document.createElement("div");
                        banner.id = "cmi-locked-cmi-banner";
                        banner.style.cssText = "display:flex;align-items:center;gap:12px;background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#9a3412;box-shadow:0 2px 8px rgba(217,119,6,0.06);";
                        banner.innerHTML = `
                          <div style="width:36px;height:36px;border-radius:8px;background:#ffedd5;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#c2410c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                          </div>
                          <div>
                            <strong style="font-size:13.5px;color:#9a3412">Table Locked by PTA</strong><br>
                            <span style="font-size:12px;color:#b45309">This table is designated as PTA-only. Only PTA administrators can input or fill out this report. CMI users cannot make entries here.</span>
                          </div>`;
                        body.insertBefore(banner, body.firstChild);
                    }

                    body.querySelectorAll("input, select, textarea").forEach((el) => {
                        el.disabled = true;
                        el.style.background = "#f9fafb";
                        el.style.color = "#9ca3af";
                        el.style.cursor = "not-allowed";
                    });
                    body.querySelectorAll("button").forEach((el) => {
                        const text = (el.textContent || '').toLowerCase();
                        const title = (el.getAttribute("title") || '').toLowerCase();
                        const isDocs = el.classList.contains("t-docs-btn") || (el.getAttribute("onclick") || '').includes("openDocs") || el.id?.includes('doc') || el.classList.contains('btn-docs');
                        if (isDocs) {
                            el.disabled = true;
                            el.style.pointerEvents = "none";
                            el.style.opacity = "0.5";
                            el.style.cursor = "not-allowed";
                            return;
                        }
                        const isActionBtn = text.includes('save') || text.includes('add') || text.includes('remove') || text.includes('delete') ||
                                            title.includes('remove') || title.includes('delete') ||
                                            el.classList.contains("row-remove-btn") || el.classList.contains("row-del-btn") || el.classList.contains("btn-del") ||
                                            el.dataset.action === 'save';
                        if (isActionBtn) {
                            el.style.display = "none";
                        }
                    });
                    return;
                }
            }

            // 2. Check if whole report or table is submitted
            if (isTableSubmitted) {
                body.querySelectorAll("input, select, textarea").forEach((el) => {
                    el.disabled = true;
                    el.style.background = "#f5f5f5";
                    el.style.color = "#999";
                    el.style.cursor = "not-allowed";
                });
                body.querySelectorAll("button[onclick], button").forEach((el) => {
                    const onclickAttr = el.getAttribute("onclick") || "";
                    const text = (el.textContent || '').toLowerCase();
                    const title = (el.getAttribute("title") || '').toLowerCase();
                    const isDocs = el.classList.contains("t-docs-btn") || onclickAttr.includes("openDocs") || el.id?.includes('doc') || el.classList.contains('btn-docs');
                    if (isDocs) {
                        el.disabled = true;
                        el.style.pointerEvents = "none";
                        el.style.opacity = "0.5";
                        el.style.cursor = "not-allowed";
                        return;
                    }
                    const isActionBtn = text.includes('save') || text.includes('add') || text.includes('remove') || text.includes('delete') ||
                                        title.includes('remove') || title.includes('delete') ||
                                        el.classList.contains("row-remove-btn") || el.classList.contains("row-del-btn") || el.classList.contains("btn-del") ||
                                        el.dataset.action === 'save';
                    if (onclickAttr || isActionBtn) {
                        el.style.display = "none";
                    }
                });

                const topDraftBtn = document.getElementById("btn-save-draft");
                if (topDraftBtn) {
                    topDraftBtn.disabled = true;
                    topDraftBtn.style.opacity = "0.5";
                    topDraftBtn.style.cursor = "not-allowed";
                    topDraftBtn.style.pointerEvents = "none";
                }

                if (!body.querySelector("#cmi-submitted-banner")) {
                    const date = _submittedAt
                        ? new Date(_submittedAt).toLocaleString("en-PH", {
                              year: "numeric",
                              month: "long",
                              day: "numeric",
                              hour: "2-digit",
                              minute: "2-digit",
                              timeZone: "Asia/Manila",
                          })
                        : "";
                    const banner = document.createElement("div");
                    banner.id = "cmi-submitted-banner";
                    banner.style.cssText = "display:flex;align-items:center;gap:12px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:#1b5e20;";
                    banner.innerHTML = `
                      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
                      <div>
                        <strong>Annual Accomplishment Report Submitted</strong><br>
                        <span style="font-size:12px;opacity:.85">Submitted on ${date}. This table is read-only.</span>
                      </div>`;
                    body.insertBefore(banner, body.firstChild);
                }
                return;
            }

            // 3. Open / Editable table
            body.querySelectorAll("input, select, textarea").forEach((el) => {
                el.disabled = false;
                el.style.background = "";
                el.style.color = "";
                el.style.cursor = "";
            });
            body.querySelectorAll("button").forEach((el) => {
                el.disabled = false;
                el.style.pointerEvents = "";
                el.style.opacity = "";
                el.style.cursor = "";
                if (el.style.display === "none") {
                    el.style.display = "";
                }
            });
            const topDraftBtn = document.getElementById("btn-save-draft");
            if (topDraftBtn) {
                topDraftBtn.disabled = false;
                topDraftBtn.style.opacity = "";
                topDraftBtn.style.cursor = "";
                topDraftBtn.style.pointerEvents = "";
            }
        } finally {
            _isApplyingLock = false;
        }
    }

    CMI.applyLock = applyLock;

    CMI.isLocked = function (no) {
        if (!no) no = window._cmiActiveTable;
        if (!no) return false;
        const canon = normalizeTableNo(no);
        const canonUpper = String(canon).toUpperCase();
        const canonLower = String(canon).toLowerCase();
        const rawUpper = String(no).toUpperCase();
        const rawLower = String(no).toLowerCase();

        // 1. Check if table is designated as PTA-only and locked for CMI users
        const tDef = _templateDefs[no] || _templateDefs[canon] || _templateDefs[rawUpper] || _templateDefs[rawLower] || _templateDefs[canonUpper] || _templateDefs[canonLower];
        if (tDef && !!tDef.is_locked && !window.IS_PTA_USER) {
            return true;
        }

        // 2. Check if this specific table is officially submitted or accepted
        const st = _status[no] || _status[canon] || _status[rawUpper] || _status[rawLower] || _status[canonUpper] || _status[canonLower];
        if (st === 'submitted' || st === 'accepted') {
            return true;
        }

        // 3. If the report was submitted, check if this specific table was included in the submitted tables list
        if (_isSubmitted && Array.isArray(_submittedTables)) {
            const inList = _submittedTables.some(t => {
                const tu = String(t).toUpperCase();
                return tu === rawUpper || tu === canonUpper || normalizeTableNo(t) === canon;
            });
            if (inList) return true;
        }

        return false;
    };

    CMI.lockReport = function () {
        _isSubmitted = true;
        const no = window._cmiActiveTable;
        if (no && CMI.isLocked(no)) applyLock(no);
    };

    CMI.saveDraft = function () {
        const active = window._cmiActiveTable || 'T1';
        const sActive = String(active).trim();
        const normActive = normalizeTableNo(sActive);
        const cleanActive = sActive.replace(/^T/i, '');
        const withTActive = 'T' + cleanActive;
        const candidates = [sActive, normActive, cleanActive, withTActive, sActive.toUpperCase(), sActive.toLowerCase()];

        let tDef = null;
        for (const c of candidates) {
            if (_templateDefs[c]) { tDef = _templateDefs[c]; break; }
        }

        const isPtaLocked = tDef && !!tDef.is_locked;
        if (isPtaLocked && !window.IS_PTA_USER) {
            const toastFn = (typeof window.showToast === "function") ? window.showToast : ((typeof showToast === "function") ? showToast : null);
            if (toastFn) toastFn("Table " + active + " is designated as PTA-only and locked for CMI entry.");
            return;
        }

        window._cmiSavingDraft = true;
        const mod = getTableModule(active);
        if (mod && typeof mod.save === 'function') {
            mod.save('draft');
        } else {
            const body = document.getElementById("fillBody");
            if (body) {
                const draftBtn = body.querySelector('button[data-action="draft"]');
                const saveBtn =
                    body.querySelector('button[data-action="save"]') ||
                    [...body.querySelectorAll("button")].find((b) =>
                        /save/i.test(b.textContent),
                    );
                if (draftBtn) {
                    draftBtn.click();
                } else if (saveBtn) {
                    saveBtn.click();
                } else {
                    const toastFn = (typeof window.showToast === "function") ? window.showToast : ((typeof showToast === "function") ? showToast : null);
                    if (toastFn) toastFn("Nothing to save yet — add some data first.");
                }
            }
        }
        setTimeout(function () {
            window._cmiSavingDraft = false;
        }, 1200);
    };

    /* ─────────────────────────────────────────
     LOCK WATCHDOG (Safely disabled to prevent recursive loops)
  ───────────────────────────────────────── */
    function ensureBodyObserver() {
        // No-op: applyLock is explicitly called during table rendering without recursive observers
    }

    /* ─────────────────────────────────────────
     CUSTOM CONFIRM MODAL
  ───────────────────────────────────────── */
    function cmiConfirm({ title, html, okLabel = "OK", onOk }) {
        if (!document.getElementById("cmi-confirm-overlay")) {
            const el = document.createElement("div");
            el.id = "cmi-confirm-overlay";
            el.style.cssText = [
                "position:fixed;inset:0;z-index:9999",
                "background:rgba(0,0,0,.45)",
                "display:flex;align-items:center;justify-content:center",
                "opacity:0;transition:opacity .15s",
            ].join(";");
            el.innerHTML = `
        <div id="cmi-confirm-box" style="
          background:#fff;border-radius:12px;
          box-shadow:0 8px 32px rgba(0,0,0,.22);
          width:min(420px,92vw);overflow:hidden;
          transform:translateY(12px);transition:transform .15s;
        ">
          <div id="cmi-confirm-hdr" style="
            padding:18px 22px 14px;
            font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;
            color:#1a3a1a;border-bottom:1px solid #e5ede5;
          "></div>
          <div id="cmi-confirm-body" style="
            padding:16px 22px;font-size:13.5px;line-height:1.65;color:#333;
          "></div>
          <div style="
            display:flex;gap:10px;justify-content:flex-end;
            padding:14px 22px;background:#f7faf7;border-top:1px solid #e5ede5;
          ">
            <button id="cmi-confirm-cancel" style="
              padding:8px 20px;border-radius:7px;border:1px solid #ccc;
              background:#fff;font-size:13px;cursor:pointer;font-family:inherit;
            ">Cancel</button>
            <button id="cmi-confirm-ok" style="
              padding:8px 22px;border-radius:7px;border:none;
              background:#2e7d32;color:#fff;font-size:13px;
              font-weight:600;cursor:pointer;font-family:inherit;
            "></button>
          </div>
        </div>`;
            document.body.appendChild(el);

            const close = () => {
                el.style.opacity = "0";
                document.getElementById("cmi-confirm-box").style.transform = "translateY(12px)";
                setTimeout(() => (el.style.display = "none"), 160);
            };
            document.getElementById("cmi-confirm-cancel").addEventListener("click", close);
            el.addEventListener("click", (e) => {
                if (e.target === el) close();
            });
        }

        const overlay = document.getElementById("cmi-confirm-overlay");
        const box = document.getElementById("cmi-confirm-box");
        const okBtn = document.getElementById("cmi-confirm-ok");

        document.getElementById("cmi-confirm-hdr").textContent = title;
        document.getElementById("cmi-confirm-body").innerHTML = html;
        okBtn.textContent = okLabel;

        const newOk = okBtn.cloneNode(true);
        okBtn.parentNode.replaceChild(newOk, okBtn);
        newOk.textContent = okLabel;
        newOk.addEventListener("click", () => {
            overlay.style.display = "none";
            if (typeof onOk === "function") onOk();
        });

        overlay.style.display = "flex";
        requestAnimationFrame(() => {
            overlay.style.opacity = "1";
            box.style.transform = "translateY(0)";
        });
    }
    CMI.submitReport = async function () {
        window._cmiIsSubmitting = true;
        const yr = window.CMI_REPORTING_YEAR || (document.getElementById('fillupYearSel') ? parseInt(document.getElementById('fillupYearSel').value) : new Date().getFullYear());
        const targetUserId = window.CMI_TARGET_USER_ID || 0;
        const q = targetUserId ? `&cmi_user_id=${targetUserId}` : '';

        // Fetch fresh statuses from backend first so draft detection is 100% accurate
        try {
            const stRes = await fetch(`/api/cmi/tables/statuses?year=${yr}${q}`);
            const stData = await stRes.json();
            if (stData && stData.statuses) {
                for (let k in stData.statuses) {
                    _status[k] = stData.statuses[k];
                    _status[k.toUpperCase()] = stData.statuses[k];
                    _status[k.toLowerCase()] = stData.statuses[k];
                }
                renderFillNav();
            }
        } catch(e) {}

        const allNos = getActiveTableNos();
        const getStatusFor = (n) => {
            return _status[n] || _status[n.toUpperCase()] || _status[n.toLowerCase()] || "not-started";
        };
        const done = allNos.filter((n) => ['done', 'accepted', 'submitted'].includes(getStatusFor(n))).length;
        const draftTableNos = allNos.filter((n) => getStatusFor(n) === "draft");
        const draft = draftTableNos.length;
        const blank = allNos.filter((n) => getStatusFor(n) === "not-started").length;
        const total = allNos.length;

        const pct = total > 0 ? Math.round((done / total) * 100) : 0;
        const bar = `
      <div style="background:#e8f5e9;border-radius:6px;height:8px;margin:10px 0 4px;overflow:hidden">
        <div style="width:${pct}%;height:100%;background:#2e7d32;border-radius:6px;transition:width .4s"></div>
      </div>
      <div style="font-size:11px;color:#666;margin-bottom:12px">${pct}% complete</div>`;

        const rows = [
            [SUBMIT_ICONS.done, "Complete", done, "#2e7d32"],
            [SUBMIT_ICONS.draft, "Draft", draft, "#e65100"],
            [SUBMIT_ICONS.blank, "Blank", blank, "#888"],
        ]
            .map(
                ([icon, label, count, color]) =>
                    `<div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f0f0f0">
        <span style="display:flex;align-items:center;gap:6px">${icon} ${label}</span>
        <strong style="color:${color}">${count} / ${total}</strong>
      </div>`,
            )
            .join("");

        if (draft > 0) {
            const draftList = draftTableNos.join(', ');
            cmiConfirm({
                title: `Cannot Submit CY ${yr} Annual Report`,
                html: `${bar}${rows}
            <div style="margin:14px 0 0;padding:12px;background:#fff7ed;border:1px solid #fdba74;border-radius:8px;font-size:12.5px;color:#c2410c;line-height:1.4">
              <strong>Draft Tables Detected (${draft}):</strong><br>
              Table(s) <strong>${draftList}</strong> are currently saved as draft.<br>
              Draft reports cannot be submitted. All draft tables must be saved and completed before submitting your report.
            </div>`,
                okLabel: "Review Drafts",
                onOk() {
                    window._cmiIsSubmitting = false;
                    if (draftTableNos[0] && typeof CMI.showTable === 'function') {
                        CMI.showTable(draftTableNos[0]);
                    }
                },
                onCancel() {
                    window._cmiIsSubmitting = false;
                }
            });
            return;
        }

        if (done === 0) {
            cmiConfirm({
                title: `Cannot Submit CY ${yr} Annual Report`,
                html: `${bar}${rows}
            <div style="margin:14px 0 0;padding:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;font-size:12.5px;color:#b91c1c;line-height:1.4">
              <strong>No Completed Tables:</strong><br>
              Please complete and save at least one table before submitting your report.
            </div>`,
                okLabel: "OK",
                onOk() {
                    window._cmiIsSubmitting = false;
                },
                onCancel() {
                    window._cmiIsSubmitting = false;
                }
            });
            return;
        }

        cmiConfirm({
            title: `Submit CY ${yr} Annual Report`,
            html: `${bar}${rows}
        <p style="margin:14px 0 0;font-size:12px;color:#888">
          Report tables will be submitted to PTA.<br>
          You can view your submitted tables under <em>My Submissions</em>.
        </p>`,
            okLabel: "Submit Now",
            onOk() {
                window.openSubmitModal();
            },
            onCancel() {
                window._cmiIsSubmitting = false;
            }
        });
    };

    window.openSubmitModal = function () {
        const modal = document.getElementById("modalConfirmSubmit");
        const yrText = document.getElementById("confirmSubmitYearText");
        if (yrText) yrText.textContent = "CY " + (window.CMI_REPORTING_YEAR || (document.getElementById('fillupYearSel') ? parseInt(document.getElementById('fillupYearSel').value) : new Date().getFullYear()));
        if (modal) modal.style.display = "flex";
    };

    window.closeSubmitModal = function () {
        window._cmiIsSubmitting = false;
        const modal = document.getElementById("modalConfirmSubmit");
        if (modal) modal.style.display = "none";
    };

    window.confirmAndExecuteSubmit = async function () {
        window._cmiIsSubmitting = true;
        const allNos = getActiveTableNos();
        const getStatusFor = (n) => {
            return _status[n] || _status[n.toUpperCase()] || _status[n.toLowerCase()] || "not-started";
        };
        const draftTableNos = allNos.filter((n) => getStatusFor(n) === "draft");
        if (draftTableNos.length > 0) {
            window._cmiIsSubmitting = false;
            window.closeSubmitModal();
            toast(`Cannot submit: Table(s) ${draftTableNos.join(', ')} are in draft. Please complete and save them before submitting.`, 5000);
            return;
        }

        const done = allNos.filter((n) => ['done', 'accepted', 'submitted'].includes(getStatusFor(n))).length;
        if (done === 0) {
            window._cmiIsSubmitting = false;
            window.closeSubmitModal();
            toast('Cannot submit: No completed tables found. Please complete at least one table before submitting.', 5000);
            return;
        }

        const btn = document.querySelector('#modalConfirmSubmit .btn-submit-report');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span> Submitting...';
        }

        const yr = window.CMI_REPORTING_YEAR || (document.getElementById('fillupYearSel') ? parseInt(document.getElementById('fillupYearSel').value) : new Date().getFullYear());

        toast("Submitting report…", 99999);
        try {
            const r = await fetch(API_SUBMIT, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ year: yr, submitted_at_client: new Date().toISOString() })
            });
            const res = await r.json();
            const wrap = document.getElementById("toastWrap");
            if (wrap) wrap.innerHTML = "";

            if (res.success) {
                window.closeSubmitModal();
                toast("Report submitted successfully! It is now pending PTA review.");
                _isSubmitted = true;
                _submittedAt = res.submitted_at || new Date().toISOString();

                // Refresh fresh statuses from backend so all submitted tables update in memory and sidebar immediately
                try {
                    const stRes = await fetch('/api/cmi/tables/statuses?year=' + yr);
                    const stData = await stRes.json();
                    if (stData && typeof CMI.setStatuses === 'function') {
                        CMI.setStatuses(stData.statuses || {}, stData);
                    }
                } catch(err) {
                    allNos.forEach(n => {
                        const cur = _status[n] || _status[n.toUpperCase()] || _status[n.toLowerCase()];
                        if (cur === 'draft' || cur === 'done') {
                            _status[n] = 'submitted';
                            _status[n.toUpperCase()] = 'submitted';
                            _status[n.toLowerCase()] = 'submitted';
                        }
                    });
                    renderFillNav();
                }

                _submittedTables = allNos.filter((n) => {
                    const s = _status[n] || _status[n.toUpperCase()] || _status[n.toLowerCase()];
                    return s === "done" || s === "submitted" || s === "accepted";
                });
                CMI.lockReport();
            } else {
                toast("Submission failed: " + (res.error || res.message || "Unknown error"));
            }
        } catch(e) {
            const wrap = document.getElementById("toastWrap");
            if (wrap) wrap.innerHTML = "";
            toast("Network error — please try again.");
        } finally {
            window._cmiIsSubmitting = false;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Yes, Submit Report';
            }
            window.closeSubmitModal();
        }
    };

    /* ─────────────────────────────────────────
     INIT — load statuses then show T1
  ───────────────────────────────────────── */
    document.addEventListener("DOMContentLoaded", function () {
        renderFillNav();
        ensureBodyObserver();

        const btnSubmit = document.getElementById("btn-submit");
        if (btnSubmit) btnSubmit.addEventListener("click", CMI.submitReport);
    });
})();
