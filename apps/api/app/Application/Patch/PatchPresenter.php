<?php

declare(strict_types=1);

namespace App\Application\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\ProfileDocument;
use App\Models\Patch;

final class PatchPresenter
{
    /** @return array<string,mixed> */
    public static function data(Patch $patch): array
    {
        if (! ProfileDocument::isUlid((string) $patch->getKey())
            || ! ProfileDocument::isUlid((string) $patch->source_cv_version_id)
            || ! ProfileDocument::isUlid((string) $patch->match_report_id)
            || ! ProfileDocument::isUlid((string) $patch->interview_id)
            || ! in_array($patch->status, ['pending_validation', 'pending', 'rejected', 'invalid', 'applied'], true)
            || ! is_array($patch->target)
            || ! is_array($patch->old_value)
            || ! is_array($patch->new_value)
            || ! is_array($patch->evidence_source_ids)
            || ! is_array($patch->provenance)) {
            throw new ApiProblem('PATCH_INVALID', 'The stored Patch is invalid.', 500);
        }

        return [
            'id' => (string) $patch->getKey(),
            'source_cv_version_id' => (string) $patch->source_cv_version_id,
            'match_report_id' => (string) $patch->match_report_id,
            'interview_id' => (string) $patch->interview_id,
            'predecessor_patch_id' => $patch->predecessor_patch_id !== null ? (string) $patch->predecessor_patch_id : null,
            'status' => (string) $patch->status,
            'allowed_actions' => match ($patch->status) {
                'pending' => ['edit', 'reject', 'approve'],
                'rejected', 'invalid' => ['regenerate'],
                default => [],
            },
            'revision' => (int) $patch->revision,
            'patch_schema_version' => (string) $patch->patch_schema_version,
            'prompt_version' => (string) $patch->prompt_version,
            'provider_model_version' => (string) $patch->provider_model_version,
            'target' => $patch->target,
            'old_value' => array_key_exists('value', $patch->old_value) ? $patch->old_value['value'] : $patch->old_value,
            'new_value' => array_key_exists('value', $patch->new_value) ? $patch->new_value['value'] : $patch->new_value,
            'reason' => (string) $patch->reason,
            'evidence_source_ids' => array_values($patch->evidence_source_ids),
            'provenance' => $patch->provenance,
            'applied_version_id' => $patch->applied_version_id !== null ? (string) $patch->applied_version_id : null,
            'created_at' => $patch->created_at?->toISOString(),
            'updated_at' => $patch->updated_at?->toISOString(),
            'source' => $patch->relationLoaded('sourceVersion') && $patch->sourceVersion !== null
                ? ['id' => (string) $patch->sourceVersion->getKey(), 'snapshot' => $patch->sourceVersion->snapshot]
                : null,
            'evidence' => $patch->relationLoaded('interview') && $patch->interview !== null && $patch->interview->relationLoaded('answers')
                ? $patch->interview->answers->filter(fn ($answer): bool => in_array((string) $answer->getKey(), array_map('strval', $patch->evidence_source_ids), true))->map(static fn ($answer): array => [
                    'id' => (string) $answer->getKey(),
                    'interview_id' => (string) $answer->interview_id,
                    'question_id' => (string) $answer->question_id,
                    'question_version' => (string) $answer->question_version,
                    'area_signal_id' => (string) $answer->area_signal_id,
                    'outcome' => (string) $answer->outcome,
                    'answer' => $answer->answer_original,
                    'provenance' => (string) $answer->provenance,
                    'created_at' => $answer->created_at?->toISOString(),
                ])->values()->all()
                : [],
        ];
    }
}
