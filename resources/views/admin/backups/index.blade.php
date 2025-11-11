@extends('layouts.app')

@section('title', 'Backup Management - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Backup Management</h1>
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
            <div class="mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Backup Management</h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Create and manage system backups</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Create Backup Section -->
            <div class="mb-6 sm:mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6">Create New Backup</h2>
                    <form action="{{ route('admin.backups.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="full">
                        <button type="submit" class="btn-primary w-full sm:w-auto flex items-center justify-center gap-2">
                            <x-icon name="archive-box" class="w-5 h-5" />
                            <span>Create Backup</span>
                        </button>
                    </form>

                    <!-- Clean Old Backups -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Cleanup Old Backups</h3>
                        <form action="{{ route('admin.backups.clean') }}" method="POST" class="flex flex-col sm:flex-row items-end gap-3 sm:gap-4">
                            @csrf
                            <div class="flex-1 w-full sm:w-auto">
                                <label for="days" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    Delete backups older than (days)
                                </label>
                                <input type="number" name="days" id="days" value="30" min="1" max="365" 
                                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <button type="submit" class="btn-danger w-full sm:w-auto">
                                Clean Old Backups
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backups List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Backup History</h2>
                        
                        <!-- Filters -->
                        @if($backupsPaginated->total() > 0)
                        <form method="GET" action="{{ route('admin.backups.index') }}" class="flex items-center gap-3">
                            <label for="per_page" class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                Per Page:
                            </label>
                            <select 
                                id="per_page" 
                                name="per_page" 
                                class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                                onchange="this.form.submit();"
                            >
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                        @endif
                    </div>
                </div>

                @if($backupsPaginated->count() > 0)
                    <!-- Bulk Actions Bar -->
                    <div id="bulkActionsBar" class="hidden px-4 sm:px-6 py-3 sm:py-4 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <span id="selectedCount" class="text-sm font-medium text-orange-900 dark:text-orange-200">
                                0 backups selected
                            </span>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button type="button" onclick="bulkDeleteBackups()" 
                                    class="flex-1 sm:flex-none px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <x-icon name="trash" type="solid" class="w-4 h-4" />
                                    <span>Delete</span>
                                </button>
                                <button type="button" onclick="clearSelection()" 
                                    class="flex-1 sm:flex-none px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($backupsPaginated as $backup)
                            <div class="p-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" class="backup-checkbox h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mt-1 flex-shrink-0" 
                                        value="{{ $backup['name'] }}" onchange="updateBulkActions()">
                                    <div class="flex-1 min-w-0">
                                        <!-- Name -->
                                        <div class="text-sm font-medium text-gray-900 dark:text-white break-all mb-2">
                                            {{ $backup['name'] }}
                                        </div>
                                        
                                        <!-- Meta Info -->
                                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            <span class="flex items-center gap-1">
                                                <x-icon name="archive-box" class="w-3 h-3" />
                                                {{ $backup['formatted_size'] }}
                                            </span>
                                            <span>•</span>
                                            <span class="flex items-center gap-1">
                                                <x-icon name="clock" class="w-3 h-3" />
                                                {{ $backup['formatted_date'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <a href="{{ route('admin.backups.download', $backup['name']) }}" 
                                        class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="Download">
                                        <x-icon name="arrow-down-tray" type="solid" class="w-5 h-5" />
                                    </a>
                                    <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this backup?')"
                                            class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                            <x-icon name="trash" type="solid" class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 lg:px-6 py-3 text-left">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                            class="w-4 h-4 text-orange-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 dark:focus:ring-orange-600 focus:ring-2">
                                    </th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size</th>
                                    <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created At</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($backupsPaginated as $backup)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <input type="checkbox" class="backup-checkbox w-4 h-4 text-orange-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 dark:focus:ring-orange-600 focus:ring-2" 
                                                value="{{ $backup['name'] }}" onchange="updateBulkActions()">
                                        </td>
                                        <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $backup['name'] }}
                                        </td>
                                        <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                            {{ $backup['formatted_size'] }}
                                        </td>
                                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $backup['formatted_date'] }}
                                        </td>
                                        <td class="px-4 lg:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.backups.download', $backup['name']) }}" 
                                                    class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="Download">
                                                    <x-icon name="arrow-down-tray" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                                </a>
                                                <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this backup?')"
                                                        class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                                        <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($backupsPaginated->hasPages())
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="overflow-x-auto">
                                {{ $backupsPaginated->links('vendor.pagination.minimal') }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="px-4 sm:px-6 py-12 text-center">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                            <x-icon name="archive-box" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-sm sm:text-base font-medium text-gray-900 dark:text-white mb-1">No backups yet</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Get started by creating a new backup.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

<script>
// Comments-style realtime for backups
function initializeBackupsBroadcasting() {
    if (!window.Echo || window.Echo._isDummy) {
        setTimeout(initializeBackupsBroadcasting, 100);
        return;
    }
    try {
        window.Echo.private('admin.backups')
            .subscribed(() => {})
            .error(() => {})
            .listen('.backup.started', () => { if (typeof window.refreshBackups === 'function') window.refreshBackups(); })
            .listen('.backup.completed', () => { if (typeof window.refreshBackups === 'function') window.refreshBackups(); })
            .listen('.backup.failed', () => { if (typeof window.refreshBackups === 'function') window.refreshBackups(); });
    } catch (e) {
        console.error('Backups broadcasting init failed:', e);
    }
}
document.addEventListener('DOMContentLoaded', initializeBackupsBroadcasting);

// Refresh backups list without reload
window.refreshBackups = async function() {
    try {
        const url = new URL(window.location.href);
        const response = await fetch(url.toString(), { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
        if (!response.ok) return;
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newContainer = doc.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border');
        const currentContainer = document.querySelector('.bg-white.dark\\:bg-gray-800.rounded-lg.sm\\:rounded-xl.border');
        if (newContainer && currentContainer && currentContainer.parentElement) {
            const parent = currentContainer.parentElement;
            currentContainer.style.transition = 'opacity 0.3s';
            currentContainer.style.opacity = '0.5';
            setTimeout(() => {
                if (currentContainer.parentElement === parent) {
                    parent.replaceChild(document.importNode(newContainer, true), currentContainer);
                }
            }, 300);
        }
    } catch (e) {
        console.error('Failed to refresh backups:', e);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }

    // Close sidebar on mobile when clicking a link
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

// Bulk actions functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.backup-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');

    if (count > 0) {
        bulkBar.classList.remove('hidden');
        selectedCount.textContent = `${count} backup${count !== 1 ? 's' : ''} selected`;
    } else {
        bulkBar.classList.add('hidden');
        selectAllCheckbox.checked = false;
    }
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.backup-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function bulkDeleteBackups() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    const filenames = Array.from(checkboxes).map(cb => cb.value);

    if (filenames.length === 0) {
        return;
    }

    showDeleteModal(
        'Delete Backups',
        `Are you sure you want to delete ${filenames.length} backup${filenames.length !== 1 ? 's' : ''}?`,
        () => {
            executeBulkDeleteBackups(filenames);
        }
    );
}

function executeBulkDeleteBackups(filenames) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.backups.bulk-delete") }}';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Add filenames
    filenames.forEach(filename => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'filenames[]';
        input.value = filename;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
