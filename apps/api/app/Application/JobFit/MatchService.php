<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\JobFit\Contracts\MatchWorkflow;

final class MatchService
{
    public const REPORT_SCHEMA_VERSION = '1.0.0';

    public const RULE_VERSION = '1.0.0';

    public function __construct(private readonly MatchWorkflow $workflow) {}

    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array
    {
        return $this->workflow->create($user, $payload, $idempotencyKey, $route);
    }

    public function findOwned(AuthenticatedUser $user, string $id): mixed
    {
        return $this->workflow->findOwned($user, $id);
    }

    public function list(AuthenticatedUser $user, int $page, int $perPage): mixed
    {
        return $this->workflow->list($user, $page, $perPage);
    }
}
