<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class TemplateRecord
{
    /**
     * @param  list<string>  $supportedSnapshotSchemaVersions
     */
    public function __construct(
        public string $id,
        public string $version,
        public string $name,
        public ?string $description,
        public bool $isActive,
        public array $supportedSnapshotSchemaVersions,
    ) {}
}
