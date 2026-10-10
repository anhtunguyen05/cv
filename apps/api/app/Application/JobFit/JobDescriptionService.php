<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\JobFit\Contracts\JobDescriptionWorkflow;

/** Application use-case boundary; Eloquent execution is supplied by Infrastructure. */
final class JobDescriptionService
{
    public function __construct(private readonly JobDescriptionWorkflow $workflow) {}

    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array
    {
        return $this->workflow->create($user, $payload, $idempotencyKey, $route);
    }

    public function findOwnedActive(AuthenticatedUser $user, string $id): mixed
    {
        return $this->workflow->findOwnedActive($user, $id);
    }

    public function update(AuthenticatedUser $user, string $id, array $input, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->update($user, $id, $input, $ifMatch, $idempotencyKey, $route);
    }

    public function delete(AuthenticatedUser $user, string $id, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->workflow->delete($user, $id, $ifMatch, $idempotencyKey, $route);
    }

    public function list(AuthenticatedUser $user, int $page, int $perPage): mixed
    {
        return $this->workflow->list($user, $page, $perPage);
    }
}
