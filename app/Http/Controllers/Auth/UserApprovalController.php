<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserApprovalController extends Controller
{
    public function approveUser(Request $request): JsonResponse
    {
        $userId  = (int) $request->input('user_id', 0);
        $action  = trim($request->input('action', ''));
        $adminId = Auth::id() ?? session('user_id');

        if ($userId <= 0 || !in_array($action, ['approve', 'reject', 'activate', 'deactivate'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid request parameters.']);
        }

        $user = User::where('id', $userId)->whereIn('role', ['cmi', 'viewer'])->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $name = $user->name;

        try {
            return DB::transaction(function () use ($action, $user, $adminId, $name) {
                switch ($action) {
                    case 'approve':
                        if ($user->status !== 'pending') {
                            return response()->json(['success' => false, 'message' => 'Account is no longer pending.']);
                        }
                        if ($user->role === 'cmi') {
                            $activeExists = User::where('role', 'cmi')
                                ->whereRaw('LOWER(TRIM(institution)) = ?', [mb_strtolower(trim((string)$user->institution))])
                                ->where('status', 'active')
                                ->where('id', '!=', $user->id)
                                ->exists();
                            if ($activeExists) {
                                return response()->json(['success' => false, 'message' => "Cannot approve: An active CMI Representative already exists for {$user->institution}."]);
                            }
                        }
                        $user->update([
                            'status'      => 'active',
                            'approved_at' => now(),
                            'approved_by' => $adminId,
                        ]);
                        ActivityLogService::log($adminId, "Approved account for {$name} ({$user->email})");

                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\ApprovalMail($user->first_name . ' ' . $user->last_name, 'approved')
                            );
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("Could not send approval email to {$user->email}: " . $e->getMessage());
                        }

                        return response()->json(['success' => true, 'message' => "Account approved — {$name} can now log in."]);

                    case 'reject':
                        if ($user->status !== 'pending') {
                            return response()->json(['success' => false, 'message' => 'Account is no longer pending.']);
                        }
                        $user->update(['status' => 'inactive']);
                        ActivityLogService::log($adminId, "Rejected registration for {$name} ({$user->email})");

                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\ApprovalMail($user->first_name . ' ' . $user->last_name, 'rejected')
                            );
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("Could not send rejection email to {$user->email}: " . $e->getMessage());
                        }

                        return response()->json(['success' => true, 'message' => "Registration rejected — {$name} will not be able to log in."]);

                    case 'deactivate':
                        if ($user->status !== 'active') {
                            return response()->json(['success' => false, 'message' => 'Account is not currently active.']);
                        }
                        $user->update(['status' => 'inactive']);
                        ActivityLogService::log($adminId, "Deactivated account for {$name}");

                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\ApprovalMail($user->first_name . ' ' . $user->last_name, 'deactivated')
                            );
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("Could not send deactivation email to {$user->email}: " . $e->getMessage());
                        }

                        return response()->json(['success' => true, 'message' => "{$name}'s account has been deactivated."]);

                    case 'activate':
                        if ($user->status !== 'inactive') {
                            return response()->json(['success' => false, 'message' => 'Account is not currently inactive.']);
                        }
                        if ($user->role === 'cmi') {
                            $activeExists = User::where('role', 'cmi')
                                ->whereRaw('LOWER(TRIM(institution)) = ?', [mb_strtolower(trim((string)$user->institution))])
                                ->where('status', 'active')
                                ->where('id', '!=', $user->id)
                                ->exists();
                            if ($activeExists) {
                                return response()->json(['success' => false, 'message' => "Cannot reactivate: An active CMI Representative already exists for {$user->institution}."]);
                            }
                        }
                        $user->update(['status' => 'active']);
                        ActivityLogService::log($adminId, "Reactivated account for {$name}");

                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\ApprovalMail($user->first_name . ' ' . $user->last_name, 'approved')
                            );
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("Could not send activation email to {$user->email}: " . $e->getMessage());
                        }

                        return response()->json(['success' => true, 'message' => "{$name}'s account has been reactivated."]);
                }
            });
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Database error. Please try again.']);
        }
    }

    public function toggleUserStatus(Request $request): JsonResponse
    {
        $targetId  = intval($request->input('user_id', 0));
        $newStatus = trim($request->input('new_status', ''));

        if (!$targetId || !in_array($newStatus, ['active', 'inactive'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid request.']);
        }

        $target = User::find($targetId);
        if (!$target) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        if (!in_array($target->role, ['cmi', 'viewer'], true)) {
            return response()->json(['success' => false, 'message' => 'You cannot manage this account.']);
        }

        if ($newStatus === 'active' && $target->role === 'cmi') {
            $activeExists = User::where('role', 'cmi')
                ->whereRaw('LOWER(TRIM(institution)) = ?', [mb_strtolower(trim((string)$target->institution))])
                ->where('status', 'active')
                ->where('id', '!=', $target->id)
                ->exists();
            if ($activeExists) {
                return response()->json(['success' => false, 'message' => "Cannot activate: An active CMI Representative already exists for {$target->institution}."]);
            }
        }

        $target->update(['status' => $newStatus]);
        $label = $newStatus === 'active' ? 'reactivated' : 'deactivated';

        try {
            $mailResult = $newStatus === 'active' ? 'approved' : 'deactivated';
            \Illuminate\Support\Facades\Mail::to($target->email)->send(
                new \App\Mail\ApprovalMail($target->first_name . ' ' . $target->last_name, $mailResult)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not send {$label} email to {$target->email}: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => "Account {$label} successfully."]);
    }
}
