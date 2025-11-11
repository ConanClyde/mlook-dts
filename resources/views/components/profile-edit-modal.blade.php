<div id="editProfileModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeEditProfileModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit Profile</h3>
            <button type="button" onclick="closeEditProfileModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="editProfileForm" onsubmit="submitEditProfileForm(event)" enctype="multipart/form-data" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            <!-- Error display -->
            <div id="editProfileErrors" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm"></div>

            <!-- Avatar -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Profile Picture
                </label>
                <div class="flex items-center space-x-4">
                    <div id="avatarPreview" class="flex-shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover">
                        @else
                            @php
                                $firstLetter = strtoupper(substr(auth()->user()->first_name, 0, 1));
                                $colors = [
                                    'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
                                    'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
                                    'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
                                    'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
                                    'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
                                    'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
                                    'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
                                    'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
                                    'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
                                    'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
                                    'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
                                    'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
                                    'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
                                    'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
                                    'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
                                    'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
                                    'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
                                    'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
                                    'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
                                    'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
                                    'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
                                    'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
                                    'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
                                    'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
                                    'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
                                    'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
                                ];
                                $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
                            @endphp
                            <div class="w-20 h-20 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-2xl">{{ $firstLetter }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input 
                            id="avatar" 
                            type="file" 
                            name="avatar" 
                            accept="image/*"
                            onchange="previewAvatar(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max size: 2MB (JPEG, PNG, JPG, GIF)</p>
                        <div class="mt-2">
                            @if(auth()->user()->avatar)
                                <button 
                                    type="button" 
                                    id="removeAvatarBtn"
                                    onclick="window.removeAvatar()"
                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                                >
                                    Remove Profile Picture
                                </button>
                            @endif
                            <input type="hidden" name="remove_avatar" id="removeAvatar" value="{{ auth()->user()->avatar ? '0' : '0' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email (Read-only) -->
            <div>
                <label for="edit_email" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Email
                </label>
                <input 
                    id="edit_email" 
                    type="email" 
                    value="{{ auth()->user()->email }}"
                    disabled
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg cursor-not-allowed"
                >
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email cannot be changed</p>
            </div>

            <!-- First Name | Last Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_first_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_first_name" 
                        type="text" 
                        name="first_name" 
                        value="{{ auth()->user()->first_name }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>

                <div>
                    <label for="edit_last_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_last_name" 
                        type="text" 
                        name="last_name" 
                        value="{{ auth()->user()->last_name }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeEditProfileModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary min-h-[44px]">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Ensure functions are in global scope
window.openEditProfileModal = function() {
    document.getElementById('editProfileModal').classList.remove('hidden');
    document.getElementById('editProfileModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
};

window.closeEditProfileModal = function() {
    document.getElementById('editProfileModal').classList.add('hidden');
    document.getElementById('editProfileModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('editProfileForm').reset();
    document.getElementById('editProfileErrors').classList.add('hidden');
    // Reset avatar preview
    location.reload(); // Simple reload to reset preview
};

window.previewAvatar = function(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Avatar" class="w-20 h-20 rounded-full object-cover">`;
            // Reset remove avatar flag when new image is selected
            const removeAvatarInput = document.getElementById('removeAvatar');
            if (removeAvatarInput) {
                removeAvatarInput.value = '0';
            }
            // Show the remove button again if it was hidden
            const removeButton = document.getElementById('removeAvatarBtn');
            if (removeButton) {
                removeButton.style.display = '';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.removeAvatar = function() {
    const preview = document.getElementById('avatarPreview');
    @php
        $firstLetter = strtoupper(substr(auth()->user()->first_name, 0, 1));
        $colors = [
            'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
            'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
            'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
            'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
            'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
            'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
            'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
            'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
            'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
            'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
            'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
            'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
            'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
            'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
            'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
            'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
            'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
            'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
            'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
            'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
            'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
            'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
            'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
            'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
            'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
            'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
        ];
        $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
    @endphp
    preview.innerHTML = `<div class="w-20 h-20 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center">
        <span class="text-white font-semibold text-2xl">{{ $firstLetter }}</span>
    </div>`;
    
    // Clear file input and set remove flag
    document.getElementById('avatar').value = '';
    const removeAvatarInput = document.getElementById('removeAvatar');
    if (removeAvatarInput) {
        removeAvatarInput.value = '1';
    }
    
    // Hide the remove button since avatar is now "removed"
    const removeButton = document.getElementById('removeAvatarBtn');
    if (removeButton) {
        removeButton.style.display = 'none';
    }
};

window.submitEditProfileForm = function(event) {
    event.preventDefault();
    
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('editProfileErrors');
    
    fetch('{{ route("profile.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
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
        errorDiv.textContent = 'An error occurred while updating the profile.';
        errorDiv.classList.remove('hidden');
    });
}
</script>

