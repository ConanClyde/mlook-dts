<div id="successModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeSuccessModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Success!</h3>
            <button type="button" onclick="closeSuccessModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Content -->
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                    <x-icon name="check-circle" class="w-10 h-10 text-green-600 dark:text-green-400" />
                </div>
                <h3 id="successModalTitle" class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Success!
                </h3>
                <p id="successModalMessage" class="text-sm text-gray-600 dark:text-gray-400">
                    Operation completed successfully.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeSuccessModal()" class="flex-1 btn-success min-h-[44px]">
                OK
            </button>
        </div>
    </div>
</div>

<script>
function showSuccessModal(title, message) {
    document.getElementById('successModalTitle').textContent = title || 'Success!';
    document.getElementById('successModalMessage').textContent = message || 'Operation completed successfully.';
    document.getElementById('successModal').classList.remove('hidden');
    document.getElementById('successModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('successModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Close on outside click
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessModal();
    }
});

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('successModal').classList.contains('hidden')) {
        closeSuccessModal();
    }
});

// Auto-close after 3 seconds
let successModalTimeout;
function showSuccessModalWithAutoClose(title, message, duration = 3000) {
    showSuccessModal(title, message);
    clearTimeout(successModalTimeout);
    successModalTimeout = setTimeout(() => {
        closeSuccessModal();
    }, duration);
}
</script>

