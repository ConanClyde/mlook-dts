@auth
@php
    $csrfToken = csrf_token();
@endphp
<div class="relative" x-data="{ 
    open: false, 
    notifications: [], 
    unreadCount: 0,
    csrfToken: '{{ $csrfToken }}',
	refreshTimeout: null,
	scheduleRefresh(delay = 50) {
		if (this.refreshTimeout) {
			clearTimeout(this.refreshTimeout);
			this.refreshTimeout = null;
		}
		this.refreshTimeout = setTimeout(() => {
			this.loadNotifications();
			this.loadUnreadCount();
			this.refreshTimeout = null;
		}, delay);
	},
    async loadNotifications() {
        try {
            const response = await fetch('/notifications?limit=10');
            const data = await response.json();
            this.notifications = data;
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },
    async loadUnreadCount() {
        try {
            const response = await fetch('/notifications/count');
            const data = await response.json();
            this.unreadCount = data.count;
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    },
    handleNotificationClick(notification, event) {
        // Don't navigate if clicking on action buttons
        if (event && (event.target.closest('.notification-action') || event.target.closest('button'))) {
            return;
        }
        
        // Close dropdown
        this.open = false;
        
        // Mark as read
        if (!notification.read) {
            fetch(`/notifications/${notification.id}/read`, { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': this.csrfToken } 
            })
            .then(() => { 
                notification.read = true; 
                if (this.unreadCount > 0) this.unreadCount--; 
            });
        }
        
        // Handle navigation based on notification type
        if (notification.link) {
            // Check if notification has special data for modal opening
            if (notification.data) {
                const url = new URL(notification.link, window.location.origin);
                
                // Handle user-related notifications (user_registered, user_updated)
                if (notification.data.user_id) {
                    url.searchParams.set('view_user', notification.data.user_id);
                }
                
                // Handle document-related notifications
                if (notification.data.document_id) {
                    url.searchParams.set('document_id', notification.data.document_id);
                }
                
                // Handle tracking-related notifications
                if (notification.data.tracking_id) {
                    url.searchParams.set('tracking_id', notification.data.tracking_id);
                }
                
                window.location.href = url.toString();
            } else {
                // Just navigate to the link
                window.location.href = notification.link;
            }
        }
    },
    async markAsRead(notification, event) {
        event.stopPropagation();
        if (!notification.read) {
            const response = await fetch(`/notifications/${notification.id}/read`, { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': this.csrfToken } 
            });
            if (response.ok) {
                notification.read = true;
                if (this.unreadCount > 0) this.unreadCount--;
            }
        }
    },
    async deleteNotification(notification, event) {
        event.stopPropagation();
        const response = await fetch(`/notifications/${notification.id}`, { 
            method: 'DELETE', 
            headers: { 'X-CSRF-TOKEN': this.csrfToken } 
        });
        if (response.ok) {
            this.notifications = this.notifications.filter(n => n.id !== notification.id);
            if (!notification.read && this.unreadCount > 0) this.unreadCount--;
        }
    },
    async clearAllRead() {
        const response = await fetch('/notifications/clear-all', { 
            method: 'POST', 
            headers: { 'X-CSRF-TOKEN': this.csrfToken } 
        });
        if (response.ok) {
            this.notifications = this.notifications.filter(n => !n.read);
        }
    }
}" x-init="
    loadNotifications();
    loadUnreadCount();
    
    // Request browser notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    
    // Listen for real-time notifications
    if (window.Echo) {
        // Listen to user's personal channel for notifications
        window.Echo.private('App.Models.User.{{ auth()->id() }}')
			.subscribed(() => {
				// could set a UI indicator if desired
			})
			.error(() => {
				// fallback polling will continue
			})
			.listen('.notification.created', (e) => {
                // Add new notification to the list
                notifications.unshift(e);
                // Update unread count immediately (no API call needed)
                if (!e.read) {
                    unreadCount++;
                }
				// Optional: reconcile with server after a delay
				scheduleRefresh(2000);
                
                // Show browser notification if permission granted
                if ('Notification' in window && Notification.permission === 'granted') {
                    const notification = new Notification(e.title, {
                        body: e.message,
                        icon: '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: 'notification-' + e.id,
                        requireInteraction: false,
                        data: {
                            url: e.link,
                            notification_id: e.id
                        }
                    });
                    
                    notification.onclick = function(event) {
                        event.preventDefault();
                        window.focus();
                        if (e.link) {
                            window.location.href = e.link;
                        }
                        notification.close();
                    };
                }
            })
            .listen('.notification.read', (e) => {
                // Update notification as read in the list
                const notif = notifications.find(n => n.id === e.notification_id);
                if (notif && !notif.read) {
                    notif.read = true;
                    if (unreadCount > 0) unreadCount--;
                }
            });
        
        // Also listen to admin notifications if user is admin
        @if(auth()->user()->isAdmin())
        window.Echo.private('admin.notifications')
			.subscribed(() => {})
			.error(() => {})
			.listen('.notification.created', (e) => {
                // Add new notification to the list
                notifications.unshift(e);
                // Update unread count immediately (no API call needed)
                if (!e.read) {
                    unreadCount++;
                }
				// Optional: reconcile with server after a delay
				scheduleRefresh(2000);
                
                // Show browser notification if permission granted
                if ('Notification' in window && Notification.permission === 'granted') {
                    const notification = new Notification(e.title, {
                        body: e.message,
                        icon: '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: 'notification-' + e.id,
                        requireInteraction: false,
                        data: {
                            url: e.link,
                            notification_id: e.id
                        }
                    });
                    
                    notification.onclick = function(event) {
                        event.preventDefault();
                        window.focus();
                        if (e.link) {
                            window.location.href = e.link;
                        }
                        notification.close();
                    };
                }
            });
        @endif
    }
    
    // Refresh every 30 seconds (fallback if websocket fails)
    setInterval(() => {
        loadNotifications();
        loadUnreadCount();
    }, 30000);
">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open"
        class="relative p-2 min-w-[36px] min-h-[36px] rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition flex items-center justify-center"
        aria-label="Notifications"
    >
        <x-icon name="bell" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
        <!-- Unread Badge -->
        <span 
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 flex items-center justify-center text-[10px] font-bold leading-none text-white bg-red-500 rounded-full w-[1.125rem] h-[1.125rem] border-2 border-white dark:border-gray-900"
        ></span>
    </button>

    <!-- Notification Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed sm:absolute right-2 sm:right-0 top-[3.5rem] sm:top-auto sm:mt-2 w-[calc(100vw-1rem)] sm:w-80 lg:w-96 max-w-[calc(100vw-1rem)] sm:max-w-none bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-[calc(100vh-5rem)] sm:max-h-[32rem] overflow-hidden flex flex-col"
        style="display: none;"
    >
        <!-- Header -->
        <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2 flex-shrink-0">
            <h3 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white flex-shrink-0">Notifications</h3>
            <div class="flex gap-1 sm:gap-2">
                <button 
                    @click="
                        fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } })
                            .then(() => { 
                                notifications.forEach(n => n.read = true); 
                                unreadCount = 0;
                            });
                    "
                    class="text-xs sm:text-sm text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 min-h-[44px] px-2 sm:px-3 py-1.5 flex items-center justify-center whitespace-nowrap flex-shrink-0"
                    x-show="unreadCount > 0"
                    title="Mark all as read"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="hidden sm:inline ml-1">Mark all</span>
                </button>
                <button 
                    @click="clearAllRead()"
                    class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 min-h-[44px] px-2 sm:px-3 py-1.5 flex items-center justify-center whitespace-nowrap flex-shrink-0"
                    x-show="notifications.some(n => n.read)"
                    title="Clear all read notifications"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="hidden sm:inline ml-1">Clear</span>
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center">
                    <x-icon name="inbox" class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div
                    @click="handleNotificationClick(notification, $event)"
                    class="block px-3 sm:px-4 py-2.5 sm:py-3 min-h-[44px] hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition cursor-pointer group"
                    :class="notification.read ? '' : 'bg-orange-50 dark:bg-orange-900/20'"
                >
                    <div class="flex items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white break-words" x-text="notification.title"></p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 break-words" x-text="notification.message"></p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500" x-text="new Date(notification.created_at).toLocaleString()"></p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button 
                                x-show="!notification.read"
                                @click="markAsRead(notification, $event)"
                                class="notification-action p-1.5 min-w-[32px] min-h-[32px] text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded transition opacity-0 group-hover:opacity-100"
                                title="Mark as read"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                            <button 
                                @click="deleteNotification(notification, $event)"
                                class="notification-action p-1.5 min-w-[32px] min-h-[32px] text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded transition opacity-0 group-hover:opacity-100"
                                title="Delete notification"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <span 
                                x-show="!notification.read"
                                class="w-2 h-2 bg-orange-500 rounded-full"
                            ></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-t border-gray-200 dark:border-gray-700 text-center">
            <button @click="open = false" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white min-h-[44px] inline-flex items-center justify-center px-3 py-2 break-words w-full">
                Close
            </button>
        </div>
    </div>
</div>
@endauth

