<?php

declare(strict_types=1);

namespace App\Domain\Cv\Policies;

use App\Domain\Shared\ValueObjects\SnapshotHash;

final class ImmutableSnapshotPolicy
{
    public static function matchesHash(string $encodedSnapshot, SnapshotHash $persistedHash): bool
    {
        return hash_equals(hash('sha256', $encodedSnapshot), $persistedHash->value);
    }

    public static function canChange(): bool
    {
        return false;
    }
}
