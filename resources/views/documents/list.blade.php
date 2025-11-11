@extends('layouts.app')

@section('title', $pageTitle . ' - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">{{ $pageTitle }}</h1>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">View and manage your documents</p>
                </div>
                @if(auth()->user()->canCreateDocuments() && $pageTitle !== 'Sent Documents' && $pageTitle !== 'Completed Documents' && $pageTitle !== 'Inbox' && $pageTitle !== 'Archive' && (!isset($section) || $section !== 'sent'))
                <button type="button" onclick="openCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
                    <x-icon name="plus" class="w-4 h-4 mr-2" />
                    <span class="hidden sm:inline">Create Document</span>
                    <span class="sm:hidden">Create</span>
                </button>
                @endif
            </div>

            @if (session('status'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
                <form method="GET" id="filter-form" class="w-full">
                    @if(isset($section))
                    <input type="hidden" name="section" value="{{ $section }}">
                    @endif
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
                                placeholder="Tracking number, title..."
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                                oninput="autoSubmitForm()"
                            >
                        </div>

                        <!-- Status Filter -->
                        @if($pageTitle === 'My Documents' || $pageTitle === 'Sent Documents' || $pageTitle === 'Completed Documents')
                        <div class="lg:flex-1 lg:min-w-0">
                            <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Status
                            </label>
                            <select 
                                id="status" 
                                name="status" 
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                                onchange="autoSubmitForm()"
                            >
                                <option value="">All Statuses</option>
                                @php
                                    // Define statuses based on page
                                    if ($pageTitle === 'My Documents' && (!isset($section) || $section === 'draft')) {
                                        $statuses = [
                                            \App\Models\Document::STATUS_DRAFT => 'Draft',
                                            \App\Models\Document::STATUS_RETURNED => 'Returned',
                                        ];
                                    } elseif ($pageTitle === 'Sent Documents' || (isset($section) && $section === 'sent')) {
                                        $statuses = [
                                            \App\Models\Document::STATUS_ROUTING => 'Routing',
                                            \App\Models\Document::STATUS_RECEIVED => 'Received',
                                            \App\Models\Document::STATUS_IN_REVIEW => 'In Review',
                                            \App\Models\Document::STATUS_FOR_APPROVAL => 'For Approval',
                                        ];
                                    } elseif ($pageTitle === 'Completed Documents') {
                                        $statuses = [
                                            \App\Models\Document::STATUS_COMPLETED => 'Completed',
                                            \App\Models\Document::STATUS_APPROVED => 'Approved',
                                            \App\Models\Document::STATUS_REJECTED => 'Rejected',
                                        ];
                                    } elseif ($pageTitle === 'Archive') {
                                        $statuses = [];
                                    } else {
                                        $statuses = [];
                                    }
                                @endphp
                                @foreach($statuses as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" {{ request('status') == $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Document Type Filter -->
                        <div class="lg:flex-1 lg:min-w-0">
                            <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Document Type
                            </label>
                            <select 
                                id="type" 
                                name="type" 
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                                onchange="autoSubmitForm()"
                            >
                                <option value="">All Types</option>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
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
                                onchange="document.getElementById('filter-form').submit();"
                            >
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <!-- Clear Button -->
                        <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                            @php
                                $currentRoute = request()->route()->getName();
                                $clearRoute = $currentRoute;
                                $clearParams = [];
                                
                                // Determine route and params based on current route
                                if ($currentRoute === 'documents.index' || $currentRoute === 'documents.sent') {
                                    // For index and sent routes, preserve section if it exists
                                    if (isset($section)) {
                                        $clearParams['section'] = $section;
                                    }
                                }
                            @endphp
                            <a href="{{ route($clearRoute, $clearParams) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                                Clear
                            </a>
                        </div>
                    </div>

                    <!-- Results Count -->
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            {{ $documents->total() }} {{ Str::plural('result', $documents->total()) }} found
                        </div>
                    @endif
                </form>
            </div>

            <!-- Documents Table -->
            <div id="documents-table" class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <!-- Mobile Card View -->
                <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($documents as $document)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" class="document-checkbox h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mt-1 flex-shrink-0" value="{{ $document->id }}">
                                <div class="flex-1 min-w-0">
                                    <!-- Tracking Number & Unread Indicator -->
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-mono text-gray-600 dark:text-gray-400">{{ $document->tracking_number }}</span>
                                        @if($document->current_holder_id === auth()->id())
                                            @php
                                                $unreadTracking = $document->tracking()->where('to_user_id', auth()->id())->where('is_read', false)->exists();
                                            @endphp
                                            @if($unreadTracking)
                                                <span class="flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-orange-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    <!-- Title -->
                                    <div class="text-sm font-medium text-gray-900 dark:text-white break-words mb-1">{{ $document->title }}</div>
                                    
                                    <!-- Creator -->
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">by {{ $document->creator->full_name }}</div>
                                    
                                    <!-- Tags -->
                                    @if($document->tags && $document->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1 mb-2">
                                            @foreach($document->tags->take(3) as $tag)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" 
                                                      style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                            @if($document->tags->count() > 3)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">+{{ $document->tags->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Meta Info -->
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <!-- Type -->
                                        <span class="text-gray-600 dark:text-gray-400">{{ $document->documentType->name }}</span>
                                        <span class="text-gray-400 dark:text-gray-600">•</span>
                                        
                                        <!-- Status -->
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                                'routing' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'received' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                                                'in_review' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                                                'for_approval' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
                                                'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                                'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'returned' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                                'archived' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                                            ];
                                            $statusColor = $statusColors[$document->status] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full {{ $statusColor }} font-semibold">
                                            {{ \Illuminate\Support\Str::headline($document->status) }}
                                        </span>
                                        
                                        <!-- Urgency -->
                                        @php
                                            $urgencyColors = [
                                                'low' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                                                'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                                'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                            ];
                                            $urgencyColor = $urgencyColors[$document->urgency_level] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full {{ $urgencyColor }} font-semibold capitalize">
                                            {{ $document->urgency_level }}
                                        </span>
                                        @if($document->expiration_date)
                                            @php
                                                $isExpired = $document->is_expired;
                                                $daysUntilExpiration = !$isExpired ? \Carbon\Carbon::parse($document->expiration_date)->diffInDays(now(), false) : 0;
                                                $expirationColor = $isExpired 
                                                    ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' 
                                                    : ($daysUntilExpiration <= 7 
                                                        ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400' 
                                                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400');
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full {{ $expirationColor }} font-semibold text-xs">
                                                @if($isExpired)
                                                    ⚠️ Expired
                                                @else
                                                    📅 {{ \Carbon\Carbon::parse($document->expiration_date)->format('M d') }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Current Holder & Date -->
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>Holder: {{ $document->currentHolder ? $document->currentHolder->full_name : 'N/A' }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $document->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <a href="{{ route('documents.show', $document) }}" 
                                   class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                    <x-icon name="eye" type="solid" class="w-5 h-5" />
                                </a>
                                @can('update', $document)
                                    <button onclick="editDocument({{ $document->id }})" 
                                            class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                        <x-icon name="pencil" type="solid" class="w-5 h-5" />
                                    </button>
                                @endcan
                                @can('delete', $document)
                                    <button onclick="deleteDocument({{ $document->id }})" 
                                            class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                        <x-icon name="trash" type="solid" class="w-5 h-5" />
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <x-icon name="document-text" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                            <div class="text-sm text-gray-500 dark:text-gray-400">No documents found</div>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500 dark:focus:ring-orange-400">
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tracking #</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Document</th>
                                <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Urgency</th>
                                <th class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Holder</th>
                                <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($documents as $document)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 document-row">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" class="document-checkbox rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500 dark:focus:ring-orange-400" value="{{ $document->id }}">
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap relative">
                                        <div class="text-sm font-mono text-gray-900 dark:text-white">{{ $document->tracking_number }}</div>
                                        @if($document->current_holder_id === auth()->id())
                                            @php
                                                $unreadTracking = $document->tracking()->where('to_user_id', auth()->id())->where('is_read', false)->exists();
                                            @endphp
                                            @if($unreadTracking)
                                                <span class="absolute top-2 left-2 flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->title }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">by {{ $document->creator->full_name }}</div>
                                        @if($document->tags && $document->tags->count() > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach($document->tags->take(3) as $tag)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" 
                                                          style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                                @if($document->tags->count() > 3)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">+{{ $document->tags->count() - 3 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $document->documentType->name }}</div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                                'routing' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'received' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                                                'in_review' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                                                'for_approval' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
                                                'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                                'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'returned' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                                'archived' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                                            ];
                                            $statusColor = $statusColors[$document->status] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusColor }}">
                                            {{ \Illuminate\Support\Str::headline($document->status) }}
                                        </span>
                                    </td>
                                    <td class="hidden lg:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                                        @php
                                            $urgencyColors = [
                                                'low' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                                                'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                                'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                            ];
                                            $urgencyColor = $urgencyColors[$document->urgency_level] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $urgencyColor }} capitalize">
                                            {{ $document->urgency_level }}
                                        </span>
                                        @if($document->expiration_date)
                                            @php
                                                $isExpired = $document->is_expired;
                                                $daysUntilExpiration = !$isExpired ? \Carbon\Carbon::parse($document->expiration_date)->diffInDays(now(), false) : 0;
                                                $expirationColor = $isExpired 
                                                    ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' 
                                                    : ($daysUntilExpiration <= 7 
                                                        ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400' 
                                                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400');
                                            @endphp
                                            <span class="ml-1 px-2 py-0.5 text-xs font-semibold rounded-full {{ $expirationColor }}">
                                                @if($isExpired)
                                                    ⚠️
                                                @else
                                                    📅
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="hidden lg:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $document->currentHolder ? $document->currentHolder->full_name : 'N/A' }}</div>
                                    </td>
                                    <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $document->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('documents.show', $document) }}" 
                                               class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                                <x-icon name="eye" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                            </a>
                                            @can('update', $document)
                                                <button onclick="editDocument({{ $document->id }})" 
                                                        class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                                    <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                                </button>
                                            @endcan
                                            @can('delete', $document)
                                                <button onclick="deleteDocument({{ $document->id }})" 
                                                        class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                                    <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 sm:px-6 py-12 text-center">
                                        <x-icon name="document-text" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No documents found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($documents->hasPages())
                    <div class="px-4 sm:px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $documents->links('vendor.pagination.minimal') }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

<!-- Bulk Forward Modal -->
<div id="bulkForwardModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeBulkForwardModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
                <span id="bulk-forward-title">{{ ($pageTitle === 'Inbox') ? 'Forward' : 'Send' }} Selected Documents</span>
            </h3>
            <button type="button" onclick="closeBulkForwardModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="bulkForwardForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <span id="bulk-forward-action-text">{{ ($pageTitle === 'Inbox') ? 'Forwarding' : 'Sending' }}</span> <span id="bulk-forward-count" class="font-semibold text-gray-900 dark:text-white">0</span> document(s)
                </p>
            </div>

            @if(isset($users) && $users->count() > 0)
            <div x-data="{
                search: '',
                selectedUser: null,
                showDropdown: false,
                users: @js($users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'type' => ucfirst($user->usertype),
                        'department' => $user->department ? $user->department->name : null,
                        'display' => $user->full_name . ' - ' . ucfirst($user->usertype) . ($user->department ? ' (' . $user->department->name . ')' : '')
                    ];
                })->toArray()),
                get filteredUsers() {
                    if (!this.search) return this.users;
                    const query = this.search.toLowerCase();
                    return this.users.filter(user => 
                        user.name.toLowerCase().includes(query) ||
                        user.type.toLowerCase().includes(query) ||
                        (user.department && user.department.toLowerCase().includes(query))
                    );
                },
                selectUser(user) {
                    this.selectedUser = user;
                    this.search = user.display;
                    this.showDropdown = false;
                    document.getElementById('list_to_user_id').value = user.id;
                },
                clearSelection() {
                    this.selectedUser = null;
                    this.search = '';
                    this.showDropdown = false;
                    document.getElementById('list_to_user_id').value = '';
                }
            }" 
            @click.away="showDropdown = false"
            class="relative">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span id="bulk-forward-to-label">{{ ($pageTitle === 'Inbox') ? 'Forward To' : 'Send To' }}</span> <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text"
                        x-model="search"
                        @focus="showDropdown = true"
                        @click.stop="showDropdown = true"
                        @keydown.escape="showDropdown = false"
                        placeholder="Search by name, role, or department..."
                        class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    >
                    <div x-show="selectedUser" @click.stop="clearSelection()" class="absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </div>
                    <div x-show="!selectedUser" class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                        <x-icon name="magnifying-glass" class="w-5 h-5" />
                    </div>
                </div>
                <input type="hidden" name="to_user_id" id="list_to_user_id" :value="selectedUser ? selectedUser.id : ''" required>
                
                <!-- Dropdown Results -->
                <div x-show="showDropdown && filteredUsers.length > 0" 
                     @click.stop
                     x-transition
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <div @click.stop="selectUser(user)" 
                             class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm">
                            <div class="font-medium text-gray-900 dark:text-white" x-text="user.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="user.type + (user.department ? ' • ' + user.department : '')"></div>
                        </div>
                    </template>
                </div>
                <div x-show="showDropdown && search && filteredUsers.length === 0" 
                     @click.stop
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    No users found
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Forward Purpose
                </label>
                <select name="intent" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="route" selected>Route for processing</option>
                    <option value="approval">Request approval</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Select "Request approval" when sending documents to a dean, registrar, or administrator for sign-off.
                </p>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Instructions
                </label>
                <textarea name="instructions" rows="3" 
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                          placeholder="Enter forwarding instructions"></textarea>
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-sm text-gray-500 dark:text-gray-400">No users available to forward documents to.</p>
            </div>
            @endif

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 rounded-b-lg sm:rounded-b-xl">
                <button type="button" onclick="closeBulkForwardModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                @if(isset($users) && $users->count() > 0)
                <button type="submit" class="btn-primary text-sm">
                    <x-icon name="paper-airplane" class="w-4 h-4 mr-2" />
                    <span id="bulk-forward-submit-text">{{ ($pageTitle === 'Inbox') ? 'Forward Documents' : 'Send Documents' }}</span>
                </button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div id="bulk-actions-bar" class="hidden fixed bottom-4 sm:bottom-6 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 sm:px-6 py-3 sm:py-4 rounded-lg sm:rounded-xl shadow-xl flex flex-col sm:flex-row items-center gap-3 sm:gap-4 z-50 max-w-xl sm:max-w-none">
    <div class="flex items-center gap-4 w-full sm:w-auto">
        <span id="selected-count" class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">
            <span class="inline-flex items-center justify-center w-5 h-5 mr-2 text-xs font-bold text-white bg-orange-500 rounded-full" id="selected-badge">0</span>
            <span id="selected-text">selected</span>
        </span>
        <div class="hidden sm:block h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
    </div>
    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end sm:justify-start">
        @php
            $isCompletedPage = $pageTitle === 'Completed Documents';
            $isArchivePage = $pageTitle === 'Archive';
            $isDraftOrSent = isset($section) && in_array($section, ['draft', 'sent']);
        @endphp
        @if(!$isCompletedPage && !$isArchivePage)
        @php
            // Determine button text based on page
            $forwardButtonText = ($pageTitle === 'Inbox') ? 'Forward' : 'Send';
        @endphp
        <button onclick="bulkForward()" class="btn-primary inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium">
            <x-icon name="paper-airplane" class="w-4 h-4 mr-1.5" />
            <span id="bulk-forward-button-text">{{ $forwardButtonText }}</span>
        </button>
        @endif
        <button onclick="bulkDelete()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
            <x-icon name="trash" class="w-4 h-4 mr-1.5" />
            Delete
        </button>
        @if(auth()->user()->isAdmin() && ($isCompletedPage || !$isArchivePage))
        <button onclick="bulkArchive()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
            <x-icon name="archive-box" class="w-4 h-4 mr-1.5" />
            Archive
        </button>
        @endif
        @if(auth()->user()->isAdmin() && $isArchivePage)
        <button onclick="bulkUnarchive()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
            <x-icon name="arrow-uturn-left" class="w-4 h-4 mr-1.5" />
            Unarchive
        </button>
        @endif
        <button onclick="clearSelection()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs sm:text-sm font-medium transition-colors">
            <x-icon name="x-mark" class="w-4 h-4 mr-1.5" />
            Clear
        </button>
    </div>
</div>

<!-- Success Modal -->
<x-success-modal />

<!-- Delete Modal -->
<x-delete-modal />

<!-- File Preview Modal -->
<x-file-preview-modal :attachments="[]" />

<!-- Edit Document Modal -->
<div id="editDocumentModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeEditModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header (Fixed) -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit Document</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form (Scrollable Content) -->
        <form id="editDocumentForm" enctype="multipart/form-data" class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-6">
            @csrf
            <input type="hidden" id="edit_document_id" name="id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Document Type <span class="text-red-500">*</span>
                    </label>
                    <select id="edit_document_type_id" name="document_type_id" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        <option value="">Select Type</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit_title" name="title" required 
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                           placeholder="Enter document title">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Description
                    </label>
                    <textarea id="edit_description" name="description" rows="3" 
                              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                              placeholder="Enter document description"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Urgency Level <span class="text-red-500">*</span>
                    </label>
                    <select id="edit_urgency_level" name="urgency_level" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Expiration Date
                    </label>
                    <input type="date" id="edit_expiration_date" name="expiration_date" 
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                           min="{{ date('Y-m-d') }}">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Set when document expires</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit_auto_archive_on_expiration" name="auto_archive_on_expiration" value="1"
                               class="w-4 h-4 text-orange-500 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 dark:focus:ring-orange-600 focus:ring-2">
                        <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Auto-archive document when it expires</span>
                    </label>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Attach File
                    </label>
                    
                    <!-- Current File Display (matches show.blade.php attached files style) -->
                    <div id="edit_current_file" class="hidden mb-3">
                        <div class="file-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center gap-4">
                            <!-- File Icon/Thumbnail -->
                            <div id="edit_file_icon_container" class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                <!-- Image thumbnail (shown for images) -->
                                <img id="edit_current_file_thumbnail" src="" alt="File thumbnail" class="w-full h-full object-cover hidden">
                                <!-- File type badge (shown for non-images) -->
                                <div id="edit_file_type_badge" class="hidden w-full h-full flex items-center justify-center text-white font-semibold text-xs">
                                    <span id="edit_file_type_text"></span>
                                </div>
                            </div>
                            
                            <!-- File Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" id="edit_current_file_name"></p>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="text-xs text-gray-500 dark:text-gray-400" id="edit_current_file_size"></span>
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Uploaded
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex-shrink-0 flex items-center gap-2">
                                <button type="button" onclick="previewEditFile()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-blue-500 transition-colors" title="Preview">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button type="button" onclick="removeEditFile()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Remove">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <x-file-upload 
                        name="file" 
                        :multiple="false" 
                        :maxSize="10" 
                        acceptedTypes=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a new file to replace the current document file (optional)</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                        Tags
                    </label>
                    <div class="space-y-3">
                        <!-- Selected Tags Display -->
                        <div class="flex flex-wrap gap-2 p-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900/30 min-h-[60px] transition-colors hover:border-orange-400 dark:hover:border-orange-500" id="edit_selected_tags_container">
                            <p class="text-xs text-gray-400 dark:text-gray-500 w-full flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                No tags selected. Select tags below to organize this document.
                            </p>
                        </div>
                        
                        <!-- Available Tags -->
                        <div>
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Available Tags (Click to select)</p>
                            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50" id="edit_tags_container">
                                <!-- Tags will be populated dynamically -->
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-start">
                        <svg class="w-3.5 h-3.5 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Select multiple tags to help organize and categorize this document for easier searching</span>
                    </p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Remarks
                    </label>
                    <textarea id="edit_remarks" name="remarks" rows="2" 
                              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                              placeholder="Additional remarks"></textarea>
                </div>
            </div>
        </form>

        <!-- Footer (Fixed) -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-b-lg sm:rounded-b-xl">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" form="editDocumentForm" class="btn-primary text-sm">
                    Update Document
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Document Modal -->
<div id="createDocumentModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeCreateModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header (Fixed) -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Create New Document</h3>
            <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form (Scrollable Content) -->
        <form id="createDocumentForm" enctype="multipart/form-data" class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Document Type <span class="text-red-500">*</span>
                    </label>
                    <select name="document_type_id" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        <option value="">Select Type</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required 
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                           placeholder="Enter document title">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Description
                    </label>
                    <textarea name="description" rows="3" 
                              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                              placeholder="Enter document description"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Urgency Level <span class="text-red-500">*</span>
                    </label>
                    <select name="urgency_level" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        <option value="normal" selected>Normal</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Expiration Date
                    </label>
                    <input type="date" name="expiration_date" 
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                           min="{{ date('Y-m-d') }}">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Set when document expires</p>
                </div>
                
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_archive_on_expiration" value="1" class="w-4 h-4 text-orange-600 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2">
                        <span class="ml-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300">Auto-archive when expired</span>
                    </label>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Attach Files <span class="text-red-500">*</span>
                    </label>
                    <x-file-upload 
                        name="files[]" 
                        :multiple="true" 
                        :maxSize="20" 
                        acceptedTypes=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                        Tags
                    </label>
                    <div class="space-y-3">
                        <!-- Custom Tag Input -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Add Custom Tag</label>
                            <input type="text" 
                                   id="create_custom_tag_input"
                                   placeholder="Type a tag name and press Enter"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                   onkeydown="if(event.key === 'Enter') { event.preventDefault(); addCreateCustomTag(); }">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Press Enter to add a custom tag (max 50 characters)</p>
                        </div>
                        
                        <!-- Selected Tags Display -->
                        <div class="flex flex-wrap gap-2 p-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900/30 min-h-[60px] transition-colors hover:border-orange-400 dark:hover:border-orange-500" id="create_selected_tags_container">
                            <p class="text-xs text-gray-400 dark:text-gray-500 w-full flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                No tags selected. Select tags below or type your own above.
                            </p>
                        </div>
                        
                        <!-- Available Tags -->
                        <div>
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Available Tags (Click to select)</p>
                            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                @if(isset($tags) && $tags->count() > 0)
                                    @foreach($tags as $tag)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="create-tag-checkbox hidden" onchange="updateCreateSelectedTags()">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 transition-all duration-200 border border-transparent hover:border-orange-400 dark:hover:border-orange-500 peer-checked:bg-orange-100 peer-checked:text-orange-800 dark:peer-checked:bg-orange-900/30 dark:peer-checked:text-orange-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                @else
                                    <p class="w-full text-xs text-gray-500 dark:text-gray-400 text-center py-4">No tags available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-start">
                        <svg class="w-3.5 h-3.5 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Select multiple tags to help organize and categorize this document for easier searching</span>
                    </p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Remarks
                    </label>
                    <textarea name="remarks" rows="2" 
                              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                              placeholder="Additional remarks"></textarea>
                </div>
            </div>
        </form>

        <!-- Footer (Fixed) -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-b-lg sm:rounded-b-xl">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeCreateModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" form="createDocumentForm" class="btn-primary text-sm">
                    Create Document
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Bulk Actions
let selectedDocuments = [];

// Helper function to get only visible checkboxes
function getVisibleCheckboxes() {
    const allCheckboxes = document.querySelectorAll('.document-checkbox');
    return Array.from(allCheckboxes).filter(checkbox => {
        // Check if checkbox is actually visible (not hidden by CSS)
        const rect = checkbox.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && checkbox.offsetParent !== null;
    });
}

// Select all checkbox
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = getVisibleCheckboxes();
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // Individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('document-checkbox')) {
            updateBulkActions();
        }
    });
});

function updateBulkActions() {
    const checkboxes = getVisibleCheckboxes().filter(cb => cb.checked);
    selectedDocuments = checkboxes.map(cb => cb.value);
    
    const bulkBar = document.getElementById('bulk-actions-bar');
    const badge = document.getElementById('selected-badge');
    const text = document.getElementById('selected-text');
    const selectAll = document.getElementById('select-all');
    
    if (selectedDocuments.length > 0) {
        if (bulkBar) bulkBar.classList.remove('hidden');
        if (badge) badge.textContent = selectedDocuments.length;
        if (text) text.textContent = 'selected';
        
        // Update select all checkbox state
        if (selectAll) {
            const allVisible = getVisibleCheckboxes();
            selectAll.checked = allVisible.length === checkboxes.length;
            selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allVisible.length;
        }
    } else {
        if (bulkBar) bulkBar.classList.add('hidden');
        if (badge) badge.textContent = '0';
        if (text) text.textContent = 'selected';
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }
}

function clearSelection() {
    getVisibleCheckboxes().forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    updateBulkActions();
}

function openBulkForwardModal() {
    if (selectedDocuments.length === 0) return;
    
    const modal = document.getElementById('bulkForwardModal');
    const countSpan = document.getElementById('bulk-forward-count');
    
    if (modal) {
        if (countSpan) countSpan.textContent = selectedDocuments.length;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeBulkForwardModal() {
    const modal = document.getElementById('bulkForwardModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const form = document.getElementById('bulkForwardForm');
        if (form) form.reset();
    }
}

function bulkForward() {
    if (selectedDocuments.length === 0) return;
    openBulkForwardModal();
}

function bulkDelete() {
    if (selectedDocuments.length === 0) return;
    
    showDeleteModal(
        'Delete Documents',
        `Are you sure you want to delete ${selectedDocuments.length} selected document(s)? This action cannot be undone.`,
        () => {
            executeBulkDelete();
        }
    );
}

function executeBulkDelete() {
    let deleted = 0;
    let failed = 0;
    const total = selectedDocuments.length;
    
    selectedDocuments.forEach(docId => {
        fetch(`/documents/${docId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                deleted++;
            } else {
                failed++;
                response.json().then(json => console.error('Error deleting document:', json)).catch(() => {});
            }
            
            if (deleted + failed === total) {
                if (failed === 0) {
                    showSuccessModal('Success', `Successfully deleted ${deleted} document(s)!`);
                    window.refreshDocumentsTable();
                } else {
                    showSuccessModal('Partial Success', `Deleted ${deleted} document(s). ${failed} document(s) failed.`);
                    window.refreshDocumentsTable();
                }
            }
        }).catch(error => {
            console.error('Error deleting document:', error);
            failed++;
            if (deleted + failed === total) {
                showSuccessModal('Partial Success', `Deleted ${deleted} document(s). ${failed} document(s) failed.`);
                window.refreshDocumentsTable();
            }
        });
    });
}

function bulkArchive() {
    if (selectedDocuments.length === 0) return;
    
    showDeleteModal(
        'Archive Documents',
        `Are you sure you want to archive ${selectedDocuments.length} selected document(s)?`,
        () => {
            executeBulkArchive();
        }
    );
}

function executeBulkArchive() {
    let archived = 0;
    let failed = 0;
    const total = selectedDocuments.length;
    
    selectedDocuments.forEach(docId => {
        fetch(`/documents/${docId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(response => response.json().then(json => {
            if (response.ok && json.success) {
                archived++;
            } else {
                failed++;
                console.error('Error archiving document:', json.message || json.error);
            }
            
            if (archived + failed === total) {
                if (failed === 0) {
                    showSuccessModal('Success', `Successfully archived ${archived} document(s)!`);
                    window.refreshDocumentsTable();
                } else {
                    showSuccessModal('Partial Success', `Archived ${archived} document(s). ${failed} document(s) failed. Only completed or approved documents can be archived.`);
                    window.refreshDocumentsTable();
                }
            }
        }).catch(error => {
            console.error('Error archiving document:', error);
            failed++;
            if (archived + failed === total) {
                showSuccessModal('Partial Success', `Archived ${archived} document(s). ${failed} document(s) failed.`);
                window.refreshDocumentsTable();
            }
        }));
    });
}

function bulkUnarchive() {
    if (selectedDocuments.length === 0) return;
    
    showDeleteModal(
        'Unarchive Documents',
        `Are you sure you want to unarchive ${selectedDocuments.length} selected document(s)?`,
        () => {
            executeBulkUnarchive();
        }
    );
}

function executeBulkUnarchive() {
    let unarchived = 0;
    let failed = 0;
    const total = selectedDocuments.length;
    
    selectedDocuments.forEach(docId => {
        fetch(`/documents/${docId}/unarchive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(response => response.json().then(json => {
            if (response.ok && json.success) {
                unarchived++;
            } else {
                failed++;
                console.error('Error unarchiving document:', json.message || json.error);
            }
            
            if (unarchived + failed === total) {
                if (failed === 0) {
                    showSuccessModal('Success', `Successfully unarchived ${unarchived} document(s)!`);
                    window.refreshDocumentsTable();
                } else {
                    showSuccessModal('Partial Success', `Unarchived ${unarchived} document(s). ${failed} document(s) failed.`);
                    window.refreshDocumentsTable();
                }
            }
        }).catch(error => {
            console.error('Error unarchiving document:', error);
            failed++;
            if (unarchived + failed === total) {
                showSuccessModal('Partial Success', `Unarchived ${unarchived} document(s). ${failed} document(s) failed.`);
                window.refreshDocumentsTable();
            }
        }));
    });
}

// Handle bulk forward form submission
document.addEventListener('DOMContentLoaded', function() {
    const bulkForwardForm = document.getElementById('bulkForwardForm');
    if (bulkForwardForm) {
        bulkForwardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (selectedDocuments.length === 0) {
                closeBulkForwardModal();
                return;
            }
            
            const formData = new FormData(this);
            const data = {
                document_ids: selectedDocuments,
                to_user_id: formData.get('to_user_id'),
                intent: formData.get('intent') || 'route',
                instructions: formData.get('instructions') || '',
            };
            
            let forwarded = 0;
            let failed = 0;
            const total = selectedDocuments.length;
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                const pageTitle = '{{ $pageTitle }}';
                const isInbox = pageTitle === 'Inbox';
                submitBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' + (isInbox ? 'Forwarding...' : 'Sending...');
            }
            
            selectedDocuments.forEach(docId => {
                fetch(`/documents/${docId}/forward`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        to_user_id: data.to_user_id,
                        intent: data.intent,
                        instructions: data.instructions,
                    }),
                }).then(response => {
                    if (response.ok) {
                        forwarded++;
                    } else {
                        failed++;
                        response.json().then(json => console.error('Error forwarding document:', json)).catch(() => {});
                    }
                    
                    if (forwarded + failed === total) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                        
                        closeBulkForwardModal();
                        clearSelection();
                        
                        if (failed === 0) {
                            showSuccessModal(`Successfully forwarded ${forwarded} document(s)!`);
                            window.refreshDocumentsTable();
                        } else {
                            alert(`Forwarded ${forwarded} document(s). ${failed} document(s) failed.`);
                            window.refreshDocumentsTable();
                        }
                    }
                }).catch(error => {
                    console.error('Error forwarding document:', error);
                    failed++;
                    if (forwarded + failed === total) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                        closeBulkForwardModal();
                        clearSelection();
                        alert(`Forwarded ${forwarded} document(s). ${failed} document(s) failed.`);
                        window.refreshDocumentsTable();
                    }
                });
            });
        });
    }
});

// Auto submit form with debounce
let searchTimeout;
function autoSubmitForm() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filter-form').submit();
    }, 500);
}

// Modal Functions
function openCreateModal() {
    document.getElementById('createDocumentModal').classList.remove('hidden');
    document.getElementById('createDocumentModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createDocumentModal').classList.add('hidden');
    document.getElementById('createDocumentModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('createDocumentForm').reset();
    
    // Clear custom tags
    document.querySelectorAll('.create-custom-tag-input').forEach(input => input.remove());
    document.getElementById('create_custom_tag_input').value = '';
    
    // Clear selected tags display
    updateCreateSelectedTags();
    
    // Clear file upload state
    const fileUploadContainer = document.querySelector('#createDocumentModal .file-upload-container');
    if (fileUploadContainer) {
        const fileList = fileUploadContainer.querySelector('.file-list');
        if (fileList) {
            fileList.innerHTML = '';
        }
        // Clear file upload state for this modal
        if (window.fileUploadState) {
            const fileItems = fileList.querySelectorAll('.file-item');
            fileItems.forEach(item => {
                const fileId = item.dataset.fileId;
                window.fileUploadState.delete(fileId);
            });
        }
    }
}

// Add custom tag for create modal
function addCreateCustomTag() {
    const input = document.getElementById('create_custom_tag_input');
    const tagName = input.value.trim();
    
    if (!tagName) return;
    
    if (tagName.length > 50) {
        alert('Tag name must be 50 characters or less');
        return;
    }
    
    // Check if tag already exists in checkboxes
    const existingCheckboxes = Array.from(document.querySelectorAll('#createDocumentModal .create-tag-checkbox'));
    const existingTag = existingCheckboxes.find(cb => 
        cb.nextElementSibling.textContent.trim().toLowerCase() === tagName.toLowerCase()
    );
    
    if (existingTag) {
        // Tag exists, just check it
        existingTag.checked = true;
        input.value = '';
        updateCreateSelectedTags();
        return;
    }
    
    // Create a new hidden input for the custom tag
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'custom_tags[]';
    hiddenInput.value = tagName;
    hiddenInput.className = 'create-custom-tag-input';
    document.getElementById('createDocumentForm').appendChild(hiddenInput);
    
    // Add to selected tags display
    const container = document.getElementById('create_selected_tags_container');
    if (container.querySelector('p')) {
        container.innerHTML = '';
    }
    
    const tagBadge = document.createElement('span');
    tagBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white shadow-sm hover:shadow-md transition-all cursor-pointer hover:bg-red-600 dark:hover:bg-red-700';
    tagBadge.title = 'Click to remove';
    tagBadge.onclick = function() {
        hiddenInput.remove();
        tagBadge.remove();
        if (document.getElementById('create_selected_tags_container').children.length === 0) {
            updateCreateSelectedTags();
        }
    };
    tagBadge.innerHTML = `
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
        </svg>
        ${tagName}
        <svg class="w-3.5 h-3.5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
    `;
    container.appendChild(tagBadge);
    
    input.value = '';
}

// Update selected tags display for create modal
function updateCreateSelectedTags() {
    const container = document.getElementById('create_selected_tags_container');
    if (!container) return;
    
    const checkboxes = document.querySelectorAll('#createDocumentModal .create-tag-checkbox:checked');
    const customTagInputs = document.querySelectorAll('.create-custom-tag-input');
    
    // Update visual state of available tag buttons
    document.querySelectorAll('#createDocumentModal .create-tag-checkbox').forEach(checkbox => {
        const span = checkbox.nextElementSibling;
        if (checkbox.checked) {
            span.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:hover:bg-gray-600', 'dark:text-gray-300');
            span.classList.add('bg-orange-100', 'text-orange-800', 'dark:bg-orange-900/30', 'dark:text-orange-400', 'border-orange-400', 'dark:border-orange-500');
            // Change icon to checkmark
            span.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        } else {
            span.classList.remove('bg-orange-100', 'text-orange-800', 'dark:bg-orange-900/30', 'dark:text-orange-400', 'border-orange-400', 'dark:border-orange-500');
            span.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:hover:bg-gray-600', 'dark:text-gray-300');
            // Change icon back to plus
            span.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>';
        }
    });
    
    if (checkboxes.length === 0 && customTagInputs.length === 0) {
        container.innerHTML = `
            <p class="text-xs text-gray-400 dark:text-gray-500 w-full flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                No tags selected. Select tags below or type your own above.
            </p>`;
        return;
    }
    
    container.innerHTML = '';
    
    // Add custom tags first
    customTagInputs.forEach(hiddenInput => {
        const tagName = hiddenInput.value;
        const tagBadge = document.createElement('span');
        tagBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white shadow-sm hover:shadow-md transition-all cursor-pointer hover:bg-red-600 dark:hover:bg-red-700';
        tagBadge.title = 'Click to remove';
        tagBadge.onclick = function() {
            hiddenInput.remove();
            updateCreateSelectedTags();
        };
        tagBadge.innerHTML = `
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
            </svg>
            ${tagName}
            <svg class="w-3.5 h-3.5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        `;
        container.appendChild(tagBadge);
    });
    
    // Add checked tags
    checkboxes.forEach(checkbox => {
        const tagName = checkbox.nextElementSibling.textContent.trim();
        const tagId = checkbox.value;
        
        const tagBadge = document.createElement('span');
        tagBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white shadow-sm hover:shadow-md transition-all cursor-pointer hover:bg-red-600 dark:hover:bg-red-700';
        tagBadge.title = 'Click to remove';
        tagBadge.onclick = function() {
            document.querySelector('input.create-tag-checkbox[value="' + tagId + '"]').checked = false;
            updateCreateSelectedTags();
        };
        tagBadge.innerHTML = `
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
            </svg>
            ${tagName}
            <svg class="w-3.5 h-3.5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        `;
        container.appendChild(tagBadge);
    });
}

// Handle create document form submission
document.getElementById('createDocumentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get files from file upload component
    const fileUploadContainer = this.querySelector('.file-upload-container');
    if (!fileUploadContainer) {
        alert('File upload component not found.');
        return;
    }
    
    const fileList = fileUploadContainer.querySelector('.file-list');
    const fileItems = fileList.querySelectorAll('.file-item');
    
    // Check if at least one file is uploaded
    let hasCompletedFile = false;
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            hasCompletedFile = true;
        }
    });
    
    if (!hasCompletedFile) {
        alert('Please upload at least one file.');
        return;
    }
    
    const formData = new FormData(this);
    
    // Add files to FormData
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            formData.append('files[]', fileData.file);
        }
    });
    
    const submitButton = document.querySelector('button[form="createDocumentForm"]');
    const originalText = submitButton ? submitButton.textContent : 'Create Document';
    
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Creating...';
    }
    
    try {
        const response = await fetch('{{ route('documents.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            alert('An error occurred. Please check the console for details.');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
            return;
        }
        
        const data = await response.json();
        
        if (!response.ok) {
            // Handle validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                alert('Validation errors:\n' + errorMessages);
            } else {
                alert(data.message || 'Error creating document. Please try again.');
            }
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
            return;
        }
        
        if (data.success) {
            closeCreateModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Document created successfully!');
            }
            refreshDocumentsTable();
        } else {
            alert(data.message || 'Error creating document. Please try again.');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.\n' + error.message);
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }
});

// Edit Document
async function editDocument(id) {
    try {
        const response = await fetch(`/documents/${id}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.document) {
            openEditModal(data);
        } else {
            alert(data.message || 'An error occurred while fetching document details.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while fetching document details. Please check the console for details.');
    }
}

function openEditModal(data) {
    // Populate form fields
    const doc = data.document;
    document.getElementById('edit_document_id').value = doc.id;
    document.getElementById('edit_document_type_id').value = doc.document_type_id;
    document.getElementById('edit_title').value = doc.title;
    document.getElementById('edit_description').value = doc.description || '';
    document.getElementById('edit_urgency_level').value = doc.urgency_level;
    document.getElementById('edit_remarks').value = doc.remarks || '';
    document.getElementById('edit_expiration_date').value = doc.expiration_date || '';
    document.getElementById('edit_auto_archive_on_expiration').checked = doc.auto_archive_on_expiration == 1;
    
    // Show current file if exists
    const currentFileDiv = document.getElementById('edit_current_file');
    const currentFileName = document.getElementById('edit_current_file_name');
    const currentFileSize = document.getElementById('edit_current_file_size');
    const currentFileThumbnail = document.getElementById('edit_current_file_thumbnail');
    const fileTypeBadge = document.getElementById('edit_file_type_badge');
    const fileTypeText = document.getElementById('edit_file_type_text');
    const iconContainer = document.getElementById('edit_file_icon_container');
    
    if (doc.file_name && doc.file_path) {
        currentFileName.textContent = doc.file_name;
        currentFileSize.textContent = doc.file_size || '';
        
        // Set thumbnail based on file type
        const fileUrl = `/storage/${doc.file_path}`;
        const extension = doc.file_name.split('.').pop().toLowerCase();
        
        // Check if it's an image
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(extension)) {
            // Show actual image thumbnail
            currentFileThumbnail.src = fileUrl;
            currentFileThumbnail.classList.remove('hidden');
            fileTypeBadge.classList.add('hidden');
            iconContainer.classList.remove('bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-orange-500');
            iconContainer.classList.add('bg-gray-50', 'dark:bg-gray-700');
        } else {
            // Show file type badge
            currentFileThumbnail.classList.add('hidden');
            fileTypeBadge.classList.remove('hidden');
            fileTypeText.textContent = extension.toUpperCase();
            
            // Set badge color based on file type
            iconContainer.classList.remove('bg-gray-50', 'dark:bg-gray-700');
            if (extension === 'pdf') {
                iconContainer.classList.add('bg-red-500');
            } else if (['doc', 'docx'].includes(extension)) {
                iconContainer.classList.add('bg-blue-500');
            } else if (['xls', 'xlsx'].includes(extension)) {
                iconContainer.classList.add('bg-green-500');
            } else {
                iconContainer.classList.add('bg-orange-500');
            }
        }
        
        // Store file info globally for preview/remove functions
        window.editCurrentFile = {
            name: doc.file_name,
            path: doc.file_path,
            url: fileUrl,
            extension: extension
        };
        currentFileDiv.classList.remove('hidden');
    } else {
        currentFileDiv.classList.add('hidden');
        window.editCurrentFile = null;
    }
    
    // Store tags data globally for the update function
    window.editTagsData = data.tags || [];
    
    // Populate tags
    if (data.tags) {
        const tagContainer = document.getElementById('edit_tags_container');
        if (tagContainer) {
            tagContainer.innerHTML = '';
            const selectedTagIds = doc.tags ? doc.tags.map(t => t.id) : [];
            
            data.tags.forEach(tag => {
                const label = document.createElement('label');
                label.className = 'inline-flex items-center cursor-pointer';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'tags[]';
                checkbox.value = tag.id;
                checkbox.className = 'edit-tag-checkbox hidden';
                checkbox.checked = selectedTagIds.includes(tag.id);
                checkbox.onchange = updateEditSelectedTags;
                
                const span = document.createElement('span');
                const isChecked = selectedTagIds.includes(tag.id);
                span.className = `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-all duration-200 border ${
                    isChecked 
                    ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border-orange-400 dark:border-orange-500' 
                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 border-transparent hover:border-orange-400 dark:hover:border-orange-500'
                }`;
                span.innerHTML = `
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isChecked ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4'}"></path>
                    </svg>
                    ${tag.name}
                `;
                
                label.appendChild(checkbox);
                label.appendChild(span);
                tagContainer.appendChild(label);
            });
            
            updateEditSelectedTags();
        }
    }
    
    // Show modal
    document.getElementById('editDocumentModal').classList.remove('hidden');
    document.getElementById('editDocumentModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function updateEditSelectedTags() {
    const container = document.getElementById('edit_selected_tags_container');
    if (!container) return;
    
    const checkboxes = document.querySelectorAll('.edit-tag-checkbox:checked');
    const tagsData = window.editTagsData || [];
    
    // Update visual state of available tag buttons
    document.querySelectorAll('.edit-tag-checkbox').forEach(checkbox => {
        const span = checkbox.nextElementSibling;
        if (checkbox.checked) {
            span.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:hover:bg-gray-600', 'dark:text-gray-300');
            span.classList.add('bg-orange-100', 'text-orange-800', 'dark:bg-orange-900/30', 'dark:text-orange-400', 'border-orange-400', 'dark:border-orange-500');
            // Change icon to checkmark
            span.querySelector('svg path').setAttribute('d', 'M5 13l4 4L19 7');
        } else {
            span.classList.remove('bg-orange-100', 'text-orange-800', 'dark:bg-orange-900/30', 'dark:text-orange-400', 'border-orange-400', 'dark:border-orange-500');
            span.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:hover:bg-gray-600', 'dark:text-gray-300');
            // Change icon back to plus
            span.querySelector('svg path').setAttribute('d', 'M12 4v16m8-8H4');
        }
    });
    
    if (checkboxes.length === 0) {
        container.innerHTML = `
            <p class="text-xs text-gray-400 dark:text-gray-500 w-full flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                No tags selected. Select tags below to organize this document.
            </p>`;
        return;
    }
    
    container.innerHTML = '';
    checkboxes.forEach(checkbox => {
        const tagId = parseInt(checkbox.value);
        const tag = tagsData.find(t => t.id === tagId);
        if (tag) {
            const tagElement = document.createElement('span');
            tagElement.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white shadow-sm hover:shadow-md transition-all cursor-pointer hover:bg-red-600 dark:hover:bg-red-700';
            tagElement.title = 'Click to remove';
            tagElement.onclick = function() {
                document.querySelector('.edit-tag-checkbox[value="' + tagId + '"]').checked = false;
                updateEditSelectedTags();
            };
            tagElement.innerHTML = `
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
                ${tag.name}
                <svg class="w-3.5 h-3.5 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            `;
            container.appendChild(tagElement);
        }
    });
}

function closeEditModal() {
    document.getElementById('editDocumentModal').classList.add('hidden');
    document.getElementById('editDocumentModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('editDocumentForm').reset();
    window.editCurrentFile = null;
}

// Preview current file in edit modal
function previewEditFile() {
    if (window.editCurrentFile && window.editCurrentFile.url) {
        // Use the file preview modal component
        if (typeof window.previewFile === 'function') {
            // Extract file extension to determine type
            const fileName = window.editCurrentFile.name;
            const extension = fileName.split('.').pop().toLowerCase();
            let fileType = 'document';
            
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(extension)) {
                fileType = 'image';
            } else if (extension === 'pdf') {
                fileType = 'pdf';
            }
            
            // Call previewFile with correct parameters: (id, fileName, fileType, fileUrl, fileSize)
            window.previewFile(
                window.editCurrentFile.path,
                fileName,
                fileType,
                window.editCurrentFile.url,
                '' // fileSize is optional
            );
        } else {
            // Fallback: open in new tab
            window.open(window.editCurrentFile.url, '_blank');
        }
    }
}

// Remove current file from edit modal
function removeEditFile() {
    showDeleteModal(
        'Remove File',
        'Are you sure you want to remove this file? You will need to upload a new file to replace it.',
        () => {
            const currentFileDiv = document.getElementById('edit_current_file');
            if (currentFileDiv) {
                currentFileDiv.classList.add('hidden');
            }
            window.editCurrentFile = null;
            // Add a hidden input to signal file removal on form submit
            const form = document.getElementById('editDocumentForm');
            let removeInput = form.querySelector('input[name="remove_file"]');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_file';
                removeInput.value = '1';
                form.appendChild(removeInput);
            }
        }
    );
}

// Delete Document
async function deleteDocument(id) {
    window.showDeleteModal(
        'Delete Document',
        'Are you sure you want to delete this document? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`/documents/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', data.message || 'Document deleted successfully!');
                    }
                    window.refreshDocumentsTable();
                } else {
                    alert(data.message || 'Error deleting document. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    );
}

// Handle edit document form submission
document.getElementById('editDocumentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const documentId = document.getElementById('edit_document_id').value;
    const submitButton = document.querySelector('button[form="editDocumentForm"]');
    const originalText = submitButton ? submitButton.textContent : 'Update Document';
    
    // Add _method field for Laravel to recognize this as a PUT request
    formData.append('_method', 'PUT');
    
    // Ensure tags are included (even if empty array)
    const tagCheckboxes = document.querySelectorAll('.edit-tag-checkbox:checked');
    formData.delete('tags[]'); // Remove existing tags
    tagCheckboxes.forEach(checkbox => {
        formData.append('tags[]', checkbox.value);
    });
    
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Updating...';
    }
    
    try {
        const response = await fetch(`/documents/${documentId}`, {
            method: 'POST', // Use POST with _method spoofing for file uploads
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            alert('An error occurred. The server returned an unexpected response. Please check the console for details.');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
            return;
        }
        
        const data = await response.json();
        
        if (!response.ok) {
            // Handle validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                alert('Validation errors:\n' + errorMessages);
            } else {
                alert(data.message || 'Error updating document. Please try again.');
            }
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
            return;
        }
        
        if (data.success) {
            closeEditModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Document updated successfully!');
            }
            refreshDocumentsTable();
        } else {
            alert(data.message || 'Error updating document. Please try again.');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.\n' + error.message);
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});

// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
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

    // Refresh documents table without reload (MUST be global)
    // Single-flight/throttled refresh flags
    window.__docRefreshBusy = window.__docRefreshBusy || false;
    window.__docRefreshPending = window.__docRefreshPending || false;
    window.__docLastRefreshAt = window.__docLastRefreshAt || 0;
    window.refreshDocumentsTable = async function() {
        const now = Date.now();
        if (now - (window.__docLastRefreshAt || 0) < 250) return Promise.resolve();
        if (window.__docRefreshBusy) { window.__docRefreshPending = true; return Promise.resolve(); }
        window.__docRefreshBusy = true;
        window.__docLastRefreshAt = now;
        
        return new Promise(async (resolve) => {
            try {
                const params = new URLSearchParams(window.location.search);
                const response = await fetch(window.location.pathname + '?' + params.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    resolve();
                    return;
                }
                
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const newTableContainer = doc.querySelector('#documents-table');
                
                if (newTableContainer) {
                    const currentTableContainer = document.querySelector('#documents-table');
                    
                    if (currentTableContainer) {
                        currentTableContainer.style.transition = 'opacity 0.2s';
                        currentTableContainer.style.opacity = '0.5';
                        
                        setTimeout(() => {
                            const clone = document.importNode(newTableContainer, true);
                            currentTableContainer.replaceWith(clone);
                            
                            // Execute any inline scripts within the new content
                            try {
                                clone.querySelectorAll('script').forEach(oldScript => {
                                    const newScript = document.createElement('script');
                                    for (const attr of oldScript.attributes) newScript.setAttribute(attr.name, attr.value);
                                    if (oldScript.textContent) newScript.textContent = oldScript.textContent;
                                    oldScript.parentNode.replaceChild(newScript, oldScript);
                                });
                            } catch (e) {}
                            
                            setTimeout(() => {
                                const restoredContent = document.querySelector('#documents-table');
                                if (restoredContent) {
                                    restoredContent.style.opacity = '1';
                                }
                                resolve();
                            }, 30);
                        }, 200);
                    } else {
                        resolve();
                    }
                } else {
                    resolve();
                }
            } catch (error) {
                resolve();
            } finally {
                window.__docRefreshBusy = false;
                if (window.__docRefreshPending) {
                    window.__docRefreshPending = false;
                    setTimeout(window.refreshDocumentsTable, 50);
                }
            }
        });
    }

    // Update inbox badge count in sidebar
    async function updateInboxBadge() {
        try {
            const response = await fetch('/inbox?count_only=1');
            const data = await response.json();
            const inboxLink = document.querySelector('a[href*="documents.inbox"]');
            if (inboxLink) {
                let badge = inboxLink.querySelector('span.ml-auto');
                if (data.count > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400';
                        inboxLink.appendChild(badge);
                    }
                    badge.textContent = data.count;
                    badge.style.display = '';
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error updating inbox badge:', error);
        }
    }

    // Real-time broadcasting listeners for documents (following comments pattern)
    @if(config('broadcasting.default') !== 'null')
    function initializeDocumentsBroadcasting() {
        if (typeof window.Echo !== 'undefined' && !window.Echo._isDummy) {
            try {
                // Listen to private documents channel
                const documentsChannel = window.Echo.private('documents');
                
                documentsChannel
                    .listen('.document.created', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.updated', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.deleted', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.forwarded', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.received', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.completed', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.approved', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.rejected', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.returned', (e) => {
                        window.refreshDocumentsTable();
                    });
                
                // Listen to user's private channel
                const userChannel = window.Echo.private('App.Models.User.{{ auth()->id() }}');
                
                userChannel
                    .listen('.document.forwarded', (e) => {
                        window.refreshDocumentsTable();
                        refreshSidebarBadges();
                    })
                    .listen('.document.received', (e) => {
                        window.refreshDocumentsTable();
                        refreshSidebarBadges();
                    })
                    .listen('.document.created', (e) => {
                        window.refreshDocumentsTable();
                    })
                    .listen('.document.updated', (e) => {
                        window.refreshDocumentsTable();
                    });
                
            } catch (e) {
                // Silent fail
            }
        } else {
            setTimeout(initializeDocumentsBroadcasting, 100);
        }
    }
    
    // Refresh sidebar badges
    function refreshSidebarBadges() {
        try {
            // Trigger Alpine.js counter refreshes
            const inboxCounter = document.querySelector('[x-data*="inboxCounter"]');
            if (inboxCounter && inboxCounter.__x) {
                inboxCounter.__x.$data.refreshCount();
            }
            
            const documentsCounter = document.querySelector('[x-data*="documentsCounter"]');
            if (documentsCounter && documentsCounter.__x) {
                documentsCounter.__x.$data.refreshCount();
            }
            
            const sentCounter = document.querySelector('[x-data*="sentCounter"]');
            if (sentCounter && sentCounter.__x) {
                sentCounter.__x.$data.refreshCount();
            }
        } catch (error) {
            // Silent fail
        }
    }
    
    // Initialize on page load
    initializeDocumentsBroadcasting();
    @endif
});
</script>
@endsection
