<?php

declare(strict_types=1);

namespace App\Application\Evidence;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Evidence\Contracts\EvidenceInterviewWorkflow;

final class EvidenceInterviewService
{
    public function __construct(private readonly EvidenceInterviewWorkflow $workflow) {}

    public function start(AuthenticatedUser $user, string $matchReportId, string $idempotencyKey, string $route, array $requestPayload = []): array
    {
        return $this->workflow->start($user, $matchReportId, $idempotencyKey, $route, $requestPayload);
    }

    public function show(AuthenticatedUser $user, string $interviewId): array
    {
        return $this->workflow->show($user, $interviewId);
    }
}
