<?php

declare(strict_types=1);

namespace App\Application\Auth\Contracts;

use App\Models\User;

interface UserRepository
{
    public function create(string $name, string $email, string $password): User;
}
