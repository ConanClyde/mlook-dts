/**
 * Dashboard Analytics Charts
 * Fetches data from API endpoints and displays charts
 */

// Load charts when dashboard loads
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname === '/dashboard') {
        loadDashboardCharts();
    }
});

async function loadDashboardCharts() {
    try {
        // Load all analytics in parallel
        await Promise.all([
            loadTagAnalytics(),
            loadCategoryAnalytics(),
            loadExpirationAnalytics(),
        ]);
    } catch (error) {
        console.error('Error loading dashboard charts:', error);
    }
}

/**
 * Load tag usage chart
 */
async function loadTagAnalytics() {
    const container = document.getElementById('tag-analytics-chart');
    if (!container) return;

    try {
        const response = await fetch('/dashboard/api/tag-analytics');
        const data = await response.json();

        if (data.success && data.tags.length > 0) {
            const labels = data.tags.map(t => t.name);
            const values = data.tags.map(t => t.usage_count);
            const colors = data.tags.map(t => t.color || '#f97316');

            container.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Tags</h3>
                    <div class="space-y-3">
                        ${data.tags.map((tag, i) => `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: ${tag.color}"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">${tag.name}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">${tag.usage_count}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading tag analytics:', error);
    }
}

/**
 * Load category distribution
 */
async function loadCategoryAnalytics() {
    const container = document.getElementById('category-analytics-chart');
    if (!container) return;

    try {
        const response = await fetch('/dashboard/api/category-analytics');
        const data = await response.json();

        if (data.success && data.categories.length > 0) {
            container.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Categories</h3>
                    <div class="space-y-3">
                        ${data.categories.map(cat => `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: ${cat.color}"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">${cat.name}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">${cat.usage_count}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading category analytics:', error);
    }
}

/**
 * Load expiration analytics
 */
async function loadExpirationAnalytics() {
    const container = document.getElementById('expiration-analytics-chart');
    if (!container) return;

    try {
        const response = await fetch('/dashboard/api/expiration-analytics');
        const data = await response.json();

        if (data.success) {
            const stats = data.stats;
            container.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Document Expiration</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">${stats.expired}</div>
                            <div class="text-xs text-red-600 dark:text-red-400 mt-1">Expired</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${stats.expiring_soon}</div>
                            <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Expiring Soon</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">${stats.expiring_this_month}</div>
                            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">This Month</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">${stats.total_with_expiration}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total</div>
                        </div>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading expiration analytics:', error);
    }
}

// Dashboard charts module loaded
