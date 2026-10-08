<?php

declare(strict_types=1);

namespace App\Application\Evidence;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\ProfileDocument;
use App\Models\EvidenceAnswer;
use App\Models\EvidenceInterview;

final class EvidenceInterviewPresenter
{
    /** @return array<string,mixed> */
    public static function data(EvidenceInterview $interview): array
    {
        $areas = $interview->areas;
        $questions = $interview->questions;
        if (! ProfileDocument::isUlid((string) $interview->getKey())
            || ! ProfileDocument::isUlid((string) $interview->match_report_id)
            || ! ProfileDocument::isUlid((string) $interview->cv_version_id)
            || ! ProfileDocument::isUlid((string) $interview->job_description_id)
            || ! ProfileDocument::isUlid((string) $interview->job_description_revision_id)
            || ! ProfileDocument::isUlid((string) $interview->analysis_id)
            || $interview->question_set_version !== '1.0'
            || ! in_array($interview->status, ['active', 'completed', 'expired'], true)
            || ! is_array($areas)
            || ! is_array($questions)
            || ! self::validAreas($areas)
            || ! self::validQuestions($questions)
            || count($areas) !== count($questions)
            || array_diff(
                array_map(static fn (array $question): string => $question['area_signal_id'], $questions),
                array_map(static fn (array $area): string => $area['signal_id'], $areas),
            ) !== []) {
            throw new ApiProblem('EVIDENCE_INTERVIEW_INVALID', 'The stored Evidence Interview is invalid.', 500);
        }

        return [
            'id' => (string) $interview->getKey(),
            'match_report_id' => (string) $interview->match_report_id,
            'cv_version_id' => (string) $interview->cv_version_id,
            'job_description_id' => (string) $interview->job_description_id,
            'job_description_revision_id' => (string) $interview->job_description_revision_id,
            'analysis_id' => (string) $interview->analysis_id,
            'areas' => $areas,
            'questions' => $questions,
            'question_set_version' => (string) $interview->question_set_version,
            'status' => (string) $interview->status,
            'expires_at' => $interview->expires_at?->toISOString(),
            'created_at' => $interview->created_at?->toISOString(),
            'updated_at' => $interview->updated_at?->toISOString(),
            'answers' => $interview->relationLoaded('answers')
                ? $interview->answers->map(static fn (EvidenceAnswer $answer): array => EvidenceAnswerPresenter::data($answer))->values()->all()
                : [],
            'progress' => [
                'answered' => $interview->relationLoaded('answers') ? $interview->answers->count() : 0,
                'total' => count($questions),
                'remaining' => max(0, count($questions) - ($interview->relationLoaded('answers') ? $interview->answers->count() : 0)),
            ],
        ];
    }

    /** @param array<int,mixed> $areas */
    private static function validAreas(array $areas): bool
    {
        $signalIds = [];
        foreach ($areas as $area) {
            if (! is_array($area)
                || ! is_string($area['signal_id'] ?? null)
                || trim($area['signal_id']) === ''
                || ! is_string($area['label'] ?? null)
                || trim($area['label']) === ''
                || ! in_array($area['importance'] ?? null, ['required', 'preferred'], true)
                || ! in_array($area['evidence_level'] ?? null, ['missing', 'weak'], true)
                || ! is_array($area['source_references'] ?? null)
                || array_filter($area['source_references'], static fn (mixed $reference): bool => ! is_string($reference) || trim($reference) === '') !== []) {
                return false;
            }
            if (in_array($area['signal_id'], $signalIds, true)) {
                return false;
            }
            $signalIds[] = $area['signal_id'];
        }

        return count($areas) <= 5;
    }

    /** @param array<int,mixed> $questions */
    private static function validQuestions(array $questions): bool
    {
        $questionIds = [];
        $areaSignalIds = [];
        foreach ($questions as $question) {
            if (! is_array($question)
                || ! ProfileDocument::isUlid($question['id'] ?? null)
                || ! is_string($question['area_signal_id'] ?? null)
                || trim($question['area_signal_id']) === ''
                || ($question['question_version'] ?? null) !== '1.0'
                || ! is_string($question['question'] ?? null)
                || trim($question['question']) === '') {
                return false;
            }
            if (in_array($question['id'], $questionIds, true) || in_array($question['area_signal_id'], $areaSignalIds, true)) {
                return false;
            }
            $questionIds[] = $question['id'];
            $areaSignalIds[] = $question['area_signal_id'];
        }

        return count($questions) <= 5;
    }
}
