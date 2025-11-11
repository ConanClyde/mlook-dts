<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for performance monitoring and logging.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Set to true to enable performance monitoring middleware.
    |
    */

    'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log All Requests
    |--------------------------------------------------------------------------
    |
    | Set to true to log all requests, not just slow ones or errors.
    | When false, only slow requests and errors are logged.
    |
    */

    'log_all_requests' => env('PERFORMANCE_LOG_ALL', false),

    /*
    |--------------------------------------------------------------------------
    | Slow Request Threshold
    |--------------------------------------------------------------------------
    |
    | Requests taking longer than this (in milliseconds) will be marked
    | as slow requests.
    |
    */

    'slow_request_threshold_ms' => env('PERFORMANCE_SLOW_REQUEST_THRESHOLD', 1000),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Database queries taking longer than this (in milliseconds) will be
    | logged as slow queries.
    |
    */

    'slow_query_threshold_ms' => env('PERFORMANCE_SLOW_QUERY_THRESHOLD', 100),

    /*
    |--------------------------------------------------------------------------
    | Metrics Retention Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep performance metrics in the database.
    | Older metrics will be cleaned up automatically.
    |
    */

    'retention_days' => env('PERFORMANCE_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from performance monitoring.
    |
    */

    'excluded_routes' => [
        'up', // Health check
        'horizon.*', // Laravel Horizon
        'telescope.*', // Laravel Telescope
    ],

];
