<?php

declare(strict_types=1);

use App\Http\Controllers\Employee\EmployeeAuthController;
use App\Http\Controllers\Employee\EmployeeInvitationController;
use App\Http\Middleware\InitializeTenancyBySession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Tenant context is resolved server-side via the session (tenant_id stored
| on login). InitializeTenancyBySession reads it and switches the DB.
|
| The login endpoint is the only exception — it identifies the tenant by
| looking up the employee email in the central employee_workspaces table,
| then manually initializes tenancy and stores tenant_id in the session.
|
*/

Route::middleware(['api'])->prefix('api/employees')->group(function () {

    // ── Login: tenant resolved from central employee_workspaces table ─────────
    // No session middleware here — the controller initializes tenancy manually.

    Route::post('login', [EmployeeAuthController::class, 'login'])
        ->middleware('throttle:authLimiter')
        ->name('tenant.employee.login');

    // ── All protected routes: tenant from session ─────────────────────────────

    Route::middleware(InitializeTenancyBySession::class)->group(function () {

        Route::middleware('auth:employee')->group(function () {
            Route::post('logout', [EmployeeAuthController::class, 'logout'])
                ->name('tenant.employee.logout');
            Route::get('me', [EmployeeAuthController::class, 'me'])
                ->name('tenant.employee.me');
        });

        // Accessible by both owner (auth:sanctum) and employee (auth:employee)
        Route::middleware('auth:sanctum,employee')->group(function () {
            Route::get('roles', [EmployeeInvitationController::class, 'roles'])
                ->name('tenant.employee.roles');
            Route::post('invite', [EmployeeInvitationController::class, 'invite'])
                ->middleware('throttle:10,1')
                ->name('tenant.employee.invite');
        });
    });
});
