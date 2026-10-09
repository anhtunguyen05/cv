<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class VersionRecord
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>|null  $provenance
     */
    public function __construct(
        public string $id,
        public int $userId,
        public string $sourceProfileId,
        public int $sourceProfileRevision,
        public string $name,
        public ?string $sourceCvVersionId,
        public ?string $sourceMatchReportId,
        public ?string $sourceInterviewId,
        public ?string $sourcePatchId,
        public string $snapshotSchemaVersion,
        public array $snapshot,
        public ?string $snapshotHash,
        public ?array $provenance,
        public ?string $createdAt,
    ) {}
}
