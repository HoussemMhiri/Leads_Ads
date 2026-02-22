<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Employee\EmployeeInvitationController;
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

    // Email verification routes
    Route::controller(EmailVerificationController::class)->group(function () {
        Route::get('email/verify/{id}/{hash}', 'verify')
            ->middleware(['signed'])
            ->name('verification.verify');
        Route::post('email/resend', 'resend')
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.send');
        Route::post('resend-verification-email', 'resendByEmail')
            ->middleware('throttle:3,1');
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

Route::prefix('employees')->group(function () {

    // ── Employee auth (login/logout/me) lives in tenant.php ───────────────────

    // ── Invitation management (owner-only) ────────────────────────────────────

    Route::get('roles', [EmployeeInvitationController::class, 'roles'])
        ->middleware(['auth:sanctum'])
        ->name('employee.roles');

    Route::post('invite', [EmployeeInvitationController::class, 'invite'])
        ->middleware(['auth:sanctum', 'throttle:10,1'])
        ->name('employee.invite');

    // ── Invitation acceptance (public, signed URL) ────────────────────────────

    Route::get('invitation/{tenant}/{employee}/accept', [EmployeeInvitationController::class, 'show'])
        ->middleware('signed')
        ->name('employee.invitation.show');

    Route::post('invitation/{tenant}/{employee}/accept', [EmployeeInvitationController::class, 'accept'])
        ->middleware('signed')
        ->name('employee.invitation.accept');
});
