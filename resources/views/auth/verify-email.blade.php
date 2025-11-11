@extends('layouts.guest')

@section('title', 'Verify Email - MLOOK')

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
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Your Email</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">We sent a 6-digit code to <strong>{{ session('verification_email') }}</strong></p>
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

    @if (session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('verify.code') }}" class="space-y-5">
        @csrf

        <!-- Verification Code -->
        <div>
            <label for="code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Verification Code
            </label>
            <input 
                id="code" 
                type="text" 
                name="code" 
                maxlength="6"
                pattern="[0-9]{6}"
                required 
                autofocus
                class="w-full px-3 py-2 text-center text-2xl tracking-widest font-mono border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('code') border-red-500 @enderror"
                placeholder="000000"
            >
            @error('code')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Enter the 6-digit code sent to your email</p>
        </div>

        <!-- Verify Button -->
        <button type="submit" class="btn-primary w-full">
            Verify & Continue
        </button>
    </form>

    <!-- Resend Code -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            Didn't receive the code?
        </p>
        <form method="POST" action="{{ route('verify.resend') }}" id="resend-form">
            @csrf
            <button 
                type="submit" 
                id="resend-btn"
                class="text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300 font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Resend Code
            </button>
        </form>
        <p id="cooldown-text" class="text-xs text-gray-500 dark:text-gray-400 mt-1 hidden">
            You can resend in <span id="cooldown-timer">60</span> seconds
        </p>
    </div>

    <!-- Back to Register -->
    <div class="mt-6 text-center">
        <a href="{{ route('register') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to registration
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const resendBtn = document.getElementById('resend-btn');
    const resendForm = document.getElementById('resend-form');
    const cooldownText = document.getElementById('cooldown-text');
    const cooldownTimer = document.getElementById('cooldown-timer');

    // Auto-format code input (numbers only)
    codeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Cooldown timer for resend button
    let cooldownSeconds = 60;
    let cooldownInterval = null;

    function startCooldown() {
        resendBtn.disabled = true;
        cooldownText.classList.remove('hidden');
        cooldownSeconds = 60;
        cooldownTimer.textContent = cooldownSeconds;

        cooldownInterval = setInterval(function() {
            cooldownSeconds--;
            cooldownTimer.textContent = cooldownSeconds;

            if (cooldownSeconds <= 0) {
                clearInterval(cooldownInterval);
                resendBtn.disabled = false;
                cooldownText.classList.add('hidden');
            }
        }, 1000);
    }

    // Handle resend form submission
    resendForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (resendBtn.disabled) {
            return;
        }

        // Start cooldown immediately
        startCooldown();

        // Submit form via fetch
        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl';
                successDiv.innerHTML = '<p class="text-sm">' + data.message + '</p>';
                
                const form = document.querySelector('form[action="{{ route('verify.code') }}"]');
                form.parentNode.insertBefore(successDiv, form);
                
                // Remove after 5 seconds
                setTimeout(() => successDiv.remove(), 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Check if we should start cooldown on page load (from session)
    @if(session('resend_cooldown'))
        startCooldown();
    @endif
});
</script>
@endsection
