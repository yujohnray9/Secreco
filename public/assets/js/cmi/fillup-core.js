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
        done: '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>',
        draft: '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        error: '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        "not-started":
            '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
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

    CMI.setStatuses = function (statuses, meta = null) {
        for (let k in _status) delete _status[k];
        Object.assign(_status, statuses || {});

        if (meta && meta.submitted) {
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

        const VALID = new Set(["done", "draft", "error", "not-started"]);

        nav.innerHTML = SECTIONS.map((s) => {
            const items = s.tables
                .map((no) => {
                    const raw = _status[no];
                    const st = VALID.has(raw) ? raw : "not-started";
                    const ttl = TABLE_TITLES[no] || no;
                    const active =
                        window._cmiActiveTable === no ? " active" : "";
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
        const def = _registry[no];
        const body = document.getElementById("fillBody");
        if (!body) return;

        if (!def) {
            body.innerHTML =
                `<div style="padding:32px;color:var(--text-muted);font-size:14px">` +
                `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right:6px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>` +
                `Table <strong>${no}</strong> is not yet implemented.</div>`;
            window._cmiActiveTable = no;
            renderFillNav();
            return;
        }

        window._cmiActiveTable = no;
        renderFillNav();
        def.render(body);

        if (_isSubmitted && _submittedTables.includes(no)) {
            applyLock(no);
        }
    };

    /* ─────────────────────────────────────────
     LOCK — per table, only if in submitted snapshot
  ───────────────────────────────────────── */
    function applyLock(tableNo) {
        const body = document.getElementById("fillBody");
        if (!body) return;

        if (!_submittedTables.includes(tableNo)) return;

        body.querySelectorAll("input, select, textarea").forEach((el) => {
            el.disabled = true;
            el.style.background = "#f5f5f5";
            el.style.color = "#999";
            el.style.cursor = "not-allowed";
        });

        body.querySelectorAll("button[onclick]").forEach((el) => {
            el.style.display = "none";
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
          <strong style="font-size:13.5px">Report Already Submitted</strong><br>
          <span style="font-size:12px;color:#388e3c">
            This table was submitted on <strong>${date}</strong>.
            No further edits are allowed. Go to <em>My Submissions</em> to view or edit.
          </span>
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
                document.getElementById("cmi-confirm-box").style.transform =
                    "translateY(12px)";
                setTimeout(() => (el.style.display = "none"), 160);
            };
            document
                .getElementById("cmi-confirm-cancel")
                .addEventListener("click", close);
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

    /* ─────────────────────────────────────────
     SUBMIT REPORT
  ───────────────────────────────────────── */
    CMI.submitReport = function () {
        // Save current active table open on screen first
        const active = window._cmiActiveTable || 'T1';
        const winModule = window[active] || window[active.toUpperCase()] || window[active.toLowerCase()];
        if (winModule && typeof winModule.save === 'function') {
            winModule.save('done');
        }

        const allNos = Object.keys(TABLE_TITLES);
        const done = allNos.filter((n) => _status[n] === "done").length;
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
                toast("Submitting report…", 99999);
                fetch(API_SUBMIT, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ year: yr }) })
                    .then((r) => r.json())
                    .then((res) => {
                        const wrap = document.getElementById("toastWrap");
                        if (wrap) wrap.innerHTML = "";
                        if (res.success) {
                            toast("✅ Report submitted successfully! Redirecting to My Submissions...");
                            _isSubmitted = true;
                            _submittedAt = res.submitted_at || new Date().toISOString();
                            _submittedTables = allNos.filter((n) => _status[n] === "done" || _status[n] === "draft");
                            CMI.lockReport();
                            setTimeout(function () {
                                window.location.href = "/dashboard/cmi/submissions";
                            }, 1200);
                        } else {
                            toast("Submission failed: " + (res.error || "Unknown error"));
                        }
                    })
                    .catch(() => {
                        const wrap = document.getElementById("toastWrap");
                        if (wrap) wrap.innerHTML = "";
                        toast("Network error — please try again.");
                    });
            },
        });
    };

    /* ─────────────────────────────────────────
     INIT — load statuses then show T1
  ───────────────────────────────────────── */
    document.addEventListener("DOMContentLoaded", function () {
        renderFillNav();
        ensureBodyObserver();

        const btnSubmit = document.getElementById("btn-submit");
        if (btnSubmit) btnSubmit.addEventListener("click", CMI.submitReport);

        const year = window.CMI_REPORTING_YEAR || new Date().getFullYear();
        fetch("/api/cmi/tables/statuses?year=" + year)
            .then((r) => r.json())
            .then((data) => {
                if (data) CMI.setStatuses(data.statuses || {}, data);
            })
            .catch(() => {})
            .finally(() => {
                const urlParam = new URLSearchParams(
                    window.location.search,
                ).get("t");
                CMI.showTable(urlParam || "T1");
            });
    });
})();
