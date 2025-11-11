<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get whitelisted IPs from config
        $whitelist = config('security.ip_whitelist', []);

        // If whitelist is empty, allow all (disabled)
        if (empty($whitelist)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        // Check if IP is whitelisted
        if (! in_array($clientIp, $whitelist)) {
            abort(403, 'Access denied. Your IP address is not whitelisted.');
        }

        return $next($request);
    }
}
