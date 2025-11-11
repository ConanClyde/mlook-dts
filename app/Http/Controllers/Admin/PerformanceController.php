<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceMetric;
use App\Services\PerformanceMonitoringService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected PerformanceMonitoringService $monitoringService;

    public function __construct(PerformanceMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Display performance metrics dashboard.
     */
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $days = in_array($days, [1, 7, 30, 90]) ? $days : 7;

        $statistics = $this->monitoringService->getStatistics($days);
        $slowQueries = PerformanceMetric::getSlowQueryStats($days);

        // Per page for all tables
        $topSlowRoutesPerPage = $request->input('top_routes_per_page', 10);
        $topSlowRoutesPerPage = in_array($topSlowRoutesPerPage, [10, 25, 50, 100]) ? $topSlowRoutesPerPage : 10;

        $recentSlowRequestsPerPage = $request->input('slow_requests_per_page', 10);
        $recentSlowRequestsPerPage = in_array($recentSlowRequestsPerPage, [10, 25, 50, 100]) ? $recentSlowRequestsPerPage : 10;

        $errorRequestsPerPage = $request->input('error_requests_per_page', 10);
        $errorRequestsPerPage = in_array($errorRequestsPerPage, [10, 25, 50, 100]) ? $errorRequestsPerPage : 10;

        // Get top slow routes with pagination
        $topSlowRoutesQuery = PerformanceMetric::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                route_name,
                COUNT(*) as request_count,
                AVG(response_time_ms) as avg_response_time,
                MAX(response_time_ms) as max_response_time,
                AVG(query_count) as avg_query_count,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as error_count
            ')
            ->whereNotNull('route_name')
            ->groupBy('route_name')
            ->orderByDesc('avg_response_time');

        $topSlowRoutes = $topSlowRoutesQuery->paginate($topSlowRoutesPerPage, ['*'], 'top_routes_page')
            ->withQueryString();

        // Get recent slow requests with pagination
        $recentSlowRequests = PerformanceMetric::slowRequests()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->paginate($recentSlowRequestsPerPage, ['*'], 'slow_requests_page')
            ->withQueryString();

        // Get error requests with pagination
        $errorRequests = PerformanceMetric::withErrors()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->paginate($errorRequestsPerPage, ['*'], 'error_requests_page')
            ->withQueryString();

        return view('admin.performance.index', compact(
            'statistics',
            'topSlowRoutes',
            'slowQueries',
            'recentSlowRequests',
            'errorRequests',
            'days'
        ));
    }

    /**
     * Clean old performance metrics.
     */
    public function clean(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $deleted = $this->monitoringService->cleanOldMetrics($days);

        return back()->with('success', "Cleaned up {$deleted} old performance metrics.");
    }

    /**
     * Get performance metrics as JSON (for API/charts).
     */
    public function metrics(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $statistics = $this->monitoringService->getStatistics($days);
        $topSlowRoutes = $this->monitoringService->getTopSlowRoutes($days, 10);

        return response()->json([
            'statistics' => $statistics,
            'top_slow_routes' => $topSlowRoutes,
        ]);
    }
}
