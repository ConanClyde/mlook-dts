/**
 * Admin Real-time Helper Functions
 * Provides utilities for admin pages to update without reloads
 */

// Refresh any admin table
window.refreshAdminTable = async function(tableId, url) {
    try {
        const response = await fetch(url || window.location.href);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const newTable = doc.querySelector(`#${tableId}`);
        const currentTable = document.getElementById(tableId);
        
        if (newTable && currentTable) {
            currentTable.innerHTML = newTable.innerHTML;
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error refreshing admin table:', error);
        return false;
    }
};

// Refresh users table
window.refreshUserTable = async function() {
    return await refreshAdminTable('users-table', '/admin/users');
};

// Refresh departments table
window.refreshDepartmentsTable = async function() {
    return await refreshAdminTable('departments-table', window.location.href);
};

// Refresh programs table
window.refreshProgramsTable = async function() {
    return await refreshAdminTable('programs-table', window.location.href);
};

// Refresh tags table
window.refreshTagsTable = async function() {
    return await refreshAdminTable('tags-table', window.location.href);
};

// Refresh document types table
window.refreshDocumentTypesTable = async function() {
    return await refreshAdminTable('document-types-table', window.location.href);
};

// Refresh routing rules table
window.refreshRoutingRulesTable = async function() {
    return await refreshAdminTable('routing-rules-table', window.location.href);
};

// Refresh templates table
window.refreshTemplatesTable = async function() {
    return await refreshAdminTable('templates-table', window.location.href);
};

// Refresh permissions table
window.refreshPermissionsTable = async function() {
    return await refreshAdminTable('permissions-table', window.location.href);
};

// Refresh audit logs table
window.refreshAuditLogsTable = async function() {
    return await refreshAdminTable('audit-logs-table', '/admin/audit-logs');
};

// Refresh backups list
window.refreshBackupsList = async function() {
    return await refreshAdminTable('backups-list', '/admin/backups');
};

// Update dashboard statistics
window.updateDashboardStats = async function() {
    try {
        const response = await fetch('/dashboard/stats');
        const data = await response.json();
        
        if (data.success) {
            // Update stat cards
            Object.keys(data.stats).forEach(key => {
                updateStatCard(key, data.stats[key]);
            });
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error updating dashboard stats:', error);
        return false;
    }
};

// Update dashboard charts
window.updateDashboardCharts = async function() {
    try {
        const response = await fetch('/dashboard/charts');
        const data = await response.json();
        
        if (data.success && window.Chart) {
            // Update charts if they exist
            Object.keys(data.charts).forEach(chartId => {
                const chartElement = document.getElementById(chartId);
                if (chartElement && chartElement.chart) {
                    chartElement.chart.data = data.charts[chartId];
                    chartElement.chart.update();
                }
            });
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error updating dashboard charts:', error);
        return false;
    }
};

// Setup broadcasting listeners for all user types
window.setupAdminBroadcasting = function() {
    if (!window.Echo || window.Echo._isDummy) {
        return;
    }
    
    // Get user info from meta tags or body data
    const userRole = document.body.dataset.userRole || 
                     document.querySelector('meta[name="user-role"]')?.content;
    const hasAdminPrivileges = ['admin', 'registrar', 'dean', 'department_head'].includes(userRole);
    
    try {
		// Tags updates - available to all authenticated users; refresh tags table on admin page
		window.Echo.private('tags')
			.listen('.tag.updated', (e) => {
				console.log('Tag event received:', e);
				if (typeof refreshTagsTable === 'function') {
					refreshTagsTable();
				}
				// Also refresh selectors if global helper exists
				if (typeof window.refreshTagSelectors === 'function') {
					window.refreshTagSelectors();
				}
			});
		
        // Admin users channel - Only for users with admin privileges
        if (hasAdminPrivileges) {
            window.Echo.private('admin.users')
                .listen('.user.created', (e) => {
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                })
                .listen('.user.updated', (e) => {
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                })
                .listen('.user.deleted', (e) => {
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                });
        }
        
        // Admin settings channel - Only for users with admin privileges
        if (hasAdminPrivileges) {
            window.Echo.private('admin.settings')
                .listen('.department.created', (e) => {
                    if (typeof refreshDepartmentsTable === 'function') {
                        refreshDepartmentsTable();
                    }
                })
                .listen('.department.updated', (e) => {
                    if (typeof refreshDepartmentsTable === 'function') {
                        refreshDepartmentsTable();
                    }
                })
                .listen('.department.deleted', (e) => {
                    if (typeof refreshDepartmentsTable === 'function') {
                        refreshDepartmentsTable();
                    }
                })
                .listen('.program.created', (e) => {
                    if (typeof refreshProgramsTable === 'function') {
                        refreshProgramsTable();
                    }
                })
                .listen('.program.updated', (e) => {
                    if (typeof refreshProgramsTable === 'function') {
                        refreshProgramsTable();
                    }
                })
                .listen('.program.deleted', (e) => {
                    if (typeof refreshProgramsTable === 'function') {
                        refreshProgramsTable();
                    }
                })
                .listen('.document-type.created', (e) => {
                    if (typeof refreshDocumentTypesTable === 'function') {
                        refreshDocumentTypesTable();
                    }
                })
                .listen('.document-type.updated', (e) => {
                    if (typeof refreshDocumentTypesTable === 'function') {
                        refreshDocumentTypesTable();
                    }
                })
                .listen('.document-type.deleted', (e) => {
                    if (typeof refreshDocumentTypesTable === 'function') {
                        refreshDocumentTypesTable();
                    }
                })
                .listen('.routing-rule.created', (e) => {
                    if (typeof refreshRoutingRulesTable === 'function') {
                        refreshRoutingRulesTable();
                    }
                })
                .listen('.routing-rule.updated', (e) => {
                    if (typeof refreshRoutingRulesTable === 'function') {
                        refreshRoutingRulesTable();
                    }
                })
                .listen('.routing-rule.deleted', (e) => {
                    if (typeof refreshRoutingRulesTable === 'function') {
                        refreshRoutingRulesTable();
                    }
                });
        }
    } catch (error) {
        console.error('❌ Error setting up broadcasting:', error);
    }
};

// Auto-setup on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAdminBroadcasting);
} else {
    setupAdminBroadcasting();
}
