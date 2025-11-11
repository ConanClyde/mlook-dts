@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">Add an extra layer of security to your account</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- 2FA Status -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="p-2.5 sm:p-3 rounded-full {{ $user->two_factor_enabled ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $user->two_factor_enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                            Two-Factor Authentication
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            Status: 
                            <span class="font-medium {{ $user->two_factor_enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </p>
                        @if($user->two_factor_confirmed_at)
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                Enabled on {{ $user->two_factor_confirmed_at->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($user->two_factor_enabled)
                    <button onclick="document.getElementById('disable-2fa-modal').classList.remove('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                        Disable 2FA
                    </button>
                @else
                    <form action="{{ route('two-factor.enable') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full">
                            Enable 2FA
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Recovery Codes -->
        @if($user->two_factor_enabled)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recovery Codes</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Store these recovery codes in a secure location. They can be used to access your account if you lose your 2FA device.
                </p>

                @if(count($recoveryCodes) > 0)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>

                    <button onclick="document.getElementById('regenerate-codes-modal').classList.remove('hidden')"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                        Regenerate Recovery Codes
                    </button>
                @else
                    <p class="text-sm text-yellow-600 dark:text-yellow-400">
                        All recovery codes have been used. Please regenerate new codes.
                    </p>
                    <button onclick="document.getElementById('regenerate-codes-modal').classList.remove('hidden')"
                        class="mt-4 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                        Regenerate Recovery Codes
                    </button>
                @endif
            </div>
        @endif

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">How it works:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Download an authenticator app (Google Authenticator, Authy, etc.)</li>
                        <li>Scan the QR code or enter the secret key manually</li>
                        <li>Enter the 6-digit code from your app to verify</li>
                        <li>Save your recovery codes in a secure location</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div id="disable-2fa-modal" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-4 sm:p-6 max-w-md w-full shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Disable Two-Factor Authentication</h3>
            <button type="button" onclick="document.getElementById('disable-2fa-modal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Enter your password to confirm disabling 2FA.
        </p>
        <form action="{{ route('two-factor.disable') }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white min-h-[44px]">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="document.getElementById('disable-2fa-modal').classList.add('hidden')"
                    class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2.5 min-h-[44px] rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium">
                    Disable 2FA
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Regenerate Codes Modal -->
<div id="regenerate-codes-modal" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-4 sm:p-6 max-w-md w-full shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Regenerate Recovery Codes</h3>
            <button type="button" onclick="document.getElementById('regenerate-codes-modal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            This will invalidate your old recovery codes. Enter your password to confirm.
        </p>
        <form action="{{ route('two-factor.recovery-codes.regenerate') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="regen-password" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input type="password" name="password" id="regen-password" required
                    class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white min-h-[44px]">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="document.getElementById('regenerate-codes-modal').classList.add('hidden')"
                    class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2.5 min-h-[44px] rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium">
                    Regenerate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
