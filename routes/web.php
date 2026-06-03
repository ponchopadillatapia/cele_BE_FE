<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Requiere autenticación
Route::middleware('auth')->group(function () {
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // Chat API
    Route::get('/api/departments', [ChatController::class, 'departments']);
    Route::get('/api/departments/{department}/messages', [ChatController::class, 'messages']);
    Route::post('/api/departments/{department}/messages', [ChatController::class, 'send']);

    // Notificaciones API
    Route::get('/api/notifications', [NotificationController::class, 'index']);
    Route::patch('/api/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/api/notifications', [NotificationController::class, 'store']);
});
