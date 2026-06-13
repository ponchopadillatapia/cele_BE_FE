<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz redirige al chat o login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/chat');
    }
    return redirect('/login');
});

// Vistas de autenticación Vue
Route::get('/login', function () {
    return view('auth.login-vue');
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return view('auth.register-vue');
})->middleware('guest')->name('register');

// Verificación de email (redirige desde el link del correo)
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Link inválido.');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    Auth::login($user);
    return redirect('/chat');
})->name('verification.verify');

Route::get('/dashboard', function () {
    return redirect('/chat');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Vista del chat (SPA Vue)
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // APIs internas (sesión web + Sanctum stateful)
    Route::get('/api/departments', [ChatController::class, 'departments']);
    Route::get('/api/departments/{department}/messages', [ChatController::class, 'messages']);
    Route::post('/api/departments/{department}/messages', [ChatController::class, 'send']);
    Route::get('/api/notifications', [NotificationController::class, 'index']);
    Route::patch('/api/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/api/notifications', [NotificationController::class, 'store']);

    // Logout web
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
