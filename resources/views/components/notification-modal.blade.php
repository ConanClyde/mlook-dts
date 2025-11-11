<!-- Notification Modal -->
<div id="notificationModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-[60] hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="notificationTitle" class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Notification</h3>
            <button onclick="closeNotificationModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex items-start gap-3">
                <div id="notificationIcon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                    <!-- Icon will be injected -->
                </div>
                <div class="flex-1">
                    <p id="notificationMessage" class="text-sm text-gray-700 dark:text-gray-300"></p>
                </div>
            </div>
        </div>
        
        <div class="flex gap-3 px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <button id="notificationOkButton" onclick="closeNotificationModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-[60] hidden items-center justify-center p-4" onclick="if(event.target === this) closeConfirmationModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="confirmationTitle" class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Confirm Action</h3>
            <button onclick="closeConfirmationModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p id="confirmationMessage" class="text-sm text-gray-700 dark:text-gray-300"></p>
                </div>
            </div>
        </div>
        
        <div class="flex gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeConfirmationModal()" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Cancel
            </button>
            <button id="confirmButton" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;

// Show notification modal
window.showNotification = function(message, type = 'success', title = null) {
    const modal = document.getElementById('notificationModal');
    const titleEl = document.getElementById('notificationTitle');
    const messageEl = document.getElementById('notificationMessage');
    const iconEl = document.getElementById('notificationIcon');
    const okButton = document.getElementById('notificationOkButton');
    
    // Set title
    if (title) {
        titleEl.textContent = title;
    } else {
        titleEl.textContent = type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Info';
    }
    
    // Set message
    messageEl.textContent = message;
    
    // Set icon and button color based on type
    if (type === 'success') {
        iconEl.className = 'flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        okButton.className = 'flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors';
    } else if (type === 'error') {
        iconEl.className = 'flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        okButton.className = 'flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors';
    } else {
        iconEl.className = 'flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        okButton.className = 'flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 rounded-lg transition-colors';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.closeNotificationModal = function() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

// Show confirmation modal
window.showConfirmation = function(message, callback, title = 'Confirm Action', buttonColor = 'black') {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmationTitle');
    const messageEl = document.getElementById('confirmationMessage');
    const confirmBtn = document.getElementById('confirmButton');
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    confirmCallback = callback;
    
    // Set button color
    if (buttonColor === 'orange') {
        confirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors';
    } else {
        confirmBtn.className = 'flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.closeConfirmationModal = function() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    confirmCallback = null;
};

// Handle confirm button click
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmButton');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (confirmCallback && typeof confirmCallback === 'function') {
                confirmCallback();
            }
            closeConfirmationModal();
        });
    }
});
</script>
