<?php

namespace App\Services;

use App\Models\PerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringService
{
    protected float $slowQueryThreshold;

    protected float $slowRequestThreshold;

    public function __construct()
    {
        $this->slowQueryThreshold = (float) config('performance.slow_query_threshold_ms', 100.0);
        $this->slowRequestThreshold = (float) config('performance.slow_request_threshold_ms', 1000.0);
    }

    protected array $queryLog = [];

    protected int $queryCount = 0;

    protected float $queryTime = 0.0;

    protected int $cacheHits = 0;

    protected int $cacheMisses = 0;

    protected array $slowQueries = [];

    protected float $startTime;

    protected int $startMemory;

    /**
     * Start monitoring a request.
     */
    public function startMonitoring(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        $this->queryCount = 0;
        $this->queryTime = 0.0;
        $this->cacheHits = 0;
        $this->cacheMisses = 0;
        $this->slowQueries = [];
        $this->queryLog = [];

        // Enable query logging
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
    }

    /**
     * Record a cache hit.
     */
    public function recordCacheHit(): void
    {
        $this->cacheHits++;
    }

    /**
     * Record a cache miss.
     */
    public function recordCacheMiss(): void
    {
        $this->cacheMisses++;
    }

    /**
     * Stop monitoring and record metrics.
     */
    public function stopMonitoring(Request $request, int $statusCode, ?string $errorMessage = null): ?PerformanceMetric
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $responseTime = ($endTime - $this->startTime) * 1000; // Convert to milliseconds
        $memoryUsage = ($endMemory - $this->startMemory) / 1024 / 1024; // Convert to MB

        // Get query information
        if (config('app.debug')) {
            $queries = DB::getQueryLog();
            $this->queryCount = count($queries);
            $this->queryTime = 0.0;

            foreach ($queries as $query) {
                $queryTime = $query['time'] ?? 0;
                $this->queryTime += $queryTime;

                // Track slow queries
                if ($queryTime > $this->slowQueryThreshold) {
                    $this->slowQueries[] = [
                        'query' => $query['query'] ?? '',
                        'bindings' => $query['bindings'] ?? [],
                        'time' => $queryTime,
                    ];
                }
            }
        }

        $isSlowRequest = $responseTime > $this->slowRequestThreshold || ! empty($this->slowQueries);

        // Get route information
        $route = $request->route();
        $routeName = $route?->getName();
        $controllerAction = $route?->getActionName();

        // Only log if it's a slow request, has errors, or is enabled in config
        if ($this->shouldLog($responseTime, $statusCode, $errorMessage)) {
            $metric = PerformanceMetric::create([
                'route_name' => $routeName,
                'method' => $request->method(),
                'uri' => $request->path(),
                'controller_action' => $controllerAction,
                'status_code' => $statusCode,
                'response_time_ms' => round($responseTime, 2),
                'memory_usage_mb' => round($memoryUsage, 2),
                'query_count' => $this->queryCount,
                'query_time_ms' => round($this->queryTime, 2),
                'cache_hits' => $this->cacheHits,
                'cache_misses' => $this->cacheMisses,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'slow_queries' => ! empty($this->slowQueries) ? $this->slowQueries : null,
                'is_slow_request' => $isSlowRequest,
                'error_message' => $errorMessage,
                'metadata' => $this->getMetadata($request),
                'created_at' => now(),
            ]);

            // Log slow requests or errors
            if ($isSlowRequest) {
                Log::warning('Slow request detected', [
                    'route' => $routeName,
                    'uri' => $request->path(),
                    'response_time_ms' => $responseTime,
                    'query_count' => $this->queryCount,
                    'slow_queries' => $this->slowQueries,
                ]);
            }

            if ($errorMessage || $statusCode >= 400) {
                Log::error('Request error', [
                    'route' => $routeName,
                    'uri' => $request->path(),
                    'status_code' => $statusCode,
                    'error' => $errorMessage,
                ]);
            }

            return $metric;
        }

        return null;
    }

    /**
     * Determine if we should log this request.
     */
    protected function shouldLog(float $responseTime, int $statusCode, ?string $errorMessage): bool
    {
        // Always log errors
        if ($errorMessage || $statusCode >= 400) {
            return true;
        }

        // Log slow requests
        if ($responseTime > $this->slowRequestThreshold) {
            return true;
        }

        // Log if slow queries detected
        if (! empty($this->slowQueries)) {
            return true;
        }

        // Log based on configuration
        return (bool) config('performance.log_all_requests', false);
    }

    /**
     * Get additional metadata about the request.
     */
    protected function getMetadata(Request $request): array
    {
        return [
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'content_length' => $request->header('content-length'),
            'accept' => $request->header('accept'),
        ];
    }

    /**
     * Get performance statistics.
     */
    public function getStatistics(int $days = 7): array
    {
        $since = now()->subDays($days);

        $stats = PerformanceMetric::where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(response_time_ms) as avg_response_time,
                MAX(response_time_ms) as max_response_time,
                MIN(response_time_ms) as min_response_time,
                AVG(query_count) as avg_query_count,
                AVG(query_time_ms) as avg_query_time,
                SUM(cache_hits) as total_cache_hits,
                SUM(cache_misses) as total_cache_misses,
                COUNT(CASE WHEN is_slow_request = 1 THEN 1 END) as slow_requests,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as error_requests
            ')
            ->first();

        $cacheHitRate = 0;
        if (($stats->total_cache_hits + $stats->total_cache_misses) > 0) {
            $cacheHitRate = ($stats->total_cache_hits / ($stats->total_cache_hits + $stats->total_cache_misses)) * 100;
        }

        return [
            'total_requests' => $stats->total_requests ?? 0,
            'avg_response_time_ms' => round($stats->avg_response_time ?? 0, 2),
            'max_response_time_ms' => round($stats->max_response_time ?? 0, 2),
            'min_response_time_ms' => round($stats->min_response_time ?? 0, 2),
            'avg_query_count' => round($stats->avg_query_count ?? 0, 2),
            'avg_query_time_ms' => round($stats->avg_query_time ?? 0, 2),
            'total_cache_hits' => $stats->total_cache_hits ?? 0,
            'total_cache_misses' => $stats->total_cache_misses ?? 0,
            'cache_hit_rate' => round($cacheHitRate, 2),
            'slow_requests' => $stats->slow_requests ?? 0,
            'error_requests' => $stats->error_requests ?? 0,
            'period_days' => $days,
        ];
    }

    /**
     * Get top slow routes.
     */
    public function getTopSlowRoutes(int $days = 7, int $limit = 10): array
    {
        return PerformanceMetric::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                route_name,
                COUNT(*) as request_count,
                AVG(response_time_ms) as avg_response_time,
                MAX(response_time_ms) as max_response_time,
                AVG(query_count) as avg_query_count
            ')
            ->groupBy('route_name')
            ->orderByDesc('avg_response_time')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Clean old metrics.
     */
    public function cleanOldMetrics(?int $days = null): int
    {
        $days = $days ?? (int) config('performance.retention_days', 30);

        return PerformanceMetric::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
