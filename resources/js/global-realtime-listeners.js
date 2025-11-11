// Global real-time broadcasting listeners
// This file sets up listeners that should be active across all pages

document.addEventListener('DOMContentLoaded', () => {
    // Wait for Echo to initialize
    const initGlobalListeners = () => {
        if (typeof window.Echo === 'undefined' || window.Echo._isDummy) {
            setTimeout(initGlobalListeners, 100);
            return;
        }

        // Listen for tag updates (all authenticated users)
        if (window.Echo && typeof window.Echo.private === 'function') {
            window.Echo.private('tags')
                .listen('.tag.updated', (e) => {
                    // Refresh tag selectors if they exist
                    if (typeof window.refreshTagSelectors === 'function') {
                        window.refreshTagSelectors();
                    }
                });

            // Listen for category updates
            window.Echo.private('categories')
                .listen('.category.updated', (e) => {
                    // Refresh category selectors if they exist
                    if (typeof window.refreshCategorySelectors === 'function') {
                        window.refreshCategorySelectors();
                    }
                });
        }
    };

    // Start initialization
    initGlobalListeners();
});

// Helper function to refresh tag selectors
window.refreshTagSelectors = function() {
    const tagSelects = document.querySelectorAll('select[name="tags[]"], select[name="tags"]');
    if (tagSelects.length > 0) {
        // Reload the page or fetch new tags via AJAX
        console.log('Refreshing tag selectors...');
    }
};

// Helper function to refresh category selectors
window.refreshCategorySelectors = function() {
    const categorySelects = document.querySelectorAll('select[name="category_id"], select[name="categories[]"]');
    if (categorySelects.length > 0) {
        console.log('Refreshing category selectors...');
    }
};
