<?php

namespace App\Providers;

use App\Application\Patch\DeterministicFakePatchProposalProvider;
use App\Application\Patch\PatchProposalProvider;
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
        $this->app->bind(PatchProposalProvider::class, DeterministicFakePatchProposalProvider::class);
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

        RateLimiter::for('login-ip', function (Request $request): Limit {
            return Limit::perMinutes(10, 5)->by($this->hashedKey('ip', $request->ip() ?? 'unknown'));
        });

        RateLimiter::for('login-email', function (Request $request): Limit {
            $email = mb_strtolower(trim((string) $request->input('email', '')));

            return Limit::perMinutes(10, 3)->by($this->hashedKey('email', $email));
        });

        RateLimiter::for('job-fit-analysis', function (Request $request): Limit {
            $user = $request->user();
            $owner = $user?->getAuthIdentifier() ?? 'guest';

            return Limit::perMinute(10)->by($this->hashedKey('job-fit-analysis', $owner.'|'.($request->ip() ?? 'unknown')));
        });

        RateLimiter::for('job-fit-mutations', function (Request $request): Limit {
            $user = $request->user();
            $owner = $user?->getAuthIdentifier() ?? 'guest';

            return Limit::perMinute(30)->by($this->hashedKey('job-fit-mutations', $owner.'|'.($request->ip() ?? 'unknown')));
        });
    }

    private function hashedKey(string $type, string $value): string
    {
        return $type.':'.hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
