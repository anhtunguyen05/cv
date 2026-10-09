<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Evidence;

use App\Application\Evidence\EvidenceInterviewPresenter;

final class EvidenceInterviewResource
{
    /** @return array<string,mixed> */
    public static function data(object $interview): array
    {
        return EvidenceInterviewPresenter::data($interview);
    }
}
