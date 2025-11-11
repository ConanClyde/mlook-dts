@extends('layouts.app')

@section('title', 'Settings - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Settings</h1>
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
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage your account preferences and security settings</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                <!-- Left Sidebar Tabs -->
                <div class="w-full lg:w-80 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2">
                        <nav class="flex flex-col gap-1 space-y-1">
                            <a href="{{ route('settings.index', ['tab' => 'appearance']) }}" 
                               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'appearance' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="paint-brush" class="w-5 h-5 mr-3 flex-shrink-0" />
                                <span>Appearance</span>
                            </a>
                            <a href="{{ route('settings.index', ['tab' => 'notifications']) }}" 
                               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'notifications' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="bell" class="w-5 h-5 mr-3 flex-shrink-0" />
                                <span>Notification Preferences</span>
                            </a>
                            <a href="{{ route('settings.index', ['tab' => 'security']) }}" 
                               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'security' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="shield-check" class="w-5 h-5 mr-3 flex-shrink-0" />
                                <span>Security (2FA)</span>
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Right Content Panel -->
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        @if($tab === 'appearance')
                            @include('settings.partials.appearance')
                        @elseif($tab === 'notifications')
                            @include('settings.partials.notifications')
                        @elseif($tab === 'security')
                            @include('settings.partials.security')
                        @endif
                    </div>
                </div>
            </div>
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
@endsection
