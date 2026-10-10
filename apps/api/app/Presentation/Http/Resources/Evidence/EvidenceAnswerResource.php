<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Evidence;

use App\Application\Evidence\EvidenceAnswerPresenter;

final class EvidenceAnswerResource
{
    /** @return array<string,mixed> */
    public static function data(object $answer): array
    {
        return EvidenceAnswerPresenter::data($answer);
    }
}
