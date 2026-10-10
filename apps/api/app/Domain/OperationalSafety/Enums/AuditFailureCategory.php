<?php

declare(strict_types=1);

namespace App\Domain\OperationalSafety\Enums;

enum AuditFailureCategory: string
{
    case None = 'none';

    case Timeout = 'timeout';

    case RateLimited = 'rate_limited';

    case Transport = 'transport';

    case Malformed = 'malformed';

    case Validation = 'validation';

    case Cancelled = 'cancelled';

    case Dependency = 'dependency';

    case Unknown = 'unknown';
}
