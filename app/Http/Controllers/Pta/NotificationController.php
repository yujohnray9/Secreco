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
        $year          = (int) ($request->input('year') ?? date('Y'));
        $notifications = [];

        $pendingUsers = User::where('status', 'pending')
            ->whereIn('role', ['cmi', 'viewer'])
            ->orderByDesc('created_at')
            ->get();

        if ($pendingUsers->count() > 0) {
            $count = $pendingUsers->count();
            $names = array_slice(array_map(fn($u) => $u['first_name'] . ' ' . $u['last_name'] . ($u['institution'] ? ' (' . $u['institution'] . ')' : ''), $pendingUsers->toArray()), 0, 2);
            $label = implode(' and ', $names) . ($count > 2 ? " and " . ($count - 2) . " more" : '');

            $notifications[] = [
                'type'         => 'yellow',
                'icon'         => '👥',
                'msg'          => $count === 1 ? "1 account pending approval — {$label}." : "{$count} accounts pending approval — {$label}.",
                'action'       => 'users',
                'action_label' => 'Review',
                'time'         => $pendingUsers[0]->created_at ? $pendingUsers[0]->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => true,
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

            $notifications[] = [
                'type'         => $type,
                'icon'         => $icon,
                'msg'          => "{$inst} {$statusLabel}.",
                'action'       => 'submissions',
                'action_label' => 'View',
                'time'         => $sub->submitted_at ? $sub->submitted_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => true,
            ];
        }

        $actLogs = ActivityLog::with('user')->orderByDesc('created_at')->take(10)->get();

        foreach ($actLogs as $log) {
            $notifications[] = [
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
