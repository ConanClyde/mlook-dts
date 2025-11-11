<?php

namespace App\Http\Middleware;

use App\Services\PerformanceMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware
{
    protected PerformanceMonitoringService $monitoringService;

    public function __construct(PerformanceMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip monitoring if disabled or route is excluded
        if (! config('performance.enabled', true) || $this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Start monitoring
        $this->monitoringService->startMonitoring();

        try {
            $response = $next($request);

            // Stop monitoring and record metrics
            $statusCode = $response->getStatusCode();
            $this->monitoringService->stopMonitoring($request, $statusCode);

            return $response;
        } catch (\Throwable $e) {
            // Record error in metrics
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $this->monitoringService->stopMonitoring($request, $statusCode, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Check if the route should be excluded from monitoring.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $excludedRoutes = config('performance.excluded_routes', []);
        $routeName = $request->route()?->getName();
        $path = $request->path();

        foreach ($excludedRoutes as $pattern) {
            if ($routeName && fnmatch($pattern, $routeName)) {
                return true;
            }
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }
}
