<?php

declare(strict_types=1);

namespace App\Domain\OperationalSafety\Enums;

enum AuditEventStatus: string
{
    case Succeeded = 'succeeded';

    case Failed = 'failed';

    case TimedOut = 'timed_out';

    case Cancelled = 'cancelled';

    case NoData = 'no_data';

    case Unavailable = 'unavailable';
}
