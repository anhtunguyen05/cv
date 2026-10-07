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
            'snapshot_schema_version' => $version->snapshot_schema_version,
            'snapshot' => $version->snapshot,
            'created_at' => $version->created_at?->toISOString(),
        ];
    }
}
