@extends('layouts.guest')

@section('title', 'Register - MLOOK')

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
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Get started</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Create your MLOOK account - it's free</p>
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

    <form method="POST" action="{{ route('register.send-code') }}" class="space-y-5" id="registration-form">
        @csrf

        <!-- STEP 1: Basic Information -->
        <div id="step-1" class="space-y-5">
            <!-- First Name -->
            <div>
                <label for="first_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    First Name
                </label>
                <input 
                    id="first_name" 
                    type="text" 
                    name="first_name" 
                    value="{{ old('first_name') }}"
                    required 
                    autofocus
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('first_name') border-red-500 @enderror"
                    placeholder="Juan"
                >
                <p id="first_name-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
                @error('first_name')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Last Name -->
            <div>
                <label for="last_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Last Name
                </label>
                <input 
                    id="last_name" 
                    type="text" 
                    name="last_name" 
                    value="{{ old('last_name') }}"
                    required
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('last_name') border-red-500 @enderror"
                    placeholder="Dela Cruz"
                >
                <p id="last_name-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
                @error('last_name')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    DMMMSU Email Address
                </label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                    placeholder="your.name@dmmmsu.edu.ph or @student.dmmmsu.edu.ph"
                >
                <p id="email-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Send Verification Code Button -->
            <button type="submit" id="send-code-btn" class="btn-primary w-full opacity-50 cursor-not-allowed" disabled>
                Send Verification Code
            </button>
        </div>

    </form>

    <!-- Login Link -->
    <div class="mt-8 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300 font-medium">
                Login here
            </a>
        </p>
    </div>

    <!-- Back to Home -->
    <div class="mt-6 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to home
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');

    const firstNameError = document.getElementById('first_name-error');
    const lastNameError = document.getElementById('last_name-error');
    const emailError = document.getElementById('email-error');

    // Send code button
    const sendCodeBtn = document.getElementById('send-code-btn');

    let emailValidated = false;

    // Debounce function - delays validation by 1 second
    function debounce(func, delay) {
        let timeoutId;
        const debounced = function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
        debounced.cancel = function() {
            clearTimeout(timeoutId);
        };
        return debounced;
    }

    // Email validation: @dmmmsu.edu.ph or @student.dmmmsu.edu.ph
    function validateEmail(value) {
        if (!value.includes('@')) return false;
        const domain = value.split('@')[1];
        return domain === 'dmmmsu.edu.ph' || domain === 'student.dmmmsu.edu.ph';
    }

    // First Name validation
    const validateFirstName = debounce(function() {
        const value = firstNameInput.value.trim();
        if (value.length > 0) {
            firstNameError.classList.add('hidden');
            firstNameInput.classList.remove('border-red-500');
        }
    }, 1000);

    firstNameInput.addEventListener('input', validateFirstName);
    firstNameInput.addEventListener('blur', function() {
        validateFirstName.cancel();
        const value = firstNameInput.value.trim();
        if (value.length === 0) {
            firstNameError.textContent = 'First name is required';
            firstNameError.classList.remove('hidden');
            firstNameInput.classList.add('border-red-500');
        } else {
            firstNameError.classList.add('hidden');
            firstNameInput.classList.remove('border-red-500');
        }
    });

    // Last Name validation
    const validateLastName = debounce(function() {
        const value = lastNameInput.value.trim();
        if (value.length > 0) {
            lastNameError.classList.add('hidden');
            lastNameInput.classList.remove('border-red-500');
        }
    }, 1000);

    lastNameInput.addEventListener('input', validateLastName);
    lastNameInput.addEventListener('blur', function() {
        validateLastName.cancel();
        const value = lastNameInput.value.trim();
        if (value.length === 0) {
            lastNameError.textContent = 'Last name is required';
            lastNameError.classList.remove('hidden');
            lastNameInput.classList.add('border-red-500');
        } else {
            lastNameError.classList.add('hidden');
            lastNameInput.classList.remove('border-red-500');
        }
    });

    // Email validation
    const validateEmailInput = debounce(function() {
        const value = emailInput.value.trim();
        if (value.length === 0) {
            emailError.classList.add('hidden');
            emailInput.classList.remove('border-red-500');
        } else if (validateEmail(value)) {
            emailError.classList.add('hidden');
            emailInput.classList.remove('border-red-500');
            emailValidated = true;
            
            // Enable send code button
            sendCodeBtn.disabled = false;
            sendCodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            emailError.textContent = 'Email must be @dmmmsu.edu.ph or @student.dmmmsu.edu.ph';
            emailError.classList.remove('hidden');
            emailInput.classList.add('border-red-500');
            emailValidated = false;
            
            // Disable send code button
            sendCodeBtn.disabled = true;
            sendCodeBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }, 1000);

    emailInput.addEventListener('input', validateEmailInput);
    emailInput.addEventListener('blur', function() {
        validateEmailInput.cancel();
        const value = emailInput.value.trim();
        if (value.length === 0) {
            emailError.textContent = 'Email is required';
            emailError.classList.remove('hidden');
            emailInput.classList.add('border-red-500');
        } else if (!validateEmail(value)) {
            emailError.textContent = 'Email must be @dmmmsu.edu.ph or @student.dmmmsu.edu.ph';
            emailError.classList.remove('hidden');
            emailInput.classList.add('border-red-500');
        } else {
            emailError.classList.add('hidden');
            emailInput.classList.remove('border-red-500');
            emailValidated = true;
            
            // Enable send code button
            sendCodeBtn.disabled = false;
            sendCodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

});
</script>
@endsection
