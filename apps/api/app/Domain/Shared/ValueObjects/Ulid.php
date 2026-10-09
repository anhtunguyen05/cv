<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class Ulid
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value) !== 1) {
            throw new InvalidArgumentException('The value must be a valid ULID.');
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
