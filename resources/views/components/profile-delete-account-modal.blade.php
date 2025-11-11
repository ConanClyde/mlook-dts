<div id="deleteAccountModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDeleteAccountModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Delete Account</h3>
            <button type="button" onclick="closeDeleteAccountModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="deleteAccountForm" onsubmit="submitDeleteAccountForm(event)" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('DELETE')

            <!-- Error display -->
            <div id="deleteAccountErrors" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm"></div>

            <!-- Warning Message -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <x-icon name="exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Are you sure you want to delete your account? This action cannot be undone. All your data will be permanently removed.
                    </p>
                </div>
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="delete_password" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Enter your password to confirm <span class="text-red-500">*</span>
                </label>
                <input 
                    id="delete_password" 
                    type="password" 
                    name="password" 
                    required
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="Your password"
                >
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeDeleteAccountModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-danger min-h-[44px]">
                    Delete Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('hidden');
    document.getElementById('deleteAccountModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.add('hidden');
    document.getElementById('deleteAccountModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('deleteAccountForm').reset();
    document.getElementById('deleteAccountErrors').classList.add('hidden');
}

function submitDeleteAccountForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('deleteAccountForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('deleteAccountErrors');
    
    fetch('{{ route("profile.destroy") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'DELETE',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("home") }}';
        } else {
            errorDiv.innerHTML = '<ul class="list-disc list-inside">';
            if (data.errors) {
                Object.values(data.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorDiv.innerHTML += `<li>${error}</li>`;
                    });
                });
            } else {
                errorDiv.innerHTML += `<li>${data.message || 'An error occurred'}</li>`;
            }
            errorDiv.innerHTML += '</ul>';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred while deleting the account.';
        errorDiv.classList.remove('hidden');
    });
}
</script>

