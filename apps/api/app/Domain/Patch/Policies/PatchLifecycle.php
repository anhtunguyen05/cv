<?php

declare(strict_types=1);

namespace App\Domain\Patch\Policies;

use App\Domain\Patch\Enums\PatchStatus;

final class PatchLifecycle
{
    public static function canTransition(PatchStatus $from, PatchStatus $to): bool
    {
        return match ($from) {
            PatchStatus::PendingValidation => $to === PatchStatus::Pending,
            PatchStatus::Pending => in_array($to, [PatchStatus::Rejected, PatchStatus::Invalid, PatchStatus::Applied], true),
            PatchStatus::Rejected, PatchStatus::Invalid, PatchStatus::Applied => false,
        };
    }
}
