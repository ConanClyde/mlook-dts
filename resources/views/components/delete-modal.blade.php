<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-[60] hidden items-center justify-center p-4" onclick="if(event.target === this) closeDeleteModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-red-600 dark:text-red-400" id="deleteModalTitle">Confirm Deletion</h3>
            <button type="button" onclick="closeDeleteModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="mx-auto flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-300" id="deleteModalMessage">
                        Are you sure you want to delete this item? This action cannot be undone.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Cancel
            </button>
            <button type="button" onclick="confirmDelete()" class="flex-1 btn-danger min-h-[44px]">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteCallback = null;

window.showDeleteModal = function(title, message, callback) {
    document.getElementById('deleteModalTitle').textContent = title || 'Confirm Deletion';
    document.getElementById('deleteModalMessage').textContent = message || 'Are you sure you want to delete this item? This action cannot be undone.';
    deleteCallback = callback;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
};

window.closeDeleteModal = function() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    deleteCallback = null;
};

window.confirmDelete = function() {
    if (deleteCallback && typeof deleteCallback === 'function') {
        deleteCallback();
    }
    closeDeleteModal();
};

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('deleteModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    }
});
</script>

