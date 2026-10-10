<?php

declare(strict_types=1);

namespace App\Domain\Evidence\Policies;

use App\Domain\Evidence\Enums\EvidenceInterviewStatus;

final class EvidenceInterviewLifecycle
{
    public static function canTransition(EvidenceInterviewStatus $from, EvidenceInterviewStatus $to): bool
    {
        return match ($from) {
            EvidenceInterviewStatus::Active => in_array($to, [EvidenceInterviewStatus::Completed, EvidenceInterviewStatus::Expired], true),
            EvidenceInterviewStatus::Completed, EvidenceInterviewStatus::Expired => false,
        };
    }
}
