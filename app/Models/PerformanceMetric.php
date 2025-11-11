<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'method',
        'uri',
        'controller_action',
        'status_code',
        'response_time_ms',
        'memory_usage_mb',
        'query_count',
        'query_time_ms',
        'cache_hits',
        'cache_misses',
        'user_id',
        'ip_address',
        'slow_queries',
        'is_slow_request',
        'error_message',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'response_time_ms' => 'float',
        'memory_usage_mb' => 'integer',
        'query_count' => 'integer',
        'query_time_ms' => 'float',
        'cache_hits' => 'integer',
        'cache_misses' => 'integer',
        'slow_queries' => 'array',
        'is_slow_request' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Scope to get slow requests.
     */
    public function scopeSlowRequests($query, int $thresholdMs = 1000)
    {
        return $query->where('response_time_ms', '>', $thresholdMs)
            ->orWhere('is_slow_request', true);
    }

    /**
     * Scope to get requests by route.
     */
    public function scopeByRoute($query, string $routeName)
    {
        return $query->where('route_name', $routeName);
    }

    /**
     * Scope to get requests by status code.
     */
    public function scopeByStatusCode($query, int $statusCode)
    {
        return $query->where('status_code', $statusCode);
    }

    /**
     * Scope to get requests with errors.
     */
    public function scopeWithErrors($query)
    {
        return $query->whereNotNull('error_message')
            ->orWhere('status_code', '>=', 400);
    }

    /**
     * Get average response time for a route.
     */
    public static function getAverageResponseTime(string $routeName, int $days = 7): ?float
    {
        return static::where('route_name', $routeName)
            ->where('created_at', '>=', now()->subDays($days))
            ->avg('response_time_ms');
    }

    /**
     * Get slow query statistics.
     */
    public static function getSlowQueryStats(int $days = 7): array
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('slow_queries')
            ->get()
            ->pluck('slow_queries')
            ->flatten(1)
            ->groupBy('query')
            ->map(function ($queries) {
                return [
                    'query' => $queries->first()['query'] ?? '',
                    'count' => $queries->count(),
                    'avg_time' => $queries->avg('time'),
                    'max_time' => $queries->max('time'),
                ];
            })
            ->sortByDesc('avg_time')
            ->values()
            ->toArray();
    }
}
