<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Railway's proxy (all proxies in production)
        $middleware->trustProxies(at: '*');
        
       
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'ip.whitelist' => \App\Http\Middleware\IpWhitelist::class,
            'performance' => \App\Http\Middleware\PerformanceMonitoringMiddleware::class,
            'no.cache' => \App\Http\Middleware\NoCacheHeaders::class,
        ]);

        // Add performance monitoring and no-cache headers to web routes
        $middleware->web(append: [
            \App\Http\Middleware\PerformanceMonitoringMiddleware::class,
            \App\Http\Middleware\NoCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle 404 errors
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error' => 'NOT_FOUND',
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle 403 errors
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'error' => 'FORBIDDEN',
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        });

        // Handle custom document exceptions
        $exceptions->render(function (\App\Exceptions\DocumentException $e, Request $request) {
            return $e->render($request);
        });

        // Handle general exceptions
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Log the exception with context
            \Illuminate\Support\Facades\Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again later.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => class_basename($e),
                    'status_code' => $statusCode,
                ], $statusCode);
            }

            // Show error page for web requests
            if (config('app.debug')) {
                return null; // Let Laravel show the debug page
            }

            return response()->view('errors.500', [
                'message' => $e->getMessage(),
            ], 500);
        });
    })->withBroadcasting(
        channels: __DIR__.'/../routes/channels.php',
    )->create();
