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
            $storedQuery->where('message', 'not like', '%updated by PTA%')
                        ->where('message', 'not like', '%PTA updated%')
                        ->where('message', 'not like', '%accepted by PTA%')
                        ->where('message', 'not like', '%deleted by PTA%')
                        ->where('message', 'not like', '%Approved:%');
        }

        $stored = $storedQuery->orderByDesc('created_at')->take(50)->get();

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

            $notifications[] = [
                'id'           => (int) $n->id,
                'key'          => $key,
                'type'         => $n->color ?: 'blue',
                'icon'         => $n->icon ?: '📋',
                'msg'          => $n->message,
                'action'       => $n->action_url,
                'action_label' => $n->action_label ?: 'View',
                'time'         => $n->created_at ? $n->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => $unread,
                'notif_type'   => $n->type,
                'source'       => 'stored',
            ];
        }

        if ($userRole === 'pta') {
            $pendingUsers = User::where('status', 'pending')
                ->whereIn('role', ['cmi', 'viewer'])
                ->orderByDesc('created_at')
                ->get();

            if ($pendingUsers->count() > 0) {
                $count  = $pendingUsers->count();
                $names  = array_slice(array_map(
                    fn($u) => $u['first_name'] . ' ' . $u['last_name'] . ($u['institution'] ? ' (' . $u['institution'] . ')' : ''),
                    $pendingUsers->toArray()
                ), 0, 2);
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
                        'action'       => '/dashboard/pta/users',
                        'action_label' => 'Review',
                        'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
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
                    'msg'          => "{$cr->table_no} flagged for correction by {$ptaName}: \"{$cr->reason}\"",
                    'action'       => '/dashboard/cmi/fillup',
                    'action_label' => 'Go to ' . $cr->table_no,
                    'time'         => $t ? $t->format('Y-m-d\TH:i:s+08:00') : null,
                    'unread'       => $unread,
                    'notif_type'   => 'correction',
                    'source'       => 'derived',
                ];
            }
        }

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
}
