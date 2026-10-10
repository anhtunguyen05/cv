<?php

declare(strict_types=1);

namespace App\Domain\Evidence\Enums;

enum EvidenceInterviewStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Expired = 'expired';
}
