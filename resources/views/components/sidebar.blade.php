@props(['active' => null])

<aside class="fixed left-0 top-0 h-screen w-72 sm:w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-50 transition-transform duration-300 -translate-x-full lg:translate-x-0" id="sidebar">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-14 sm:h-16 px-4 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.svg') }}" alt="MLOOK Logo" class="h-8 sm:h-10 w-auto">
                <span class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">MLOOK</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 sm:px-4 py-4 sm:py-6 space-y-1 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <x-icon name="squares-2x2" class="w-5 h-5 mr-3 flex-shrink-0" />
                Dashboard
            </a>

            @if(auth()->user()->isAdmin())
                <!-- Admin Section -->
                <div class="pt-3 sm:pt-4">
                    <p class="px-3 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Administration</p>
                    
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <x-icon name="users" class="w-5 h-5 mr-3 flex-shrink-0" />
                        User Management
                    </a>
                </div>
            @endif

            <!-- Documents Section -->
            <div class="pt-3 sm:pt-4">
                <p class="px-3 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Documents</p>
                
                <a href="{{ route('documents.inbox') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('documents.inbox') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" x-data="inboxCounter({{ auth()->id() }})" x-init="init()">
                    <x-icon name="inbox" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Inbox</span>
                    @php
                        // Inbox: total documents currently routed to me (matches Inbox table)
                        $inboxCount = \App\Models\Document::where('current_holder_id', auth()->id())
                            ->whereIn('status', [
                                \App\Models\Document::STATUS_ROUTING,
                                \App\Models\Document::STATUS_RECEIVED,
                                \App\Models\Document::STATUS_IN_REVIEW,
                                \App\Models\Document::STATUS_FOR_APPROVAL
                            ])
                            ->count();
                    @endphp
                    <span x-text="count" :class="{'invisible': count == 0}" class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400" x-init="count = {{ (int) ($inboxCount ?? 0) }}"></span>
                </a>
                
                <a href="{{ route('documents.my-documents') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('documents.my-documents') && request('section', 'draft') === 'draft' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" x-data="documentsCounter({{ auth()->id() }})" x-init="init()">
                    <x-icon name="document-text" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Documents</span>
                    @php
                        // My Documents: total Draft + Returned created by me (matches Documents table default section)
                        $documentsCount = \App\Models\Document::where('created_by', auth()->id())
                            ->whereIn('status', [
                                \App\Models\Document::STATUS_DRAFT,
                                \App\Models\Document::STATUS_RETURNED
                            ])
                            ->count();
                    @endphp
                    <span x-text="count" :class="{'invisible': count == 0}" class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400" x-init="count = {{ (int) ($documentsCount ?? 0) }}"></span>
                </a>
                
                <a href="{{ route('documents.sent') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('documents.sent') || (request()->routeIs('documents.my-documents') && request('section') === 'sent') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" x-data="sentCounter({{ auth()->id() }})" x-init="init()">
                    <x-icon name="paper-airplane" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Sent</span>
                    @php
                        // Sent: total documents I have sent still in flow (matches Sent table)
                        $sentCount = \App\Models\Document::whereHas('tracking', function ($q) {
                                $q->where('from_user_id', auth()->id());
                            })
                            ->whereIn('status', [
                                \App\Models\Document::STATUS_ROUTING,
                                \App\Models\Document::STATUS_RECEIVED,
                                \App\Models\Document::STATUS_IN_REVIEW,
                                \App\Models\Document::STATUS_FOR_APPROVAL
                            ])
                            ->count();
                    @endphp
                    <span x-text="count" :class="{'invisible': count == 0}" class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400" x-init="count = {{ (int) ($sentCount ?? 0) }}"></span>
                </a>
                
                <a href="{{ route('documents.completed') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('documents.completed') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" x-data="completedCounter({{ auth()->id() }})" x-init="init()">
                    <x-icon name="check-circle" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Completed</span>
                    @php
                        // Completed: total completed/approved/rejected visible to me (matches Completed table)
                        $user = auth()->user();
                        $completedQuery = \App\Models\Document::whereIn('status', [
                            \App\Models\Document::STATUS_COMPLETED,
                            \App\Models\Document::STATUS_APPROVED,
                            \App\Models\Document::STATUS_REJECTED
                        ]);

                        if (! $user->isAdmin()) {
                            $completedQuery->where(function ($q) use ($user) {
                                $q->where('created_by', $user->id)
                                    ->orWhere('current_holder_id', $user->id)
                                    ->orWhereHas('tracking', function ($tq) use ($user) {
                                        $tq->where('from_user_id', $user->id)
                                            ->orWhere('to_user_id', $user->id);
                                    });
                            });
                        }

                        $completedCount = $completedQuery->count();
                    @endphp
                    <span x-text="count" :class="{'invisible': count == 0}" class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400" x-init="count = {{ (int) ($completedCount ?? 0) }}"></span>
                </a>
                
                <a href="{{ route('documents.archive') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('documents.archive') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="archive-box" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Archive</span>
                </a>
            </div>

            @if(auth()->user()->isAdmin())
            <!-- Admin Settings -->
            <div class="pt-3 sm:pt-4">
                <p class="px-3 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Admin</p>
                
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="cog-6-tooth" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Settings</span>
                </a>
                
                <a href="{{ route('admin.backups.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.backups.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="circle-stack" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Backups</span>
                </a>
                
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.audit-logs.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="clipboard-document-list" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Audit Logs</span>
                </a>
                
                <a href="{{ route('admin.performance.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.performance.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="chart-bar" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Performance</span>
                </a>
                
                <a href="{{ route('admin.compliance.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('admin.compliance.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="shield-check" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Compliance</span>
                </a>
            </div>
            @else
            <!-- Non-Admin Settings -->
            <div class="pt-3 sm:pt-4">
                <p class="px-3 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Account</p>
                
                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <x-icon name="cog-6-tooth" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Settings</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- User Profile Card -->
        <div class="px-4 py-4">
            <a href="{{ route('profile') }}" class="block">
                <div class="rounded-lg p-4 border transition-colors cursor-pointer {{ request()->routeIs('profile') ? 'bg-orange-100 dark:bg-orange-900/30 border-orange-200 dark:border-orange-800' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                    <div class="flex items-center space-x-3">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
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
                            <div class="w-10 h-10 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-semibold text-sm">{{ $firstLetter }}</span>
                            </div>
                        @endif
						<div class="flex-1 min-w-0">
							<p class="text-sm font-medium text-gray-900 dark:text-white truncate" data-sidebar-user-name>{{ auth()->user()->full_name }}</p>
							<p class="text-xs text-gray-500 dark:text-gray-400 capitalize" data-sidebar-user-role>{{ auth()->user()->usertype }}</p>
						</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Logout -->
        <div class="px-4 pb-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-2.5 text-base font-medium rounded-lg transition-colors text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400">
                    <x-icon name="arrow-right-on-rectangle" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="truncate">Logout</span>
                </button>
            </form>
        </div>
        </div>
    </aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for user updates to refresh sidebar profile
    function setupUserUpdateListener() {
        if (window.Echo) {
            const userId = {{ auth()->id() }};
            
            window.Echo.private('App.Models.User.' + userId)
                .listen('.user.updated', (e) => {
                    // Update sidebar user info dynamically
                    updateSidebarUserInfo(e.user);
                })
                .listen('.user.deleted', (e) => {
                    if (e.user_id == userId) {
                        // Show account deleted modal
                        if (typeof showAccountDeletedModal === 'function') {
                            showAccountDeletedModal();
                        }
                    }
                });
        } else {
            // Wait for Echo to initialize
            setTimeout(setupUserUpdateListener, 100);
        }
    }

    // Helper function to update sidebar user info
    function updateSidebarUserInfo(user) {
        if (!user) return;
        
        // Update user name
        const nameElement = document.querySelector('[data-sidebar-user-name]');
        if (nameElement && user.full_name) {
            nameElement.textContent = user.full_name;
        }
        
        // Update user avatar
        const avatarElement = document.querySelector('[data-sidebar-user-avatar]');
        if (avatarElement && user.avatar_url) {
            avatarElement.src = user.avatar_url;
        }
        
        // Update user role
        const roleElement = document.querySelector('[data-sidebar-user-role]');
        if (roleElement && user.usertype) {
            roleElement.textContent = user.usertype;
        }
    }

    setupUserUpdateListener();
    });
</script>

<script>
function inboxCounter(userId) {
    return {
        count: 0,
        
        init() {
			this.setupBroadcasting();
			this.refreshCount();
			setInterval(() => this.refreshCount(), 15000);
        },
        
        setupBroadcasting() {
            if (window.Echo && !window.Echo._isDummy) {
                // Listen to user's private channel for document updates
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.document.forwarded', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.received', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.completed', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.approved', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.returned', (e) => {
                        this.refreshCount();
					});
				try {
					window.Echo.private('documents')
						.listen('.document.created', () => this.refreshCount())
						.listen('.document.deleted', () => this.refreshCount());
				} catch (_) {}
            } else {
                // Retry if Echo not ready
                setTimeout(() => this.setupBroadcasting(), 100);
            }
        },
        
        async refreshCount() {
            try {
                const response = await fetch('/api/inbox-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.count !== undefined) {
                    this.count = data.count;
                }
            } catch (error) {
                // Error refreshing inbox count
            }
        }
    }
}

function documentsCounter(userId) {
    return {
        count: 0,
        
        init() {
			this.setupBroadcasting();
        },
        
        setupBroadcasting() {
            if (window.Echo && !window.Echo._isDummy) {
                // Listen to user's private channel for document updates
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.document.forwarded', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.returned', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.completed', (e) => {
                        this.refreshCount();
                    })
					.listen('.document.approved', (e) => {
						this.refreshCount();
					})
					.listen('.document.created', (e) => this.refreshCount())
					.listen('.document.deleted', (e) => this.refreshCount());
				// Fallback shared channel
				try {
					window.Echo.private('documents')
						.listen('.document.created', () => this.refreshCount())
						.listen('.document.deleted', () => this.refreshCount());
				} catch (_) {}
            } else {
                // Retry if Echo not ready
                setTimeout(() => this.setupBroadcasting(), 100);
            }
        },
        
        async refreshCount() {
            try {
                const response = await fetch('/api/documents-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.count !== undefined) {
                    this.count = data.count;
                }
            } catch (error) {
                // Error refreshing documents count
            }
        }
    }
}

function sentCounter(userId) {
    return {
        count: 0,
        
        init() {
			this.setupBroadcasting();
        },
        
        setupBroadcasting() {
            if (window.Echo && !window.Echo._isDummy) {
                // Listen to user's private channel for document updates
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.document.forwarded', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.received', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.completed', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.approved', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.returned', (e) => {
						this.refreshCount();
					})
					.listen('.document.updated', (e) => {
						this.refreshCount();
					});
				// Fallback shared channel
				try {
					window.Echo.private('documents')
						.listen('.document.updated', () => this.refreshCount());
				} catch (_) {}
            } else {
                // Retry if Echo not ready
                setTimeout(() => this.setupBroadcasting(), 100);
            }
        },
        
        async refreshCount() {
            try {
                const response = await fetch('/api/sent-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.count !== undefined) {
                    this.count = data.count;
                }
            } catch (error) {
                // Error refreshing sent count
            }
        }
    }
}

function completedCounter(userId) {
    return {
        count: 0,
        
        init() {
			this.setupBroadcasting();
        },
        
        setupBroadcasting() {
            if (window.Echo && !window.Echo._isDummy) {
                // Listen to user's private channel for document updates
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('.document.completed', (e) => {
                        this.refreshCount();
                    })
                    .listen('.document.approved', (e) => {
						this.refreshCount();
					})
					.listen('.document.rejected', (e) => {
						this.refreshCount();
					});
				// Fallback shared channel
				try {
					window.Echo.private('documents')
						.listen('.document.completed', () => this.refreshCount())
						.listen('.document.approved', () => this.refreshCount())
						.listen('.document.rejected', () => this.refreshCount());
				} catch (_) {}
            } else {
                // Retry if Echo not ready
                setTimeout(() => this.setupBroadcasting(), 100);
            }
        },
        
        async refreshCount() {
            try {
                const response = await fetch('/api/completed-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.count !== undefined) {
                    this.count = data.count;
                }
            } catch (error) {
                // Error refreshing completed count
            }
        }
    }
}
</script>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 dark:bg-black/50 z-40 hidden lg:hidden transition-opacity duration-300"></div>
