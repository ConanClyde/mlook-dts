<div x-data="notificationPreferences">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Notification Preferences</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Choose how you want to receive notifications
        </p>
    </div>

    <div class="space-y-4">
        <!-- In-App Notifications -->
        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex-1">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">In-App Notifications</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Get notified within the application</p>
            </div>
            <button type="button" 
                    @click="togglePreference('in_app')"
                    :class="preferences.in_app ? 'bg-orange-500' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <span :class="preferences.in_app ? 'translate-x-5' : 'translate-x-0'"
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>

        <!-- Browser Push Notifications -->
        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex-1">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">Browser Push Notifications</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Get push notifications in your browser</p>
            </div>
            <button type="button" 
                    @click="togglePreference('browser')"
                    :class="preferences.browser ? 'bg-orange-500' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <span :class="preferences.browser ? 'translate-x-5' : 'translate-x-0'"
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationPreferences', () => ({
        saving: false,
        preferences: {
            in_app: {{ (auth()->user()->notification_preferences['in_app'] ?? true) ? 'true' : 'false' }},
            email: {{ (auth()->user()->notification_preferences['email'] ?? true) ? 'true' : 'false' }},
            browser: false, // Will be synced with actual browser permission
        },
        
        init() {
            // Sync browser notification toggle with actual browser permission
            this.syncBrowserPermission();
            
            // Check permission every 2 seconds to keep in sync
            setInterval(() => {
                this.syncBrowserPermission();
            }, 2000);
        },
        
        syncBrowserPermission() {
            if ('Notification' in window) {
                // Set toggle based on actual browser permission
                const isGranted = Notification.permission === 'granted';
                if (this.preferences.browser !== isGranted) {
                    this.preferences.browser = isGranted;
                    
                    // Update database to match browser permission
                    if ({{ (auth()->user()->notification_preferences['browser'] ?? true) ? 'true' : 'false' }} !== isGranted) {
                        this.savePreferencesSilently();
                    }
                }
            }
        },
        
        async savePreferencesSilently() {
            try {
                await fetch('{{ route('profile.notifications.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        preferences: this.preferences
                    })
                });
            } catch (error) {
                console.error('Error syncing preferences:', error);
            }
        },
        
        async togglePreference(type) {
            // Special handling for browser notifications
            if (type === 'browser') {
                if (!this.preferences[type]) {
                    // User is trying to enable browser notifications
                    if ('Notification' in window) {
                        // Check current permission state
                        const currentPermission = Notification.permission;
                        
                        if (currentPermission === 'denied') {
                            // Already blocked, can't request again
                            alert('Browser notifications are currently BLOCKED.\n\n' +
                                  'To enable them:\n' +
                                  '1. Click the lock/info icon (🔒) in your browser address bar\n' +
                                  '2. Find "Notifications" and change it to "Allow"\n' +
                                  '3. The toggle will turn on automatically');
                            return;
                        }
                        
                        // Request permission (only works if 'default' or 'granted')
                        const permission = await Notification.requestPermission();
                        
                        if (permission === 'denied') {
                            alert('Browser notifications were blocked.\n\n' +
                                  'To enable them:\n' +
                                  '1. Click the lock/info icon (🔒) in your browser address bar\n' +
                                  '2. Find "Notifications" and change it to "Allow"\n' +
                                  '3. The toggle will turn on automatically');
                            return;
                        }
                        
                        if (permission !== 'granted') {
                            alert('Browser notification permission was not granted. Please try again.');
                            return;
                        }
                        
                        // Permission granted, sync will update toggle
                        this.syncBrowserPermission();
                        
                        // Show test notification
                        if (Notification.permission === 'granted') {
                            new Notification('Browser Notifications Enabled', {
                                body: 'You will now receive browser notifications from MLOOK DTS',
                                icon: '/images/logo.svg'
                            });
                        }
                        return;
                    } else {
                        alert('Your browser does not support notifications.');
                        return;
                    }
                } else {
                    // User is trying to disable - just inform them
                    alert('To disable browser notifications:\n\n' +
                          '1. Click the lock/info icon (🔒) in your browser address bar\n' +
                          '2. Find "Notifications" and change it to "Block"\n' +
                          '3. The toggle will turn off automatically');
                    return;
                }
            }
            
            // For other notification types, toggle normally
            this.preferences[type] = !this.preferences[type];
            
            // Auto-save
            try {
                const response = await fetch('{{ route('profile.notifications.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        preferences: this.preferences
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show brief success message
                    this.showSuccessMessage('Preferences saved');
                    
                    // Show test notification for browser notifications
                    if (type === 'browser' && this.preferences[type] && 'Notification' in window && Notification.permission === 'granted') {
                        new Notification('Browser Notifications Enabled', {
                            body: 'You will now receive browser notifications from MLOOK DTS',
                            icon: '/images/logo.svg'
                        });
                    }
                }
            } catch (error) {
                console.error('Error saving preferences:', error);
                // Revert the toggle on error
                this.preferences[type] = !this.preferences[type];
                alert('Failed to save preferences. Please try again.');
            }
        },
        
        showSuccessMessage(message) {
            // Create a temporary success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 2000);
        }
    }));
});
</script>
