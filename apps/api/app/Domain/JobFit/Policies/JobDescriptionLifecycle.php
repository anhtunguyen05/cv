<?php

declare(strict_types=1);

namespace App\Domain\JobFit\Policies;

final class JobDescriptionLifecycle
{
    public static function canUpdate(bool $deleted): bool
    {
        return ! $deleted;
    }

    public static function canDelete(bool $deleted): bool
    {
        return ! $deleted;
    }

    public static function nextRevision(int $currentRevision): int
    {
        return $currentRevision + 1;
    }
}
