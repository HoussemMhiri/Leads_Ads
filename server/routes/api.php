<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;
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

 // Google OAuth routes
    Route::controller(SocialAuthController::class)->group(function () {
        Route::get('google', 'redirect')->name('google.redirect');
        Route::get('google/callback', 'callback')->name('google.callback');
        Route::post('google/exchange', 'exchange')->name('google.exchange');
    });

    //  Protected route to get current user
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});
