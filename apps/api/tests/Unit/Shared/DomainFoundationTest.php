<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Domain\Entities\AggregateRoot;
use App\Shared\Domain\Entities\BaseEntity;
use App\Shared\Domain\Events\DomainEvent;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DomainFoundationTest extends TestCase
{
    public function test_entities_are_equal_only_when_type_and_identity_match(): void
    {
        $entity = new TestEntity('entity-1');

        self::assertTrue($entity->equals(new TestEntity('entity-1')));
        self::assertFalse($entity->equals(new TestEntity('entity-2')));
        self::assertFalse($entity->equals(new OtherTestEntity('entity-1')));
    }

    public function test_aggregate_records_and_releases_domain_events(): void
    {
        $aggregate = new TestAggregate('aggregate-1');
        $event = new TestEvent('event-1', new DateTimeImmutable('2026-01-01T00:00:00+00:00'));

        $aggregate->raise($event);

        self::assertSame([$event], $aggregate->releaseDomainEvents());
        self::assertSame([], $aggregate->releaseDomainEvents());
    }
}

final class TestEntity extends BaseEntity
{
}

final class OtherTestEntity extends BaseEntity
{
}

final class TestAggregate extends AggregateRoot
{
    public function raise(DomainEvent $event): void
    {
        $this->recordDomainEvent($event);
    }
}

final readonly class TestEvent extends DomainEvent
{
}
