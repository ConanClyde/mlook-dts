/**
 * Progressive Web App (PWA) functionality
 * Service Worker registration and PWA features
 */

// Check if service workers are supported
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        registerServiceWorker();
    });
}

/**
 * Register service worker
 */
async function registerServiceWorker() {
    try {
        const registration = await navigator.serviceWorker.register('/service-worker.js', {
            scope: '/'
        });
        
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            
            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // New service worker available
                    showUpdateNotification();
                }
            });
        });
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            requestNotificationPermission();
        }
        
    } catch (error) {
        console.error('❌ Service Worker registration failed:', error);
    }
}

/**
 * Show update notification
 */
function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-orange-500 text-white px-6 py-4 rounded-lg shadow-2xl z-50 max-w-sm';
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="font-semibold mb-1">Update Available</p>
                <p class="text-sm opacity-90">A new version is ready. Refresh to update.</p>
            </div>
            <button onclick="location.reload()" class="ml-4 px-4 py-2 bg-white text-orange-500 rounded font-medium hover:bg-gray-100">
                Refresh
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        notification.remove();
    }, 10000);
}

/**
 * Request notification permission
 */
async function requestNotificationPermission() {
    try {
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            subscribeToNotifications();
        } else {
        }
    } catch (error) {
        console.error('Error requesting notification permission:', error);
    }
}

/**
 * Subscribe to push notifications
 */
async function subscribeToNotifications() {
    try {
        const registration = await navigator.serviceWorker.ready;
        
        // Check if already subscribed
        let subscription = await registration.pushManager.getSubscription();
        
        if (!subscription) {
            // Subscribe to push notifications
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(window.vapidPublicKey || '')
            });
            
            // Send subscription to server
            await fetch('/api/push-subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(subscription)
            });
            
        }
    } catch (error) {
        console.error('❌ Push notification subscription failed:', error);
    }
}

/**
 * Convert VAPID key
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    
    return outputArray;
}

/**
 * Install prompt
 */
window.deferredPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    
    // Stash the event so it can be triggered later
    window.deferredPrompt = e;
    
    // Show install button
    showInstallPrompt();
});

/**
 * Show install prompt
 */
function showInstallPrompt() {
    const installBanner = document.createElement('div');
    installBanner.id = 'install-banner';
    installBanner.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-sm bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-4 rounded-lg shadow-2xl z-50';
    installBanner.innerHTML = `
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <p class="font-bold">Install DTS App</p>
                </div>
                <p class="text-sm opacity-90 mb-3">Install our app for quick access and offline support</p>
                <div class="flex gap-2">
                    <button onclick="installPWA()" class="px-4 py-2 bg-white text-orange-500 rounded font-medium hover:bg-gray-100 transition-colors">
                        Install
                    </button>
                    <button onclick="dismissInstallPrompt()" class="px-4 py-2 bg-orange-700 hover:bg-orange-800 rounded font-medium transition-colors">
                        Not Now
                    </button>
                </div>
            </div>
            <button onclick="dismissInstallPrompt()" class="ml-2 text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(installBanner);
}

/**
 * Install PWA
 */
window.installPWA = async function() {
    if (!window.deferredPrompt) {
        return;
    }
    
    // Show the install prompt
    window.deferredPrompt.prompt();
    
    // Wait for the user to respond
    const { outcome } = await window.deferredPrompt.userChoice;
    
    
    // Clear the deferred prompt
    window.deferredPrompt = null;
    
    // Remove install banner
    dismissInstallPrompt();
};

/**
 * Dismiss install prompt
 */
window.dismissInstallPrompt = function() {
    const banner = document.getElementById('install-banner');
    if (banner) {
        banner.remove();
    }
    
    // Store dismissal in localStorage
    localStorage.setItem('pwa-install-dismissed', Date.now());
};

/**
 * Check if app is installed
 */
window.addEventListener('appinstalled', () => {
    window.deferredPrompt = null;
});

/**
 * Detect if running as PWA
 */
function isPWA() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
}

if (isPWA()) {
    document.body.classList.add('pwa-mode');
}

/**
 * Online/Offline detection
 */
window.addEventListener('online', () => {
    showConnectionStatus('online');
});

window.addEventListener('offline', () => {
    showConnectionStatus('offline');
});

function showConnectionStatus(status) {
    const existing = document.getElementById('connection-status');
    if (existing) existing.remove();
    
    const statusBar = document.createElement('div');
    statusBar.id = 'connection-status';
    statusBar.className = `fixed top-0 left-0 right-0 z-50 px-4 py-2 text-center text-white ${
        status === 'online' ? 'bg-green-500' : 'bg-red-500'
    }`;
    statusBar.textContent = status === 'online' ? '✓ Back Online' : '✗ No Internet Connection';
    
    document.body.appendChild(statusBar);
    
    if (status === 'online') {
        setTimeout(() => statusBar.remove(), 3000);
    }
}

// PWA module loaded
