<?php

declare(strict_types=1);

namespace App\Application\Auth\Data;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
