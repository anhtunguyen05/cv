<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\OperationalSafety;

use App\Application\OperationalSafety\Contracts\AuditEventAppender;
use App\Application\OperationalSafety\SanitizedAuditEventBuilder;
use App\Infrastructure\Persistence\OperationalSafety\Eloquent\Models\OperationalAuditEvent;
use InvalidArgumentException;

final class EloquentAuditEventStore implements AuditEventAppender
{
    public function __construct(private readonly SanitizedAuditEventBuilder $builder) {}

    /** @param array<string,mixed> $serverContext @param array<string,mixed> $outcome */
    public function append(array $serverContext, array $outcome, ?int $actorUserId = null): object
    {
        $result = $this->builder->build($serverContext, $outcome);
        if (! $result['accepted']) {
            throw new InvalidArgumentException('AUDIT_EVENT_REJECTED:'.$result['diagnostic']);
        }
        $event = $result['event'];
        if ($event['non_authoritative'] !== true) {
            throw new InvalidArgumentException('AUDIT_EVENT_REJECTED:NON_AUTHORITATIVE_REQUIRED');
        }

        return OperationalAuditEvent::query()->create([
            'id' => $event['event_id'],
            ...$event,
            'actor_user_id' => $actorUserId,
        ]);
    }
}
