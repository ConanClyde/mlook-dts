@extends('layouts.app')

@section('title', '503 - Service Unavailable')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50/30 to-gray-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <!-- Animated Icon -->
        <div class="flex justify-center mb-8">
            <div class="relative">
                <div class="absolute inset-0 bg-blue-500/20 dark:bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-full p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-6xl sm:text-7xl md:text-8xl lg:text-9xl font-bold bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent mb-4 animate-pulse">
                503
            </h1>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-900 dark:text-white mb-4">
                Service Unavailable
            </h2>
            <p class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400 max-w-md mx-auto px-4">
                We're currently performing maintenance to improve your experience. We'll be back shortly.
            </p>
        </div>

        <!-- Status Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 bg-blue-500 rounded-full animate-pulse"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs sm:text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Maintenance in Progress</h3>
                    <p class="text-xs sm:text-sm text-blue-700 dark:text-blue-400">
                        Our team is working to bring the system back online. This usually takes just a few minutes.
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-center">
            <button onclick="window.location.reload()" 
                    class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 min-h-[44px] bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 group text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh Page
            </button>
        </div>

        <!-- Additional Info -->
        <div class="mt-12 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Expected downtime: Less than 5 minutes
            </p>
        </div>
    </div>
</div>
@endsection
