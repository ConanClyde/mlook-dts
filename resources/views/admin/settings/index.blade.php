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
            <div class="px-3 sm:px-4 lg:px-8">
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
        <main class="px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
            <!-- Header - Hidden on mobile since nav bar already shows "Settings" -->
            <div class="mb-4 sm:mb-6 lg:mb-8 hidden sm:block">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Settings</h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Manage your preferences and system settings</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-3 sm:gap-4 lg:gap-6">
                <!-- Left Sidebar Tabs -->
                <div class="w-full lg:w-80 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 rounded-lg lg:rounded-xl border border-gray-200 dark:border-gray-700 p-1.5 sm:p-2">
                        <nav class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-1 gap-1.5 sm:gap-2 lg:gap-1 lg:space-y-1">
                            <a href="{{ route('admin.settings.index', ['tab' => 'appearance']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'appearance' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="paint-brush" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left">Appearance</span>
                            </a>
                            <a href="{{ route('admin.settings.index', ['tab' => 'notifications']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'notifications' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="bell" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left whitespace-nowrap">Notifications</span>
                            </a>
                            <a href="{{ route('admin.settings.index', ['tab' => 'security']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'security' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="shield-check" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left">Security</span>
                            </a>
                            <div class="hidden lg:block col-span-1 border-t border-gray-200 dark:border-gray-700 my-2"></div>
                            <div class="col-span-2 sm:col-span-3 lg:hidden border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            <a href="{{ route('admin.settings.index', ['tab' => 'document-types']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'document-types' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="document-duplicate" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left whitespace-nowrap">Document Types</span>
                            </a>
                            <a href="{{ route('admin.settings.index', ['tab' => 'departments']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'departments' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="building-office" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left">Departments</span>
                            </a>
                            <a href="{{ route('admin.settings.index', ['tab' => 'programs']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'programs' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="academic-cap" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left">Programs</span>
                            </a>
                            <a href="{{ route('admin.settings.index', ['tab' => 'tags']) }}" 
                               class="flex flex-col sm:flex-row items-center justify-center sm:justify-start px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium rounded-lg transition-colors min-h-[44px] {{ $tab === 'tags' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <x-icon name="tag" class="w-4 h-4 sm:w-5 sm:h-5 mb-1 sm:mb-0 sm:mr-2 lg:mr-3 flex-shrink-0" />
                                <span class="text-center sm:text-left">Tags</span>
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Right Content Panel -->
                <div class="flex-1 min-w-0 w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg lg:rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4 lg:p-6">
                        @if($tab === 'appearance')
                            @include('admin.settings.partials.appearance')
                        @elseif($tab === 'notifications')
                            @include('admin.settings.partials.notifications')
                        @elseif($tab === 'security')
                            @include('admin.settings.partials.security')
                        @elseif($tab === 'document-types')
                            @include('admin.settings.partials.document-types')
                        @elseif($tab === 'departments')
                            @include('admin.settings.partials.departments')
                        @elseif($tab === 'programs')
                            @include('admin.settings.partials.programs')
                        @elseif($tab === 'tags')
                            @include('admin.settings.partials.tags')
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<x-success-modal />
<x-delete-modal />

<script>
// Hamburger menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
});
</script>

@endsection
