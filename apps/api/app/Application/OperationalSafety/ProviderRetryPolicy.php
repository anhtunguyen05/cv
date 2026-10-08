<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use DateTimeImmutable;

final class ProviderRetryPolicy
{
    public const MAX_ATTEMPTS = 2;

    public const WINDOW_SECONDS = 60;

    public function allows(int $attemptCount, ?DateTimeImmutable $oldestAttemptAt, DateTimeImmutable $now): bool
    {
        if ($attemptCount < 0) {
            return false;
        }

        if ($attemptCount < self::MAX_ATTEMPTS) {
            return true;
        }

        if ($oldestAttemptAt === null) {
            return false;
        }

        return $oldestAttemptAt->getTimestamp() <= $now->getTimestamp() - self::WINDOW_SECONDS;
    }

    public function retryAfterSeconds(?DateTimeImmutable $oldestAttemptAt, DateTimeImmutable $now): int
    {
        if ($oldestAttemptAt === null) {
            return self::WINDOW_SECONDS;
        }

        return max(0, ($oldestAttemptAt->getTimestamp() + self::WINDOW_SECONDS) - $now->getTimestamp());
    }
}
