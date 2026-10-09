<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class IdempotencyKey
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('The value must be a lowercase UUID v4.');
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
