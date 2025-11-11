@extends('layouts.app')

@section('title', 'Document Error')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mt-4">Document Error</h2>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2 px-4 break-words">
                {{ $message ?? 'An error occurred while processing the document.' }}
            </p>
            @if(isset($errorCode))
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-500 mt-1">
                    Error Code: {{ $errorCode }}
                </p>
            @endif
        </div>

        <div class="space-y-4">
            <a href="{{ route('documents.index') }}" class="inline-block w-full sm:w-auto px-6 py-3 min-h-[44px] bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-center flex items-center justify-center">
                Back to Documents
            </a>
            <div>
                <button onclick="window.history.back()" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors min-h-[44px] px-4 py-2">
                    ← Go Back
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

