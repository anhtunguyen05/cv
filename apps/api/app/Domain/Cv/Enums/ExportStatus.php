<?php

declare(strict_types=1);

namespace App\Domain\Cv\Enums;

enum ExportStatus: string
{
    case Initiated = 'initiated';
    case Completed = 'completed';
    case Failed = 'failed';
}
