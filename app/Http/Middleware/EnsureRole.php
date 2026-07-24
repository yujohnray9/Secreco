<?php

namespace App\Http\Middleware;

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

        if (!$userRole || !in_array($userRole, $roles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden', 'ok' => false, 'message' => 'Forbidden.'], 403);
            }
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
