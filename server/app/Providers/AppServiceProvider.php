<?php

namespace App\Providers;

use App\Tenancy\Resolvers\WorkspaceSlugTenantResolver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Resolvers\RequestDataTenantResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swap the default resolver so the X-Tenant header is matched against
        // the workspace slug (domain column) instead of the tenant UUID.
        $this->app->bind(RequestDataTenantResolver::class, WorkspaceSlugTenantResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureTenancyByHeader();
        $this->configureAuthRateLimiting();
        $this->configurePasswordResetRateLimiting();
    }

    protected function configureTenancyByHeader(): void
    {
        // Tell the middleware to read the workspace slug from X-Tenant header.
        // The actual resolution (slug → tenant) is handled by WorkspaceSlugTenantResolver.
        InitializeTenancyByRequestData::$header = 'X-Tenant';

        // Return a clear JSON 404 when the workspace slug is not found,
        // instead of letting the exception bubble up as a 500.
        InitializeTenancyByRequestData::$onFail = function ($e, $request, $next) {
            return response()->json([
                'message' => 'Workspace not found. Please check the workspace name and try again.',
            ], 404);
        };
    }


    protected function configureAuthRateLimiting(): void
   {
     RateLimiter::for('authLimiter', function (Request $request) {
        $key = $request->input('email') 
            ? $request->input('email') . '|' . $request->ip()
            : $request->ip();

        return Limit::perMinute(5)
            ->by($key)
            ->response(fn($request, $headers) => response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => $headers['Retry-After'] ?? 60,
            ], 429, $headers));
    });
}

protected function configurePasswordResetRateLimiting(): void
{
    RateLimiter::for('passwordReset', function (Request $request) {
        return [
            Limit::perMinute(3)
                ->by($request->ip())
                ->response(fn($request, $headers) => response()->json([
                    'message' => 'Too many password reset requests. Please wait before trying again.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429, $headers)),
            
            Limit::perHour(5)
                ->by(($request->input('email') ?? 'anonymous') . '|' . $request->ip())
                ->response(fn($request, $headers) => response()->json([
                    'message' => 'Too many password reset attempts for this email.',
                    'retry_after' => $headers['Retry-After'] ?? 3600,
                ], 429, $headers)),
        ];
    });
}
}
