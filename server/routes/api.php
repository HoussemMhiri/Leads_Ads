<?php

use App\Http\Controllers\Auth\AuthController;
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
});
