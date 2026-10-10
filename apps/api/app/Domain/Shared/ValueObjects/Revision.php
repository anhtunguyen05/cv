<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class Revision
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidArgumentException('A revision must be positive.');
        }

        return new self($value);
    }

    public function etag(): string
    {
        return sprintf('"%d"', $this->value);
    }
}
