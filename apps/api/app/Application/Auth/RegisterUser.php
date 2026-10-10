<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Auth\Contracts\UserRepository;
use App\Application\Auth\Data\RegisteredUser;
use App\Application\Auth\Data\RegisterUserData;
use App\Shared\Application\Contracts\TransactionManager;

final readonly class RegisterUser
{
    public function __construct(
        private TransactionManager $transactions,
        private UserRepository $users,
    ) {}

    public function handle(RegisterUserData $data): RegisteredUser
    {
        return $this->transactions->run(
            fn (): RegisteredUser => $this->users->create($data->name, $data->email, $data->password),
        );
    }
}
