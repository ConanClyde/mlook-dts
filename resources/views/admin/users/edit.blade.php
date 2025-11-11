@extends('layouts.app')

@section('title', 'Edit User - MLOOK')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-72">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-icon name="bars-3" class="w-6 h-6" />
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h1>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <x-notifications />
                        <x-dark-mode-toggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
            <div class="max-w-5xl mx-auto">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 lg:mb-8">Edit User</h1>

                @if ($errors->any())
                    <div class="mb-4 sm:mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl">
                        <ul class="list-disc list-inside text-xs sm:text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 lg:p-8">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')

                <!-- First Name | Last Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="first_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="first_name" 
                            type="text" 
                            name="first_name" 
                            value="{{ old('first_name', $user->first_name) }}"
                            required 
                            autofocus
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('first_name') border-red-500 @enderror"
                            placeholder="Juan"
                        >
                        @error('first_name')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="last_name" 
                            type="text" 
                            name="last_name" 
                            value="{{ old('last_name', $user->last_name) }}"
                            required
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('last_name') border-red-500 @enderror"
                            placeholder="Dela Cruz"
                        >
                        @error('last_name')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- University ID | Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="university_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            University ID <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="university_id" 
                            type="text" 
                            name="university_id" 
                            value="{{ old('university_id', $user->university_id) }}"
                            required
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('university_id') border-red-500 @enderror"
                            placeholder="221-0238-2"
                        >
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format: 2XX-XXXX-2</p>
                        @error('university_id')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                            placeholder="user@dmmmsu.edu.ph"
                        >
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">@dmmmsu.edu.ph or @student.dmmmsu.edu.ph</p>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- User Type | (College/Department/Program) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="usertype" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            User Type <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="usertype" 
                            name="usertype" 
                            required
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('usertype') border-red-500 @enderror"
                        >
                            <option value="admin" {{ old('usertype', $user->usertype) === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="registrar" {{ old('usertype', $user->usertype) === 'registrar' ? 'selected' : '' }}>Registrar</option>
                            <option value="dean" {{ old('usertype', $user->usertype) === 'dean' ? 'selected' : '' }}>Dean</option>
                            <option value="department_head" {{ old('usertype', $user->usertype) === 'department_head' ? 'selected' : '' }}>Department Head</option>
                            <option value="faculty" {{ old('usertype', $user->usertype) === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ old('usertype', $user->usertype) === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="student" {{ old('usertype', $user->usertype) === 'student' ? 'selected' : '' }}>Student</option>
                        </select>
                        @error('usertype')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- College (for Deans) -->
                    <div id="college-field" class="hidden">
                        <label for="college_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            College <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="college_id" 
                            name="college_id" 
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        >
                            <option value="">Select College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ (old('college_id', $user->department_id) == $college->id && $user->usertype === 'dean') ? 'selected' : '' }}>
                                    {{ $college->name }} ({{ $college->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department (for Department Heads, Staff, Registrar) -->
                    <div id="department-field" class="hidden">
                        <label for="department_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="department_id" 
                            name="department_id" 
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        >
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Program (for Students and Faculty) -->
                    <div id="program-field" class="hidden">
                        <label for="program_id" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Program/Course <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="program_id" 
                            name="program_id" 
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        >
                            <option value="">Select Program</option>
                            @foreach($programs->groupBy('college.name') as $collegeName => $collegePrograms)
                                <optgroup label="{{ $collegeName }}">
                                    @foreach($collegePrograms as $program)
                                        <option value="{{ $program->id }}" {{ old('program_id', $user->program_id) == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Password <span class="text-xs text-gray-500 dark:text-gray-400">(leave blank to keep current)</span>
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="w-full px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                        placeholder="Enter new password (optional)"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm New Password
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        class="w-full px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        placeholder="Confirm new password (optional)"
                    >
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-3 sm:gap-4 pt-4 sm:pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary text-center min-h-[44px] flex items-center justify-center">Cancel</a>
                    <button type="submit" class="btn-primary text-center min-h-[44px] flex items-center justify-center">Update User</button>
                </div>
            </form>
        </div>
        </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }

    // Show/hide fields based on user type
    const usertypeSelect = document.getElementById('usertype');
    const collegeField = document.getElementById('college-field');
    const departmentField = document.getElementById('department-field');
    const programField = document.getElementById('program-field');
    const collegeSelect = document.getElementById('college_id');
    const departmentSelect = document.getElementById('department_id');
    const programSelect = document.getElementById('program_id');

    function toggleFields() {
        const usertype = usertypeSelect.value;
        
        // Hide all fields first
        collegeField.classList.add('hidden');
        departmentField.classList.add('hidden');
        programField.classList.add('hidden');
        
        // Clear and make optional
        collegeSelect.removeAttribute('required');
        departmentSelect.removeAttribute('required');
        programSelect.removeAttribute('required');

        // Show relevant fields based on user type
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

    if (usertypeSelect) {
        usertypeSelect.addEventListener('change', toggleFields);
        // Initialize on page load
        toggleFields();
    }
});
</script>
@endsection

