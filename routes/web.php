<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Chat - requiere autenticación
Route::middleware('auth')->group(function () {
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    Route::get('/api/departments', [ChatController::class, 'departments']);
    Route::get('/api/departments/{department}/messages', [ChatController::class, 'messages']);
    Route::post('/api/departments/{department}/messages', [ChatController::class, 'send']);
});
