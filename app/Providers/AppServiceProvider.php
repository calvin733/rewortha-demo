<?php

namespace App\Providers;

use App\Services\DecisionAnalysisService;
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
        $this->app->singleton(DecisionAnalysisService::class, fn () => new DecisionAnalysisService((int) config('decision.close_call_basis_points')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by('api:'.($request->user()?->id ?? $request->ip())));
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(5)->by('register:'.$request->ip()));
        RateLimiter::for('login', function (Request $request): array {
            $email = $request->input('email');
            $normalizedEmail = is_string($email) ? mb_strtolower($email) : '';

            return [
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
                Limit::perMinute(5)->by('login-account:'.hash('sha256', $normalizedEmail.'|'.$request->ip())),
            ];
        });
        RateLimiter::for('health', fn (Request $request) => Limit::perMinute(60)->by('health:'.$request->ip()));
    }
}
