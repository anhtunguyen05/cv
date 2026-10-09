<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class ProfileSummary
{
    public function __construct(
        public string $id,
        public string $title,
        public int $revision,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'revision' => $this->revision,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
