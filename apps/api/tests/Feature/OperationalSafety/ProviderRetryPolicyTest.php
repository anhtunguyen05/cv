<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\ProviderRetryPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProviderRetryPolicyTest extends TestCase
{
    public function test_new_operation_can_start(): void
    {
        $now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');

        self::assertTrue($this->policy()->allows(0, null, $now));
        self::assertTrue($this->policy()->allows(1, $now, $now));
    }

    public function test_two_recent_attempts_are_bounded(): void
    {
        $now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');
        $oldest = new DateTimeImmutable('2026-10-08T11:59:30+00:00');

        self::assertFalse($this->policy()->allows(2, $oldest, $now));
        self::assertSame(30, $this->policy()->retryAfterSeconds($oldest, $now));
    }

    public function test_window_expiry_allows_a_new_attempt(): void
    {
        $now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');
        $oldest = new DateTimeImmutable('2026-10-08T11:59:00+00:00');

        self::assertTrue($this->policy()->allows(2, $oldest, $now));
        self::assertSame(0, $this->policy()->retryAfterSeconds($oldest, $now));
    }

    public function test_missing_timestamp_fails_closed_after_budget(): void
    {
        $now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');

        self::assertFalse($this->policy()->allows(2, null, $now));
        self::assertSame(ProviderRetryPolicy::WINDOW_SECONDS, $this->policy()->retryAfterSeconds(null, $now));
    }

    public function test_negative_attempt_count_is_invalid(): void
    {
        $now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');

        self::assertFalse($this->policy()->allows(-1, null, $now));
    }

    private function policy(): ProviderRetryPolicy
    {
        return new ProviderRetryPolicy;
    }
}
