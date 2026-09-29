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
        const res  = await fetch(`/api/pta/notifications?year=${year}`);
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

function renderItem(n) {
    const timeStr     = formatRelativeTime(n.time);
    const unreadClass = n.unread ? ' notif-unread' : '';
    const unreadDot   = n.unread ? '<div class="notif-dot-unread"></div>' : '';
    const isInfoOnly  = n.notif_type === 'year_activation' || n.type === 'year_activation' ||
        (n.msg && n.msg.toLowerCase().includes('is now active'));
    const actionUrl   = n.action || (n.notif_type === 'user_approval' ? '/dashboard/pta/users' : '');
    const actionBtn   = (!isInfoOnly && actionUrl)
        ? `<div class="notif-action"><button class="btn-notif${n.unread ? '' : ' outline'}" onclick="window.location.href='${esc(actionUrl)}'">${esc(n.action_label ?? 'View')}</button></div>`
        : '';
    const badgeLabel  = typeBadgeLabel(n.type, n.status, n.notif_type);

    let desc = 'Institutional report information was updated and is ready for review.';
    if (n.notif_type === 'submission' || n.type === 'green' || (n.msg && n.msg.toLowerCase().includes('submitted'))) {
        desc = 'A new institutional submission has been received and is ready for validation.';
    }
    if (n.notif_type === 'user_approval' || n.type === 'yellow' || (n.msg && (n.msg.toLowerCase().includes('pending approval') || n.msg.toLowerCase().includes('account approval')))) {
        desc = 'A new user account registration is pending review and approval.';
    }

    return `
    <div class="notif-item${unreadClass}" data-type="${esc(n.type)}">
        <div class="notif-ic ${esc(n.type)}">${n.icon}</div>
        <div class="notif-body">
            <div class="notif-top">
                <div class="notif-msg">${esc(n.msg)}</div>
                ${actionBtn}
            </div>
            <p style="font-size:12px;color:#6b7280;margin:3px 0 6px 0;">${esc(desc)}</p>
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
        await fetch('/api/pta/notifications/mark-read', {
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
function getManilaDateString(d) {
    return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Manila' }).format(d);
}

function parseManilaDate(datetimeStr) {
    if (!datetimeStr) return null;
    let d;
    if (typeof datetimeStr === 'string' && !datetimeStr.includes('T') && !datetimeStr.includes('Z') && !datetimeStr.includes('+')) {
        d = new Date(datetimeStr.replace(' ', 'T') + '+08:00');
    } else {
        d = new Date(datetimeStr);
    }
    return isNaN(d.getTime()) ? null : d;
}

function groupByDate(items) {
    const groups = new Map();
    const now = new Date();
    const todayStr = getManilaDateString(now);
    const yestDate = new Date(now.getTime() - 86400000);
    const yestStr = getManilaDateString(yestDate);

    for (const n of items) {
        const d = parseManilaDate(n.time) || new Date();
        const itemDayStr = getManilaDateString(d);
        let label;
        if (itemDayStr === todayStr) label = 'Today';
        else if (itemDayStr === yestStr) label = 'Yesterday';
        else label = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' });

        if (!groups.has(label)) groups.set(label, []);
        groups.get(label).push(n);
    }
    return groups;
}

function typeBadgeLabel(type, status, notifType) {
    if (notifType === 'user_approval' || type === 'yellow') return 'Account Approval';
    const map = {
        yellow : 'Account Approval',
        red    : 'Returned',
        green  : status === 'accepted' ? 'Accepted' : 'Submitted',
        blue   : 'Activity Log',
    };
    return map[type] ?? 'System Report';
}

function formatRelativeTime(datetimeStr, fallback) {
    if (!datetimeStr && fallback) return fallback;
    if (!datetimeStr) return '—';
    const date = parseManilaDate(datetimeStr);
    if (!date) return fallback || datetimeStr;
    const now = new Date();

    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    const timeStr = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZone: 'Asia/Manila'
    });

    if (diffMins >= 0 && diffMins < 2) return 'Just now';
    if (diffMins >= 2 && diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;

    const todayStr = getManilaDateString(now);
    const itemDayStr = getManilaDateString(date);

    if (itemDayStr === todayStr) return `Today ${timeStr}`;

    const yestDate = new Date(now.getTime() - 86400000);
    const yestStr = getManilaDateString(yestDate);
    if (itemDayStr === yestStr) return `Yesterday ${timeStr}`;

    const dateStr = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        timeZone: 'Asia/Manila'
    });
    return `${dateStr} ${timeStr}`;
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
