<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Application\Auth\Contracts\UserRepository;
use App\Application\Auth\Data\RegisteredUser;
use App\Application\Auth\Exceptions\UserAlreadyExists;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use Illuminate\Database\QueryException;

final class EloquentUserRepository implements UserRepository
{
    public function create(string $name, string $email, string $password): RegisteredUser
    {
        try {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23505', '23000'], true)) {
                throw new UserAlreadyExists(previous: $exception);
            }

            throw $exception;
        }

        return new RegisteredUser(
            id: (int) $user->getKey(),
            name: (string) $user->name,
            email: (string) $user->email,
        );
    }
}
