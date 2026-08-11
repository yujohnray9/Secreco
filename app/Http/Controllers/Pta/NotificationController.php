<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ReportSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $userId    = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $year      = (int) ($request->input('year') ?? date('Y'));
        $readAll   = session('notifs_read_all_' . $userId, false) || session('notifs_read_all', false);
        $readAtTs  = session('notifs_read_at_ts_' . $userId);
        $readKeys  = session('read_notif_keys_' . $userId, []);

        $notifications = [];

        $pendingUsers = User::where('status', 'pending')
            ->whereIn('role', ['cmi', 'viewer'])
            ->orderByDesc('created_at')
            ->get();

        if ($pendingUsers->count() > 0) {
            $count  = $pendingUsers->count();
            $names  = array_slice(array_map(fn($u) => $u['first_name'] . ' ' . $u['last_name'] . ($u['institution'] ? ' (' . $u['institution'] . ')' : ''), $pendingUsers->toArray()), 0, 2);
            $label  = implode(' and ', $names) . ($count > 2 ? " and " . ($count - 2) . " more" : '');
            $key    = 'derived_pending_users';
            $t      = $pendingUsers[0]->created_at;
            $tTs    = $t ? strtotime((string)$t) : 0;
            $unread = true;
            if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                $unread = false;
            }

            $notifications[] = [
                'key'          => $key,
                'type'         => 'yellow',
                'icon'         => '👥',
                'msg'          => $count === 1 ? "1 account pending approval — {$label}." : "{$count} accounts pending approval — {$label}.",
                'action'       => 'users',
                'action_label' => 'Review',
                'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => $unread,
            ];
        }

        $recentSubs = ReportSubmission::where('reporting_year', $year)
            ->with('user')
            ->orderByDesc('submitted_at')
            ->take(10)
            ->get();

        foreach ($recentSubs as $sub) {
            $inst = $sub->user?->institution ?? $sub->user?->name;
            $statusLabel = match ($sub->status) {
                'pending'  => 'submitted their report for review',
                'accepted' => 'submission was accepted',
                'returned' => 'submission was returned for correction',
                default    => 'updated their submission',
            };
            $type = match ($sub->status) {
                'accepted' => 'green',
                'returned' => 'red',
                default    => 'green',
            };
            $icon = match ($sub->status) {
                'accepted' => '✅',
                'returned' => '↩️',
                default    => '📨',
            };
            $key    = 'derived_sub_' . $sub->id;
            $t      = $sub->submitted_at;
            $tTs    = $t ? strtotime((string)$t) : 0;
            $unread = true;
            if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                $unread = false;
            }

            $notifications[] = [
                'key'          => $key,
                'type'         => $type,
                'icon'         => $icon,
                'msg'          => "{$inst} {$statusLabel}.",
                'action'       => 'submissions',
                'action_label' => 'View',
                'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => $unread,
            ];
        }

        $actLogs = ActivityLog::with('user')->orderByDesc('created_at')->take(10)->get();

        foreach ($actLogs as $log) {
            $notifications[] = [
                'key'    => 'log_' . $log->id,
                'type'   => 'blue',
                'icon'   => '📋',
                'msg'    => $log->description,
                'action' => null,
                'time'   => $log->created_at ? $log->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread' => false,
            ];
        }

        usort($notifications, fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));

        return response()->json([
            'ok'            => true,
            'notifications' => $notifications,
            'unread_count'  => count(array_filter($notifications, fn($n) => $n['unread'])),
        ]);
    }
}
