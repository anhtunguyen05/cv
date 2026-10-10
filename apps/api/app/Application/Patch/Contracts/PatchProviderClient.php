<?php

declare(strict_types=1);

namespace App\Application\Patch\Contracts;

interface PatchProviderClient
{
    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function propose(array $payload, string $correlationId): array;
}
