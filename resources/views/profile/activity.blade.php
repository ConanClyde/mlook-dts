@extends('layouts.app')

@section('title', 'Account Activity - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Account Activity</h1>
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
        <main class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('profile') }}" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Back to Profile
                    </a>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-6">Recent Activity</h2>
                    
                    <div class="space-y-4">
                        @foreach($activities as $activity)
                        <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                    <x-icon name="clock" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-gray-400" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white break-words">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $activity['date']->format('F d, Y h:i A') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }

    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });
    });
});
</script>
@endsection

