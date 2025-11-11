/**
 * Keyboard Shortcuts for DTS
 * Global keyboard shortcuts for quick navigation and actions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Command palette state
    let commandPaletteOpen = false;

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ignore if user is typing in input/textarea
        if (e.target.tagName === 'INPUT' || 
            e.target.tagName === 'TEXTAREA' || 
            e.target.isContentEditable) {
            return;
        }

        // Ctrl/Cmd + K - Open command palette / Quick search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openCommandPalette();
        }

        // Ctrl/Cmd + N - Create new document
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const createBtn = document.querySelector('[href*="documents/create"]');
            if (createBtn) {
                window.location.href = createBtn.href;
            }
        }

        // Ctrl/Cmd + / - Show shortcuts help
        if ((e.ctrlKey || e.metaKey) && e.key === '/') {
            e.preventDefault();
            showShortcutsHelp();
        }

        // Escape - Close modals/command palette
        if (e.key === 'Escape') {
            closeCommandPalette();
            closeAllModals();
        }

        // G then D - Go to Dashboard
        if (e.key === 'g') {
            setTimeout(() => {
                document.addEventListener('keydown', function handler(e2) {
                    if (e2.key === 'd') {
                        window.location.href = '/dashboard';
                    }
                    document.removeEventListener('keydown', handler);
                }, { once: true });
            }, 0);
        }

        // G then I - Go to Inbox
        if (e.key === 'g') {
            setTimeout(() => {
                document.addEventListener('keydown', function handler(e2) {
                    if (e2.key === 'i') {
                        window.location.href = '/inbox';
                    }
                    document.removeEventListener('keydown', handler);
                }, { once: true });
            }, 0);
        }

        // G then S - Go to Search
        if (e.key === 'g') {
            setTimeout(() => {
                document.addEventListener('keydown', function handler(e2) {
                    if (e2.key === 's') {
                        window.location.href = '/search';
                    }
                    document.removeEventListener('keydown', handler);
                }, { once: true });
            }, 0);
        }
    });

    // Command Palette
    function openCommandPalette() {
        if (commandPaletteOpen) return;

        const palette = document.createElement('div');
        palette.id = 'command-palette';
        palette.className = 'fixed inset-0 z-50 flex items-start justify-center pt-20 bg-black bg-opacity-50';
        palette.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-2xl mx-4">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" 
                           id="command-search" 
                           placeholder="Type a command or search..." 
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg focus:ring-2 focus:ring-orange-500 dark:text-white"
                           autocomplete="off">
                </div>
                <div id="command-results" class="max-h-96 overflow-y-auto p-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400 px-4 py-2">Quick Actions</div>
                    <a href="/documents/create" class="command-item flex items-center gap-3 px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Create Document</div>
                            <div class="text-xs text-gray-500">Ctrl+N</div>
                        </div>
                    </a>
                    <a href="/dashboard" class="command-item flex items-center gap-3 px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Dashboard</div>
                            <div class="text-xs text-gray-500">G then D</div>
                        </div>
                    </a>
                    <a href="/inbox" class="command-item flex items-center gap-3 px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Inbox</div>
                            <div class="text-xs text-gray-500">G then I</div>
                        </div>
                    </a>
                    <a href="/search" class="command-item flex items-center gap-3 px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Search Documents</div>
                            <div class="text-xs text-gray-500">G then S</div>
                        </div>
                    </a>
                </div>
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>Press <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">ESC</kbd> to close</span>
                    <span>Press <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+/</kbd> for help</span>
                </div>
            </div>
        `;

        document.body.appendChild(palette);
        commandPaletteOpen = true;

        // Focus search input
        const searchInput = document.getElementById('command-search');
        searchInput.focus();

        // Search functionality
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.command-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Close on background click
        palette.addEventListener('click', function(e) {
            if (e.target === palette) {
                closeCommandPalette();
            }
        });
    }

    function closeCommandPalette() {
        const palette = document.getElementById('command-palette');
        if (palette) {
            palette.remove();
            commandPaletteOpen = false;
        }
    }

    function closeAllModals() {
        // Close any open modals
        const modals = document.querySelectorAll('[x-show]');
        modals.forEach(modal => {
            if (modal.__x) {
                // Alpine.js modals
                const data = modal.__x.$data;
                Object.keys(data).forEach(key => {
                    if (key.includes('show') || key.includes('modal') || key.includes('open')) {
                        data[key] = false;
                    }
                });
            }
        });
    }

    function showShortcutsHelp() {
        const helpModal = document.createElement('div');
        helpModal.id = 'shortcuts-help';
        helpModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
        helpModal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Keyboard Shortcuts</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Navigation</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Dashboard</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">G then D</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Inbox</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">G then I</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Search</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">G then S</kbd>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Actions</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Command Palette</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">Ctrl+K</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">New Document</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">Ctrl+N</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Close Modal</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">ESC</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button onclick="document.getElementById('shortcuts-help').remove()" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg">
                        Close
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(helpModal);

        // Close on background click
        helpModal.addEventListener('click', function(e) {
            if (e.target === helpModal) {
                helpModal.remove();
            }
        });
    }

    // Add keyboard shortcut indicator to UI
    const shortcutIndicator = document.createElement('div');
    shortcutIndicator.className = 'fixed bottom-4 left-4 z-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg px-3 py-2 text-xs text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
    shortcutIndicator.innerHTML = 'Press <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">Ctrl+/</kbd> for shortcuts';
    document.body.appendChild(shortcutIndicator);

    // Hide after 5 seconds
    setTimeout(() => {
        shortcutIndicator.style.opacity = '0';
        shortcutIndicator.style.transition = 'opacity 0.3s';
        setTimeout(() => shortcutIndicator.remove(), 300);
    }, 5000);
});

// Keyboard shortcuts initialized
