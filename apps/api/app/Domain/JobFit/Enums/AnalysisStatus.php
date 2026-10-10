<?php

declare(strict_types=1);

namespace App\Domain\JobFit\Enums;

enum AnalysisStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
