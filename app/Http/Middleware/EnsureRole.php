<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user() ?? (session()->has('user_id') ? \App\Models\User::find(session('user_id')) : null);
        $userRole = $user?->role ?? session('user_role');

        // Check if user is not active (pending or inactive)
        if ($user && $user->status !== 'active') {
            $userDisplay = ActivityLogService::formatUserDisplay($user);
            $action = self::resolveActionName($request, $roles);

            ActivityLogService::logAudit(
                userId: $user->id,
                action: $action,
                outcome: 'Denied (RBAC)',
                userDisplay: $userDisplay,
                ipAddress: $request->ip(),
                description: "Access denied: account status is '{$user->status}' (not accepted or deactivated)"
            );

            Auth::logout();
            session()->flush();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden', 'ok' => false, 'message' => 'Account not accepted or deactivated.'], 403);
            }
            return redirect('/login')->with('error', 'Your account has not been accepted or is deactivated.');
        }

        if (!$userRole || !in_array($userRole, $roles, true)) {
            $userDisplay = ActivityLogService::formatUserDisplay($user);
            $action = self::resolveActionName($request, $roles);

            ActivityLogService::logAudit(
                userId: $user?->id,
                action: $action,
                outcome: 'Denied (RBAC)',
                userDisplay: $userDisplay,
                ipAddress: $request->ip(),
                description: "Access denied: role '{$userRole}' lacks permission for {$request->path()} (requires: " . implode(',', $roles) . ")"
            );

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden', 'ok' => false, 'message' => 'Forbidden.'], 403);
            }
            return redirect('/dashboard/' . ($userRole ?: ''));
        }

        return $next($request);
    }

    private static function resolveActionName(Request $request, array $roles): string
    {
        $path = strtolower($request->path());
        $rolesStr = strtoupper(implode('/', $roles));

        if (str_contains($path, 'submission')) {
            return "Opened Submissions page ({$rolesStr} only)";
        }
        if (str_contains($path, 'user')) {
            return "Opened Users page ({$rolesStr} only)";
        }
        if (str_contains($path, 'format')) {
            return "Opened Formats page ({$rolesStr} only)";
        }
        if (str_contains($path, 'institution')) {
            return "Opened Institutions page ({$rolesStr} only)";
        }
        if (str_contains($path, 'setting')) {
            return "Opened Settings page ({$rolesStr} only)";
        }
        if (str_contains($path, 'report')) {
            return "Opened Reports page ({$rolesStr} only)";
        }

        $segments = explode('/', trim($request->path(), '/'));
        $lastSegment = ucfirst(end($segments) ?: 'Protected');
        return "Opened {$lastSegment} page ({$rolesStr} only)";
    }
}
