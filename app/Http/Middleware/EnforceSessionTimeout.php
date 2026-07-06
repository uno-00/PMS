<?php

namespace App\Http\Middleware;

use App\Support\PasswordPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the configurable "Session Timeout" security setting
 * (Settings > Security). Falls back to config('session.lifetime') when
 * no override has been saved yet.
 */
class EnforceSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $minutes = PasswordPolicy::sessionTimeoutMinutes();
            $lastActivity = $request->session()->get('last_activity_at');

            if ($lastActivity && now()->diffInMinutes($lastActivity) > $minutes) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Your session has expired due to inactivity.');
            }

            $request->session()->put('last_activity_at', now());
        }

        return $next($request);
    }
}
