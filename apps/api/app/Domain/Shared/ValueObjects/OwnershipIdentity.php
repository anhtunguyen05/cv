<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class OwnershipIdentity
{
    private function __construct(public int $userId) {}

    public static function fromInt(int $userId): self
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('An ownership identity must be positive.');
        }

        return new self($userId);
    }
}
