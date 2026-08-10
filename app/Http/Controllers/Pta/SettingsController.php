<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function saveSettings(Request $request): JsonResponse
    {
        $input  = $request->json()->all() ?: $request->all();
        $action = trim($input['action'] ?? '');
        $userId = (int) (Auth::id() ?? session('user_id'));

        switch ($action) {
            case 'profile':
                $firstName   = trim($input['first_name'] ?? '');
                $lastName    = trim($input['last_name'] ?? '');
                $designation = trim($input['designation'] ?? '');

                if (!$firstName || !$lastName) {
                    return response()->json(['ok' => false, 'message' => 'Name is required.']);
                }

                $user = User::find($userId);
                if ($user) {
                    $user->update([
                        'first_name'  => $firstName,
                        'last_name'   => $lastName,
                        'designation' => $designation,
                    ]);
                    session([
                        'user_name'  => $firstName . ' ' . $lastName,
                        'user_desig' => $designation,
                    ]);
                }

                return response()->json(['ok' => true, 'message' => 'Profile updated successfully.', 'name' => session('user_name')]);

            case 'password':
                $current = $input['current_password'] ?? '';
                $new     = $input['new_password']     ?? '';
                $confirm = $input['confirm_password'] ?? '';

                if (!$current || !$new || !$confirm) {
                    return response()->json(['ok' => false, 'message' => 'All password fields are required.']);
                }
                if ($new !== $confirm) {
                    return response()->json(['ok' => false, 'message' => 'New passwords do not match.']);
                }
                if (strlen($new) < 8) {
                    return response()->json(['ok' => false, 'message' => 'Password must be at least 8 characters.']);
                }

                $user = User::find($userId);
                if (!$user || !Hash::check($current, $user->password)) {
                    return response()->json(['ok' => false, 'message' => 'Current password is incorrect.']);
                }

                $user->update(['password' => Hash::make($new)]);
                return response()->json(['ok' => true, 'message' => 'Password changed successfully.']);

            case 'system':
                $role = Auth::user()?->role ?? session('user_role');
                if ($role !== 'pta') {
                    return response()->json(['ok' => false, 'message' => 'Forbidden.']);
                }

                $allowed = ['submission_deadline', 'late_submission_policy', 'consortium_name', 'email_reminders', 'deadline_reminder_days'];
                foreach ($allowed as $k) {
                    if (isset($input[$k])) {
                        $v = trim($input[$k]);
                        SystemSetting::updateOrCreate(
                            ['key' => $k],
                            ['value' => $v, 'updated_by' => $userId]
                        );
                    }
                }

                return response()->json(['ok' => true, 'message' => 'System settings saved.']);

            case 'get_profile':
                $user = User::find($userId, ['id', 'first_name', 'last_name', 'email', 'institution', 'designation', 'role', 'status', 'profile_picture', 'created_at', 'updated_at']);
                $userData = $user ? $user->toArray() : null;
                if ($userData) {
                    $userData['photo'] = $userData['profile_picture'] ?? null;
                }
                return response()->json(['ok' => true, 'user' => $userData]);

            case 'get_system':
                $role = Auth::user()?->role ?? session('user_role');
                if ($role !== 'pta') {
                    return response()->json(['ok' => false, 'message' => 'Forbidden.']);
                }
                $settings = SystemSetting::pluck('value', 'key')->all();
                return response()->json(['ok' => true, 'settings' => $settings]);

            default:
                return response()->json(['ok' => false, 'message' => "Unknown action: {$action}"]);
        }
    }

    public function getAudit(Request $request): JsonResponse
    {
        $limit = min((int) ($request->input('limit') ?? 50), 200);

        $logs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->map(function ($al) {
                return [
                    'id'          => $al->id,
                    'description' => $al->description,
                    'created_at'  => $al->created_at ? $al->created_at->format('Y-m-d\TH:i:s') : null,
                    'actor'       => $al->user ? $al->user->name : 'System',
                ];
            });

        return response()->json(['ok' => true, 'logs' => $logs]);
    }
}
