<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety\Contracts;

interface AuditEventAppender
{
    /** @param array<string,mixed> $serverContext @param array<string,mixed> $outcome */
    public function append(array $serverContext, array $outcome, ?int $actorUserId = null): object;
}
