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
        _registry[def.no] = def;
    };

    let _activeSections = SECTIONS;
    let _activeTitles = Object.assign({}, TABLE_TITLES);
    let _templateDefs = {};

    CMI.setFormatTemplates = function (templates) {
        // Always start with deep copy of default SECTIONS so all 24 standard tables are preserved
        const sections = SECTIONS.map(s => ({
            label: s.label,
            tables: [...s.tables]
        }));
        const titles = Object.assign({}, TABLE_TITLES);
        _templateDefs = {};

        if (Array.isArray(templates) && templates.length > 0) {
            templates.forEach(function (t) {
                const secName = t.section || "General";
                let secObj = sections.find(s => s.label === secName);
                if (!secObj) {
                    secObj = { label: secName, tables: [] };
                    sections.push(secObj);
                }
                if (!secObj.tables.includes(t.table_no)) {
                    secObj.tables.push(t.table_no);
                }

                let shortTitle = t.title || t.table_no;
                if (shortTitle.match(/^Table\s+[A-Za-z0-9.]+\s*[–-]?\s*/i)) {
                    shortTitle = shortTitle.replace(/^Table\s+[A-Za-z0-9.]+\s*[–-]?\s*/i, "").trim();
                }
                titles[t.table_no] = shortTitle || t.title || t.table_no;
                _templateDefs[t.table_no] = t;
            });
        }

        _activeSections = sections;
        _activeTitles = titles;
        renderFillNav();
    };

    CMI.setStatuses = function (statuses, meta = null) {
        for (let k in _status) delete _status[k];
        Object.assign(_status, statuses || {});

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
        _status[no] = status;
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
                    return (
                        `<div class="fill-nav-sub ${st}${active}" onclick="CMI.showTable('${no}')">` +
                        `${STATUS_ICON[st] ?? STATUS_ICON["not-started"]} ${no} — ${ttl}</div>`
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

        let def = _registry[no] || _registry[no.toUpperCase()] || _registry[no.toLowerCase()];
        if (!def) {
            const matchKey = Object.keys(_registry).find(k => k.toLowerCase() === String(no).toLowerCase());
            if (matchKey) def = _registry[matchKey];
        }

        if (!def) {
            let tDef = _templateDefs[no] || _templateDefs[no.toUpperCase()] || _templateDefs[no.toLowerCase()];
            if (!tDef) {
                const matchT = Object.keys(_templateDefs).find(k => k.toLowerCase() === String(no).toLowerCase());
                if (matchT) tDef = _templateDefs[matchT];
            }
            if (tDef) {
                const canonicalNo = tDef.table_no;
                window._cmiActiveTable = canonicalNo;
                renderFillNav();
                renderDynamicTable(canonicalNo, tDef, body);
                if (_isSubmitted && (_submittedTables.includes(canonicalNo) || _submittedTables.includes(no))) {
                    setTimeout(() => applyLock(canonicalNo), 600);
                }
                return;
            }

            body.innerHTML =
                `<div style="padding:32px;color:var(--text-muted);font-size:14px">` +
                `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right:6px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>` +
                `Table <strong>${no}</strong> is not defined for this year.</div>`;
            window._cmiActiveTable = no;
            renderFillNav();
            return;
        }

        const canonicalNo = def.no || no;
        window._cmiActiveTable = canonicalNo;
        renderFillNav();
        def.render(body);

        if (_isSubmitted && (_submittedTables.includes(canonicalNo) || _submittedTables.includes(no))) {
            // Defer lock until after async table loadData() completes
            setTimeout(() => applyLock(canonicalNo), 600);
        }
    };

    /* ─────────────────────────────────────────
     DYNAMIC TABLE RENDERER FOR FORMAT TEMPLATES
  ───────────────────────────────────────── */
    function renderDynamicTable(tableNo, tDef, container) {
        const title = tDef.title || `Table ${tableNo}`;
        let cols = [];
        if (Array.isArray(tDef.columns_json)) {
            cols = tDef.columns_json;
        } else if (typeof tDef.columns_json === "string") {
            try { cols = JSON.parse(tDef.columns_json); } catch (e) { cols = []; }
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

        const ths = cols.map(c => {
            const name = typeof c === "string" ? c : (c.name || c.label || "Column");
            return `<th class="group">${escHtml(name)}</th>`;
        }).join("") + `<th class="group" style="width:40px;text-align:center"></th>`;

        container.innerHTML = `
        <div class="t-page" id="dyn_${tableNo}_wrap">
          <div class="t-hdr">
            <div class="t-title">${escHtml(title)}</div>
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
          <div class="t-footer" style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:8px">
              <button class="btn btn-sm" id="dyn_${tableNo}_add_btn" type="button">+ Add Row</button>
              <button class="btn btn-sm" id="dyn_${tableNo}_save_btn" type="button" data-action="save" style="background:#2e7d32;color:#fff;border:none">Save</button>
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

        function makeRowHTML(dataObj, idx) {
            const tds = cols.map(c => {
                const colKey = typeof c === "string" ? c : (c.key || c.name || "col");
                const colType = typeof c === "string" ? "text" : (c.type || "text");
                const val = escHtml(dataObj ? (dataObj[colKey] ?? dataObj[c.name] ?? "") : "");
                if (colType === "number") {
                    return `<td><input type="number" class="dyn-col-val" data-key="${escHtml(colKey)}" step="any" placeholder="0" value="${val}" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:6px"/></td>`;
                } else if (colType === "date") {
                    return `<td><input type="date" class="dyn-col-val" data-key="${escHtml(colKey)}" value="${val}" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:6px"/></td>`;
                } else {
                    return `<td><input type="text" class="dyn-col-val" data-key="${escHtml(colKey)}" placeholder="${escHtml(c.name || colKey)}" value="${val}" style="width:100%;box-sizing:border-box;padding:6px;border:1px solid #d1d5db;border-radius:6px"/></td>`;
                }
            }).join("");

            return `
            <tr>
              <td class="dyn-row-no" style="text-align:center;font-weight:600;font-size:13px">${idx + 1}</td>
              ${tds}
              <td style="text-align:center">
                <button type="button" class="row-remove-btn" onclick="this.closest('tr').remove(); CMI.renumberDynRows('${tableNo}');">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-1px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                </button>
              </td>
            </tr>`;
        }

        const tbody = document.getElementById(`dyn_${tableNo}_rows`);
        const addBtn = document.getElementById(`dyn_${tableNo}_add_btn`);
        const saveBtn = document.getElementById(`dyn_${tableNo}_save_btn`);
        const docsBtn = document.getElementById(`dyn_${tableNo}_docs_btn`);
        const msgEl = document.getElementById(`dyn_${tableNo}_status_msg`);

        addBtn.addEventListener("click", function () {
            const count = tbody.querySelectorAll("tr").length;
            tbody.insertAdjacentHTML("beforeend", makeRowHTML({}, count));
        });

        docsBtn.addEventListener("click", function () {
            if (typeof openDocsModal === "function") {
                openDocsModal(tableNo, title);
            }
        });

        function performDynamicSave(requestedStatus) {
            const trs = tbody.querySelectorAll("tr");
            const rows = [];
            trs.forEach(tr => {
                const r = {};
                tr.querySelectorAll(".dyn-col-val").forEach(inp => {
                    const k = inp.getAttribute("data-key");
                    if (k) r[k] = inp.value;
                });
                rows.push(r);
            });

            let hasData = false;
            rows.forEach(r => {
                for (let k in r) {
                    if (String(r[k] || '').trim() !== '') { hasData = true; break; }
                }
            });
            const computedStatus = (requestedStatus === 'draft') ? 'draft' : (hasData ? 'done' : 'not-started');

            if (msgEl) msgEl.textContent = "Saving...";
            const payload = {
                table_no: tableNo,
                year: window.CMI_REPORTING_YEAR || new Date().getFullYear(),
                cmi_user_id: window.CMI_TARGET_USER_ID || 0,
                status: computedStatus,
                rows: rows
            };

            fetch(API_SAVE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok || res.success) {
                    if (msgEl) msgEl.textContent = computedStatus === 'draft' ? "Saved draft" : "Saved (Complete)";
                    CMI.updateStatus(tableNo, computedStatus);
                    if (typeof showToast === "function") showToast("Table data saved successfully.");
                } else {
                    if (msgEl) msgEl.textContent = "Error saving";
                    if (typeof showToast === "function") showToast(res.message || "Failed to save data.");
                }
            })
            .catch(() => {
                if (msgEl) msgEl.textContent = "Error saving";
                if (typeof showToast === "function") showToast("Error saving data.");
            });
        }

        saveBtn.addEventListener("click", function () {
            performDynamicSave('done');
        });

        // Load existing row data
        const yr = window.CMI_REPORTING_YEAR || new Date().getFullYear();
        const targetUser = window.CMI_TARGET_USER_ID ? `&cmi_user_id=${window.CMI_TARGET_USER_ID}` : "";
        fetch(`/api/cmi/tables/load?table_no=${tableNo}&year=${yr}${targetUser}`)
            .then(r => r.json())
            .then(res => {
                const rowsData = (res && Array.isArray(res.rows) && res.rows.length > 0) ? res.rows : [{}];
                tbody.innerHTML = rowsData.map((r, i) => makeRowHTML(r, i)).join("");
                if (res && res.status) {
                    CMI.updateStatus(tableNo, res.status);
                }
                if (res && res.docs && typeof updateDocsBadge === "function") {
                    updateDocsBadge(tableNo, res.docs.length);
                }
            })
            .catch(() => {
                tbody.innerHTML = makeRowHTML({}, 0);
            });
    }

    CMI.renumberDynRows = function (tableNo) {
        const tbody = document.getElementById(`dyn_${tableNo}_rows`);
        if (!tbody) return;
        tbody.querySelectorAll("tr").forEach((tr, i) => {
            const numCell = tr.querySelector(".dyn-row-no");
            if (numCell) numCell.textContent = i + 1;
        });
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
    function applyLock(tableNo) {
        const body = document.getElementById("fillBody");
        if (!body) return;

        if (!_submittedTables.includes(tableNo) || !_isSubmitted) {
            const banner = body.querySelector("#cmi-submitted-banner");
            if (banner) banner.remove();
            body.querySelectorAll("input, select, textarea").forEach((el) => {
                el.disabled = false;
                el.style.background = "";
                el.style.color = "";
                el.style.cursor = "";
            });
            body.querySelectorAll("button").forEach((el) => {
                if (el.style.display === "none") {
                    el.style.display = "";
                }
            });
            return;
        }

        body.querySelectorAll("input, select, textarea").forEach((el) => {
            el.disabled = true;
            el.style.background = "#f5f5f5";
            el.style.color = "#999";
            el.style.cursor = "not-allowed";
        });

        body.querySelectorAll("button[onclick]").forEach((el) => {
            const onclickAttr = el.getAttribute("onclick") || "";
            if (!el.classList.contains("t-docs-btn") && !onclickAttr.includes("openDocs")) {
                el.style.display = "none";
            }
        });

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
            banner.style.cssText = [
                "display:flex;align-items:center;gap:12px",
                "background:#e8f5e9;border:1px solid #a5d6a7",
                "border-radius:8px;padding:12px 18px;margin-bottom:16px",
                "font-size:13px;color:#1b5e20",
            ].join(";");
            banner.innerHTML = `
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <div>
          <strong>Annual Accomplishment Report Submitted</strong><br>
          <span style="font-size:12px;opacity:.85">Submitted on ${date}. This table is read-only.</span>
        </div>`;
            body.insertBefore(banner, body.firstChild);
        }
    }

    CMI.lockReport = function () {
        _isSubmitted = true;
        const no = window._cmiActiveTable;
        if (no && _submittedTables.includes(no)) applyLock(no);
    };

    CMI.saveDraft = function () {
        window._cmiSavingDraft = true;
        const active = window._cmiActiveTable || 'T1';
        const winModule = window[active] || window[active.toUpperCase()] || window[active.toLowerCase()];
        if (winModule && typeof winModule.save === 'function') {
            winModule.save('draft');
        } else {
            const body = document.getElementById("fillBody");
            if (body) {
                const saveBtn =
                    body.querySelector('button[data-action="save"]') ||
                    [...body.querySelectorAll("button")].find((b) =>
                        /save/i.test(b.textContent),
                    );
                if (saveBtn) {
                    saveBtn.click();
                } else {
                    if (typeof window.showToast === "function")
                        window.showToast("Nothing to save yet — add some data first.");
                }
            }
        }
        setTimeout(function () {
            window._cmiSavingDraft = false;
        }, 1200);
    };

    /* ─────────────────────────────────────────
     LOCK WATCHDOG
  ───────────────────────────────────────── */
    let _bodyObserver = null;
    function ensureBodyObserver() {
        const body = document.getElementById("fillBody");
        if (!body || _bodyObserver) return;
        _bodyObserver = new MutationObserver(() => {
            const no = window._cmiActiveTable;
            if (_isSubmitted && no && _submittedTables.includes(no)) {
                applyLock(no);
            }
        });
        _bodyObserver.observe(body, { childList: true, subtree: true });
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
    CMI.submitReport = function () {
        const allNos = Object.keys(TABLE_TITLES);
        const done = allNos.filter((n) => ['done', 'accepted', 'submitted'].includes(_status[n])).length;
        const draft = allNos.filter((n) => _status[n] === "draft").length;
        const blank = allNos.filter(
            (n) => !_status[n] || _status[n] === "not-started",
        ).length;
        const total = allNos.length;
        const yr = window.CMI_REPORTING_YEAR || new Date().getFullYear();

        const pct = Math.round((done / total) * 100);
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
        });
    };

    window.openSubmitModal = function () {
        const modal = document.getElementById("modalConfirmSubmit");
        const yrText = document.getElementById("confirmSubmitYearText");
        if (yrText) yrText.textContent = "CY " + (window.CMI_REPORTING_YEAR || new Date().getFullYear());
        if (modal) modal.style.display = "flex";
    };

    window.closeSubmitModal = function () {
        const modal = document.getElementById("modalConfirmSubmit");
        if (modal) modal.style.display = "none";
    };

    window.confirmAndExecuteSubmit = async function () {
        const btn = document.querySelector('#modalConfirmSubmit .btn-submit-report');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span> Submitting...';
        }

        // Formally save the active table as 'done' now that user confirmed final submit
        const active = window._cmiActiveTable || 'T1';
        const winModule = window[active] || window[active.toUpperCase()] || window[active.toLowerCase()];
        if (winModule && typeof winModule.save === 'function') {
            winModule.save('done');
        }

        const yr = window.CMI_REPORTING_YEAR || new Date().getFullYear();
        const allNos = Object.keys(TABLE_TITLES);

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
                toast("Report submitted successfully! It is now pending PTA review.");
                _isSubmitted = true;
                _submittedAt = res.submitted_at || new Date().toISOString();
                _submittedTables = allNos.filter((n) => _status[n] === "done" || _status[n] === "draft");
                CMI.lockReport();
            } else {
                toast("Submission failed: " + (res.error || "Unknown error"));
            }
        } catch(e) {
            const wrap = document.getElementById("toastWrap");
            if (wrap) wrap.innerHTML = "";
            toast("Network error — please try again.");
        } finally {
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
