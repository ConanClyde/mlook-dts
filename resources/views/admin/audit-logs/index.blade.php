@extends('layouts.app')

@section('title', 'Audit Logs - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Audit Logs</h1>
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
            <div class="mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Audit Logs</h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Track all system activities and changes</p>
            </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" id="audit-filter-form" class="w-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:flex lg:flex-row gap-3 sm:gap-4 w-full">
                <!-- Search -->
                <div class="lg:flex-1 lg:min-w-0">
                    <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Search
                    </label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search event or description..."
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                        oninput="autoSubmitAuditForm()"
                    >
                </div>

                <!-- Event Type Filter -->
                <div class="lg:flex-1 lg:min-w-0">
                    <label for="event" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Event Type
                    </label>
                    <select 
                        id="event" 
                        name="event" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                        onchange="autoSubmitAuditForm()"
                    >
                        <option value="">All Events</option>
                        <option value="document" {{ request('event') == 'document' ? 'selected' : '' }}>Document Events</option>
                        <option value="user" {{ request('event') == 'user' ? 'selected' : '' }}>User Events</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="lg:flex-1 lg:min-w-0">
                    <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Date From
                    </label>
                    <input 
                        type="date" 
                        id="date_from" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                        onchange="autoSubmitAuditForm()"
                    >
                </div>

                <!-- Date To -->
                <div class="lg:flex-1 lg:min-w-0">
                    <label for="date_to" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Date To
                    </label>
                    <input 
                        type="date" 
                        id="date_to" 
                        name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                        onchange="autoSubmitAuditForm()"
                    >
                </div>

                <!-- Per Page -->
                <div class="lg:w-auto lg:flex-shrink-0">
                    <label for="per_page" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Per Page
                    </label>
                    <select 
                        id="per_page" 
                        name="per_page" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                        onchange="document.getElementById('audit-filter-form').submit();"
                    >
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                    <a href="{{ route('admin.audit-logs.index') }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                        Clear
                    </a>
                </div>
            </div>

            <!-- Results Count -->
            @if(request()->hasAny(['search', 'event', 'date_from', 'date_to']))
                <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    {{ $logs->total() }} {{ Str::plural('result', $logs->total()) }} found
                </div>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <!-- Mobile Card View -->
        <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($logs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <!-- Event Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-2
                                {{ str_starts_with($log->event, 'document') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                {{ str_starts_with($log->event, 'user') ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                {{ !str_starts_with($log->event, 'document') && !str_starts_with($log->event, 'user') ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                {{ $log->event }}
                            </span>
                            
                            <!-- Description -->
                            <div class="text-sm text-gray-900 dark:text-white break-words mb-2">
                                {{ $log->description }}
                            </div>
                            
                            <!-- Meta Info -->
                            <div class="flex flex-col gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <x-icon name="user" class="w-3 h-3" />
                                    <span>{{ $log->user?->full_name ?? 'System' }}</span>
                                    @if($log->user)
                                        <span class="capitalize">({{ $log->user->usertype }})</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-icon name="clock" class="w-3 h-3" />
                                    <span>{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                </div>
                                <div class="flex items-center gap-2 font-mono">
                                    <x-icon name="globe-alt" class="w-3 h-3" />
                                    <span>{{ $log->ip_address }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <x-icon name="clipboard-document-list" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No audit logs found</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filters</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th scope="col" class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Event
                        </th>
                        <th scope="col" class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            IP Address
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-300">
                                <div class="font-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-gray-500 dark:text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden mt-1">
                                    {{ $log->user?->full_name ?? 'System' }}
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm">
                                <div class="font-medium text-gray-900 dark:text-gray-300">
                                    {{ $log->user?->full_name ?? 'System' }}
                                </div>
                                @if($log->user)
                                    <div class="text-gray-500 dark:text-gray-400 capitalize">
                                        {{ $log->user->usertype }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ str_starts_with($log->event, 'document') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                    {{ str_starts_with($log->event, 'user') ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                    {{ !str_starts_with($log->event, 'document') && !str_starts_with($log->event, 'user') ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 dark:text-gray-300">
                                <div class="max-w-xs truncate">{{ $log->description }}</div>
                            </td>
                            <td class="hidden lg:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-mono">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <x-icon name="clipboard-document-list" class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No audit logs found</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    {{ $logs->links('vendor.pagination.minimal') }}
                </div>
            </div>
        @endif
    </div>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit audit form with debounce
    let auditSearchTimeout;
    function autoSubmitAuditForm() {
        clearTimeout(auditSearchTimeout);
        auditSearchTimeout = setTimeout(() => {
            document.getElementById('audit-filter-form').submit();
        }, 500);
    }

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

// Comments-style realtime for audit logs
(function initializeAuditBroadcasting() {
    if (!window.Echo || window.Echo._isDummy) {
        setTimeout(initializeAuditBroadcasting, 100);
        return;
    }
    try {
        window.Echo.private('admin.audit-logs')
            .subscribed(() => {})
            .error(() => {})
            .listen('.audit.created', () => { if (typeof window.refreshAuditLogs === 'function') window.refreshAuditLogs(); });
    } catch (e) {
        console.error('Audit broadcasting init failed:', e);
    }
})();

// Refresh audit logs without reload
window.refreshAuditLogs = async function() {
    try {
        const url = new URL(window.location.href);
        const response = await fetch(url.toString(), { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
        if (!response.ok) return;
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newTable = doc.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border');
        const curTable = document.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border');
        if (newTable && curTable && curTable.parentElement) {
            const parent = curTable.parentElement;
            curTable.style.transition = 'opacity 0.3s';
            curTable.style.opacity = '0.5';
            setTimeout(() => {
                if (curTable.parentElement === parent) {
                    parent.replaceChild(document.importNode(newTable, true), curTable);
                }
            }, 300);
        }
    } catch (e) {
        console.error('Failed to refresh audit logs:', e);
    }
}
</script>
@endsection
