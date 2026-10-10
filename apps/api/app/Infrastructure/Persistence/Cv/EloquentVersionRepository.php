<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv;

use App\Application\Cv\Contracts\VersionRepository;
use App\Application\Cv\Data\VersionPage;
use App\Application\Cv\Data\VersionRecord;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;

final class EloquentVersionRepository implements VersionRepository
{
    public function findOwned(int $userId, string $versionId): ?VersionRecord
    {
        $version = CvVersion::query()
            ->where('id', $versionId)
            ->where('user_id', $userId)
            ->first();

        return $version instanceof CvVersion ? $this->record($version) : null;
    }

    public function create(array $attributes): VersionRecord
    {
        $version = CvVersion::query()->create($attributes);

        return $this->record($version->fresh() ?? $version);
    }

    public function listOwned(int $userId, int $page, int $perPage, ?string $profileId, array $query = []): VersionPage
    {
        $versions = CvVersion::query()
            ->where('user_id', $userId)
            ->when($profileId !== null, static fn ($builder) => $builder->where('source_profile_id', $profileId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, [
                'id', 'user_id', 'source_profile_id', 'source_profile_revision', 'name',
                'source_cv_version_id', 'source_match_report_id', 'source_interview_id',
                'source_patch_id', 'snapshot_schema_version', 'snapshot', 'snapshot_hash',
                'provenance', 'created_at',
            ], 'page', $page)
            ->appends($query);

        return new VersionPage(
            items: array_map(fn (CvVersion $version): VersionRecord => $this->record($version), $versions->items()),
            meta: [
                'current_page' => $versions->currentPage(),
                'per_page' => $versions->perPage(),
                'total' => $versions->total(),
                'last_page' => $versions->lastPage(),
            ],
            links: [
                'first' => $versions->url(1),
                'last' => $versions->url($versions->lastPage()),
                'prev' => $versions->previousPageUrl(),
                'next' => $versions->nextPageUrl(),
            ],
        );
    }

    private function record(CvVersion $version): VersionRecord
    {
        return new VersionRecord(
            id: (string) $version->getKey(),
            userId: (int) $version->user_id,
            sourceProfileId: (string) $version->source_profile_id,
            sourceProfileRevision: (int) $version->source_profile_revision,
            name: (string) $version->name,
            sourceCvVersionId: $version->source_cv_version_id === null ? null : (string) $version->source_cv_version_id,
            sourceMatchReportId: $version->source_match_report_id === null ? null : (string) $version->source_match_report_id,
            sourceInterviewId: $version->source_interview_id === null ? null : (string) $version->source_interview_id,
            sourcePatchId: $version->source_patch_id === null ? null : (string) $version->source_patch_id,
            snapshotSchemaVersion: (string) $version->snapshot_schema_version,
            snapshot: is_array($version->snapshot) ? $version->snapshot : [],
            snapshotHash: $version->snapshot_hash === null ? null : (string) $version->snapshot_hash,
            provenance: is_array($version->provenance) ? $version->provenance : null,
            createdAt: $version->created_at?->toISOString(),
        );
    }
}
