<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use App\Models\MatchReport;

final class MatchReportPresenter
{
    /** @return array<string,mixed> */
    public static function data(MatchReport $report): array
    {
        $score = (float) $report->overall_score;
        $collections = [
            'matched_skills' => $report->matched_skills,
            'missing_skills' => $report->missing_skills,
            'weak_evidence' => $report->weak_evidence,
            'recommendations' => $report->recommendations,
        ];
        if ((string) $report->report_schema_version !== MatchService::REPORT_SCHEMA_VERSION
            || (string) $report->analysis_rule_version !== JobDescriptionAnalyzer::RULE_VERSION
            || (string) $report->matching_rule_version !== MatchService::RULE_VERSION
            || ! self::validIdentifier((string) $report->getKey())
            || ! self::validIdentifier((string) $report->cv_version_id)
            || ! self::validIdentifier((string) $report->job_description_id)
            || ! self::validIdentifier((string) $report->job_description_revision_id)
            || ! self::validIdentifier((string) $report->analysis_id)
            || ! is_finite($score) || $score < 0 || $score > 100
            || array_filter($collections, static fn (mixed $value): bool => ! is_array($value)) !== []
            || ! self::validClassifications($report->matched_skills)
            || ! self::validClassifications($report->missing_skills)
            || ! self::validClassifications($report->weak_evidence)
            || ! self::validRecommendations($report->recommendations)) {
            throw new ApiProblem('DERIVED_RESULT_INVALID', 'The stored Match Report is invalid and cannot be displayed.', 500);
        }
        $revision = $report->relationLoaded('revision') ? $report->revision : $report->revision()->first();
        $jobDescription = $report->relationLoaded('jobDescription') ? $report->jobDescription : $report->jobDescription()->first();

        return [
            'id' => (string) $report->getKey(),
            'cv_version_id' => (string) $report->cv_version_id,
            'job_description_id' => (string) $report->job_description_id,
            'job_description_revision_id' => (string) $report->job_description_revision_id,
            'analysis_id' => (string) $report->analysis_id,
            'analysis_rule_version' => (string) $report->analysis_rule_version,
            'matching_rule_version' => (string) $report->matching_rule_version,
            'report_schema_version' => (string) $report->report_schema_version,
            'overall_score' => $score,
            'matched_skills' => $report->matched_skills,
            'missing_skills' => $report->missing_skills,
            'weak_evidence' => $report->weak_evidence,
            'recommendations' => $report->recommendations,
            'source_summary' => [
                'company' => $revision?->company,
                'role' => $revision?->title,
                'revision_number' => $revision === null ? null : (int) $revision->revision_number,
                'source_deleted' => $jobDescription?->deleted_at !== null,
                'source_is_current' => $jobDescription !== null && $jobDescription->deleted_at === null
                    && (string) $jobDescription->current_revision_id === (string) $report->job_description_revision_id,
            ],
            'created_at' => $report->created_at?->toISOString(),
        ];
    }

    private static function validIdentifier(string $value): bool
    {
        return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value) === 1;
    }

    private static function validClassifications(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (! is_array($item)
                || ! is_string($item['signal_id'] ?? null)
                || ! is_string($item['label'] ?? null)
                || ! in_array($item['importance'] ?? null, ['required', 'preferred'], true)
                || ! in_array($item['evidence_level'] ?? null, ['strong', 'weak', 'missing'], true)
                || ! is_array($item['source_references'] ?? null)
                || array_filter($item['source_references'], static fn (mixed $reference): bool => ! is_string($reference)) !== []) {
                return false;
            }
        }

        return true;
    }

    private static function validRecommendations(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (! is_array($item)
                || ! is_string($item['id'] ?? null)
                || ! in_array($item['priority'] ?? null, ['high', 'medium', 'low'], true)
                || ! is_string($item['target_cv_section'] ?? null)
                || ! is_array($item['related_signal_ids'] ?? null)
                || array_filter($item['related_signal_ids'], static fn (mixed $signal): bool => ! is_string($signal)) !== []
                || ! is_string($item['rationale'] ?? null)
                || ! is_string($item['action'] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
