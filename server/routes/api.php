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

    
     // Password reset with stricter rate limiting
    Route::controller(PasswordResetController::class)
        ->middleware(['throttle:passwordReset', 'guest'])
        ->group(function () {
            Route::post('/forgot-password', 'sendResetLink')
                ->name('password.email');
            Route::post('/reset-password', 'reset')
                ->name('password.update');
        });
    

 // Google OAuth routes
    Route::controller(SocialAuthController::class)->group(function () {
        Route::get('google', 'redirect')->name('google.redirect');
        Route::get('google/callback', 'callback')->name('google.callback');
         Route::post('google/exchange', 'exchange')
            ->middleware('throttle:authLimiter')
            ->name('google.exchange');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return response()->json(['user' => $request->user()]);
        });
    });
});
