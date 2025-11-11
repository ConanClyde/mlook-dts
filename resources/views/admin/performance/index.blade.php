@extends('layouts.app')

@section('title', 'Performance Monitoring - MLOOK')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-72">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-icon name="bars-3" class="w-6 h-6" />
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Monitoring</h1>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <x-notifications />
                        <x-dark-mode-toggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main id="main-content" class="px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Performance Monitoring</h1>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Monitor application performance and identify bottlenecks</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <!-- Time Period Filter -->
                    <form method="GET" action="{{ route('admin.performance.index') }}" class="flex gap-2 w-full sm:w-auto">
                        <select name="days" onchange="this.form.submit()" class="flex-1 sm:flex-none px-3 py-2.5 min-h-[44px] text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ $days == 1 ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
                        </select>
                    </form>
                    <!-- Clean Old Metrics -->
                    <form method="POST" action="{{ route('admin.performance.clean') }}" onsubmit="return confirm('Are you sure you want to clean old metrics?')" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-4 py-2.5 min-h-[44px] text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            Clean Old Metrics
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="chart-bar" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Total Requests</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($statistics['total_requests']) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="clock" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Avg Response Time</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($statistics['avg_response_time_ms'], 2) }}ms</p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="exclamation-triangle" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Slow Requests</p>
                    <p class="text-2xl sm:text-3xl font-bold text-orange-600 dark:text-orange-400 transition-colors duration-200">{{ number_format($statistics['slow_requests']) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="x-circle" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Error Requests</p>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600 dark:text-red-400 transition-colors duration-200">{{ number_format($statistics['error_requests']) }}</p>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Cache Hit Rate</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($statistics['cache_hit_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ number_format($statistics['total_cache_hits']) }} hits / {{ number_format($statistics['total_cache_misses']) }} misses
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Avg Query Count</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($statistics['avg_query_count'], 1) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Avg query time: {{ number_format($statistics['avg_query_time_ms'], 2) }}ms
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Max Response Time</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($statistics['max_response_time_ms'], 2) }}ms</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Min: {{ number_format($statistics['min_response_time_ms'], 2) }}ms
                    </p>
                </div>
            </div>

            <!-- Top Slow Routes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6 transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Slow Routes</h2>
                    <form method="GET" action="{{ route('admin.performance.index') }}" class="flex items-center gap-3">
                        <input type="hidden" name="days" value="{{ $days }}">
                        <label for="top_routes_per_page" class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            Per Page:
                        </label>
                        <select 
                            id="top_routes_per_page" 
                            name="top_routes_per_page" 
                            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                            onchange="this.form.submit();"
                        >
                            <option value="10" {{ request('top_routes_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('top_routes_per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('top_routes_per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('top_routes_per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Route</th>
                                <th class="text-right py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Requests</th>
                                <th class="hidden sm:table-cell text-right py-2 px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Avg Time</th>
                                <th class="text-right py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Max Time</th>
                                <th class="hidden md:table-cell text-right py-2 px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Avg Queries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSlowRoutes as $route)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 px-2 sm:px-4 text-gray-900 dark:text-white text-xs sm:text-sm">{{ $route->route_name ?? 'N/A' }}</td>
                                    <td class="py-2 px-2 sm:px-4 text-right text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ number_format($route->request_count) }}</td>
                                    <td class="hidden sm:table-cell py-2 px-4 text-right text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ number_format((float)$route->avg_response_time, 2) }}ms</td>
                                    <td class="py-2 px-2 sm:px-4 text-right text-orange-600 dark:text-orange-400 text-xs sm:text-sm">{{ number_format((float)$route->max_response_time, 2) }}ms</td>
                                    <td class="hidden md:table-cell py-2 px-4 text-right text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ number_format((float)$route->avg_query_count, 1) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">No slow routes found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($topSlowRoutes->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            {{ $topSlowRoutes->links('vendor.pagination.minimal') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Slow Requests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6 transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Slow Requests</h2>
                    <form method="GET" action="{{ route('admin.performance.index') }}" class="flex items-center gap-3">
                        <input type="hidden" name="days" value="{{ $days }}">
                        <label for="slow_requests_per_page" class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            Per Page:
                        </label>
                        <select 
                            id="slow_requests_per_page" 
                            name="slow_requests_per_page" 
                            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                            onchange="this.form.submit();"
                        >
                            <option value="10" {{ request('slow_requests_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('slow_requests_per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('slow_requests_per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('slow_requests_per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Route</th>
                                <th class="hidden sm:table-cell text-left py-2 px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Method</th>
                                <th class="text-right py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Response Time</th>
                                <th class="hidden md:table-cell text-right py-2 px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Queries</th>
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSlowRequests as $request)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 px-2 sm:px-4 text-gray-900 dark:text-white text-xs sm:text-sm">{{ $request->route_name ?? $request->uri }}</td>
                                    <td class="hidden sm:table-cell py-2 px-4 text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ $request->method }}</td>
                                    <td class="py-2 px-2 sm:px-4 text-right text-orange-600 dark:text-orange-400 text-xs sm:text-sm">{{ number_format($request->response_time_ms, 2) }}ms</td>
                                    <td class="hidden md:table-cell py-2 px-4 text-right text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ $request->query_count }}</td>
                                    <td class="py-2 px-2 sm:px-4 text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ $request->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">No slow requests found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentSlowRequests->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            {{ $recentSlowRequests->links('vendor.pagination.minimal') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Error Requests -->
            @if($errorRequests->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Error Requests</h2>
                    <form method="GET" action="{{ route('admin.performance.index') }}" class="flex items-center gap-3">
                        <input type="hidden" name="days" value="{{ $days }}">
                        <label for="error_requests_per_page" class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            Per Page:
                        </label>
                        <select 
                            id="error_requests_per_page" 
                            name="error_requests_per_page" 
                            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                            onchange="this.form.submit();"
                        >
                            <option value="10" {{ request('error_requests_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('error_requests_per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('error_requests_per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('error_requests_per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Route</th>
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Status</th>
                                <th class="hidden md:table-cell text-left py-2 px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Error</th>
                                <th class="text-left py-2 px-2 sm:px-4 text-gray-700 dark:text-gray-300 text-xs sm:text-sm">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($errorRequests as $request)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 px-2 sm:px-4 text-gray-900 dark:text-white text-xs sm:text-sm">{{ $request->route_name ?? $request->uri }}</td>
                                    <td class="py-2 px-2 sm:px-4">
                                        <span class="px-2 py-1 text-xs rounded bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                            {{ $request->status_code }}
                                        </span>
                                    </td>
                                    <td class="hidden md:table-cell py-2 px-4 text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ Str::limit($request->error_message, 50) }}</td>
                                    <td class="py-2 px-2 sm:px-4 text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ $request->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($errorRequests->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            {{ $errorRequests->links('vendor.pagination.minimal') }}
                        </div>
                    </div>
                @endif
            </div>
            @endif
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle Functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('hidden');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
            }
            sidebarOverlay.classList.add('hidden');
        });
    }

    if (sidebar) {
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                }
            });
        });
    }
});
</script>

<script>
// Periodic AJAX refresh for performance widgets and tables (no full reload)
async function refreshPerformance() {
	try {
		const url = new URL(window.location.href);
		const response = await fetch(url.toString(), { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
		if (!response.ok) return;
		const html = await response.text();
		const doc = new DOMParser().parseFromString(html, 'text/html');

		// Sections to refresh by container
		const selectors = [
			// Statistics cards grid
			'.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-4.mb-6',
			// Additional stats grid
			'.grid.grid-cols-1.md\\:grid-cols-3.gap-4.mb-6',
			// Top slow routes card
			'.bg-white.dark\\:bg-gray-800.rounded-lg.border.border-gray-200.dark\\:border-gray-700.p-4.sm\\:p-6.mb-6',
		];

		selectors.forEach((selector, idx) => {
			const fresh = doc.querySelector(selector);
			const current = document.querySelector(selector);
			if (fresh && current && current.parentElement) {
				const parent = current.parentElement;
				current.style.transition = 'opacity 0.2s';
				current.style.opacity = '0.5';
				setTimeout(() => {
					if (current.parentElement === parent) {
						parent.replaceChild(document.importNode(fresh, true), current);
					}
				}, 200);
			}
		});
	} catch (e) {
		console.error('Failed to refresh performance page:', e);
	}
}

// Refresh every 30 seconds
setInterval(refreshPerformance, 30000);
</script>
@endsection

