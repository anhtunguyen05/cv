<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class IdempotencyRecord
{
    /** @param array<string, mixed> $responseBody */
    public function __construct(
        public string $requestHash,
        public array $responseBody,
        public int $responseStatus,
        public bool $expired,
    ) {}
}
