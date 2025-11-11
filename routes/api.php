<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Documents
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Document actions
    Route::post('/documents/{document}/forward', [DocumentController::class, 'forward']);
    Route::post('/documents/{document}/approve', [DocumentController::class, 'approve']);
    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject']);
    Route::post('/documents/{document}/complete', [DocumentController::class, 'complete']);

    // Document comments
    Route::get('/documents/{document}/comments', [DocumentController::class, 'comments']);
    Route::post('/documents/{document}/comments', [DocumentController::class, 'addComment']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
});
