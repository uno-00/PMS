<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user?->must_change_password && ! $request->routeIs('password.force', 'logout')) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}
