<?php

declare(strict_types=1);

namespace App\Domain\Patch\Enums;

enum PatchStatus: string
{
    case PendingValidation = 'pending_validation';
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Invalid = 'invalid';
    case Applied = 'applied';
}
