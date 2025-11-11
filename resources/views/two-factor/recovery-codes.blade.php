@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">New Recovery Codes</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">Your new recovery codes have been generated</p>
        </div>

        <!-- Success Message -->
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
            Recovery codes have been regenerated successfully. Your old codes are no longer valid.
        </div>

        <!-- Recovery Codes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your New Recovery Codes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Store these recovery codes in a secure location. Each code can only be used once.
            </p>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="printRecoveryCodes()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium">
                    Print Codes
                </button>
                <button onclick="copyRecoveryCodes()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium">
                    Copy to Clipboard
                </button>
            </div>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                    <p class="font-semibold mb-1">Important:</p>
                    <p>Make sure to save these codes before leaving this page. You won't be able to see them again.</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('two-factor.index') }}" 
            class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to 2FA Settings
        </a>
    </div>
</div>

<script>
function printRecoveryCodes() {
    window.print();
}

function copyRecoveryCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    
    navigator.clipboard.writeText(text).then(() => {
        alert('Recovery codes copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy recovery codes.');
    });
}
</script>
@endsection
