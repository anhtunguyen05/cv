<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\JobFit\Contracts\AnalysisWorkflow;

final class AnalysisService
{
    public function __construct(private readonly AnalysisWorkflow $workflow) {}

    public function create(AuthenticatedUser $user, string $jobDescriptionId, string $idempotencyKey, string $route): array
    {
        return $this->workflow->create($user, $jobDescriptionId, $idempotencyKey, $route);
    }

    public function findOwned(AuthenticatedUser $user, string $jobDescriptionId, string $analysisId): mixed
    {
        return $this->workflow->findOwned($user, $jobDescriptionId, $analysisId);
    }
}
