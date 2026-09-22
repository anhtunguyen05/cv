<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Application\Auth\Contracts\UserRepository;
use App\Models\User;

final class EloquentUserRepository implements UserRepository
{
    public function create(string $name, string $email, string $password): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
