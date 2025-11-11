import './bootstrap';
import './file-upload';
import './global-realtime-listeners';

// Dark Mode Toggle Functionality
window.toggleDarkMode = function() {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    }
    updateDarkModeIcons();
    updateAppearanceTabSelection();
    // Dispatch custom event for appearance tab listeners
    document.dispatchEvent(new CustomEvent('themeChanged'));
}

window.updateDarkModeIcons = function() {
    const darkIcons = document.querySelectorAll('.theme-toggle-dark-icon');
    const lightIcons = document.querySelectorAll('.theme-toggle-light-icon');
    
    if (document.documentElement.classList.contains('dark')) {
        darkIcons.forEach(icon => icon.classList.remove('hidden'));
        lightIcons.forEach(icon => icon.classList.add('hidden'));
    } else {
        darkIcons.forEach(icon => icon.classList.add('hidden'));
        lightIcons.forEach(icon => icon.classList.remove('hidden'));
    }
}

// Update appearance tab selection when theme changes
// This function handles both button-based (new) and radio-based (old) appearance tabs
window.updateAppearanceTabSelection = function() {
    const isDark = document.documentElement.classList.contains('dark');
    
    // Try button-based selection first (new appearance tab)
    const lightBtn = document.getElementById('theme-light-btn');
    const darkBtn = document.getElementById('theme-dark-btn');
    
    if (lightBtn && darkBtn) {
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
        return; // Exit early if buttons found
    }
    
    // Fallback to radio-based selection (old appearance tab - if it exists)
    const lightLabel = document.getElementById('theme-light-label');
    const darkLabel = document.getElementById('theme-dark-label');
    const lightRadio = document.getElementById('theme-light');
    const darkRadio = document.getElementById('theme-dark');
    
    if (lightLabel && darkLabel && lightRadio && darkRadio) {
        if (isDark) {
            darkLabel.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
            darkLabel.classList.remove('border-gray-200', 'dark:border-gray-700');
            lightLabel.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
            lightLabel.classList.add('border-gray-200', 'dark:border-gray-700');
            darkRadio.checked = true;
        } else {
            lightLabel.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
            lightLabel.classList.remove('border-gray-200', 'dark:border-gray-700');
            darkLabel.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
            darkLabel.classList.add('border-gray-200', 'dark:border-gray-700');
            lightRadio.checked = true;
        }
    }
}

// Global helper to safely subscribe to Echo channels
// This ensures channels are properly subscribed even after page navigation
window.safeEchoSubscribe = function(channelType, channelName, eventName, callback) {
    if (!window.Echo || window.Echo._isDummy) {
        return null;
    }
    
    try {
        let channel;
        if (channelType === 'private') {
            channel = window.Echo.private(channelName);
        } else if (channelType === 'channel') {
            channel = window.Echo.channel(channelName);
        } else {
            return null;
        }
        
        return channel.listen(eventName, callback);
    } catch (error) {
        console.error(`Failed to subscribe to ${channelName}:`, error);
        return null;
    }
};

// Initialize icons on page load
document.addEventListener('DOMContentLoaded', () => {
    updateDarkModeIcons();
    initCursorGlow();
    
    // Ensure Echo is available globally after initialization
    if (window.Echo) {
        // Laravel Echo initialized
    }
});

// Cursor-following glow effect
function initCursorGlow() {
    const heroSection = document.getElementById('hero-section');
    const glow = document.getElementById('cursor-glow');
    const dashboardPreview = document.querySelector('#hero-section .relative.mx-auto');
    
    if (!heroSection || !glow) return;
    
    // Function to get center position of dashboard preview (lower position)
    function getCenterPosition() {
        if (!dashboardPreview) {
            // Fallback to hero section center
            const heroRect = heroSection.getBoundingClientRect();
            return {
                x: heroRect.width / 2,
                y: heroRect.height / 2 + 200
            };
        }
        
        const heroRect = heroSection.getBoundingClientRect();
        const dashboardRect = dashboardPreview.getBoundingClientRect();
        
        return {
            x: dashboardRect.left - heroRect.left + dashboardRect.width / 2,
            y: dashboardRect.top - heroRect.top + dashboardRect.height / 2 + 200
        };
    }
    
    // Set initial position to center of dashboard preview
    const centerPos = getCenterPosition();
    glow.style.left = centerPos.x + 'px';
    glow.style.top = centerPos.y + 'px';
    
    // Track cursor movement
    heroSection.addEventListener('mousemove', (e) => {
        const rect = heroSection.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        glow.style.left = x + 'px';
        glow.style.top = y + 'px';
    });
    
    // Return to center when cursor leaves the hero section
    heroSection.addEventListener('mouseleave', () => {
        const centerPos = getCenterPosition();
        glow.style.left = centerPos.x + 'px';
        glow.style.top = centerPos.y + 'px';
    });
    
    // Recalculate center position on window resize
    window.addEventListener('resize', () => {
        // Only update if cursor is not in hero section
        if (!heroSection.matches(':hover')) {
            const centerPos = getCenterPosition();
            glow.style.left = centerPos.x + 'px';
            glow.style.top = centerPos.y + 'px';
        }
    });
}
