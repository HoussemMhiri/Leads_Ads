<?php

declare(strict_types=1);

use App\Http\Controllers\Employee\EmployeeAuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are accessible from any domain. The tenant is identified by
| the X-Tenant header (workspace slug, e.g. "acme"), which the frontend
| sends on every request after login. InitializeTenancyByRequestData looks
| up the matching tenant in the domains table and switches the DB connection
| automatically — no manual tenancy()->initialize() needed.
|
*/

Route::middleware([
    'api',
    InitializeTenancyByRequestData::class,
])->prefix('api/employees')->group(function () {

    // ── Employee auth (public within tenant context) ───────────────────────────

    Route::post('login', [EmployeeAuthController::class, 'login'])
        ->middleware('throttle:authLimiter')
        ->name('tenant.employee.login');

    // ── Employee auth (protected) ──────────────────────────────────────────────

    Route::middleware('auth:employee')->group(function () {
        Route::post('logout', [EmployeeAuthController::class, 'logout'])
            ->name('tenant.employee.logout');
        Route::get('me', [EmployeeAuthController::class, 'me'])
            ->name('tenant.employee.me');
    });
});
