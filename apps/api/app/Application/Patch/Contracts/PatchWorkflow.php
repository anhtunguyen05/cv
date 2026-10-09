<?php

declare(strict_types=1);

namespace App\Application\Patch\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface PatchWorkflow
{
    public function generate(AuthenticatedUser $user, string $interviewId, array $payload, string $idempotencyKey, string $route, ?string $predecessorId = null, string $operation = 'generate-patch', ?int $predecessorRevision = null): array;

    public function show(AuthenticatedUser $user, string $patchId): array;

    public function edit(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array;

    public function reject(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array;

    public function approve(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array;

    public function regenerate(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array;
}
