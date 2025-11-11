@extends('layouts.app')

@section('title', 'Compliance & GDPR - MLOOK')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex overflow-hidden">
    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-72 overflow-y-auto h-screen">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-md shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-icon name="bars-3" class="w-6 h-6" />
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Compliance & GDPR</h1>
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
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">Compliance & GDPR</h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Manage data compliance, retention policies, and GDPR requirements</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Documents -->
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="document-text" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Total Documents</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($report['total_documents']) }}</p>
                </div>

                <!-- Active Documents -->
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="check-circle" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Active Documents</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($report['active_documents']) }}</p>
                </div>

                <!-- Archived Documents -->
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="archive-box" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Archived</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($report['archived_documents']) }}</p>
                </div>

                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                    <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                        <x-icon name="users" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">Total Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ number_format($report['total_users']) }}</p>
                </div>
            </div>

            <!-- Data Retention Policy Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                            <x-icon name="clock" class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Data Retention Policy</h2>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Automatically archive documents after 1 year and delete after 7 years
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="applyRetention()" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <x-icon name="play" class="w-4 h-4 mr-2" />
                        Apply Retention Policy Now
                    </button>
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                        <x-icon name="information-circle" class="w-4 h-4 mr-1" />
                        This will process all eligible documents
                    </div>
                </div>
            </div>

            <!-- GDPR User Actions Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <x-icon name="shield-check" class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">GDPR User Data Management</h2>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Export, anonymize, or delete user data in compliance with GDPR
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="gdprUserSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select User
                        </label>
                        <select id="gdprUserSelect" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                            <option value="">Choose a user...</option>
                            @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="gdprActions" class="hidden">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 font-medium">Available Actions:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button onclick="exportUserData()" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <x-icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                                    Export Data
                                </button>
                                <button onclick="anonymizeUser()" class="inline-flex items-center justify-center px-4 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <x-icon name="eye-slash" class="w-4 h-4 mr-2" />
                                    Anonymize
                                </button>
                                <button onclick="deleteUserData()" class="inline-flex items-center justify-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <x-icon name="trash" class="w-4 h-4 mr-2" />
                                    Delete Data
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                <x-icon name="exclamation-triangle" class="w-4 h-4 inline mr-1" />
                                Warning: Anonymize and Delete actions cannot be undone
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
const gdprSelect = document.getElementById('gdprUserSelect');
gdprSelect.addEventListener('change', function() {
    document.getElementById('gdprActions').classList.toggle('hidden', !this.value);
});

function applyRetention() {
    if (!confirm('Apply retention policy? This will archive/delete old documents.')) return;
    fetch('/admin/compliance/retention/apply', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
	}).then(r => r.json()).then(async d => {
		alert(d.message);
		await refreshCompliance();
	});
}

function exportUserData() {
    const userId = gdprSelect.value;
    window.location.href = `/admin/compliance/users/${userId}/export`;
}

function anonymizeUser() {
    const userId = gdprSelect.value;
    if (!confirm('Anonymize this user? This cannot be undone.')) return;
    fetch(`/admin/compliance/users/${userId}/anonymize`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
	}).then(r => r.json()).then(async d => { 
		alert(d.message); 
		await refreshCompliance();
	});
}

function deleteUserData() {
    const userId = gdprSelect.value;
    if (!confirm('DELETE all user data? This CANNOT be undone!')) return;
    fetch(`/admin/compliance/users/${userId}/data`, {
        method: 'DELETE',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
		}
	}).then(r => r.json()).then(async d => { 
		alert(d.message); 
		await refreshCompliance();
    }).catch(err => {
        alert('Error deleting user data');
    });
}

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
});

// Refresh Compliance page sections without reload
async function refreshCompliance() {
	try {
		const url = new URL(window.location.href);
		const response = await fetch(url.toString(), { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
		if (!response.ok) return;
		const html = await response.text();
		const doc = new DOMParser().parseFromString(html, 'text/html');

		// Refresh the stats grid and GDPR card container
		const selectors = [
			'.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-4.mb-6',
			'.bg-white.dark\\:bg-gray-800.rounded-xl.border.border-gray-200.dark\\:border-gray-700.p-4.sm\\:p-6' // first GDPR card
		];
		selectors.forEach(selector => {
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
		console.error('Failed to refresh compliance page:', e);
	}
}
</script>
@endsection
