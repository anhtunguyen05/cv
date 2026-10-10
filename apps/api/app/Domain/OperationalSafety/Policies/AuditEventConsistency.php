<?php

declare(strict_types=1);

namespace App\Domain\OperationalSafety\Policies;

use App\Domain\OperationalSafety\Enums\AuditFailureCategory;
use App\Domain\OperationalSafety\Enums\AuditLatencyCategory;

final class AuditEventConsistency
{
    public static function isConsistent(
        string $status,
        AuditFailureCategory $failure,
        AuditLatencyCategory $latency,
    ): bool {
        return match ($status) {
            'succeeded' => $failure === AuditFailureCategory::None && $latency === AuditLatencyCategory::Fast,
            'timed_out' => $failure === AuditFailureCategory::Timeout && $latency === AuditLatencyCategory::Timeout,
            'cancelled' => $failure === AuditFailureCategory::Cancelled && $latency === AuditLatencyCategory::Cancelled,
            'failed' => $failure !== AuditFailureCategory::None && $latency === AuditLatencyCategory::BoundedFailure,
            default => false,
        };
    }
}
