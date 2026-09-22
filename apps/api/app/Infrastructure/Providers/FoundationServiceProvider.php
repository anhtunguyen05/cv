<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Auth\Contracts\UserRepository;
use App\Infrastructure\Persistence\Auth\EloquentUserRepository;
use App\Shared\Application\Contracts\TransactionManager;
use App\Shared\Infrastructure\Persistence\LaravelTransactionManager;
use Illuminate\Support\ServiceProvider;

final class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }
}
