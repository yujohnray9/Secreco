<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ReportSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $userId    = Auth::id() ?? session('user_id');
        $year      = (int) ($request->input('year') ?? date('Y'));
        $readAll   = session('notifs_read_all_' . $userId, false) || session('notifs_read_all', false);
        $readAtTs  = session('notifs_read_at_ts_' . $userId);
        $readKeys  = session('read_notif_keys_' . $userId, []);
        $deletedKeys = session('deleted_notif_keys_' . $userId, []);
        $deleteAll   = session('notifs_delete_all_' . $userId, false);

        if ($deleteAll) {
            return response()->json([
                'ok'            => true,
                'notifications' => [],
                'unread_count'  => 0,
            ]);
        }

        $notifications = [];

        // 1. Pending accounts waiting for PTA approval (incoming action needed)
        $pendingUsers = User::where('status', 'pending')
            ->whereIn('role', ['cmi', 'viewer', 'executive'])
            ->orderByDesc('created_at')
            ->get();

        if ($pendingUsers->count() > 0) {
            $count  = $pendingUsers->count();
            $names  = array_slice(array_map(fn($u) => $u['first_name'] . ' ' . $u['last_name'] . ($u['institution'] ? ' (' . $u['institution'] . ')' : ''), $pendingUsers->toArray()), 0, 2);
            $label  = implode(' and ', $names) . ($count > 2 ? " and " . ($count - 2) . " more" : '');
            $key    = 'derived_pending_users';
            if (!in_array($key, $deletedKeys, true)) {
                $t      = $pendingUsers[0]->created_at;
                $tTs    = $t ? strtotime((string)$t) : 0;
                $unread = true;
                if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                    $unread = false;
                }

                $notifications[] = [
                    'id'           => null,
                    'key'          => $key,
                    'type'         => 'yellow',
                    'icon'         => '👥',
                    'msg'          => $count === 1 ? "1 account pending approval — {$label}." : "{$count} accounts pending approval — {$label}.",
                    'action'       => null,
                    'action_label' => null,
                    'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
                    'unread'       => $unread,
                    'notif_type'   => 'user_approval',
                ];
            }
        }

        // 2. Incoming CMI Submissions waiting for PTA review (ONLY pending/submitted from CMI; no accepted/returned self-actions)
        $recentSubs = ReportSubmission::where('reporting_year', $year)
            ->whereIn('status', ['pending', 'submitted', 'in-progress'])
            ->with('user')
            ->orderByDesc('submitted_at')
            ->get()
            ->unique('user_id')
            ->take(15);

        foreach ($recentSubs as $sub) {
            $key = 'derived_sub_' . $sub->id;
            if (in_array($key, $deletedKeys, true)) {
                continue;
            }
            $inst   = $sub->user?->institution ?? $sub->user?->name ?? 'CMI Representative';
            $t      = $sub->submitted_at ?? $sub->updated_at;
            $tTs    = $t ? strtotime((string)$t) : 0;
            $unread = true;
            if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                $unread = false;
            }

            $notifications[] = [
                'id'           => null,
                'key'          => $key,
                'type'         => 'green',
                'icon'         => '📨',
                'msg'          => "{$inst} submitted accomplishment reports for CY {$year}.",
                'action'       => '/dashboard/pta/submissions',
                'action_label' => 'View',
                'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => $unread,
                'notif_type'   => 'submission',
            ];
        }

        // 3. Stored database notifications intended for PTA from CMI activities (excluding PTA self-actions / audit logs)
        $stored = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) {
                  $q2->whereNull('user_id')->where('role', 'pta');
              });
        })
        ->where('message', 'not like', '%updated by PTA%')
        ->where('message', 'not like', '%PTA updated%')
        ->where('message', 'not like', '%accepted by PTA%')
        ->where('message', 'not like', '%deleted by PTA%')
        ->where('message', 'not like', '%Approved:%')
        ->orderByDesc('created_at')
        ->take(30)
        ->get();

        foreach ($stored as $n) {
            $key = 'stored_' . $n->id;
            if (in_array($key, $deletedKeys, true)) {
                continue;
            }
            $unread = !$n->is_read;
            $tTs    = $n->created_at ? strtotime((string)$n->created_at) : 0;
            if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                $unread = false;
            }

            $isApprovalOrActivation = in_array($n->type, ['year_activation', 'approval', 'user_approval', 'accepted'], true)
                || str_contains(strtolower($n->message), 'accepted')
                || str_contains(strtolower($n->message), 'is now active')
                || str_contains(strtolower($n->message), 'approved');

            $notifications[] = [
                'id'           => (int) $n->id,
                'key'          => $key,
                'type'         => $n->color ?: 'blue',
                'icon'         => $n->icon ?: '📋',
                'msg'          => $n->message,
                'action'       => $isApprovalOrActivation ? null : $n->action_url,
                'action_label' => $isApprovalOrActivation ? null : ($n->action_label ?: 'View'),
                'time'         => $n->created_at ? $n->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => $unread,
                'notif_type'   => $n->type,
            ];
        }

        // Deduplicate notifications
        $uniqueNotifs = [];
        $seenKeys = [];
        foreach ($notifications as $n) {
            $dedupKey = trim($n['msg']) . '_' . ($n['notif_type'] ?? '') . '_' . substr($n['time'] ?? '', 0, 16);
            if (!isset($seenKeys[$dedupKey])) {
                $seenKeys[$dedupKey] = true;
                $uniqueNotifs[] = $n;
            }
        }
        $notifications = $uniqueNotifs;

        usort($notifications, fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));

        return response()->json([
            'ok'            => true,
            'notifications' => $notifications,
            'unread_count'  => count(array_filter($notifications, fn($n) => $n['unread'])),
        ]);
    }
}
