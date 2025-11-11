@extends('layouts.app')

@section('title', '403 - Access Denied')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-6 sm:mb-8">
            <h1 class="text-6xl sm:text-7xl md:text-8xl lg:text-9xl font-bold text-gray-200 dark:text-gray-700">403</h1>
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-white mt-4">Access Denied</h2>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2 px-4">
                You don't have permission to access this resource.
            </p>
        </div>

        <div class="space-y-4">
            <a href="{{ route('dashboard') }}" class="inline-block w-full sm:w-auto px-6 py-3 min-h-[44px] bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-center flex items-center justify-center">
                Go to Dashboard
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

