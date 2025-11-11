@extends('layouts.guest')

@section('title', 'Enter Reset Code - MLOOK')

@section('content')
<!-- Dark Mode Toggle - Fixed Position -->
<div class="fixed top-6 right-6 z-50">
    <x-dark-mode-toggle />
</div>

<div class="w-full max-w-md">
    <!-- Logo/Header -->
    <div class="text-center mb-6">
        <div class="flex justify-center mb-2">
            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                <x-icon name="key" class="w-6 h-6 text-orange-500 dark:text-orange-400" />
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Enter Reset Code</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">We sent a 6-digit code to {{ session('email') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if (session('reset_code'))
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 px-4 py-3 rounded-xl text-sm">
            <strong>Development Mode:</strong> Your reset code is: <span class="font-mono text-lg font-bold">{{ session('reset_code') }}</span>
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

    <form method="POST" action="{{ route('password.code') }}" class="space-y-5">
        @csrf

        <!-- Reset Code -->
        <div>
            <label for="code" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                6-Digit Reset Code
            </label>
            <input 
                id="code" 
                type="text" 
                name="code" 
                value="{{ old('code') }}"
                required 
                autofocus
                maxlength="6"
                pattern="\d{6}"
                inputmode="numeric"
                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition font-mono text-center text-xl sm:text-2xl tracking-widest min-h-[44px] @error('code') border-red-500 @enderror"
                placeholder="000000"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
            >
            @error('code')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary w-full">
            Verify Code
        </button>
    </form>

    <!-- Resend Code -->
    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('password.resend') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300 font-medium inline-flex items-center">
                <x-icon name="arrow-path" class="w-4 h-4 mr-1" />
                Resend Code
            </button>
        </form>
    </div>

    <!-- Back to Email Entry -->
    <div class="mt-6 text-center">
        <a href="{{ route('password.request') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to email entry
        </a>
    </div>
</div>
@endsection

