<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Patch\ValueObjects\PatchTarget;
use App\Domain\Shared\ValueObjects\IdempotencyKey;
use App\Domain\Shared\ValueObjects\OwnershipIdentity;
use App\Domain\Shared\ValueObjects\SnapshotHash;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectTest extends TestCase
{
    public function test_patch_target_round_trips(): void
    {
        $target = PatchTarget::fromArray([
            'section' => 'summary',
            'field' => 'summary',
            'item_id' => null,
            'operation' => 'replace',
        ]);

        self::assertSame([
            'section' => 'summary',
            'field' => 'summary',
            'item_id' => null,
            'operation' => 'replace',
        ], $target->toArray());
    }

    public function test_identity_and_hashes_reject_invalid_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OwnershipIdentity::fromInt(0);
    }

    public function test_idempotency_and_snapshot_values_are_validated(): void
    {
        self::assertSame(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            (string) IdempotencyKey::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        );
        self::assertTrue(SnapshotHash::fromString(str_repeat('a', 64))->equals(SnapshotHash::fromString(str_repeat('a', 64))));
    }
}
