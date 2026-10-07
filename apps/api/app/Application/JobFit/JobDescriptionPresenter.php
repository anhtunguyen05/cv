<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Models\JobDescription;

final class JobDescriptionPresenter
{
    /** @return array<string,mixed> */
    public static function data(JobDescription $jobDescription): array
    {
        $revision = $jobDescription->relationLoaded('currentRevision')
            ? $jobDescription->currentRevision
            : $jobDescription->currentRevision()->first();

        return [
            'id' => (string) $jobDescription->getKey(),
            'company' => $revision?->company,
            'role' => $revision?->title,
            'current_revision' => $revision === null ? null : [
                'id' => (string) $revision->getKey(),
                'job_description_id' => (string) $revision->job_description_id,
                'revision_number' => (int) $revision->revision_number,
                'raw_text' => $revision->raw_text,
                'company' => $revision->company,
                'role' => $revision->title,
                'created_at' => $revision->created_at?->toISOString(),
            ],
            'deleted_at' => $jobDescription->deleted_at?->toISOString(),
            'created_at' => $jobDescription->created_at?->toISOString(),
            'updated_at' => $jobDescription->updated_at?->toISOString(),
        ];
    }
}
