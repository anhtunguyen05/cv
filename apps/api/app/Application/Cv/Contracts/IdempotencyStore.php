<?php

declare(strict_types=1);

namespace App\Application\Cv\Contracts;

use App\Application\Cv\Data\IdempotencyRecord;

interface IdempotencyStore
{
    public function find(int $userId, string $operation, string $key): ?IdempotencyRecord;

    public function forget(int $userId, string $operation, string $key): void;

    /** @param array<string, mixed> $responseBody */
    public function record(int $userId, string $operation, string $key, string $requestHash, array $responseBody, int $responseStatus): void;
}
