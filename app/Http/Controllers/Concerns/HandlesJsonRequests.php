<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait HandlesJsonRequests
{
    /**
     * Check if the request expects JSON response.
     */
    protected function expectsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }

    /**
     * Handle authorization exception with JSON response support.
     *
     * @param  callable  $authorizeCallback
     * @param  string|null  $errorMessage
     * @return mixed
     */
    protected function handleAuthorization(callable $authorizeCallback, Request $request, ?string $errorMessage = null)
    {
        try {
            return $authorizeCallback();
        } catch (AuthorizationException $e) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage ?? 'You are not authorized to perform this action.',
                    'message' => 'Authorization failed',
                ], 403);
            }

            throw $e;
        }
    }
}

