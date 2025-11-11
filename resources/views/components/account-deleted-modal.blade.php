<!-- Account Deleted Modal -->
<div id="accountDeletedModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-[100] hidden items-center justify-center p-4" onclick="if(event.target === this) confirmAccountDeleted()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-red-600 dark:text-red-400">Account Deleted</h3>
            <button type="button" onclick="confirmAccountDeleted()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Content -->
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                    <x-icon name="exclamation-triangle" class="w-10 h-10 text-red-600 dark:text-red-400" />
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Your account has been deleted by an administrator. You will be logged out and redirected to the home page.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="confirmAccountDeleted()" class="flex-1 btn-danger min-h-[44px]">
                OK, Log Me Out
            </button>
        </div>
    </div>
</div>

<script>
window.showAccountDeletedModal = function() {
    const modal = document.getElementById('accountDeletedModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
};

window.confirmAccountDeleted = function() {
    // Logout and redirect to landing page
    fetch('{{ route("logout") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(() => {
        window.location.href = '/';
    })
    .catch(() => {
        // Force redirect even if logout fails
        window.location.href = '/';
    });
};
</script>

