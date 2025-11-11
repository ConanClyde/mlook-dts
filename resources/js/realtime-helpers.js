/**
 * Real-time Broadcasting Helper Functions
 * Provides utilities for updating UI without page reloads
 */

// Update table row dynamically
window.updateTableRow = function(tableId, rowId, data) {
    const table = document.getElementById(tableId);
    if (!table) return false;
    
    const row = table.querySelector(`[data-id="${rowId}"]`);
    if (!row) return false;
    
    // Update row data attributes
    Object.keys(data).forEach(key => {
        row.setAttribute(`data-${key}`, data[key]);
    });
    
    return true;
};

// Append new row to table
window.appendTableRow = function(tableId, rowHtml) {
    const table = document.getElementById(tableId);
    if (!table) return false;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return false;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = rowHtml.trim();
    const newRow = tempDiv.firstChild;
    
    tbody.insertBefore(newRow, tbody.firstChild);
    return true;
};

// Remove table row
window.removeTableRow = function(tableId, rowId) {
    const table = document.getElementById(tableId);
    if (!table) return false;
    
    const row = table.querySelector(`[data-id="${rowId}"]`);
    if (!row) return false;
    
    row.remove();
    return true;
};

// Update badge count
window.updateBadgeCount = function(badgeId, count) {
    const badge = document.getElementById(badgeId);
    if (!badge) return false;
    
    badge.textContent = count > 99 ? '99+' : count;
    
    if (count > 0) {
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
    
    return true;
};

// Update stat card value
window.updateStatCard = function(cardId, value) {
    const card = document.getElementById(cardId);
    if (!card) return false;
    
    const valueElement = card.querySelector('[data-stat-value]');
    if (!valueElement) return false;
    
    // Animate the change
    const currentValue = parseInt(valueElement.textContent) || 0;
    const targetValue = parseInt(value) || 0;
    
    animateValue(valueElement, currentValue, targetValue, 500);
    
    return true;
};

// Animate number changes
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current);
    }, 16);
}

// Toast notification function removed

// Refresh table data via AJAX
window.refreshTable = async function(tableId, url) {
    try {
        const response = await fetch(url);
        const html = await response.text();
        
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTableBody = doc.querySelector(`#${tableId} tbody`);
        
        if (!newTableBody) return false;
        
        const currentTable = document.getElementById(tableId);
        if (!currentTable) return false;
        
        const currentTBody = currentTable.querySelector('tbody');
        if (currentTBody) {
            currentTBody.innerHTML = newTableBody.innerHTML;
        }
        
        return true;
    } catch (error) {
        console.error('Error refreshing table:', error);
        return false;
    }
};

// Update element content
window.updateElement = function(elementId, content) {
    const element = document.getElementById(elementId);
    if (!element) return false;
    
    element.innerHTML = content;
    return true;
};

// Fetch and update partial
window.updatePartial = async function(elementId, url) {
    try {
        const response = await fetch(url);
        const html = await response.text();
        
        const element = document.getElementById(elementId);
        if (!element) return false;
        
        element.innerHTML = html;
        return true;
    } catch (error) {
        console.error('Error updating partial:', error);
        return false;
    }
};

// Dispatch custom event for Alpine.js components
window.dispatchAlpineEvent = function(eventName, detail = {}) {
    window.dispatchEvent(new CustomEvent(eventName, { detail }));
};

// Update Alpine.js data
window.updateAlpineData = function(componentId, property, value) {
    const component = document.getElementById(componentId);
    if (!component || !component.__x) return false;
    
    const alpineData = component.__x.$data;
    if (alpineData && property in alpineData) {
        alpineData[property] = value;
        return true;
    }
    
    return false;
};

// Reload specific Alpine component
window.reloadAlpineComponent = function(componentId, method) {
    const component = document.getElementById(componentId);
    if (!component || !component.__x) return false;
    
    const alpineData = component.__x.$data;
    if (alpineData && typeof alpineData[method] === 'function') {
        alpineData[method]();
        return true;
    }
    
    return false;
};
