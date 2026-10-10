<?php

declare(strict_types=1);

namespace App\Domain\Patch\Enums;

enum PatchAttemptStatus: string
{
    case Requested = 'requested';

    case Running = 'running';

    case Succeeded = 'succeeded';

    case RetryableFailure = 'retryable_failure';

    case TerminalFailure = 'terminal_failure';
}
