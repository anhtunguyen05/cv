<?php

declare(strict_types=1);

namespace App\Application\Evidence;

use App\Models\EvidenceAnswer;

final class EvidenceAnswerPresenter
{
    /** @return array<string,mixed> */
    public static function data(EvidenceAnswer $answer): array
    {
        return [
            'id' => (string) $answer->getKey(),
            'interview_id' => (string) $answer->interview_id,
            'question_id' => (string) $answer->question_id,
            'question_version' => (string) $answer->question_version,
            'area_signal_id' => (string) $answer->area_signal_id,
            'outcome' => (string) $answer->outcome,
            'answer' => $answer->outcome === 'answer' ? $answer->answer_original : null,
            'provenance' => (string) $answer->provenance,
            'created_at' => $answer->created_at?->toISOString(),
        ];
    }
}
