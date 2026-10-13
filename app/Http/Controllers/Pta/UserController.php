<?php

namespace App\Http\Controllers\Pta;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = User::where('role', '!=', 'pta')
            ->where('status', '!=', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        $roleLabel = [
            'cmi'       => 'CMI Representative',
            'viewer'    => 'Guest',
            'guest'     => 'Guest',
            'executive' => 'Executive',
            'pta'       => 'Project Technical Assistant II',
        ];

        $users = $rows->map(function ($r) use ($roleLabel) {
            $isExecutive = ($r->role === 'executive');
            return [
                'id'          => (int) $r->id,
                'first_name'  => $r->first_name,
                'last_name'   => $r->last_name,
                'name'        => $r->name ?: trim("{$r->first_name} {$r->last_name}"),
                'email'       => $r->email,
                'role'        => $roleLabel[$r->role] ?? $r->role,
                'position'    => $r->designation ?: ($isExecutive ? 'Executive Director' : '—'),
                'institution' => $isExecutive ? '—' : ($r->institution ?? '—'),
                'status'      => ucfirst($r->status),
                'created_at'  => $r->created_at ? $r->created_at->toDateTimeString() : null,
                'approved_at' => $r->approved_at ? $r->approved_at->toDateTimeString() : null,
                'lastLogin'   => '',
            ];
        });

        return response()->json($users);
    }

    public function getPending(): JsonResponse
    {
        $pending = User::where('status', 'pending')
            ->whereIn('role', ['cmi', 'viewer', 'executive'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($u) {
                $isExecutive = ($u->role === 'executive');
                return [
                    'id'          => (int) $u->id,
                    'first_name'  => $u->first_name,
                    'last_name'   => $u->last_name,
                    'name'        => $u->name ?: trim("{$u->first_name} {$u->last_name}"),
                    'email'       => $u->email,
                    'role'        => $u->role,
                    'institution' => $isExecutive ? '—' : ($u->institution ?? '—'),
                    'designation' => $u->designation ?: ($isExecutive ? 'Executive Director' : '—'),
                    'position'    => $u->designation ?: ($isExecutive ? 'Executive Director' : '—'),
                    'status'      => $u->status,
                    'created_at'  => $u->created_at ? $u->created_at->toDateTimeString() : null,
                    'approved_at' => $u->approved_at ? $u->approved_at->toDateTimeString() : null,
                ];
            });

        return response()->json(['ok' => true, 'pending' => $pending]);
    }

    public function create(Request $request): JsonResponse
    {
        $firstName   = trim($request->input('first_name', ''));
        $lastName    = trim($request->input('last_name', ''));
        if (!$firstName && $request->has('name')) {
            $fullName    = trim($request->input('name', ''));
            $nameParts   = explode(' ', $fullName, 2);
            $firstName   = $nameParts[0] ?? '';
            $lastName    = $nameParts[1] ?? '';
        }
        $fullName    = trim("{$firstName} {$lastName}");
        $email       = trim($request->input('email', ''));
        $role        = trim($request->input('role', ''));
        $institution = trim($request->input('institution', ''));
        $designation = trim($request->input('position', ''));

        $roleMap = [
            'CMI Representative' => 'cmi',
            'Guest'              => 'viewer',
            'Viewer'             => 'viewer',
            'guest'              => 'viewer',
            'cmi'                => 'cmi',
            'viewer'             => 'viewer',
            'executive'          => 'executive',
            'Executive'          => 'executive',
        ];
        $dbRole  = $roleMap[$role] ?? null;

        if (!$firstName || !$email || !$dbRole) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email'], 400);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['error' => 'Email already registered'], 409);
        }

        if ($dbRole === 'cmi') {
            if (empty($institution)) {
                return response()->json(['error' => 'Institution is required for CMI Representatives.'], 400);
            }

        } elseif ($dbRole === 'executive') {
            $institution = null;
            if (empty($designation)) {
                $designation = 'Executive Director';
            }
        }

        $tempPassword = bin2hex(random_bytes(6));
        $hashedPw     = Hash::make($tempPassword);
        $adminId      = Auth::id() ?? session('user_id');

        $newUser = User::create([
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'password'    => $hashedPw,
            'role'        => $dbRole,
            'institution' => $institution,
            'designation' => $designation,
            'status'      => 'active',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        ActivityLogService::log($adminId, "Created user: {$fullName} ({$email})");

        return response()->json([
            'success'       => true,
            'new_user_id'   => (int) $newUser->id,
            'temp_password' => $tempPassword,
        ]);
    }

    public function approve(Request $request): JsonResponse
    {
        $pendingId = intval($request->input('user_id', 0));
        if (!$pendingId) {
            return response()->json(['error' => 'Missing user_id'], 400);
        }

        $adminId = Auth::id() ?? session('user_id');

        try {
            return DB::transaction(function () use ($pendingId, $adminId) {
                $pending = User::where('id', $pendingId)->where('status', 'pending')->first();
                if (!$pending) {
                    return response()->json(['error' => 'Pending user not found'], 404);
                }



                $updateData = [
                    'status'      => 'active',
                    'approved_by' => $adminId,
                    'approved_at' => now(),
                ];
                if ($pending->role === 'executive') {
                    $updateData['institution'] = null;
                    if (empty($pending->designation)) {
                        $updateData['designation'] = 'Executive';
                    }
                }

                $pending->update($updateData);

                ActivityLogService::log($adminId, "Approved: {$pending->first_name} {$pending->last_name} ({$pending->email})");

                try {
                    \Illuminate\Support\Facades\Mail::to($pending->email)->send(
                        new \App\Mail\ApprovalMail($pending->first_name . ' ' . $pending->last_name, 'approved')
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not send approval email to {$pending->email}: " . $e->getMessage());
                }

                return response()->json(['success' => true, 'new_user_id' => (int) $pendingId]);
            });
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
