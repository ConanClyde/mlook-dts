<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user has 2FA enabled but hasn't verified this session
        if ($user && $user->two_factor_enabled && ! session('2fa_verified')) {
            // Allow access to 2FA routes
            if ($request->routeIs('two-factor.*')) {
                return $next($request);
            }

            // Redirect to 2FA challenge
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
