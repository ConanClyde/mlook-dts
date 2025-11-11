@props(['colleges', 'departments', 'programs'])

<div id="createUserModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeCreateModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Create New User</h3>
            <button type="button" onclick="closeCreateModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="createUserForm" onsubmit="submitCreateForm(event)" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf

            <!-- Error display -->
            <div id="createErrors" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm"></div>

            <!-- First Name | Last Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="create_first_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="create_first_name" 
                        type="text" 
                        name="first_name" 
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Juan"
                    >
                </div>

                <div>
                    <label for="create_last_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="create_last_name" 
                        type="text" 
                        name="last_name" 
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Dela Cruz"
                    >
                </div>
            </div>

            <!-- University ID | Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="create_university_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        University ID <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="create_university_id" 
                        type="text" 
                        name="university_id" 
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="221-0238-2"
                    >
                </div>

                <div>
                    <label for="create_email" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="create_email" 
                        type="email" 
                        name="email" 
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="user@dmmmsu.edu.ph"
                    >
                </div>
            </div>

            <!-- User Type | Dynamic Field -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="create_usertype" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        User Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="create_usertype" 
                        name="usertype" 
                        required
                        onchange="toggleCreateFields()"
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select User Type</option>
                        <option value="admin">Admin</option>
                        <option value="registrar">Registrar</option>
                        <option value="dean">Dean</option>
                        <option value="department_head">Department Head</option>
                        <option value="faculty">Faculty</option>
                        <option value="staff">Staff</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <!-- College (for Deans) -->
                <div id="create_college_field" class="hidden">
                    <label for="create_college_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        College <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="create_college_id" 
                        name="college_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select College</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">
                                {{ $college->name }} ({{ $college->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department (for Department Heads, Staff, Registrar) -->
                <div id="create_department_field" class="hidden">
                    <label for="create_department_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="create_department_id" 
                        name="department_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select Department</option>
                        <optgroup label="Colleges">
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">
                                    {{ $college->name }} ({{ $college->code }})
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Departments">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- Program (for Students and Faculty) -->
                <div id="create_program_field" class="hidden">
                    <label for="create_program_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Program/Course <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="create_program_id" 
                        name="program_id" 
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Select Program</option>
                        @foreach($programs->groupBy('college.name') as $collegeName => $collegePrograms)
                            <optgroup label="{{ $collegeName }}">
                                @foreach($collegePrograms as $program)
                                    <option value="{{ $program->id }}">
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Password | Confirm Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="create_password" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            id="create_password" 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Enter password"
                        >
                        <button type="button"
                                onclick="togglePasswordVisibility('create_password', 'create_password_eye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="create_password_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="create_password_confirmation" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            id="create_password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            required
                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Confirm password"
                        >
                        <button type="button"
                                onclick="togglePasswordVisibility('create_password_confirmation', 'create_password_confirmation_eye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="create_password_confirmation_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary min-h-[44px]">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createUserModal').classList.remove('hidden');
    document.getElementById('createUserModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createUserModal').classList.add('hidden');
    document.getElementById('createUserModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('createUserForm').reset();
    document.getElementById('createErrors').classList.add('hidden');
    
    // Hide all dynamic fields
    document.getElementById('create_college_field').classList.add('hidden');
    document.getElementById('create_department_field').classList.add('hidden');
    document.getElementById('create_program_field').classList.add('hidden');
    
    // Reset password fields to hidden
    const passwordField = document.getElementById('create_password');
    const confirmPasswordField = document.getElementById('create_password_confirmation');
    if (passwordField) passwordField.type = 'password';
    if (confirmPasswordField) confirmPasswordField.type = 'password';
}

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        // Eye-slash icon (solid)
        icon.innerHTML = '<path d="M3.53 2.47a.75.75 0 00-1.06 1.06l18 18a.75.75 0 101.06-1.06l-18-18zM22.676 12.553a11.249 11.249 0 01-2.631 4.31l-3.099-3.099a5.25 5.25 0 00-6.71-6.71L7.759 4.577a11.217 11.217 0 014.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113z" /><path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0115.75 12zM12.53 15.713l-4.243-4.244a3.75 3.75 0 004.243 4.243z" /><path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 00-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 016.75 12z" />';
    } else {
        input.type = 'password';
        // Eye icon (solid)
        icon.innerHTML = '<path d="M12 15a3 3 0 100-6 3 3 0 000 6z" /><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />';
    }
}

function toggleCreateFields() {
    const usertype = document.getElementById('create_usertype').value;
    const collegeField = document.getElementById('create_college_field');
    const departmentField = document.getElementById('create_department_field');
    const programField = document.getElementById('create_program_field');
    const collegeSelect = document.getElementById('create_college_id');
    const departmentSelect = document.getElementById('create_department_id');
    const programSelect = document.getElementById('create_program_id');
    
    // Hide all fields first
    collegeField.classList.add('hidden');
    departmentField.classList.add('hidden');
    programField.classList.add('hidden');
    
    // Remove required from all
    collegeSelect.removeAttribute('required');
    departmentSelect.removeAttribute('required');
    programSelect.removeAttribute('required');
    
    // Clear selections
    collegeSelect.value = '';
    departmentSelect.value = '';
    programSelect.value = '';
    
    // Show appropriate field based on usertype
    if (usertype === 'dean') {
        collegeField.classList.remove('hidden');
        collegeSelect.setAttribute('required', 'required');
    } else if (['department_head', 'staff', 'registrar'].includes(usertype)) {
        departmentField.classList.remove('hidden');
        departmentSelect.setAttribute('required', 'required');
    } else if (['faculty', 'student'].includes(usertype)) {
        programField.classList.remove('hidden');
        programSelect.setAttribute('required', 'required');
    }
}

function submitCreateForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('createUserForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('createErrors');
    
    fetch('{{ route("admin.users.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateModal();
            showSuccessModalWithAutoClose('Success!', data.message);
            refreshUserTable();
        } else {
            // Show validation errors
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
        errorDiv.textContent = 'An error occurred while creating the user.';
        errorDiv.classList.remove('hidden');
    });
}
</script>

