<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->controller(AuthController::class)->group(function () {

    Route::middleware('throttle:authLimiter')->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout');
    });

    // Send password reset link
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('guest')
        ->name('password.email');

    // Reset password
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('guest')
        ->name('password.update');
});
