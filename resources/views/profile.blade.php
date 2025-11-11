@extends('layouts.app')

@section('title', 'Profile - MLOOK')

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
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Profile</h1>
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
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Profile Information -->
                    <div class="lg:col-span-2">
                        <!-- Profile Header with Avatar -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8 mb-6">
                            <div class="flex items-center space-x-4">
								@if(auth()->user()->avatar)
									<img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover flex-shrink-0" data-profile-avatar>
                                @else
                                    @php
                                        $firstLetter = strtoupper(substr(auth()->user()->first_name, 0, 1));
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
									<div class="w-12 h-12 sm:w-16 sm:h-16 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center flex-shrink-0" data-profile-avatar-fallback>
                                        <span class="text-white font-semibold text-lg sm:text-xl">{{ $firstLetter }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
									<div class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white" id="profile_full_name">
										{{ auth()->user()->full_name }}
                                    </div>
									<div class="text-sm sm:text-base text-gray-700 dark:text-gray-300 mt-1" id="profile_email">
										{{ auth()->user()->email }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Info Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-6">Profile Info</h3>
                            
                            <div class="space-y-6">
                                <!-- First Name | Last Name -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            First Name
                                        </label>
										<div class="text-sm sm:text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600" id="profile_first_name_value">
											{{ auth()->user()->first_name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Last Name
                                        </label>
										<div class="text-sm sm:text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600" id="profile_last_name_value">
											{{ auth()->user()->last_name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- University ID | User Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            University ID
                                        </label>
										<div class="text-sm sm:text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600" id="profile_university_id_value">
											{{ auth()->user()->university_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            User Type
                                        </label>
										<div class="text-sm sm:text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 capitalize" id="profile_usertype_value">
                                            {{ auth()->user()->usertype }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Member Since -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Member Since
                                    </label>
                                    <div class="text-sm sm:text-base text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                        {{ auth()->user()->created_at->format('F d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Actions -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-6">Account Settings</h3>
                            
                            <div class="space-y-3">
                                <!-- Edit Profile -->
                                <button onclick="openEditProfileModal()" class="btn-secondary w-full">
                                    Edit Profile
                                </button>

                                <!-- Change Password -->
                                <button onclick="openChangePasswordModal()" class="btn-secondary w-full">
                                    Change Password
                                </button>

                                <!-- Delete Account -->
                                <button onclick="openDeleteAccountModal()" class="btn-danger w-full">
                                    Delete Account
                                </button>

                                <!-- Account Activity -->
                                <button onclick="openActivityModal()" class="btn-secondary w-full">
                                    Account Activity
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modals -->
<x-profile-edit-modal />
<x-profile-change-password-modal />
<x-profile-delete-account-modal />
<x-profile-activity-modal />

<script>
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
    
    // Listen for user updates to refresh profile page
    function setupUserUpdateListener() {
        if (window.Echo) {
            const userId = {{ auth()->id() }};
            window.Echo.private('App.Models.User.' + userId)
                .listen('.user.updated', (e) => {
                    // Update profile info dynamically
                    updateProfileInfo(e.user);
                });
        } else {
            // Wait for Echo to initialize
            setTimeout(setupUserUpdateListener, 100);
        }
    }
		// Helper function to update profile info
    function updateProfileInfo(user) {
        if (!user) return;
        
			// Update profile header
			const nameEl = document.getElementById('profile_full_name');
			if (nameEl && user.full_name) nameEl.textContent = user.full_name;
			const emailEl = document.getElementById('profile_email');
			if (emailEl && user.email) emailEl.textContent = user.email;

			// Update read-only values
			const map = {
				profile_first_name_value: user.first_name,
				profile_last_name_value: user.last_name,
				profile_university_id_value: user.university_id,
				profile_usertype_value: user.usertype
			};
			Object.keys(map).forEach(id => {
				const el = document.getElementById(id);
				if (el && typeof map[id] !== 'undefined' && map[id] !== null) {
					el.textContent = map[id];
				}
			});

			// Update form inputs if present
        const fields = {
            'first_name': user.first_name,
            'last_name': user.last_name,
            'email': user.email,
            'university_id': user.university_id
        };
        
        Object.keys(fields).forEach(field => {
            const input = document.querySelector(`input[name="${field}"]`);
            if (input && fields[field]) {
                input.value = fields[field];
            }
        });
        
        // Update avatar if changed
			const avatarImg = document.querySelector('[data-profile-avatar]');
			const avatarFallback = document.querySelector('[data-profile-avatar-fallback]');
			if (avatarImg && user.avatar_url) {
				avatarImg.src = user.avatar_url;
				if (avatarFallback) avatarFallback.classList.add('hidden');
			}
        
        // Dispatch event for other components
        window.dispatchAlpineEvent('profile-updated', { user });
    }

    setupUserUpdateListener();
});
</script>
@endsection

