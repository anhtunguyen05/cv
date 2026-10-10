<?php

declare(strict_types=1);

namespace App\Application\Evidence\Contracts;

use App\Application\Auth\Data\AuthenticatedUser;

interface EvidenceAnswerWorkflow
{
    public function answer(AuthenticatedUser $user, string $interviewId, array $payload, string $idempotencyKey, string $route): array;
}
