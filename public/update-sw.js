/**
 * Force Service Worker Update
 * Run this to clear old service worker cache
 */

(async function() {
    if ('serviceWorker' in navigator) {
        try {
            // Get all registrations
            const registrations = await navigator.serviceWorker.getRegistrations();
            
            console.log('🔄 Unregistering old service workers...');
            
            // Unregister all service workers
            for (let registration of registrations) {
                await registration.unregister();
                console.log('✅ Unregistered:', registration.scope);
            }
            
            // Clear all caches
            if ('caches' in window) {
                const cacheNames = await caches.keys();
                console.log('🗑️ Clearing caches:', cacheNames);
                
                for (let cacheName of cacheNames) {
                    await caches.delete(cacheName);
                    console.log('✅ Deleted cache:', cacheName);
                }
            }
            
            console.log('✅ Service worker and caches cleared!');
            console.log('🔄 Reloading page to register new service worker...');
            
            // Wait a bit then reload
            setTimeout(() => {
                window.location.reload(true);
            }, 1000);
            
        } catch (error) {
            console.error('❌ Error updating service worker:', error);
        }
    }
})();
