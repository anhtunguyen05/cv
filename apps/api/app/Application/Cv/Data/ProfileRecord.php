<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class ProfileRecord
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function __construct(
        public string $id,
        public int $userId,
        public string $title,
        public int $revision,
        public string $schemaVersion,
        public array $document,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
