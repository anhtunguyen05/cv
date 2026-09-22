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
        RateLimiter::for('registration-ip', function (Request $request): Limit {
            return Limit::perMinutes(10, 5)->by($this->hashedKey('ip', $request->ip() ?? 'unknown'));
        });

        RateLimiter::for('registration-email', function (Request $request): Limit {
            $email = mb_strtolower(trim((string) $request->input('email', '')));

            return Limit::perMinutes(10, 3)->by($this->hashedKey('email', $email));
        });
    }

    private function hashedKey(string $type, string $value): string
    {
        return $type.':'.hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
