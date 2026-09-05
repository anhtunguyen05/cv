<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entities;

abstract class BaseEntity
{
    public function __construct(
        protected readonly string $id,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    final public function equals(self $other): bool
    {
        return static::class === $other::class
            && $this->id === $other->id;
    }
}
