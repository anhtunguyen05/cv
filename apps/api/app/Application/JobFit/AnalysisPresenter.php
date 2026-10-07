<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\ProfileDocument;
use App\Models\JobDescriptionAnalysis;

final class AnalysisPresenter
{
    /** @return array<string,mixed> */
    public static function data(JobDescriptionAnalysis $analysis): array
    {
        if (! self::validIdentifier((string) $analysis->getKey())
            || ! self::validIdentifier((string) $analysis->job_description_revision_id)
            || (string) $analysis->analysis_schema_version !== JobDescriptionAnalyzer::SCHEMA_VERSION
            || (string) $analysis->analysis_rule_version !== JobDescriptionAnalyzer::RULE_VERSION
            || ! in_array($analysis->status, ['succeeded', 'failed', 'pending', 'running'], true)
            || ! self::validSignal($analysis->extracted_role, true)
            || ! self::validSignal($analysis->required_skills)
            || ! self::validSignal($analysis->nice_to_have_skills)
            || ! self::validSignal($analysis->responsibilities)
            || ! self::validSignal($analysis->keywords)
            || ! self::validSignal($analysis->soft_skills)
            || ! self::validSignal($analysis->domain_context)
            || ! self::validSignal(['state' => $analysis->seniority_state ?: ($analysis->seniority === null ? 'absent' : 'detected'), 'value' => $analysis->seniority], true)) {
            throw new ApiProblem('DERIVED_RESULT_INVALID', 'The stored Analysis is invalid and cannot be displayed.', 500);
        }
        $signals = [
            'role' => self::scalarSignal($analysis->extracted_role),
            'required_skills' => self::listSignal($analysis->required_skills),
            'nice_to_have_skills' => self::listSignal($analysis->nice_to_have_skills),
            'responsibilities' => self::listSignal($analysis->responsibilities),
            'keywords' => self::listSignal($analysis->keywords),
            'seniority' => self::scalarSignal($analysis->seniority === null ? ['state' => $analysis->seniority_state ?: 'absent', 'value' => null] : ['state' => $analysis->seniority_state ?: 'detected', 'value' => $analysis->seniority]),
            'soft_skills' => self::listSignal($analysis->soft_skills),
            'domain_context' => self::listSignal($analysis->domain_context),
        ];

        return [
            'id' => (string) $analysis->getKey(),
            'job_description_revision_id' => (string) $analysis->job_description_revision_id,
            'analysis_schema_version' => (string) ($analysis->analysis_schema_version ?: '1.0.0'),
            'analysis_rule_version' => (string) $analysis->analysis_rule_version,
            'status' => (string) $analysis->status,
            'signals' => $signals,
            'created_at' => $analysis->created_at?->toISOString(),
        ];
    }

    private static function scalarSignal(mixed $value): array
    {
        if (is_array($value) && isset($value['state'])) {
            return ['state' => $value['state'], 'value' => $value['value'] ?? null];
        }
        if (is_array($value) && array_key_exists('value', $value)) {
            return ['state' => $value['value'] === null ? 'absent' : 'detected', 'value' => $value['value']];
        }

        return ['state' => 'absent', 'value' => null];
    }

    private static function validIdentifier(string $value): bool
    {
        return ProfileDocument::isUlid($value);
    }

    private static function validSignal(mixed $value, bool $scalar = false): bool
    {
        if (! is_array($value) || ! in_array($value['state'] ?? null, ['detected', 'absent', 'unknown'], true)) {
            return false;
        }
        if ($scalar) {
            return $value['value'] === null || is_string($value['value']);
        }
        if (! is_array($value['items'] ?? null)) {
            return false;
        }
        foreach ($value['items'] as $item) {
            if (is_string($item)) {
                continue;
            }
            if (! is_array($item) || ! is_string($item['signal_id'] ?? null) || ! is_string($item['label'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private static function listSignal(mixed $value): array
    {
        if (is_array($value) && isset($value['state'])) {
            return ['state' => $value['state'], 'items' => is_array($value['items'] ?? null) ? array_values($value['items']) : []];
        }

        return ['state' => is_array($value) && $value !== [] ? 'detected' : 'absent', 'items' => is_array($value) ? array_values($value) : []];
    }
}
