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
            ->orderBy('first_name', 'asc')
            ->get();

        $roleLabel = ['cmi' => 'CMI Representative', 'viewer' => 'Viewer'];

        $users = $rows->map(function ($r) use ($roleLabel) {
            return [
                'id'          => (int) $r->id,
                'name'        => $r->name,
                'email'       => $r->email,
                'role'        => $roleLabel[$r->role] ?? $r->role,
                'position'    => $r->designation ?? '—',
                'institution' => $r->institution ?? '—',
                'status'      => ucfirst($r->status),
                'lastLogin'   => '',
            ];
        });

        return response()->json($users);
    }

    public function getPending(): JsonResponse
    {
        $pending = User::where('status', 'pending')
            ->whereIn('role', ['cmi', 'viewer'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['ok' => true, 'pending' => $pending]);
    }

    public function create(Request $request): JsonResponse
    {
        $fullName    = trim($request->input('name', ''));
        $nameParts   = explode(' ', $fullName, 2);
        $firstName   = $nameParts[0] ?? '';
        $lastName    = $nameParts[1] ?? '';
        $email       = trim($request->input('email', ''));
        $role        = trim($request->input('role', ''));
        $institution = trim($request->input('institution', ''));
        $designation = trim($request->input('position', ''));

        $roleMap = ['CMI Representative' => 'cmi', 'Viewer' => 'viewer', 'cmi' => 'cmi', 'viewer' => 'viewer'];
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

                $pending->update([
                    'status'      => 'active',
                    'approved_by' => $adminId,
                    'approved_at' => now(),
                ]);

                ActivityLogService::log($adminId, "Approved: {$pending->first_name} {$pending->last_name} ({$pending->email})");

                return response()->json(['success' => true, 'new_user_id' => (int) $pendingId]);
            });
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
