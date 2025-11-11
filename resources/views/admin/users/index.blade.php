@extends('layouts.app')

@section('title', 'Manage Users - MLOOK')

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
                    <!-- Mobile Menu Button -->
                    <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <x-icon name="bars-3" class="w-6 h-6" />
                    </button>

                    <!-- Page Title -->
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">User Management</h1>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <x-notifications />
                        <x-dark-mode-toggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1">All Users</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Create and manage all system users</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <!-- Broadcast Status -->
                    <div id="broadcast-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 w-full sm:w-auto justify-center sm:justify-start">
                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" id="broadcast-indicator"></span>
                        <span id="broadcast-text">Connecting...</span>
                    </div>
                    
                    <a href="{{ route('admin.users.export') }}" class="btn-secondary w-full sm:w-auto text-sm justify-center">
                        <x-icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                        Export
                    </a>
                    
                    <button type="button" onclick="openCreateModal()" class="btn-primary w-full sm:w-auto text-sm justify-center">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        <span class="hidden sm:inline">Create User</span>
                        <span class="sm:hidden">Create</span>
                    </button>
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

        <!-- Filters and Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" id="filter-form" class="w-full">
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
                            placeholder="Name, Email, or ID..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            oninput="autoSubmitForm()"
                        >
                    </div>

                    <!-- User Type Filter -->
                    <div class="lg:flex-1 lg:min-w-0">
                        <label for="usertype" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            User Type
                        </label>
                        <select 
                            id="usertype" 
                            name="usertype" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            onchange="updateFilterOptions(); autoSubmitForm();"
                        >
                            <option value="">All Types</option>
                            <option value="admin" {{ request('usertype') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="registrar" {{ request('usertype') === 'registrar' ? 'selected' : '' }}>Registrar</option>
                            <option value="dean" {{ request('usertype') === 'dean' ? 'selected' : '' }}>Dean</option>
                            <option value="department_head" {{ request('usertype') === 'department_head' ? 'selected' : '' }}>Department Head</option>
                            <option value="faculty" {{ request('usertype') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ request('usertype') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="student" {{ request('usertype') === 'student' ? 'selected' : '' }}>Student</option>
                        </select>
                    </div>

                    <!-- Program/Department Filter -->
                    <div id="program-filter" class="lg:flex-1 lg:min-w-0" style="display: {{ in_array(request('usertype'), ['faculty', 'student']) ? 'block' : 'none' }};">
                        <label for="program_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Program/Course
                        </label>
                        <select 
                            id="program_id" 
                            name="program_id" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            onchange="autoSubmitForm()"
                        >
                            <option value="">All Programs</option>
                            @foreach($programs->groupBy('college.name') as $collegeName => $collegePrograms)
                                <optgroup label="{{ $collegeName }}">
                                    @foreach($collegePrograms as $program)
                                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div id="department-filter" class="lg:flex-1 lg:min-w-0" style="display: {{ in_array(request('usertype'), ['dean', 'department_head', 'staff', 'registrar']) ? 'block' : 'none' }};">
                        <label for="department_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Department/College
                        </label>
                        <select 
                            id="department_id" 
                            name="department_id" 
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            onchange="autoSubmitForm()"
                        >
                            <option value="">All Departments/Colleges</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ request('department_id') == $college->id ? 'selected' : '' }}>
                                    {{ $college->name }} ({{ $college->code }})
                                </option>
                            @endforeach
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
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
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            onchange="autoSubmitForm()"
                        >
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                        <a href="{{ route('admin.users.index') }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center">
                            <span class="hidden sm:inline">Clear</span>
                            <span class="sm:hidden">Clear Filters</span>
                        </a>
                    </div>
                </div>

                <!-- Results Count -->
                @if(request()->hasAny(['search', 'usertype', 'program_id', 'department_id']))
                    <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        {{ $users->total() }} {{ Str::plural('result', $users->total()) }} found
                    </div>
                @endif
            </form>
        </div>

        <!-- Success Modal -->
        <x-success-modal />

        <!-- Create User Modal -->
        <x-user-create-modal :colleges="$colleges" :departments="$departments" :programs="$programs" />

        <!-- View, Edit, and Delete Modals Container -->
        <div id="user-modals-container">
            @foreach($users as $user)
                <x-user-view-modal :user="$user" />
                <x-user-edit-modal :user="$user" :colleges="$colleges" :departments="$departments" :programs="$programs" />
                <x-delete-confirmation 
                    :id="$user->id" 
                    title="Delete User" 
                    :message="'Are you sure you want to delete ' . $user->full_name . '? This action cannot be undone.'"
                    delete-text="Delete User"
                />
            @endforeach
        </div>

        <!-- Users Table -->
        <div id="users-table" class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <!-- Mobile Card View -->
            <div class="block sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $user)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" class="user-checkbox h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mt-1 flex-shrink-0" value="{{ $user->id }}" onchange="updateBulkUserActions()">
                            <div class="flex-1 min-w-0 flex items-start gap-3">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                @else
                                    @php
                                        $firstLetter = strtoupper(substr($user->first_name, 0, 1));
                                        $colors = [
                                            'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
                                            'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
                                            'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
                                            'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
                                            'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
                                            'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
                                            'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
                                            'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
                                            'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
                                            'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
                                            'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
                                            'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
                                            'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
                                            'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
                                            'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
                                            'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
                                            'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
                                            'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
                                            'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
                                            'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
                                            'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
                                            'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
                                            'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
                                            'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
                                            'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
                                            'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
                                        ];
                                        $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
                                    @endphp
                                    <div class="w-10 h-10 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-semibold text-base">{{ $firstLetter }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white break-words">{{ $user->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 break-all">{{ $user->email }}</div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $user->university_id }}</span>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 capitalize">
                                            {{ $user->usertype }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" onclick="window['openViewModal{{ $user->id }}']()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                <x-icon name="eye" type="solid" class="w-5 h-5" />
                            </button>
                            <button type="button" onclick="window['openEditModal{{ $user->id }}']()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                <x-icon name="pencil" type="solid" class="w-5 h-5" />
                            </button>
                            <button type="button" 
                                    onclick="openDeleteModal{{ $user->id }}(function() { deleteUser({{ $user->id }}); })" 
                                    class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                                    title="Delete">
                                <x-icon name="trash" type="solid" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">No users found</div>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                <input type="checkbox" id="select-all-users" onchange="toggleAllUsers()" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                            </th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="hidden md:table-cell px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">University ID</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User Type</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 lg:px-6 py-3 sm:py-4">
                                    <input type="checkbox" class="user-checkbox h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" value="{{ $user->id }}" onchange="updateBulkUserActions()">
                                </td>
                                <td class="px-4 lg:px-6 py-3 sm:py-4">
                                    <div class="flex items-center space-x-3">
                                        @if($user->avatar)
                                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                        @else
                                            @php
                                                $firstLetter = strtoupper(substr($user->first_name, 0, 1));
                                                $colors = [
                                                    'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
                                                    'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
                                                    'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
                                                    'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
                                                    'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
                                                    'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
                                                    'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
                                                    'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
                                                    'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
                                                    'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
                                                    'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
                                                    'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
                                                    'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
                                                    'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
                                                    'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
                                                    'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
                                                    'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
                                                    'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
                                                    'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
                                                    'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
                                                    'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
                                                    'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
                                                    'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
                                                    'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
                                                    'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
                                                    'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
                                                ];
                                                $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
                                            @endphp
                                            <div class="w-8 h-8 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center flex-shrink-0">
                                                <span class="text-white font-semibold text-sm">{{ $firstLetter }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 md:hidden">{{ $user->email }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 lg:hidden">{{ $user->university_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-4 lg:px-6 py-3 sm:py-4">
                                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ $user->email }}</div>
                                </td>
                                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $user->university_id }}</div>
                                </td>
                                <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <span class="px-2 sm:px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 capitalize">
                                        {{ $user->usertype }}
                                    </span>
                                </td>
                                <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="window['openViewModal{{ $user->id }}']()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                            <x-icon name="eye" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                        <button type="button" onclick="window['openEditModal{{ $user->id }}']()" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                            <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                        <button type="button" 
                                                onclick="openDeleteModal{{ $user->id }}(function() { deleteUser({{ $user->id }}); })" 
                                                class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                                                title="Delete">
                                            <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 sm:px-6 py-12 text-center">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">No users found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        {{ $users->links('vendor.pagination.minimal') }}
                    </div>
                </div>
            @endif
        </div>
        </main>
    </div>
</div>

<!-- Bulk Actions Bar for Users -->
<div id="bulk-users-actions-bar" class="hidden fixed bottom-4 sm:bottom-6 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 sm:px-6 py-3 sm:py-4 rounded-lg sm:rounded-xl shadow-xl flex flex-col sm:flex-row items-center gap-3 sm:gap-4 z-50 max-w-xl sm:max-w-none">
    <div class="flex items-center gap-4 w-full sm:w-auto">
        <span id="selected-users-count" class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">
            <span class="inline-flex items-center justify-center w-5 h-5 mr-2 text-xs font-bold text-white bg-orange-500 rounded-full" id="selected-users-badge">0</span>
            <span id="selected-users-text">selected</span>
        </span>
        <div class="hidden sm:block h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
    </div>
    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end sm:justify-start">
        <button onclick="bulkDeleteUsers()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-medium transition-colors">
            <x-icon name="trash" class="w-4 h-4 mr-1.5" />
            Delete
        </button>
        <button onclick="clearUserSelection()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs sm:text-sm font-medium transition-colors">
            Cancel
        </button>
    </div>
</div>

<script>
// Show success using modal instead of inline alert
function showFlashMessage(message, type = 'success') {
    if (type === 'success') {
        showSuccessModalWithAutoClose('Success!', message);
    } else {
        // For errors, still use inline alert
        const container = document.querySelector('main');
        const alertClass = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400';
        
        const alert = document.createElement('div');
        alert.className = `mb-6 ${alertClass} border px-4 py-3 rounded-xl`;
        alert.textContent = message;
        
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

// Delete user with AJAX (expose globally for inline onclick handlers)
window.deleteUser = async function(userId) {
    try {
        const response = await fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showSuccessModalWithAutoClose('User Deleted!', data.message || 'User deleted successfully!');
            await window.refreshUserTable();
        } else {
            showFlashMessage(data.message || 'Error deleting user', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showFlashMessage('An unexpected error occurred', 'error');
    }
}

// Refresh user table without page reload (MUST be global)
window.refreshUserTable = async function() {
    try {
        const url = new URL(window.location.href);
        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'text/html'
            }
        });
        
        if (response.ok) {
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Get current and new table bodies
            const currentTableBody = document.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border tbody');
            const newTableBody = doc.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border tbody');
            
            // Get current and new mobile card views
            const currentMobileView = document.querySelector('.block.sm\\:hidden.divide-y');
            const newMobileView = doc.querySelector('.block.sm\\:hidden.divide-y');
            
            if (currentTableBody && newTableBody) {
                // Get current row IDs
                const currentRows = Array.from(currentTableBody.querySelectorAll('tr')).map(row => {
                    const deleteBtn = row.querySelector('[onclick*="deleteUser"]');
                    if (deleteBtn) {
                        const match = deleteBtn.getAttribute('onclick').match(/deleteUser\((\d+)\)/);
                        return match ? match[1] : null;
                    }
                    return null;
                }).filter(id => id !== null);
                
                // Get new row IDs
                const newRows = Array.from(newTableBody.querySelectorAll('tr')).map(row => {
                    const deleteBtn = row.querySelector('[onclick*="deleteUser"]');
                    if (deleteBtn) {
                        const match = deleteBtn.getAttribute('onclick').match(/deleteUser\((\d+)\)/);
                        return match ? match[1] : null;
                    }
                    return null;
                }).filter(id => id !== null);
                
                // Find new user IDs
                const newUserIds = newRows.filter(id => !currentRows.includes(id));
                
                // Replace table content
                currentTableBody.innerHTML = newTableBody.innerHTML;
                
                // Add animation to new rows
                if (newUserIds.length > 0) {
                    newUserIds.forEach(userId => {
                        const newRow = currentTableBody.querySelector(`[onclick*="deleteUser(${userId})"]`)?.closest('tr');
                        if (newRow) {
                            newRow.classList.add('new-row-animation');
                            // Remove animation class after it completes
                            setTimeout(() => {
                                newRow.classList.remove('new-row-animation');
                            }, 2500);
                        }
                    });
                }
            }
            
            // Update mobile view if exists
            if (currentMobileView && newMobileView) {
                currentMobileView.innerHTML = newMobileView.innerHTML;
            }
            
            // Update pagination - simple replacement
            const currentPagination = document.querySelector('.px-4.sm\\:px-6.py-3.sm\\:py-4.border-t');
            const newPagination = doc.querySelector('.px-4.sm\\:px-6.py-3.sm\\:py-4.border-t');
            if (currentPagination && newPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Update modals section - CRITICAL for view/edit/delete functionality
            const modalsContainer = document.getElementById('user-modals-container');
            if (modalsContainer) {
                const newModalsContainer = doc.getElementById('user-modals-container');
                if (newModalsContainer) {
                    modalsContainer.innerHTML = newModalsContainer.innerHTML;
                }
            }
            
            // Update select-all checkbox state
            const selectAll = document.getElementById('select-all-users');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            
            // Re-attach event listeners if needed
            attachModalEventListeners();
            
            // Re-initialize bulk actions
            updateBulkUserActions();
        }
    } catch (error) {
        console.error('Error refreshing table:', error);
        // Fallback: try refreshing again
        try { await window.refreshUserTable(); } catch (_) {}
    }
}

// Attach modal event listeners after table refresh
function attachModalEventListeners() {
    // Event listeners are already inline in the HTML, so no need to reattach
}


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

    // Update broadcast status indicator
    function updateBroadcastStatus(status, message) {
        const indicator = document.getElementById('broadcast-indicator');
        const text = document.getElementById('broadcast-text');
        const container = document.getElementById('broadcast-status');
        
        if (!indicator || !text || !container) return;
        
        // Remove all status classes
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

    // Comments-style realtime: wait for Echo, subscribe with subscribed/error, update UI
    function initializeUsersBroadcasting() {
        if (!window.Echo || window.Echo._isDummy) {
            updateBroadcastStatus('connecting', 'Connecting...');
            setTimeout(initializeUsersBroadcasting, 100);
            return;
        }
        try {
        updateBroadcastStatus('connecting', 'Setting up...');
        @if(auth()->user()->isAdmin())
        window.Echo.private('admin.users')
                .subscribed(() => updateBroadcastStatus('connected', 'Live Updates'))
                .error(() => updateBroadcastStatus('error', 'Auth Error'))
                .listen('.user.created', (e) => { if (typeof window.refreshUserTable === 'function') window.refreshUserTable(); })
                .listen('.user.updated', (e) => { if (typeof window.refreshUserTable === 'function') window.refreshUserTable(); })
                .listen('.user.deleted', (e) => { if (typeof window.refreshUserTable === 'function') window.refreshUserTable(); });
            // Optional: personal channel for self-updates
        window.Echo.private('App.Models.User.' + {{ auth()->id() }})
                .subscribed(() => {})
                .error(() => {})
                .listen('.user.updated', () => {
                    if (typeof window.refreshUserTable === 'function') window.refreshUserTable();
            });
        @else
        updateBroadcastStatus('connected', 'Live Updates');
        @endif
        } catch (e) {
            updateBroadcastStatus('error', 'Setup Failed');
            console.error('Users broadcasting init failed:', e);
            }
    }
    initializeUsersBroadcasting();

    // Initialize filter visibility on page load
    updateFilterOptions();

    // Auto-submit form when per_page changes
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            autoSubmitForm();
        });
    }

    // Auto-submit search with debounce (500ms delay)
    const searchInput = document.getElementById('search');
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                autoSubmitForm();
            }, 500);
        });
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        window.refreshUserTable().then(() => {
            clearUserSelection();
            updateBulkUserActions();
        });
    });
});

// Auto-submit form function (AJAX version)
async function autoSubmitForm() {
    const form = document.getElementById('filter-form');
    if (!form) return;
    
    // Build URL with form parameters
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add all form fields to params
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Update URL without reload
    const newUrl = new URL(window.location.href);
    newUrl.search = params.toString();
    window.history.pushState({}, '', newUrl.toString());
    
    // Refresh table using AJAX
    await window.refreshUserTable();
    
    // Re-initialize bulk actions after refresh
    clearUserSelection();
    updateBulkUserActions();
}

    // Function to show/hide program and department filters based on usertype
function updateFilterOptions() {
    const usertypeSelect = document.getElementById('usertype');
    const programFilter = document.getElementById('program-filter');
    const departmentFilter = document.getElementById('department-filter');
    const programSelect = document.getElementById('program_id');
    const departmentSelect = document.getElementById('department_id');

    if (!usertypeSelect) return;

    const selectedUsertype = usertypeSelect.value;

    // Hide both filters first
    if (programFilter) {
        programFilter.style.display = 'none';
    }
    if (departmentFilter) {
        departmentFilter.style.display = 'none';
    }

    // Clear selections when hidden
    if (programSelect) programSelect.value = '';
    if (departmentSelect) departmentSelect.value = '';

    // Show appropriate filter based on usertype
    if (selectedUsertype === 'faculty' || selectedUsertype === 'student') {
        if (programFilter) {
            programFilter.style.display = 'block';
        }
    } else if (['dean', 'department_head', 'staff', 'registrar'].includes(selectedUsertype)) {
        if (departmentFilter) {
            departmentFilter.style.display = 'block';
        }
    }
}

// Check if we need to open a user view modal from notification
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const viewUserId = urlParams.get('view_user');
    
    if (viewUserId) {
        // Try to call the function directly, or find the button
        const openViewFunction = window[`openViewModal${viewUserId}`];
        if (typeof openViewFunction === 'function') {
            openViewFunction();
        } else {
            // Fallback: Find the user in the table and trigger the view modal
            const viewButton = document.querySelector(`[onclick*="openViewModal${viewUserId}"]`);
            if (viewButton) {
                viewButton.click();
            }
        }
        
        // Clean up the URL without reloading the page
        const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]view_user=\d+/, '').replace(/^&/, '?');
        window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
    }
});

// Bulk Actions for Users
let selectedUsers = [];

// Helper function to get only visible checkboxes
function getVisibleUserCheckboxes() {
    const allCheckboxes = document.querySelectorAll('.user-checkbox');
    return Array.from(allCheckboxes).filter(checkbox => {
        // Check if checkbox is actually visible (not hidden by CSS)
        const rect = checkbox.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && checkbox.offsetParent !== null;
    });
}

function toggleAllUsers() {
    const selectAll = document.getElementById('select-all-users');
    const checkboxes = getVisibleUserCheckboxes();
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkUserActions();
}

function updateBulkUserActions() {
    const checkboxes = getVisibleUserCheckboxes().filter(cb => cb.checked);
    selectedUsers = checkboxes.map(cb => cb.value);
    
    const bulkBar = document.getElementById('bulk-users-actions-bar');
    const badge = document.getElementById('selected-users-badge');
    const text = document.getElementById('selected-users-text');
    const selectAll = document.getElementById('select-all-users');
    
    if (selectedUsers.length > 0) {
        if (bulkBar) bulkBar.classList.remove('hidden');
        if (badge) badge.textContent = selectedUsers.length;
        if (text) text.textContent = selectedUsers.length === 1 ? 'selected' : 'selected';
        
        // Update select all checkbox state
        if (selectAll) {
            const allVisible = getVisibleUserCheckboxes();
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

function bulkDeleteUsers() {
    if (selectedUsers.length === 0) return;
    
    showDeleteModal(
        'Delete Users',
        `Are you sure you want to delete ${selectedUsers.length} selected user(s)? This action cannot be undone.`,
        () => {
            executeBulkDeleteUsers();
        }
    );
}

function executeBulkDeleteUsers() {
    let deleted = 0;
    let failed = 0;
    const total = selectedUsers.length;
    
    selectedUsers.forEach(userId => {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(response => response.json().then(json => {
            if (response.ok && json.success) {
                deleted++;
            } else {
                failed++;
                console.error('Error deleting user:', json.message || json.error);
            }
            
            if (deleted + failed === total) {
                if (failed === 0) {
                    showSuccessModal('Success', `Successfully deleted ${deleted} user(s)!`);
                    setTimeout(async () => { await window.refreshUserTable(); clearUserSelection(); }, 500);
                } else {
                    showSuccessModal('Partial Success', `Deleted ${deleted} user(s). ${failed} user(s) failed.`);
                    window.refreshUserTable().then(() => { clearUserSelection(); });
                }
            }
        }).catch(error => {
            console.error('Error deleting user:', error);
            failed++;
            if (deleted + failed === total) {
                showSuccessModal('Partial Success', `Deleted ${deleted} user(s). ${failed} user(s) failed.`);
                window.refreshUserTable().then(() => { clearUserSelection(); });
            }
        }));
    });
}

function clearUserSelection() {
    getVisibleUserCheckboxes().forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all-users');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    updateBulkUserActions();
}
</script>
@endsection

