<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $userId    = Auth::id() ?? session('user_id');
        $userRole  = Auth::user()?->role ?? session('user_role');
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
                'counts'        => ['urgent' => 0, 'pending' => 0, 'activity' => 0],
            ]);
        }

        $notifications = [];

        $storedQuery = Notification::where(function ($q) use ($userId, $userRole) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) use ($userRole) {
                  $q2->whereNull('user_id')->where('role', $userRole);
              });
        });

        if ($userRole === 'pta') {
            $storedQuery->where('type', '!=', 'submission_updated')
                        ->where('message', 'not like', '%updated Table%')
                        ->where('message', 'not like', '%updated by PTA%')
                        ->where('message', 'not like', '%PTA updated%')
                        ->where('message', 'not like', '%accepted by PTA%')
                        ->where('message', 'not like', '%deleted by PTA%')
                        ->where('message', 'not like', '%Approved:%');
        } elseif ($userRole === 'cmi') {
            // Do not show misleading draft update or PTA self-actions in CMI inbox
            $storedQuery->where('type', '!=', 'submission_updated')
                        ->where('message', 'not like', '%revised and fixed Table%');
        }

        $stored = $storedQuery->orderByDesc('created_at')->take(50)->get();

        if ($userRole === 'cmi') {
            // Retain notification history, but hide completed correction notices from the inbox.
            $history = CorrectionRequest::where('cmi_user_id', $userId)
                ->orderByDesc('created_at')->orderByDesc('id')->get();
            $stored = $stored->reject(function ($notification) use ($history) {
                if ($notification->type !== 'correction' || !$notification->created_at) {
                    return false;
                }
                parse_str(parse_url($notification->action_url ?? '', PHP_URL_QUERY) ?? '', $target);
                if (empty($target['table']) || empty($target['year'])) {
                    return false;
                }
                $correction = $history->first(function ($request) use ($notification, $target) {
                    return strcasecmp($request->table_no, (string) $target['table']) === 0
                        && (int) $request->reporting_year === (int) $target['year']
                        && $request->created_at
                        && $request->created_at->lessThanOrEqualTo($notification->created_at);
                });
                return $correction && $correction->status === 'resolved'
                    && $correction->resolved_at
                    && $correction->resolved_at->greaterThanOrEqualTo($notification->created_at);
            });
        }

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

            $isYearAct = ($n->type === 'year_activation') || str_contains(strtolower($n->message), 'is now active');
            $msg = $n->message;
            if ($isYearAct) {
                $msg = preg_replace('/(CY\s*\d{4}\s*is\s*now\s*active)!?\s*(?:You\s*may\s*now|—\s*you\s*may)?/i', '$1 — you may', $msg);
                if (!str_contains($msg, 'start filling up and submitting')) {
                    $msg = preg_replace('/(CY\s*\d{4}\s*is\s*now\s*active\s*—\s*you\s*may).*/i', '$1 start filling up and submitting your annual accomplishment report.', $msg);
                }
            }

            $isInfoOnly = in_array($n->type, ['year_activation', 'accepted'], true)
                || str_contains(strtolower($n->message), 'accepted')
                || str_contains(strtolower($n->message), 'is now active');

            $actionUrl   = $n->action_url;
            $actionLabel = $n->action_label ?: 'View';
            if ($n->type === 'user_approval' || str_contains(strtolower($n->message), 'pending approval')) {
                $actionUrl   = '/dashboard/pta/users';
                $actionLabel = 'View';
            } elseif ($isInfoOnly) {
                $actionUrl   = null;
                $actionLabel = null;
            }

            $notifications[] = [
                'id'           => (int) $n->id,
                'key'          => $key,
                'type'         => $isYearAct ? 'blue' : ($n->color ?: 'blue'),
                'icon'         => $isYearAct ? '🎉' : ($n->icon ?: '📋'),
                'msg'          => $msg,
                'action'       => $actionUrl,
                'action_label' => $actionLabel,
                'time'         => $this->toPhIso($n->created_at),
                'time_formatted' => $this->toPhFormatted($n->created_at),
                'unread'       => $unread,
                'notif_type'   => $n->type,
                'source'       => 'stored',
            ];
        }

        if ($userRole === 'pta') {
            $pendingUsers = User::where('status', 'pending')
                ->whereIn('role', ['cmi', 'viewer', 'executive'])
                ->orderByDesc('created_at')
                ->get();

            if ($pendingUsers->count() > 0) {
                foreach ($pendingUsers as $pu) {
                    $key = 'derived_user_' . $pu->id;
                    if (in_array($key, $deletedKeys, true) || in_array('derived_pending_users', $deletedKeys, true)) {
                        continue;
                    }
                    $t      = $pu->created_at;
                    $tTs    = $t ? strtotime((string)$t) : 0;
                    $unread = true;
                    if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true) || in_array('derived_pending_users', $readKeys, true)) {
                        $unread = false;
                    }

                    $uName = trim($pu->first_name . ' ' . $pu->last_name);
                    $uRole = strtoupper($pu->role);
                    $uInst = $pu->institution ? " ({$pu->institution})" : '';

                    $notifications[] = [
                        'id'           => null,
                        'key'          => $key,
                        'type'         => 'yellow',
                        'icon'         => '👥',
                        'msg'          => "New user registered: {$uName}{$uInst} registered as {$uRole} and is pending approval.",
                        'action'       => '/dashboard/pta/users',
                        'action_label' => 'View',
                        'time'         => $this->toPhIso($t),
                        'time_formatted' => $this->toPhFormatted($t),
                        'unread'       => $unread,
                        'notif_type'   => 'user_approval',
                        'source'       => 'derived',
                    ];
                }
            }
        }

        if ($userRole === 'cmi') {
            $corrections = CorrectionRequest::where('cmi_user_id', $userId)
                ->where('status', 'open')
                ->with('ptaUser')
                ->orderByDesc('created_at')
                ->get();

            foreach ($corrections as $cr) {
                // Avoid duplicating if a stored notification for this correction already exists
                $alreadyStored = collect($stored)->contains(function ($sn) use ($cr) {
                    return str_contains($sn->message, "Table {$cr->table_no}") && str_contains($sn->message, "returned for revision");
                });
                if ($alreadyStored) {
                    continue;
                }

                $ptaName = $cr->ptaUser?->name ?? 'PTA';
                $key     = 'derived_corr_' . $cr->id;
                if (in_array($key, $deletedKeys, true)) {
                    continue;
                }
                $t       = $cr->created_at;
                $tTs     = $t ? strtotime((string)$t) : 0;
                $unread  = true;
                if ($readAll || ($readAtTs && $tTs > 0 && $tTs <= $readAtTs) || in_array($key, $readKeys, true)) {
                    $unread = false;
                }

                $notifications[] = [
                    'id'           => null,
                    'key'          => $key,
                    'type'         => 'red',
                    'icon'         => '🔴',
                    'msg'          => "Table {$cr->table_no} was returned for revision by the PTA Admin: \"{$cr->reason}\"",
                    'action'       => '/dashboard/cmi/fillup',
                    'action_label' => 'Go to ' . $cr->table_no,
                    'time'         => $this->toPhIso($t),
                    'time_formatted' => $this->toPhFormatted($t),
                    'unread'       => $unread,
                    'notif_type'   => 'correction',
                    'source'       => 'derived',
                ];
            }
        }

        // Deduplicate notifications by message and type
        $uniqueNotifs = [];
        $seenKeys = [];
        foreach ($notifications as $n) {
            $dedupKey = trim($n['msg']) . '_' . ($n['notif_type'] ?? '') . '_' . ($n['action'] ?? '') . '_' . substr($n['time'] ?? '', 0, 16);
            if (!isset($seenKeys[$dedupKey])) {
                $seenKeys[$dedupKey] = true;
                $uniqueNotifs[] = $n;
            }
        }
        $notifications = $uniqueNotifs;

        usort($notifications, fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));

        $unreadCount = count(array_filter($notifications, fn($n) => $n['unread']));

        return response()->json([
            'ok'            => true,
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
            'counts'        => [
                'urgent'   => count(array_filter($notifications, fn($n) => $n['type'] === 'red' && $n['unread'])),
                'pending'  => count(array_filter($notifications, fn($n) => $n['type'] === 'yellow' && $n['unread'])),
                'activity' => count(array_filter($notifications, fn($n) => in_array($n['type'], ['green', 'blue'], true))),
            ],
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $userId   = Auth::id() ?? session('user_id');
        $userRole = Auth::user()?->role ?? session('user_role');

        $input   = $request->json()->all() ?: $request->all();
        $markAll = !empty($input['all']);
        $notifId = (int) ($input['id'] ?? 0);
        $key     = $input['key'] ?? null;

        if ($markAll) {
            Notification::where(function ($q) use ($userId, $userRole) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($q2) use ($userRole) {
                      $q2->whereNull('user_id')->where('role', $userRole);
                  });
            })->where('is_read', false)->update(['is_read' => true]);

            session(['notifs_read_all_' . $userId => true]);
            session(['notifs_read_all' => true]);
            session(['notifs_read_at_ts_' . $userId => time() + 86400]);

            return response()->json(['ok' => true, 'message' => 'All notifications marked as read.']);
        }

        if ($key) {
            $readKeys = session('read_notif_keys_' . $userId, []);
            if (!in_array($key, $readKeys, true)) {
                $readKeys[] = $key;
                session(['read_notif_keys_' . $userId => $readKeys]);
            }
        }

        if ($notifId > 0) {
            Notification::where('id', $notifId)
                ->where(function ($q) use ($userId, $userRole) {
                    $q->where('user_id', $userId)
                      ->orWhere(function ($q2) use ($userRole) {
                          $q2->whereNull('user_id')->where('role', $userRole);
                      });
                })->update(['is_read' => true]);
        }

        return response()->json(['ok' => true, 'message' => 'Notification marked as read.']);
    }

    public function delete(Request $request): JsonResponse
    {
        $userId   = Auth::id() ?? session('user_id');
        $userRole = Auth::user()?->role ?? session('user_role');

        $input   = $request->json()->all() ?: $request->all();
        $notifId = (int) ($input['id'] ?? 0);
        $key     = $input['key'] ?? null;

        if ($notifId > 0) {
            Notification::where('id', $notifId)
                ->where(function ($q) use ($userId, $userRole) {
                    $q->where('user_id', $userId)
                      ->orWhere(function ($q2) use ($userRole) {
                          $q2->whereNull('user_id')->where('role', $userRole);
                      });
                })->delete();
        }

        if ($key) {
            $deletedKeys = session('deleted_notif_keys_' . $userId, []);
            if (!in_array($key, $deletedKeys, true)) {
                $deletedKeys[] = $key;
                session(['deleted_notif_keys_' . $userId => $deletedKeys]);
            }
        }

        return response()->json(['ok' => true, 'message' => 'Notification deleted.']);
    }

    public function deleteAll(Request $request): JsonResponse
    {
        $userId   = Auth::id() ?? session('user_id');
        $userRole = Auth::user()?->role ?? session('user_role');

        Notification::where(function ($q) use ($userId, $userRole) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) use ($userRole) {
                  $q2->whereNull('user_id')->where('role', $userRole);
              });
        })->delete();

        session(['notifs_delete_all_' . $userId => true]);

        return response()->json(['ok' => true, 'message' => 'All notifications deleted.']);
    }

    protected function toPhIso($datetime): ?string
    {
        if (!$datetime) return null;
        try {
            return \Carbon\Carbon::parse($datetime)->setTimezone('Asia/Manila')->format('Y-m-d\TH:i:s+08:00');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function toPhFormatted($datetime): ?string
    {
        if (!$datetime) return null;
        try {
            return \Carbon\Carbon::parse($datetime)->setTimezone('Asia/Manila')->format('M j, Y h:i A');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
