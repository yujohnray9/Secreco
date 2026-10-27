<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    /**
     * Standard activity log (backward-compatible).
     */
    public static function log(
        ?int $userId,
        string $description,
        ?string $action = null,
        string $outcome = 'Success',
        ?string $ipAddress = null,
        ?string $userDisplay = null
    ): void {
        self::logAudit(
            userId: $userId,
            action: $action ?: $description,
            outcome: $outcome,
            userDisplay: $userDisplay,
            ipAddress: $ipAddress,
            description: $description
        );
    }

    /**
     * Comprehensive audit log.
     */
    public static function logAudit(
        ?int $userId,
        string $action,
        string $outcome = 'Success',
        ?string $userDisplay = null,
        ?string $ipAddress = null,
        ?string $description = null
    ): void {
        try {
            $user = $userId ? User::find($userId) : null;
            $ip = $ipAddress ?? (request() ? request()->ip() : '127.0.0.1');

            if (!$userDisplay) {
                $userDisplay = self::formatUserDisplay($user);
            }

            ActivityLog::create([
                'user_id'      => $userId,
                'action'       => $action,
                'outcome'      => $outcome,
                'ip_address'   => $ip,
                'user_display' => $userDisplay,
                'description'  => $description ?: $action,
                'created_at'   => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('ActivityLogService error: ' . $e->getMessage());
        }
    }

    /**
     * Format user display as "email (Role/Campus)".
     */
    public static function formatUserDisplay(?User $user, ?string $fallbackEmail = null): string
    {
        if ($user) {
            $email = $user->email;
            $role = strtolower($user->role ?? '');

            if ($role === 'pta') {
                return "{$email} (PTA Admin)";
            }

            if ($role === 'cmi') {
                $suffix = 'CMI';
                $inst = $user->institution ?? '';
                if ($inst) {
                    if (str_contains($inst, '-')) {
                        $parts = explode('-', $inst);
                        $suffix = 'CMI ' . trim(end($parts));
                    } elseif ($user->last_name && !in_array(strtolower($user->last_name), ['cmi', 'admin', 'user'])) {
                        $suffix = 'CMI ' . trim($user->last_name);
                    } else {
                        $suffix = 'CMI ' . trim(preg_replace('/\s+/', ' ', $inst));
                    }
                } elseif ($user->last_name && !in_array(strtolower($user->last_name), ['cmi', 'admin', 'user'])) {
                    $suffix = 'CMI ' . trim($user->last_name);
                }
                return "{$email} ({$suffix})";
            }

            if ($role === 'executive') {
                return "{$email} (Executive)";
            }

            if ($role === 'viewer') {
                return "{$email} (Viewer)";
            }

            return "{$email} (" . ucfirst($role) . ")";
        }

        if ($fallbackEmail) {
            return "{$fallbackEmail} (Unknown)";
        }

        return "System";
    }
}
