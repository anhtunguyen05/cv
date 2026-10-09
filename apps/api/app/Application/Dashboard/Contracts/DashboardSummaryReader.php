<?php

declare(strict_types=1);

namespace App\Application\Dashboard\Contracts;

interface DashboardSummaryReader
{
    /** @return array{cv_profiles:int,job_descriptions:int,match_reports:int} */
    public function forUser(int $userId): array;
}
