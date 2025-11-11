@props(['user', 'colleges', 'departments', 'programs'])

<div id="editModal{{ $user->id }}" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) window['closeEditModal{{ $user->id }}']()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit User</h3>
            <button type="button" onclick="window['closeEditModal{{ $user->id }}']()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="editForm{{ $user->id }}" onsubmit="window['submitEditForm{{ $user->id }}'](event)" enctype="multipart/form-data" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            <!-- Error display -->
            <div id="editErrors{{ $user->id }}" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm"></div>

            <!-- Avatar -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Profile Picture
                </label>
                <div class="flex items-center space-x-4">
                    <div id="editAvatarPreview{{ $user->id }}" class="flex-shrink-0">
                        @if($user->avatar)
                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover">
                        @else
                            @php
                                $firstLetter = strtoupper(substr($user->first_name, 0, 1));
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
                            id="edit_avatar{{ $user->id }}" 
                            type="file" 
                            name="avatar" 
                            accept="image/*"
                            onchange="window['previewEditAvatar{{ $user->id }}'](this)"
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max size: 2MB (JPEG, PNG, JPG, GIF)</p>
                        <div class="mt-2">
                            @if($user->avatar)
                                <button 
                                    type="button" 
                                    id="removeEditAvatarBtn{{ $user->id }}"
                                    onclick="window['removeEditAvatar{{ $user->id }}']()"
                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                                >
                                    Remove Profile Picture
                                </button>
                            @endif
                            <input type="hidden" name="remove_avatar" id="removeEditAvatar{{ $user->id }}" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- First Name | Last Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_first_name{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_first_name{{ $user->id }}" 
                        type="text" 
                        name="first_name" 
                        value="{{ $user->first_name }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>

                <div>
                    <label for="edit_last_name{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_last_name{{ $user->id }}" 
                        type="text" 
                        name="last_name" 
                        value="{{ $user->last_name }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>
            </div>

            <!-- University ID | Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_university_id{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        University ID <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_university_id{{ $user->id }}" 
                        type="text" 
                        name="university_id" 
                        value="{{ $user->university_id }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>

                <div>
                    <label for="edit_email{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="edit_email{{ $user->id }}" 
                        type="email" 
                        name="email" 
                        value="{{ $user->email }}"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>
            </div>

            <!-- User Type | Dynamic Field -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_usertype{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        User Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="edit_usertype{{ $user->id }}" 
                        name="usertype" 
                        required
                        onchange="window['toggleEditFields{{ $user->id }}']()"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="admin" {{ $user->usertype === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="registrar" {{ $user->usertype === 'registrar' ? 'selected' : '' }}>Registrar</option>
                        <option value="dean" {{ $user->usertype === 'dean' ? 'selected' : '' }}>Dean</option>
                        <option value="department_head" {{ $user->usertype === 'department_head' ? 'selected' : '' }}>Department Head</option>
                        <option value="faculty" {{ $user->usertype === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="staff" {{ $user->usertype === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="student" {{ $user->usertype === 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>

                <!-- College (for Deans) -->
                <div id="edit_college_field{{ $user->id }}" class="hidden">
                    <label for="edit_college_id{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        College <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="edit_college_id{{ $user->id }}" 
                        name="college_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select College</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ ($user->department_id == $college->id && $user->usertype === 'dean') ? 'selected' : '' }}>
                                {{ $college->name }} ({{ $college->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department (for Department Heads, Staff, Registrar) -->
                <div id="edit_department_field{{ $user->id }}" class="hidden">
                    <label for="edit_department_id{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="edit_department_id{{ $user->id }}" 
                        name="department_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Program (for Students and Faculty) -->
                <div id="edit_program_field{{ $user->id }}" class="hidden">
                    <label for="edit_program_id{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Program/Course <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="edit_program_id{{ $user->id }}" 
                        name="program_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select Program</option>
                        @foreach($programs->groupBy('college.name') as $collegeName => $collegePrograms)
                            <optgroup label="{{ $collegeName }}">
                                @foreach($collegePrograms as $program)
                                    <option value="{{ $program->id }}" {{ $user->program_id == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Password (Optional) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_password{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        New Password <span class="text-xs text-gray-500">(optional)</span>
                    </label>
                    <div class="relative">
                        <input 
                            id="edit_password{{ $user->id }}" 
                            type="password" 
                            name="password" 
                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Leave blank to keep current"
                        >
                        <button type="button"
                                onclick="togglePasswordVisibility('edit_password{{ $user->id }}', 'edit_password_eye{{ $user->id }}')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="edit_password_eye{{ $user->id }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="edit_password_confirmation{{ $user->id }}" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            id="edit_password_confirmation{{ $user->id }}" 
                            type="password" 
                            name="password_confirmation" 
                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Confirm new password"
                        >
                        <button type="button"
                                onclick="togglePasswordVisibility('edit_password_confirmation{{ $user->id }}', 'edit_password_confirmation_eye{{ $user->id }}')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="edit_password_confirmation_eye{{ $user->id }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-3 sm:py-4 flex justify-end gap-3 rounded-b-lg sm:rounded-b-xl">
            <button type="button" onclick="window['closeEditModal{{ $user->id }}']()" class="btn-secondary text-sm">
                Cancel
            </button>
            <button type="submit" form="editForm{{ $user->id }}" class="btn-primary text-sm" id="editSubmitBtn{{ $user->id }}">
                <span id="editBtnText{{ $user->id }}">Update User</span>
                <span id="editBtnLoading{{ $user->id }}" class="hidden">Updating...</span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
// Ensure functions are in global scope
window['openEditModal{{ $user->id }}'] = function() {
    const modal = document.getElementById('editModal{{ $user->id }}');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        if (typeof window['toggleEditFields{{ $user->id }}'] === 'function') {
            window['toggleEditFields{{ $user->id }}']();
        }
    }
};

window['closeEditModal{{ $user->id }}'] = function() {
    const modal = document.getElementById('editModal{{ $user->id }}');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
};

window['previewEditAvatar{{ $user->id }}'] = function(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('editAvatarPreview{{ $user->id }}');
            preview.innerHTML = `<img src="${e.target.result}" alt="Avatar" class="w-20 h-20 rounded-full object-cover">`;
            // Reset remove avatar flag when new image is selected
            const removeAvatarInput = document.getElementById('removeEditAvatar{{ $user->id }}');
            if (removeAvatarInput) {
                removeAvatarInput.value = '0';
            }
            // Show the remove button again if it was hidden
            const removeButton = document.getElementById('removeEditAvatarBtn{{ $user->id }}');
            if (removeButton) {
                removeButton.style.display = '';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window['removeEditAvatar{{ $user->id }}'] = function() {
    const preview = document.getElementById('editAvatarPreview{{ $user->id }}');
    @php
        $firstLetter = strtoupper(substr($user->first_name, 0, 1));
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
    document.getElementById('edit_avatar{{ $user->id }}').value = '';
    const removeAvatarInput = document.getElementById('removeEditAvatar{{ $user->id }}');
    if (removeAvatarInput) {
        removeAvatarInput.value = '1';
    }
    
    // Hide the remove button since avatar is now "removed"
    const removeButton = document.getElementById('removeEditAvatarBtn{{ $user->id }}');
    if (removeButton) {
        removeButton.style.display = 'none';
    }
};

window['toggleEditFields{{ $user->id }}'] = function() {
    const usertype = document.getElementById('edit_usertype{{ $user->id }}').value;
    const collegeField = document.getElementById('edit_college_field{{ $user->id }}');
    const departmentField = document.getElementById('edit_department_field{{ $user->id }}');
    const programField = document.getElementById('edit_program_field{{ $user->id }}');
    
    // Hide all
    collegeField.classList.add('hidden');
    departmentField.classList.add('hidden');
    programField.classList.add('hidden');
    
    // Show relevant field
    if (usertype === 'dean') {
        collegeField.classList.remove('hidden');
        document.getElementById('edit_college_id{{ $user->id }}').setAttribute('required', 'required');
    } else if (['department_head', 'staff', 'registrar'].includes(usertype)) {
        departmentField.classList.remove('hidden');
        document.getElementById('edit_department_id{{ $user->id }}').setAttribute('required', 'required');
    } else if (['faculty', 'student'].includes(usertype)) {
        programField.classList.remove('hidden');
        document.getElementById('edit_program_id{{ $user->id }}').setAttribute('required', 'required');
    }
}

window['submitEditForm{{ $user->id }}'] = async function(event) {
    event.preventDefault();
    
    const form = document.getElementById('editForm{{ $user->id }}');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('editSubmitBtn{{ $user->id }}');
    const btnText = document.getElementById('editBtnText{{ $user->id }}');
    const btnLoading = document.getElementById('editBtnLoading{{ $user->id }}');
    const errorsDiv = document.getElementById('editErrors{{ $user->id }}');
    
    // Show loading state
    submitBtn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    errorsDiv.classList.add('hidden');
    
    try {
        const response = await fetch('{{ route("admin.users.update", $user) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Close modal
            window['closeEditModal{{ $user->id }}']();
            
            // Show success modal
            showSuccessModalWithAutoClose('User Updated!', data.message || 'User updated successfully!');
            
            // Trigger table refresh
            if (typeof refreshUserTable === 'function') {
                await refreshUserTable();
            }
        } else {
            // Show errors
            let errorHtml = '<ul class="list-disc list-inside space-y-1">';
            if (data.errors) {
                for (let field in data.errors) {
                    data.errors[field].forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                }
            } else {
                errorHtml += `<li>${data.message || 'An error occurred'}</li>`;
            }
            errorHtml += '</ul>';
            errorsDiv.innerHTML = errorHtml;
            errorsDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        errorsDiv.innerHTML = '<p>An unexpected error occurred. Please try again.</p>';
        errorsDiv.classList.remove('hidden');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
    }
}

// Initialize fields on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditModal{{ $user->id }});
} else {
    initEditModal{{ $user->id }}();
}

function initEditModal{{ $user->id }}() {
    const modal = document.getElementById('editModal{{ $user->id }}');
    if (!modal) return;

    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            window['closeEditModal{{ $user->id }}']();
        }
    });

    // Initialize fields
    if (typeof window['toggleEditFields{{ $user->id }}'] === 'function') {
        window['toggleEditFields{{ $user->id }}']();
    }
}
})();
</script>

