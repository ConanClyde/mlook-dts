@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                Enter the 6-digit code from your authenticator app
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            @if(session('error'))
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('two-factor.verify') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Verification Code
                    </label>
                    <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-center text-xl sm:text-2xl font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white min-h-[44px]"
                        placeholder="000000" autofocus>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                    class="w-full flex justify-center py-2.5 sm:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors min-h-[44px]">
                    Verify
                </button>
            </form>

            <!-- Recovery Code Option -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button onclick="toggleRecoveryCode()" 
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                    Use a recovery code instead
                </button>
            </div>

            <!-- Recovery Code Form (Hidden by default) -->
            <div id="recovery-code-form" class="hidden mt-4">
                <form action="{{ route('two-factor.verify') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="recovery-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Recovery Code
                        </label>
                        <input type="text" name="code" id="recovery-code" maxlength="10" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-center text-sm sm:text-base font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white uppercase min-h-[44px]"
                            placeholder="XXXXXXXXXX">
                    </div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2.5 sm:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors min-h-[44px]">
                        Use Recovery Code
                    </button>
                </form>
            </div>
        </div>

        <!-- Logout Link -->
        <div class="text-center">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRecoveryCode() {
    const recoveryForm = document.getElementById('recovery-code-form');
    recoveryForm.classList.toggle('hidden');
}
</script>
@endsection
