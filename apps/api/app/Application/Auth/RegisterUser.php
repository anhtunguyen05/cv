<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Auth\Data\RegisterUserData;
use App\Models\User;
use App\Shared\Application\Contracts\TransactionManager;

final readonly class RegisterUser
{
    public function __construct(private TransactionManager $transactions) {}

    public function handle(RegisterUserData $data): User
    {
        return $this->transactions->run(
            static fn (): User => User::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
            ]),
        );
    }
}
