<?php

declare(strict_types=1);

namespace App\Application\JobFit\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface JobDescriptionWorkflow
{
    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array;

    public function findOwnedActive(AuthenticatedUser $user, string $id): mixed;

    public function update(AuthenticatedUser $user, string $id, array $input, string $ifMatch, string $idempotencyKey, string $route): array;

    public function delete(AuthenticatedUser $user, string $id, string $ifMatch, string $idempotencyKey, string $route): array;

    public function list(AuthenticatedUser $user, int $page, int $perPage): mixed;
}
