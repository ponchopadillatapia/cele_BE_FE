<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Protegidas con Sanctum
|--------------------------------------------------------------------------
*/

// --- Rutas públicas (sin autenticación) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify.api');

// --- Rutas protegidas (requieren token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Chat - accesible por admin y resident
    Route::middleware('role:admin,resident')->group(function () {
        Route::get('/departments', [ChatController::class, 'departments']);
        Route::get('/departments/{department}/messages', [ChatController::class, 'messages']);
        Route::post('/departments/{department}/messages', [ChatController::class, 'send']);
    });

    // Notificaciones - accesible por admin y resident
    Route::middleware('role:admin,resident')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Crear notificaciones - solo admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/notifications', [NotificationController::class, 'store']);
    });
});
