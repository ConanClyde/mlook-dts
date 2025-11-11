<!-- Appearance Settings -->
<div>
    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-6">Theme Settings</h2>
    
    <div class="space-y-6">
        <!-- Theme Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Theme Preference
            </label>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <!-- Light Mode Option -->
                <button 
                    type="button"
                    onclick="setThemeFromAppearance('light');"
                    class="flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 min-h-[140px] sm:min-h-[180px] border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer transition-all hover:scale-[1.02] bg-white dark:bg-gray-800"
                    id="theme-light-btn"
                >
                    <div class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 bg-gradient-to-br from-yellow-50 to-orange-50 border-2 border-gray-200 rounded-2xl mb-3 sm:mb-4 flex items-center justify-center shadow-lg">
                        <x-icon name="sun" class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 text-yellow-500" />
                    </div>
                    <span class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white mb-1">Light Mode</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center px-2">Clean and bright interface</span>
                </button>
                
                <!-- Dark Mode Option -->
                <button 
                    type="button"
                    onclick="setThemeFromAppearance('dark');"
                    class="flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 min-h-[140px] sm:min-h-[180px] border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer transition-all hover:scale-[1.02] bg-white dark:bg-gray-800"
                    id="theme-dark-btn"
                >
                    <div class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 bg-gradient-to-br from-gray-800 to-gray-900 border-2 border-gray-700 rounded-2xl mb-3 sm:mb-4 flex items-center justify-center shadow-lg">
                        <x-icon name="moon" class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 text-blue-400" />
                    </div>
                    <span class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white mb-1">Dark Mode</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center px-2">Easy on the eyes</span>
                </button>
            </div>
        </div>
        
        <!-- Note -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start">
                <x-icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" />
                <div>
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        Your theme preference is saved automatically and will be applied across all pages.
                    </p>
                </div>
            </div>
        </div>

        <!-- PWA Install Section -->
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="flex items-start flex-1 w-full">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-amber-600 dark:from-orange-600 dark:to-amber-700 rounded-xl flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0 shadow-lg dark:shadow-orange-900/50">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-2">Install as App</h3>
                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 mb-3 sm:mb-4">
                            Install DTS as a Progressive Web App for quick access, offline support, and a native app experience.
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-300 mb-3 sm:mb-4">
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md border border-gray-200 dark:border-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Offline Access
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md border border-gray-200 dark:border-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Push Notifications
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md border border-gray-200 dark:border-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Faster Loading
                            </span>
                        </div>
                        <button id="pwa-install-btn" onclick="installPWA()" 
                                class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-900 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-sm rounded-lg font-medium transition-all shadow-lg dark:shadow-gray-900/50 hover:shadow-xl dark:hover:shadow-gray-900/70 transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Install Now
                        </button>
                        <p id="pwa-installed-msg" class="hidden text-xs sm:text-sm text-green-600 dark:text-green-400 font-medium">
                            ✓ App is already installed!
                        </p>
                        <p id="pwa-not-supported" class="hidden text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            PWA installation is not available on this browser.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Set theme from appearance tab (syncs with navbar toggle)
window.setThemeFromAppearance = function(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
    // Update navbar icons
    if (typeof updateDarkModeIcons === 'function') {
        updateDarkModeIcons();
    }
    // Update appearance tab selection
    updateThemeSelection();
    // Dispatch custom event so navbar can sync (if needed)
    document.dispatchEvent(new CustomEvent('themeChanged'));
}

// Update appearance tab selection (called when theme changes)
function updateThemeSelection() {
    const isDark = document.documentElement.classList.contains('dark');
    const lightBtn = document.getElementById('theme-light-btn');
    const darkBtn = document.getElementById('theme-dark-btn');
    
    if (!lightBtn || !darkBtn) return;
    
    // Remove all border and background classes first to avoid conflicts
    const allBorderClasses = ['border-gray-200', 'dark:border-gray-700', 'border-orange-500'];
    const allBgClasses = ['bg-orange-50', 'dark:bg-orange-900/30'];
    const allShadowClasses = ['shadow-lg'];
    
    // Reset both buttons
    lightBtn.classList.remove(...allBorderClasses, ...allBgClasses, ...allShadowClasses);
    darkBtn.classList.remove(...allBorderClasses, ...allBgClasses, ...allShadowClasses);
    
    // Add default gray borders
    lightBtn.classList.add('border-gray-200', 'dark:border-gray-700');
    darkBtn.classList.add('border-gray-200', 'dark:border-gray-700');
    
    if (isDark) {
        // Dark mode active - highlight dark button
        darkBtn.classList.remove('border-gray-200', 'dark:border-gray-700');
        darkBtn.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/30', 'shadow-lg');
    } else {
        // Light mode active - highlight light button
        lightBtn.classList.remove('border-gray-200', 'dark:border-gray-700');
        lightBtn.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/30', 'shadow-lg');
    }
}

// Listen for theme changes from navbar toggle (custom event)
document.addEventListener('themeChanged', function() {
    // Use the global function from app.js which handles button-based selection
    if (typeof updateAppearanceTabSelection === 'function') {
        updateAppearanceTabSelection();
    } else {
        // Fallback to local function if app.js hasn't loaded yet
        updateThemeSelection();
    }
});

// Also listen for storage changes (backup)
window.addEventListener('storage', function(e) {
    if (e.key === 'theme') {
        if (e.newValue === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        updateThemeSelection();
        if (typeof updateDarkModeIcons === 'function') {
            updateDarkModeIcons();
        }
    }
});

// Apply theme on page load and sync selection
document.addEventListener('DOMContentLoaded', function() {
    // Read current theme state (already initialized by layout)
    const isDark = document.documentElement.classList.contains('dark');
    
    // Ensure localStorage.theme matches current state
    if (isDark) {
        localStorage.theme = 'dark';
    } else {
        localStorage.theme = 'light';
    }
    
    // Immediately update button states to ensure correct initial styling
    updateThemeSelection();
    
    // Also use the global function from app.js if available
    if (typeof updateAppearanceTabSelection === 'function') {
        updateAppearanceTabSelection();
    } else {
        // Wait a bit for app.js to load, then try again
        setTimeout(function() {
            if (typeof updateAppearanceTabSelection === 'function') {
                updateAppearanceTabSelection();
            }
        }, 100);
    }

    // PWA Install Button Logic
    checkPWAInstallStatus();
});

// Check PWA installation status
function checkPWAInstallStatus() {
    const installBtn = document.getElementById('pwa-install-btn');
    const installedMsg = document.getElementById('pwa-installed-msg');
    const notSupported = document.getElementById('pwa-not-supported');
    
    if (!installBtn || !installedMsg || !notSupported) {
        return; // Elements not found
    }
    
    // Check if already installed
    if (window.navigator.standalone || (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)) {
        // Already installed
        installBtn.classList.add('hidden');
        installedMsg.classList.remove('hidden');
        notSupported.classList.add('hidden');
        return;
    }
    
    // Function to show install button
    function showInstallButton() {
        installBtn.classList.remove('hidden');
        notSupported.classList.add('hidden');
        installedMsg.classList.add('hidden');
    }
    
    // Function to hide install button
    function hideInstallButton() {
        installBtn.classList.add('hidden');
        notSupported.classList.remove('hidden');
        installedMsg.classList.add('hidden');
    }
    
    // Check if install prompt is already available (set by pwa.js)
    if (window.deferredPrompt) {
        showInstallButton();
        return;
    }
    
    // Check browser support
    const isChromium = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    const isEdge = /Edg/.test(navigator.userAgent);
    const isOpera = /OPR/.test(navigator.userAgent) || /Opera/.test(navigator.userAgent);
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // On iOS Safari, show instructions
    if (isMobile && isSafari && !isChromium) {
        installBtn.classList.add('hidden');
        installedMsg.classList.add('hidden');
        notSupported.classList.remove('hidden');
        notSupported.textContent = 'On iOS, tap the Share button and select "Add to Home Screen"';
        return;
    }
    
    // For Chromium-based browsers
    if (isChromium || isEdge || isOpera) {
        if (window.deferredPrompt) {
            showInstallButton();
            return;
        }
        
        const beforeInstallHandler = (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            showInstallButton();
        };
        
        window.addEventListener('beforeinstallprompt', beforeInstallHandler);
        
        // Check periodically if deferredPrompt was set
        let checkCount = 0;
        const checkInterval = setInterval(() => {
            checkCount++;
            if (window.deferredPrompt) {
                clearInterval(checkInterval);
                showInstallButton();
                window.removeEventListener('beforeinstallprompt', beforeInstallHandler);
            } else if (checkCount >= 30) { // Wait longer (15 seconds)
                clearInterval(checkInterval);
                
                // Check if service worker is registered
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistration().then(registration => {
                        if (registration) {
                            hideInstallButton();
                            notSupported.innerHTML = 'Installation prompt not available yet. Try:<br>1. Refresh the page<br>2. Check if app is already installed<br>3. Ensure you\'re using Chrome/Edge';
                        } else {
                            hideInstallButton();
                            notSupported.textContent = 'Service worker not registered. Please refresh the page.';
                        }
                    });
                } else {
                    hideInstallButton();
                    notSupported.textContent = 'Service workers not supported.';
                }
                window.removeEventListener('beforeinstallprompt', beforeInstallHandler);
            }
        }, 500);
    } else {
        hideInstallButton();
        const browserName = navigator.userAgent.includes('Firefox') ? 'Firefox' : 
                           navigator.userAgent.includes('Safari') ? 'Safari' : 'this browser';
        notSupported.innerHTML = `PWA installation is not available on ${browserName}.<br>Please use Chrome, Edge, or Opera for the best experience.`;
    }
}

// Install PWA function
window.installPWA = async function() {
    if (!window.deferredPrompt) {
        console.log('Install prompt not available');
        return;
    }
    
    // Show the install prompt
    window.deferredPrompt.prompt();
    
    // Wait for the user to respond to the prompt
    const { outcome } = await window.deferredPrompt.userChoice;
    
    console.log(`User response to the install prompt: ${outcome}`);
    
    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }
    
    // Clear the deferredPrompt
    window.deferredPrompt = null;
    
    // Hide the install button
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
        installBtn.classList.add('hidden');
    }
};

// Listen for app installed event
window.addEventListener('appinstalled', function() {
    const installBtn = document.getElementById('pwa-install-btn');
    const installedMsg = document.getElementById('pwa-installed-msg');
    
    if (installBtn && installedMsg) {
        installBtn.classList.add('hidden');
        installedMsg.classList.remove('hidden');
    }
});
</script>
