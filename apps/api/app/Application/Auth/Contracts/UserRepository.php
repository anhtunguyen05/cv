<?php

declare(strict_types=1);

namespace App\Application\Auth\Contracts;

use App\Application\Auth\Data\RegisteredUser;

interface UserRepository
{
    public function create(string $name, string $email, string $password): RegisteredUser;
}
