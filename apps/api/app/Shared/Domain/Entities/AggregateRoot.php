<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entities;

use App\Shared\Domain\Events\DomainEvent;

abstract class AggregateRoot extends BaseEntity
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    final protected function recordDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return list<DomainEvent>
     */
    final public function releaseDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
