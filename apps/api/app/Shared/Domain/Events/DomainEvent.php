<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

use DateTimeImmutable;

abstract readonly class DomainEvent
{
    public function __construct(
        public string $eventId,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
