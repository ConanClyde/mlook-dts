@extends('layouts.guest')

@section('title', 'Complete Registration - MLOOK')

@section('content')
<!-- Dark Mode Toggle - Fixed Position -->
<div class="fixed top-6 right-6 z-50">
    <x-dark-mode-toggle />
</div>

<div class="w-full max-w-md">
    <!-- Logo/Header -->
    <div class="text-center mb-6">
        <div class="flex justify-center mb-3">
            <img src="{{ asset('images/logo.svg') }}" alt="MLOOK Logo" class="h-16 w-auto">
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Complete Your Registration</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Just a few more details to get started</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5" id="complete-registration-form">
        @csrf

        <!-- University ID -->
        <div id="university_id-field">
            <label for="university_id" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                <span id="university_id-label">University ID</span>
            </label>
            <input 
                id="university_id" 
                type="text" 
                name="university_id" 
                value="{{ old('university_id') }}"
                required
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('university_id') border-red-500 @enderror"
                placeholder="Enter your ID"
            >
            <p id="university_id-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
            @error('university_id')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Program (for Students) -->
        <div id="program-field" class="hidden">
            <label for="program_id" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Program/Course <span class="text-red-500">*</span>
            </label>
            <select 
                id="program_id" 
                name="program_id"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('program_id') border-red-500 @enderror"
            >
                <option value="">Select Program</option>
                @foreach($programs->groupBy('college.name') as $collegeName => $collegePrograms)
                    <optgroup label="{{ $collegeName }}">
                        @foreach($collegePrograms as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('program_id')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Department (for Staff) -->
        <div id="department-field" class="hidden">
            <label for="department_id" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Department <span class="text-red-500">*</span>
            </label>
            <select 
                id="department_id" 
                name="department_id"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('department_id') border-red-500 @enderror"
            >
                <option value="">Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->code }})
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Password
            </label>
            <div class="relative">
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required
                    class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                    placeholder="Create a strong password"
                >
                <button type="button"
                        onclick="togglePasswordVisibility('password', 'password_eye')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg id="password_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p id="password-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
            @error('password')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Confirm Password
            </label>
            <div class="relative">
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    required
                    class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    placeholder="Re-enter your password"
                >
                <button type="button"
                        onclick="togglePasswordVisibility('password_confirmation', 'password_confirmation_eye')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg id="password_confirmation_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p id="password_confirmation-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary w-full">
            Create Account
        </button>
    </form>

    <!-- Back to Verification -->
    <div class="mt-6 text-center">
        <a href="{{ route('verify.show') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to verification
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const email = '{{ session("verification_email") }}';
    const universityIdField = document.getElementById('university_id-field');
    const universityIdInput = document.getElementById('university_id');
    const universityIdLabel = document.getElementById('university_id-label');
    const programField = document.getElementById('program-field');
    const departmentField = document.getElementById('department-field');
    const programSelect = document.getElementById('program_id');
    const departmentSelect = document.getElementById('department_id');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');

    // Determine user type based on email
    const isStudent = email.endsWith('@student.dmmmsu.edu.ph');
    const isStaff = email.endsWith('@dmmmsu.edu.ph') && !isStudent;

    if (isStudent) {
        // Show University ID and Program fields for students
        universityIdField.classList.remove('hidden');
        programField.classList.remove('hidden');
        departmentField.classList.add('hidden');
        
        universityIdInput.setAttribute('required', 'required');
        universityIdInput.placeholder = '221-0238-2';
        universityIdLabel.textContent = 'Student ID';
        
        programSelect.setAttribute('required', 'required');
        departmentSelect.removeAttribute('required');
        departmentSelect.value = '';
    } else if (isStaff) {
        // Show University ID and Department fields for staff
        universityIdField.classList.remove('hidden');
        programField.classList.add('hidden');
        departmentField.classList.remove('hidden');
        
        universityIdInput.setAttribute('required', 'required');
        universityIdInput.placeholder = 'e.g., 123456';
        universityIdLabel.textContent = 'Staff ID';
        
        departmentSelect.setAttribute('required', 'required');
        programSelect.removeAttribute('required');
        programSelect.value = '';
    }

    // Password validation
    function validatePassword() {
        const password = passwordInput.value;
        const passwordError = document.getElementById('password-error');
        
        if (password.length === 0) {
            passwordError.classList.add('hidden');
            passwordInput.classList.remove('border-red-500');
            return true;
        } else if (password.length < 8) {
            passwordError.textContent = 'Password must be at least 8 characters';
            passwordError.classList.remove('hidden');
            passwordInput.classList.add('border-red-500');
            return false;
        } else {
            passwordError.classList.add('hidden');
            passwordInput.classList.remove('border-red-500');
            return true;
        }
    }

    // Confirm Password validation
    function validatePasswordConfirm() {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
        const passwordConfirmError = document.getElementById('password_confirmation-error');
        
        if (passwordConfirm.length === 0) {
            passwordConfirmError.classList.add('hidden');
            passwordConfirmInput.classList.remove('border-red-500');
            return true;
        } else if (password !== passwordConfirm) {
            passwordConfirmError.textContent = 'Passwords do not match';
            passwordConfirmError.classList.remove('hidden');
            passwordConfirmInput.classList.add('border-red-500');
            return false;
        } else {
            passwordConfirmError.classList.add('hidden');
            passwordConfirmInput.classList.remove('border-red-500');
            return true;
        }
    }

    passwordInput.addEventListener('blur', validatePassword);
    passwordConfirmInput.addEventListener('blur', validatePasswordConfirm);
    passwordInput.addEventListener('input', function() {
        if (passwordConfirmInput.value.length > 0) {
            validatePasswordConfirm();
        }
    });
});

// Toggle password visibility
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
</script>
@endsection
