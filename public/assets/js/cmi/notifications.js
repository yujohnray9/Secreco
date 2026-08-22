// ═══ NOTIFICATIONS PAGE ═══
'use strict';

let _allNotifications = [];
let _activeFilter     = 'all';
let _unreadOnly       = false;

document.addEventListener('DOMContentLoaded', loadNotifications);

// ── LOAD ──────────────────────────────────────────────────────
async function loadNotifications() {
    const year = new Date().getFullYear();
    const card = document.getElementById('notifCard');
    if (!card) return;

    try {
        const res  = await fetch(`/api/notifications?year=${year}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.error ?? 'API error');

        _allNotifications = json.notifications ?? [];
        renderSummary(_allNotifications);
        renderNotifications(_allNotifications);
        updateBellBadge(json.unread_count);
    } catch (e) {
        console.error('[notifications] Failed:', e);
        card.innerHTML = '<div class="notif-empty"><div class="notif-empty-icon">⚠️</div><div class="notif-empty-lbl">Could not load notifications</div><div class="notif-empty-sub">Check your connection and try again.</div></div>';
    }
}

// ── SUMMARY CARDS ─────────────────────────────────────────────
function renderSummary(items) {
    const urgent      = items.filter(n => n.type === 'red').length;
    const pending     = items.filter(n => n.type === 'yellow').length;
    const submissions = items.filter(n => n.type === 'green').length;
    const activity    = items.filter(n => n.type === 'blue').length;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('notifCntUrgent',      urgent);
    set('notifCntPending',     pending);
    set('notifCntSubmissions', submissions);
    set('notifCntActivity',    activity);
}

// ── RENDER LIST ───────────────────────────────────────────────
function renderNotifications(items) {
    // Apply filters
    let filtered = items;
    if (_activeFilter !== 'all') filtered = filtered.filter(n => n.type === _activeFilter);
    if (_unreadOnly)             filtered = filtered.filter(n => n.unread);

    const card = document.getElementById('notifCard');
    if (!card) return;

    if (!filtered.length) {
        card.innerHTML = `
        <div class="notif-empty">
            <div class="notif-empty-icon">🎉</div>
            <div class="notif-empty-lbl">No notifications</div>
            <div class="notif-empty-sub">You're all caught up.</div>
        </div>`;
        return;
    }

    // Group by date
    const groups = groupByDate(filtered);
    let html = '';
    for (const [label, group] of groups) {
        html += `<div class="notif-group-hdr">${esc(label)}</div>`;
        html += group.map(n => renderItem(n)).join('');
        html += '<div style="height:0;border-bottom:1px solid var(--border)"></div>';
    }

    card.innerHTML = html;
}

function getSvgIcon(type, icon) {
    const t = (type || '').toLowerCase();
    const ic = (icon || '').toLowerCase();
    if (t.includes('edit') || ic.includes('edit') || ic === '✏️' || ic === '📝') {
        return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>`;
    }
    if (t === 'red' || t.includes('corr') || t.includes('return') || ic === '🔴' || ic === '↩️') {
        return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
    }
    if (t === 'green' || t.includes('accept') || ic === '✅') {
        return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
    }
    if (t === 'yellow' || t.includes('user') || ic === '👥') {
        return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
    }
    if (ic === '📨' || t.includes('submit')) {
        return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`;
    }
    return `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;
}

function renderItem(n) {
    const timeStr     = formatRelativeTime(n.time);
    const unreadClass = n.unread ? ' notif-unread' : '';
    const unreadDot   = n.unread ? '<div class="notif-dot-unread"></div>' : '';
    const actionBtn   = n.action
        ? `<div class="notif-action"><button class="btn-notif${n.unread ? '' : ' outline'}" onclick="location.href='${esc(n.action)}'">${esc(n.action_label ?? 'View')}</button></div>`
        : '';
    const badgeLabel  = typeBadgeLabel(n.type, n.status);
    const svgIcon     = getSvgIcon(n.type, n.icon);

    return `
    <div class="notif-item${unreadClass}" data-type="${esc(n.type)}">
        <div class="notif-ic ${esc(n.type)}">${svgIcon}</div>
        <div class="notif-body">
            <div class="notif-top">
                <div class="notif-msg">${esc(n.msg)}</div>
                ${actionBtn}
            </div>
            <div class="notif-meta">
                <span class="notif-time">${timeStr}</span>
                <span class="notif-badge ${esc(n.type)}">${badgeLabel}</span>
            </div>
        </div>
        ${unreadDot}
    </div>`;
}

// ── FILTER CONTROLS ───────────────────────────────────────────
function notifSetFilter(type, btn) {
    _activeFilter = type;
    document.querySelectorAll('.notif-filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderNotifications(_allNotifications);
}

function notifToggleUnread() {
    _unreadOnly = !_unreadOnly;
    const sw = document.getElementById('notifToggleSw');
    if (sw) sw.classList.toggle('on', _unreadOnly);
    renderNotifications(_allNotifications);
}

// ── MARK ALL READ ─────────────────────────────────────────────
async function markAllRead() {
    try {
        await fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ all: true }),
        });
    } catch (e) { console.warn('[markAllRead] API error:', e); }

    _allNotifications.forEach(n => { n.unread = false; });
    renderSummary(_allNotifications);
    renderNotifications(_allNotifications);
    updateBellBadge(0);
    if (typeof toast === 'function') toast('All notifications marked as read');
}

// ── BELL BADGE ────────────────────────────────────────────────
function updateBellBadge(count) {
    const dot = document.querySelector('.notif-dot');
    if (!dot) return;
    if (count > 0) {
        dot.style.display = 'block';
    } else {
        dot.style.display = 'none';
    }
}

// ── HELPERS ───────────────────────────────────────────────────
function groupByDate(items) {
    const groups = new Map();
    const now    = new Date();
    const today  = dateOnly(now);
    const yest   = new Date(today); yest.setDate(yest.getDate() - 1);

    for (const n of items) {
        const d    = new Date(n.time);
        const day  = dateOnly(d);
        let label;
        if (+day === +today) label = 'Today';
        else if (+day === +yest) label = 'Yesterday';
        else label = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

        if (!groups.has(label)) groups.set(label, []);
        groups.get(label).push(n);
    }
    return groups;
}

function dateOnly(d) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function typeBadgeLabel(type, status) {
    const map = {
        yellow : 'Deadline',
        red    : 'Correction',
        green  : status === 'accepted' ? 'Accepted' : 'Submitted',
        blue   : 'System',
    };
    return map[type] ?? type;
}

function formatRelativeTime(datetimeStr) {
    if (!datetimeStr) return '—';
    const date = new Date(datetimeStr);
    const now  = new Date();
    const diffMins = Math.floor((now - date) / 60000);
    const timeStr  = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    const diffDays = Math.round((dateOnly(now) - dateOnly(date)) / 86400000);

    if (diffMins < 2)   return 'Just now';
    if (diffMins < 60)  return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    if (diffDays === 0) return `Today ${timeStr}`;
    if (diffDays === 1) return `Yesterday ${timeStr}`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ` ${timeStr}`;
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
