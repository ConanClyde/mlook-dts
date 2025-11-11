@extends('layouts.guest')

@section('title', 'Login - MLOOK')

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
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Login to your MLOOK account</p>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5" onsubmit="handleLogin(event)">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Email Address
            </label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}"
                required 
                autofocus
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                placeholder="your.email@student.dmmmsu.edu.ph"
            >
            <p id="email-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 hidden"></p>
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
                    class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    placeholder="Enter your password"
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
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    name="remember" 
                    id="remember"
                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                >
                <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                    Remember me
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="text-sm text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300 font-medium">
                Forgot password?
            </a>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="login-btn" class="btn-primary w-full min-h-[44px]">
            <span id="login-btn-text">Login</span>
            <span id="login-btn-loading" class="hidden">
                <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </button>
    </form>

    <!-- Register Link -->
    <div class="mt-8 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300 font-medium">
                Create account
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

<!-- Error Modal -->
<div id="error-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeErrorModal()">
    <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Login Failed</h3>
                    <p id="error-message" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button type="button" onclick="closeErrorModal()" class="px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors w-full sm:w-auto">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
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

function showErrorModal(message) {
    document.getElementById('error-message').textContent = message;
    const modal = document.getElementById('error-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeErrorModal() {
    const modal = document.getElementById('error-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function handleLogin(event) {
    event.preventDefault();
    
    // Clear previous errors
    document.getElementById('email-error').classList.add('hidden');
    document.getElementById('password-error').classList.add('hidden');
    
    // Show loading state
    document.getElementById('login-btn-text').classList.add('hidden');
    document.getElementById('login-btn-loading').classList.remove('hidden');
    document.getElementById('login-btn').disabled = true;
    
    // Get CSRF token from meta tag or form
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;
    
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        // Handle 419 CSRF token mismatch
        if (response.status === 419) {
            showErrorModal('Session expired. Please refresh the page and try again.');
            // Optionally refresh the page to get a new CSRF token
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            return null;
        }
        
        if (response.redirected) {
            window.location.href = response.url;
            return null;
        }
        
        if (!response.ok) {
            return response.json().catch(() => {
                throw new Error('Failed to parse response');
            });
        }
        
        return response.json();
    })
    .then(data => {
        if (!data) return; // Handled redirect or 419
        
        if (data && data.success) {
            // Successful login
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '/dashboard';
            }
            return;
        }
        
        if (data && data.errors) {
            // Show validation errors
            if (data.errors.email) {
                document.getElementById('email-error').textContent = data.errors.email[0];
                document.getElementById('email-error').classList.remove('hidden');
            }
            if (data.errors.password) {
                document.getElementById('password-error').textContent = data.errors.password[0];
                document.getElementById('password-error').classList.remove('hidden');
            }
            
            // Show error modal
            const errorMessage = data.message || 'Invalid email or password. Please try again.';
            showErrorModal(errorMessage);
        } else if (data && data.message) {
            showErrorModal(data.message);
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showErrorModal('An error occurred. Please refresh the page and try again.');
    })
    .finally(() => {
        // Reset loading state
        document.getElementById('login-btn-text').classList.remove('hidden');
        document.getElementById('login-btn-loading').classList.add('hidden');
        document.getElementById('login-btn').disabled = false;
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeErrorModal();
    }
});
</script>
@endsection
