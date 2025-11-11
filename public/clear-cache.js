/**
 * Clear Cache Helper
 * This script helps clear browser cache programmatically
 */

// Clear all caches on page load if version changed
(function() {
    const CURRENT_VERSION = '1.0.0'; // Increment this when you deploy new changes
    const STORAGE_KEY = 'app_version';
    
    const storedVersion = localStorage.getItem(STORAGE_KEY);
    
    if (storedVersion !== CURRENT_VERSION) {
        console.log('🔄 New version detected, clearing cache...');
        
        // Clear localStorage
        localStorage.clear();
        localStorage.setItem(STORAGE_KEY, CURRENT_VERSION);
        
        // Clear sessionStorage
        sessionStorage.clear();
        
        // Clear service worker caches if available
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        }
        
        console.log('✅ Cache cleared! Version:', CURRENT_VERSION);
        
        // Force reload to get fresh assets
        if (storedVersion) {
            window.location.reload(true);
        }
    }
})();
