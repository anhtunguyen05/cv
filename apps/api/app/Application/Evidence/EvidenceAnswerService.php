<?php

declare(strict_types=1);

namespace App\Application\Evidence;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Evidence\Contracts\EvidenceAnswerWorkflow;

final class EvidenceAnswerService
{
    public function __construct(private readonly EvidenceAnswerWorkflow $workflow) {}

    public function answer(AuthenticatedUser $user, string $interviewId, array $payload, string $idempotencyKey, string $route): array
    {
        return $this->workflow->answer($user, $interviewId, $payload, $idempotencyKey, $route);
    }
}
