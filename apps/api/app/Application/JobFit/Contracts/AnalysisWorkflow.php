<?php

declare(strict_types=1);

namespace App\Application\JobFit\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface AnalysisWorkflow
{
    public function create(AuthenticatedUser $user, string $jobDescriptionId, string $idempotencyKey, string $route): array;

    public function findOwned(AuthenticatedUser $user, string $jobDescriptionId, string $analysisId): mixed;
}
