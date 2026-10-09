<?php

declare(strict_types=1);

namespace App\Application\Evidence\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface EvidenceInterviewWorkflow
{
    public function start(AuthenticatedUser $user, string $matchReportId, string $idempotencyKey, string $route, array $requestPayload = []): array;

    public function show(AuthenticatedUser $user, string $interviewId): array;
}
