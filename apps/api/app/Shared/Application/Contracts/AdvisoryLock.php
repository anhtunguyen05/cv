<?php

declare(strict_types=1);

namespace App\Shared\Application\Contracts;

interface AdvisoryLock
{
    public function forUser(int $userId): void;
}
