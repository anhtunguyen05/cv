<?php

declare(strict_types=1);

namespace App\Application\Patch;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Patch\Contracts\PatchWorkflow;

final class PatchService
{
    public function __construct(private readonly PatchWorkflow $workflow) {}

    public function generate(AuthenticatedUser $user, string $interviewId, array $payload, string $idempotencyKey, string $route, ?string $predecessorId = null, string $operation = 'generate-patch', ?int $predecessorRevision = null): array
    {
        return $this->workflow->generate($user, $interviewId, $payload, $idempotencyKey, $route, $predecessorId, $operation, $predecessorRevision);
    }

    public function show(AuthenticatedUser $user, string $patchId): array
    {
        return $this->workflow->show($user, $patchId);
    }

    public function edit(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->edit($user, $patchId, $payload, $ifMatch, $idempotencyKey, $route);
    }

    public function reject(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->reject($user, $patchId, $payload, $ifMatch, $idempotencyKey, $route);
    }

    public function approve(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->approve($user, $patchId, $payload, $ifMatch, $idempotencyKey, $route);
    }

    public function regenerate(AuthenticatedUser $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->regenerate($user, $patchId, $payload, $ifMatch, $idempotencyKey, $route);
    }
}
