@extends('layouts.guest')

@section('title', 'Reset Password - MLOOK')

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
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Enter your new password</p>
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

    <form method="POST" action="{{ route('password.reset') }}" class="space-y-5">
        @csrf

        <!-- New Password -->
        <div>
            <label for="password" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                New Password
            </label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required
                autofocus
                class="w-full px-3 sm:px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px] @error('password') border-red-500 @enderror"
                placeholder="Enter new password"
            >
            <p id="password-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
            @error('password')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Confirm New Password
            </label>
            <input 
                id="password_confirmation" 
                type="password" 
                name="password_confirmation" 
                required
                class="w-full px-3 sm:px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                placeholder="Confirm new password"
            >
            <p id="password_confirmation-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary w-full">
            Reset Password
        </button>
    </form>

    <!-- Back to Login -->
    <div class="mt-8 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to login
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const passwordError = document.getElementById('password-error');
    const passwordConfirmError = document.getElementById('password_confirmation-error');

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

    function validatePassword() {
        const password = passwordInput.value;
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

    function validatePasswordConfirm() {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
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

    const debouncedValidatePassword = debounce(validatePassword, 1000);
    const debouncedValidatePasswordConfirm = debounce(validatePasswordConfirm, 1000);

    passwordInput.addEventListener('input', debouncedValidatePassword);
    passwordInput.addEventListener('blur', function() {
        debouncedValidatePassword.cancel();
        validatePassword();
    });

    passwordConfirmInput.addEventListener('input', debouncedValidatePasswordConfirm);
    passwordConfirmInput.addEventListener('blur', function() {
        debouncedValidatePasswordConfirm.cancel();
        validatePasswordConfirm();
    });

    // Re-validate password confirm when password changes
    passwordInput.addEventListener('input', function() {
        if (passwordConfirmInput.value.length > 0) {
            debouncedValidatePasswordConfirm();
        }
    });
});
</script>
@endsection

