<?php

declare(strict_types=1);

namespace App\Domain\OperationalSafety\Enums;

enum AuditLatencyCategory: string
{
    case Fast = 'fast';

    case BoundedFailure = 'bounded_failure';

    case Timeout = 'timeout';

    case Cancelled = 'cancelled';
}
