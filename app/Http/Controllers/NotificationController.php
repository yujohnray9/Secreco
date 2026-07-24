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
        $userId   = Auth::id() ?? session('user_id');
        $userRole = Auth::user()?->role ?? session('user_role');
        $year     = (int) ($request->input('year') ?? date('Y'));

        $notifications = [];

        $stored = Notification::where(function ($q) use ($userId, $userRole) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) use ($userRole) {
                  $q2->whereNull('user_id')->where('role', $userRole);
              });
        })->orderByDesc('created_at')->take(50)->get();

        foreach ($stored as $n) {
            $notifications[] = [
                'id'           => (int) $n->id,
                'type'         => $n->color ?: 'blue',
                'icon'         => $n->icon ?: '📋',
                'msg'          => $n->message,
                'action'       => $n->action_url,
                'action_label' => $n->action_label ?: 'View',
                'time'         => $n->created_at ? $n->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                'unread'       => !$n->is_read,
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
                $count = $pendingUsers->count();
                $names = array_slice(array_map(
                    fn($u) => $u['first_name'] . ' ' . $u['last_name'] . ($u['institution'] ? ' (' . $u['institution'] . ')' : ''),
                    $pendingUsers->toArray()
                ), 0, 2);
                $label = implode(' and ', $names) . ($count > 2 ? " and " . ($count - 2) . " more" : '');

                $notifications[] = [
                    'id'           => null,
                    'type'         => 'yellow',
                    'icon'         => '👥',
                    'msg'          => $count === 1 ? "1 account pending approval — {$label}." : "{$count} accounts pending approval — {$label}.",
                    'action'       => '/secreco/dashboards/pta/users.php',
                    'action_label' => 'Review',
                    'time'         => $pendingUsers[0]->created_at ? $pendingUsers[0]->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                    'unread'       => true,
                    'notif_type'   => 'user_approval',
                    'source'       => 'derived',
                ];
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
                $notifications[] = [
                    'id'           => null,
                    'type'         => 'red',
                    'icon'         => '🔴',
                    'msg'          => "{$cr->table_no} flagged for correction by {$ptaName}: \"{$cr->reason}\"",
                    'action'       => '/secreco/dashboards/cmi/fillup.php',
                    'action_label' => 'Go to ' . $cr->table_no,
                    'time'         => $cr->created_at ? $cr->created_at->format('Y-m-d\TH:i:s+08:00') : null,
                    'unread'       => true,
                    'notif_type'   => 'correction',
                    'source'       => 'derived',
                ];
            }
        }

        usort($notifications, fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));

        $urgent   = count(array_filter($notifications, fn($n) => $n['type'] === 'red' && $n['unread']));
        $pending  = count(array_filter($notifications, fn($n) => $n['type'] === 'yellow' && $n['unread']));
        $activity = count(array_filter($notifications, fn($n) => in_array($n['type'], ['green', 'blue'], true)));

        return response()->json([
            'ok'            => true,
            'notifications' => $notifications,
            'unread_count'  => count(array_filter($notifications, fn($n) => $n['unread'])),
            'counts'        => [
                'urgent'   => $urgent,
                'pending'  => $pending,
                'activity' => $activity,
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

        if ($markAll) {
            $affected = Notification::where(function ($q) use ($userId, $userRole) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($q2) use ($userRole) {
                      $q2->whereNull('user_id')->where('role', $userRole);
                  });
            })->where('is_read', false)->update(['is_read' => true]);

            return response()->json(['ok' => true, 'marked' => $affected, 'message' => "Marked {$affected} notifications as read."]);
        } elseif ($notifId > 0) {
            $updated = Notification::where('id', $notifId)
                ->where(function ($q) use ($userId, $userRole) {
                    $q->where('user_id', $userId)
                      ->orWhere(function ($q2) use ($userRole) {
                          $q2->whereNull('user_id')->where('role', $userRole);
                      });
                })->update(['is_read' => true]);

            if ($updated) {
                return response()->json(['ok' => true, 'message' => 'Notification marked as read.']);
            }
            return response()->json(['ok' => false, 'message' => 'Notification not found or already read.']);
        }

        return response()->json(['ok' => false, 'message' => 'Please provide "id" or "all": true.']);
    }
}
