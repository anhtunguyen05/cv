<?php

declare(strict_types=1);

namespace App\Application\Auth\Data;

final readonly class RegisteredUser
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}
