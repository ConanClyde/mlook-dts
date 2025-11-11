const CACHE_NAME = 'dts-v2'; // Increment version to force cache refresh
const urlsToCache = [
    '/',
    '/dashboard',
    '/offline.html',
    // DO NOT cache JS/CSS - let Vite handle with hash-based filenames
    // '/css/app.css',
    // '/js/app.js',
    '/images/icon-192x192.png',
    '/images/icon-512x512.png',
];

// Install event - cache resources
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
            .catch((error) => {
                console.error('[Service Worker] Cache failed:', error);
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Skip POST requests and API calls
    if (event.request.method !== 'GET' || event.request.url.includes('/api/')) {
        return;
    }

    // NEVER cache JS/CSS files - always fetch fresh from network
    if (event.request.url.includes('.js') || event.request.url.includes('.css') || event.request.url.includes('/build/')) {
        event.respondWith(fetch(event.request));
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    return response;
                }

                // Clone the request
                const fetchRequest = event.request.clone();

                return fetch(fetchRequest).then((response) => {
                    // Check if valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // Clone the response
                    const responseToCache = response.clone();

                    // Only cache images and static assets (not JS/CSS)
                    if (!event.request.url.includes('.js') && !event.request.url.includes('.css')) {
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                    }

                    return response;
                }).catch((error) => {
                    console.error('[Service Worker] Fetch failed:', error);
                    
                    // Return offline page if available
                    return caches.match('/offline.html');
                });
            })
    );
});

// Push notification event
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'DTS Notification';
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-96x96.png',
        tag: data.tag || 'dts-notification',
        data: data.data || {},
        actions: [
            {
                action: 'view',
                title: 'View',
                icon: '/images/icon-96x96.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/images/icon-96x96.png'
            }
        ],
        vibrate: [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'view') {
        const urlToOpen = event.notification.data.url || '/dashboard';
        
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then((clientList) => {
                    // Check if there's already a window open
                    for (let client of clientList) {
                        if (client.url === urlToOpen && 'focus' in client) {
                            return client.focus();
                        }
                    }
                    // Open new window
                    if (clients.openWindow) {
                        return clients.openWindow(urlToOpen);
                    }
                })
        );
    }
});

// Background sync event
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-documents') {
        event.waitUntil(syncDocuments());
    }
});

async function syncDocuments() {
    try {
        // Get pending sync data from IndexedDB
        const db = await openDatabase();
        const pendingData = await getPendingSync(db);
        
        // Sync each pending item
        for (const item of pendingData) {
            await fetch(item.url, {
                method: item.method,
                headers: item.headers,
                body: item.body,
            });
            
            // Remove from pending after successful sync
            await removePendingSync(db, item.id);
        }
    } catch (error) {
        console.error('[Service Worker] Sync failed:', error);
    }
}

// Helper functions for IndexedDB
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('DTS_DB', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pendingSync')) {
                db.createObjectStore('pendingSync', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getPendingSync(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingSync'], 'readonly');
        const store = transaction.objectStore('pendingSync');
        const request = store.getAll();
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function removePendingSync(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingSync'], 'readwrite');
        const store = transaction.objectStore('pendingSync');
        const request = store.delete(id);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}
