<?php

declare(strict_types=1);

namespace App\Application\JobFit\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface MatchWorkflow
{
    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array;

    public function findOwned(AuthenticatedUser $user, string $id): mixed;

    public function list(AuthenticatedUser $user, int $page, int $perPage): mixed;
}
