<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\CvVersion;

final class VersionPresenter
{
    public static function data(CvVersion $version): array
    {
        return [
            'id' => (string) $version->getKey(),
            'name' => $version->name,
            'source_profile_id' => (string) $version->source_profile_id,
            'source_profile_revision' => (int) $version->source_profile_revision,
            'source_cv_version_id' => $version->source_cv_version_id !== null ? (string) $version->source_cv_version_id : null,
            'source_match_report_id' => $version->source_match_report_id !== null ? (string) $version->source_match_report_id : null,
            'source_interview_id' => $version->source_interview_id !== null ? (string) $version->source_interview_id : null,
            'source_patch_id' => $version->source_patch_id !== null ? (string) $version->source_patch_id : null,
            'snapshot_schema_version' => $version->snapshot_schema_version,
            'snapshot' => $version->snapshot,
            'provenance' => $version->provenance,
            'created_at' => $version->created_at?->toISOString(),
        ];
    }
}
