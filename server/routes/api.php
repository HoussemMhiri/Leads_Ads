<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    // Auth routes (register/login) with rate limiting
    Route::controller(AuthController::class)
        ->middleware('throttle:authLimiter')
        ->group(function () {
            Route::post('register', 'register');
            Route::post('login', 'login');
        });

    // Password reset with stricter rate limiting
    Route::controller(PasswordResetController::class)
        ->middleware('throttle:passwordReset')
        ->group(function () {
            Route::post('forgot-password', 'sendResetLink')
                ->name('password.email');
            Route::post('reset-password', 'reset')
                ->name('password.update');
        });

    // Google OAuth routes
    Route::controller(SocialAuthController::class)
        ->middleware('throttle:authLimiter')
        ->group(function () {
            Route::get('google', 'redirect')
                ->name('google.redirect');
            Route::get('google/callback', 'callback')
                ->name('google.callback');
            Route::post('google/exchange', 'exchange')
                ->name('google.exchange');
        });

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});