<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f97316">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DTS">
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>@yield('title', 'MLOOK - MLUC Document Tracking System')</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/icon-192x192.png') }}">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ filemtime(public_path('manifest.json')) }}">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@700,500,400&display=swap" rel="stylesheet">
    <script>
        // Make Reverb config available to JS BEFORE Vite loads (so Echo can use it)
        @if(config('broadcasting.default') === 'reverb')
        window.REVERB_APP_KEY = '{{ config('broadcasting.connections.reverb.key') }}';
        window.REVERB_HOST = '{{ config('broadcasting.connections.reverb.options.host') }}';
        window.REVERB_PORT = '{{ config('broadcasting.connections.reverb.options.port') }}';
        window.REVERB_SCHEME = '{{ config('broadcasting.connections.reverb.options.scheme') }}';
        // Reverb config exposed to window; avoid logging sensitive details in console
        @else
        // Broadcasting not set to reverb
        @endif
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Satoshi', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
    <script>
        // Dark mode initialization
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 antialiased transition-colors duration-200">
    <!-- Skip to main content for keyboard navigation -->
    <x-skip-to-content />
    
    @yield('content')
    
    <!-- Global Account Deleted Modal -->
    <x-account-deleted-modal />

    <!-- PWA Service Worker Registration -->
    <script>
        // Only register service worker in production (not on localhost)
        if ('serviceWorker' in navigator && !window.location.hostname.includes('localhost') && !window.location.hostname.includes('127.0.0.1')) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('✅ ServiceWorker registered successfully');
                    })
                    .catch(function(err) {
                        console.error('❌ ServiceWorker registration failed: ', err);
                    });
            });
        } else if ('serviceWorker' in navigator) {
            // In development: unregister any existing service workers
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                    console.log('🔧 Development mode: Unregistered service worker');
                }
            });
        }

        // Capture the beforeinstallprompt event
        // Note: Chrome will log "Banner not shown: beforeinstallpromptevent.preventDefault() called"
        // This is intentional - we're deferring the install prompt to show it via custom UI
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            window.deferredPrompt = e;
        });
    </script>
</body>
</html>

