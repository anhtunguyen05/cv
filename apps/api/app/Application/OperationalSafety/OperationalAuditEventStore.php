<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use App\Models\OperationalAuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

final class OperationalAuditEventStore
{
    public function __construct(private readonly SanitizedAuditEventBuilder $builder) {}

    /**
     * Build and append one server-owned, sanitized event.
     *
     * Rejected input never reaches persistence and no unsafe fallback is
     * attempted. The caller must supply the actor as a server-side User model;
     * the event payload cannot provide or override actor_user_id.
     *
     * @param  array<string,mixed>  $serverContext
     * @param  array<string,mixed>  $outcome
     *
     * @throws InvalidArgumentException when the builder rejects the event
     */
    public function append(array $serverContext, array $outcome, ?User $actor = null): OperationalAuditEvent
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
            'actor_user_id' => $actor?->getKey(),
        ]);
    }

    /**
     * Fetch one event by its opaque, server-generated event identifier.
     *
     * @throws ModelNotFoundException
     */
    public function find(string $eventId): OperationalAuditEvent
    {
        return OperationalAuditEvent::query()->whereKey($eventId)->firstOrFail();
    }
}
