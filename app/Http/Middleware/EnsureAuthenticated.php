<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() && !session()->has('user_id')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized', 'success' => false, 'message' => 'Not authenticated.'], 401);
            }
            return redirect()->guest('/login');
        }

        return $next($request);
    }
}
