@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Set Up Two-Factor Authentication</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">Follow these steps to secure your account</p>
        </div>

        <!-- Step 1: Scan QR Code -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-semibold">
                    1
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Scan QR Code</h2>
            </div>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">
                Use your authenticator app (Google Authenticator, Authy, etc.) to scan this QR code:
            </p>
            <div class="flex justify-center bg-white p-3 sm:p-4 rounded-lg overflow-x-auto">
                {!! $qrCode !!}
            </div>
        </div>

        <!-- Step 2: Manual Entry -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-semibold">
                    2
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Or Enter Manually</h2>
            </div>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3">
                If you can't scan the QR code, enter this secret key manually:
            </p>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 sm:p-4">
                <code class="text-xs sm:text-sm font-mono text-gray-900 dark:text-white break-all">{{ $secret }}</code>
            </div>
        </div>

        <!-- Step 3: Verify -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-semibold">
                    3
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Verify Code</h2>
            </div>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">
                Enter the 6-digit code from your authenticator app to confirm setup:
            </p>

            @if(session('error'))
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-xs sm:text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('two-factor.confirm') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-center text-xl sm:text-2xl font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white min-h-[44px]"
                        placeholder="000000" autofocus>
                    @error('code')
                        <p class="mt-1 text-xs sm:text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 sm:py-3 min-h-[44px] rounded-lg transition-colors text-sm sm:text-base font-semibold">
                    Verify and Enable 2FA
                </button>
            </form>
        </div>

        <!-- Step 4: Recovery Codes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-yellow-600 text-white text-xs sm:text-sm font-semibold">
                    4
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Save Recovery Codes</h2>
            </div>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-4">
                Save these recovery codes in a secure location. You can use them to access your account if you lose your 2FA device.
            </p>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 sm:p-4 mb-4">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <code class="text-xs sm:text-sm font-mono text-gray-900 dark:text-white break-all">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
            <button onclick="printRecoveryCodes()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                Print Recovery Codes
            </button>
        </div>

        <!-- Warning -->
        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                    <p class="font-semibold mb-1">Important:</p>
                    <p>Make sure to save your recovery codes before completing setup. You won't be able to see them again until you regenerate them.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printRecoveryCodes() {
    window.print();
}
</script>
@endsection
