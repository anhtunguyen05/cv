<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Cv\Data\VersionRecord;

final class VersionPresenter
{
    /** @return array<string,mixed> */
    public static function summary(VersionRecord $version): array
    {
        return [
            'id' => $version->id,
            'name' => $version->name,
            'source_profile_id' => $version->sourceProfileId,
            'source_profile_revision' => $version->sourceProfileRevision,
            'created_at' => $version->createdAt,
        ];
    }

    public static function data(VersionRecord $version): array
    {
        return [
            'id' => $version->id,
            'name' => $version->name,
            'source_profile_id' => $version->sourceProfileId,
            'source_profile_revision' => $version->sourceProfileRevision,
            'source_cv_version_id' => $version->sourceCvVersionId,
            'source_match_report_id' => $version->sourceMatchReportId,
            'source_interview_id' => $version->sourceInterviewId,
            'source_patch_id' => $version->sourcePatchId,
            'snapshot_schema_version' => $version->snapshotSchemaVersion,
            'snapshot' => $version->snapshot,
            'provenance' => $version->provenance,
            'created_at' => $version->createdAt,
        ];
    }
}
