<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       $this->configureAuthRateLimiting();
       $this->configurePasswordResetRateLimiting();
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
