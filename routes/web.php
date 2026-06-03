<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz: auto-login y redirige al chat
Route::get('/', function () {
    if (!Auth::check()) {
        Auth::login(User::first());
    }
    return redirect('/chat');
});

Route::get('/dashboard', function () {
    return redirect('/chat');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Chat
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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
