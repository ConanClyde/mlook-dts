@extends('layouts.app')

@section('title', 'Documents - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Documents</h1>
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
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Document Management</h1>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Track and manage all your documents</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <!-- Broadcast Status -->
                    <div id="broadcast-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 w-full sm:w-auto justify-center sm:justify-start">
                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" id="broadcast-indicator"></span>
                        <span id="broadcast-text">Connecting...</span>
                    </div>
                    
                    @if(auth()->user()->canCreateDocuments())
                    <button type="button" onclick="openCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        <span class="hidden sm:inline">Create Document</span>
                        <span class="sm:hidden">Create</span>
                    </button>
                    @endif
                </div>
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

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
                <nav class="flex space-x-4 overflow-x-auto -mb-px">
                    <a href="{{ route('documents.index', ['tab' => 'all']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'all' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        All Documents
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $counts['all'] }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'incoming']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'incoming' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Incoming
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">{{ $counts['incoming'] }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'outgoing']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'outgoing' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Outgoing
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">{{ $counts['outgoing'] }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'pending']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'pending' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Pending Actions
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">{{ $counts['pending'] }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'completed']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'completed' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Completed
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">{{ $counts['completed'] }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'received']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'received' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Received
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">{{ $counts['received'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'returned']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'returned' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Returned
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">{{ $counts['returned'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('documents.index', ['tab' => 'archived']) }}" 
                       class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $tab === 'archived' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        Archived
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300">{{ $counts['archived'] ?? 0 }}</span>
                    </a>
                </nav>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
                <form method="GET" action="{{ route('documents.index') }}" id="filter-form" class="w-full">
                    <input type="hidden" name="tab" value="{{ $tab }}">
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

                        <!-- Status Filter -->
                        <div class="lg:flex-1 lg:min-w-0">
                            <label for="status_filter" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Status
                            </label>
                            <select 
                                id="status_filter" 
                                name="status_filter" 
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                                onchange="autoSubmitForm()"
                            >
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status_filter') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="returned" {{ request('status_filter') == 'returned' ? 'selected' : '' }}>Returned</option>
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
                            <a href="{{ route('documents.index', ['tab' => $tab]) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                                Clear
                            </a>
                        </div>
                    </div>

                    <!-- Results Count -->
                    @if(request()->hasAny(['search', 'type', 'status_filter']))
                        <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            {{ $documents->total() }} {{ Str::plural('result', $documents->total()) }} found
                        </div>
                    @endif
                </form>
            </div>

            <!-- Documents Table -->
            <div id="documents-table" class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
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
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white">
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
                <span id="bulk-forward-title">{{ auth()->user()->isStudent() ? 'Send' : 'Forward' }} Selected Documents</span>
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
                    <span id="bulk-forward-action-text">{{ auth()->user()->isStudent() ? 'Sending' : 'Forwarding' }}</span> <span id="bulk-forward-count" class="font-semibold text-gray-900 dark:text-white">0</span> document(s)
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
                    document.getElementById('bulk_to_user_id').value = user.id;
                },
                clearSelection() {
                    this.selectedUser = null;
                    this.search = '';
                    this.showDropdown = false;
                    document.getElementById('bulk_to_user_id').value = '';
                }
            }" 
            @click.away="showDropdown = false"
            class="relative">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span id="bulk-forward-to-label">{{ auth()->user()->isStudent() ? 'Send To' : 'Forward To' }}</span> <span class="text-red-500">*</span>
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
                <input type="hidden" name="to_user_id" id="bulk_to_user_id" :value="selectedUser ? selectedUser.id : ''" required>
                
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
                    <span id="bulk-forward-submit-text">{{ auth()->user()->isStudent() ? 'Send Documents' : 'Forward Documents' }}</span>
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
        @if(isset($tab))
            @if($tab === 'completed')
                <!-- Completed tab: Archive and Cancel -->
                @if(auth()->user()->isAdmin())
                <button onclick="bulkArchive()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="archive-box" class="w-4 h-4 mr-1.5" />
                    Archive
                </button>
                @endif
            @elseif($tab === 'archived')
                <!-- Archived tab: Delete, Unarchive, and Cancel -->
                <button onclick="bulkDelete()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="trash" class="w-4 h-4 mr-1.5" />
                    Delete
                </button>
                @if(auth()->user()->isAdmin())
                <button onclick="bulkUnarchive()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="arrow-path" class="w-4 h-4 mr-1.5" />
                    Unarchive
                </button>
                @endif
            @else
                <!-- Other tabs: Forward, Delete, Archive (if admin), Cancel -->
                <button onclick="bulkForward()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="paper-airplane" class="w-4 h-4 mr-1.5" />
                    <span id="bulk-forward-button-text">{{ auth()->user()->isStudent() ? 'Send' : 'Forward' }}</span>
                </button>
                <button onclick="bulkDelete()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="trash" class="w-4 h-4 mr-1.5" />
                    Delete
                </button>
                @if(auth()->user()->isAdmin())
                <button onclick="bulkArchive()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
                    <x-icon name="archive-box" class="w-4 h-4 mr-1.5" />
                    Archive
                </button>
                @endif
            @endif
        @endif
        <button onclick="clearSelection()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs sm:text-sm font-medium transition-colors">
            Cancel
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
                    
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" 
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a new file to replace the current document file (optional)</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Tags
                    </label>
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/50 min-h-[80px]" id="edit_selected_tags_container">
                            <p class="text-xs text-gray-500 dark:text-gray-400 w-full">No tags selected. Click below to add tags.</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-200 dark:border-gray-600 rounded-lg" id="edit_tags_container">
                            <!-- Tags will be populated dynamically -->
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add multiple tags to help organize and search documents</p>
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
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Tags
                    </label>
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/50 min-h-[80px]" id="create_selected_tags_container">
                            <p class="text-xs text-gray-500 dark:text-gray-400 w-full">No tags selected. Click below to add tags.</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-200 dark:border-gray-600 rounded-lg">
                            @foreach($tags as $tag)
                                <label class="flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="create-tag-checkbox rounded border-gray-300 text-orange-500 focus:ring-orange-500 dark:border-gray-600" onchange="updateCreateSelectedTags()">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add multiple tags to help organize and search documents</p>
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
                    refreshDocumentsTable().then(() => clearSelection());
                } else {
                    showSuccessModal('Partial Success', `Deleted ${deleted} document(s). ${failed} document(s) failed.`);
                    refreshDocumentsTable().then(() => clearSelection());
                }
            }
        }).catch(error => {
            console.error('Error deleting document:', error);
            failed++;
            if (deleted + failed === total) {
                showSuccessModal('Partial Success', `Deleted ${deleted} document(s). ${failed} document(s) failed.`);
                refreshDocumentsTable().then(() => clearSelection());
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
                    refreshDocumentsTable().then(() => clearSelection());
                } else {
                    showSuccessModal('Partial Success', `Archived ${archived} document(s). ${failed} document(s) failed. Only completed or approved documents can be archived.`);
                    refreshDocumentsTable().then(() => clearSelection());
                }
            }
        }).catch(error => {
            console.error('Error archiving document:', error);
            failed++;
            if (archived + failed === total) {
                showSuccessModal('Partial Success', `Archived ${archived} document(s). ${failed} document(s) failed.`);
                refreshDocumentsTable().then(() => clearSelection());
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
                    refreshDocumentsTable().then(() => clearSelection());
                } else {
                    showSuccessModal('Partial Success', `Unarchived ${unarchived} document(s). ${failed} document(s) failed.`);
                    refreshDocumentsTable().then(() => clearSelection());
                }
            }
        }).catch(error => {
            console.error('Error unarchiving document:', error);
            failed++;
            if (unarchived + failed === total) {
                showSuccessModal('Partial Success', `Unarchived ${unarchived} document(s). ${failed} document(s) failed.`);
                refreshDocumentsTable().then(() => clearSelection());
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
                const isStudent = document.getElementById('bulk-forward-submit-text')?.textContent?.includes('Send') || false;
                submitBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' + (isStudent ? 'Sending...' : 'Forwarding...');
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
                            refreshDocumentsTable();
                        } else {
                            alert(`Forwarded ${forwarded} document(s). ${failed} document(s) failed.`);
                            refreshDocumentsTable();
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
                        refreshDocumentsTable();
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

// Update selected tags display for create modal
function updateCreateSelectedTags() {
    const container = document.getElementById('create_selected_tags_container');
    if (!container) return;
    
    const checkboxes = document.querySelectorAll('#createDocumentModal .create-tag-checkbox:checked');
    
    if (checkboxes.length === 0) {
        container.innerHTML = '<p class="text-xs text-gray-500 dark:text-gray-400 w-full">No tags selected. Click below to add tags.</p>';
        return;
    }
    
    container.innerHTML = '';
    checkboxes.forEach(checkbox => {
        const tagName = checkbox.nextElementSibling.textContent;
        const tagId = checkbox.value;
        
        const tagBadge = document.createElement('span');
        tagBadge.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white';
        tagBadge.innerHTML = `
            ${tagName}
            <button type="button" onclick="this.closest('span').remove(); document.querySelector('input.create-tag-checkbox[value=\\'${tagId}\\']').checked = false; updateCreateSelectedTags();" class="ml-1 hover:opacity-70">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
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
                label.className = 'flex items-center space-x-2 cursor-pointer p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'tags[]';
                checkbox.value = tag.id;
                checkbox.className = 'edit-tag-checkbox rounded border-gray-300 text-orange-500 focus:ring-orange-500 dark:border-gray-600';
                checkbox.checked = selectedTagIds.includes(tag.id);
                checkbox.onchange = updateEditSelectedTags;
                
                const span = document.createElement('span');
                span.className = 'text-xs font-bold text-gray-900 dark:text-white';
                span.textContent = tag.name;
                
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
    
    if (checkboxes.length === 0) {
        container.innerHTML = '<p class="text-xs text-gray-500 dark:text-gray-400 w-full">No tags selected. Click below to add tags.</p>';
        return;
    }
    
    container.innerHTML = '';
    checkboxes.forEach(checkbox => {
        const tagId = parseInt(checkbox.value);
        const tag = tagsData.find(t => t.id === tagId);
        if (tag) {
            const tagElement = document.createElement('span');
            tagElement.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-gray-800 dark:bg-gray-700 text-white';
            tagElement.innerHTML = `
                ${tag.name}
                <button type="button" onclick="document.querySelector('.edit-tag-checkbox[value=\\"${tagId}\\"]').checked = false; updateEditSelectedTags();" 
                        class="ml-1 hover:opacity-75">×</button>
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
                    refreshDocumentsTable();
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
    // Remove any existing tags entries
    formData.delete('tags[]');
    formData.delete('tags');
    // Add checked tags
    if (tagCheckboxes.length > 0) {
        tagCheckboxes.forEach(checkbox => {
            formData.append('tags[]', checkbox.value);
        });
    } else {
        // Send empty array to clear tags
        formData.append('tags', JSON.stringify([]));
    }
    
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
    
    // Setup broadcasting listeners
    setupDocumentsBroadcastingListeners();
});

// Update broadcast status indicator
function updateBroadcastStatus(status, message) {
    const indicator = document.getElementById('broadcast-indicator');
    const text = document.getElementById('broadcast-text');
    const container = document.getElementById('broadcast-status');
    
    if (!indicator || !text || !container) return;
    
    indicator.classList.remove('bg-gray-400', 'bg-yellow-400', 'bg-green-500', 'bg-red-500', 'animate-pulse');
    container.classList.remove('bg-gray-100', 'bg-yellow-50', 'bg-green-50', 'bg-red-50', 
                                'dark:bg-gray-700', 'dark:bg-yellow-900/20', 'dark:bg-green-900/20', 'dark:bg-red-900/20',
                                'text-gray-600', 'text-yellow-600', 'text-green-600', 'text-red-600',
                                'dark:text-gray-400', 'dark:text-yellow-400', 'dark:text-green-400', 'dark:text-red-400');
    
    if (status === 'connecting') {
        indicator.classList.add('bg-yellow-400', 'animate-pulse');
        container.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20', 'text-yellow-600', 'dark:text-yellow-400');
    } else if (status === 'connected') {
        indicator.classList.add('bg-green-500');
        container.classList.add('bg-green-50', 'dark:bg-green-900/20', 'text-green-600', 'dark:text-green-400');
    } else if (status === 'error') {
        indicator.classList.add('bg-red-500', 'animate-pulse');
        container.classList.add('bg-red-50', 'dark:bg-red-900/20', 'text-red-600', 'dark:text-red-400');
    } else {
        indicator.classList.add('bg-gray-400');
        container.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
    }
    
    text.textContent = message;
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
                    refreshDocumentsTable();
                })
                .listen('.document.updated', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.deleted', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.forwarded', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.received', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.completed', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.approved', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.rejected', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.returned', (e) => {
                    refreshDocumentsTable();
                });
            
            // Listen to user's private channel
            const userChannel = window.Echo.private('App.Models.User.{{ auth()->id() }}');
            
            userChannel
                .listen('.document.forwarded', (e) => {
                    refreshDocumentsTable();
                    refreshSidebarBadges();
                })
                .listen('.document.received', (e) => {
                    refreshDocumentsTable();
                    refreshSidebarBadges();
                })
                .listen('.document.created', (e) => {
                    refreshDocumentsTable();
                })
                .listen('.document.updated', (e) => {
                    refreshDocumentsTable();
                });
            
            // Update status indicator
            if (typeof window.updateBroadcastStatus === 'function') {
                window.updateBroadcastStatus('connected', 'Live Updates');
            }
            
        } catch (e) {
            if (typeof window.updateBroadcastStatus === 'function') {
                window.updateBroadcastStatus('disconnected', 'Error');
            }
        }
    } else {
        if (typeof window.updateBroadcastStatus === 'function') {
            window.updateBroadcastStatus('connecting', 'Connecting...');
        }
        setTimeout(initializeDocumentsBroadcasting, 100);
    }
}

// Refresh documents table
window.__docRefreshBusy = window.__docRefreshBusy || false;
window.__docRefreshPending = window.__docRefreshPending || false;
window.__docLastRefreshAt = window.__docLastRefreshAt || 0;
async function refreshDocumentsTable() {
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
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			});
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
						// Execute any inline scripts within the replaced content
						try {
							clone.querySelectorAll('script').forEach(oldScript => {
								const s = document.createElement('script');
								for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
								if (oldScript.textContent) s.textContent = oldScript.textContent;
								oldScript.parentNode.replaceChild(s, oldScript);
							});
						} catch (_) {}
						setTimeout(() => {
							const restored = document.querySelector('#documents-table');
							if (restored) restored.style.opacity = '1';
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
				setTimeout(refreshDocumentsTable, 50);
			}
		}
	});
}

// Refresh sidebar badges
async function refreshSidebarBadges() {
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
</script>
@endsection
