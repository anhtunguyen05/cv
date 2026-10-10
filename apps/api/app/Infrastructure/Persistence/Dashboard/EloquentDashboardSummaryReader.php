<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dashboard;

use App\Application\Dashboard\Contracts\DashboardSummaryReader;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvProfile;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescription;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\MatchReport;

final class EloquentDashboardSummaryReader implements DashboardSummaryReader
{
    /** @return array{cv_profiles:int,job_descriptions:int,match_reports:int} */
    public function forUser(int $userId): array
    {
        return [
            'cv_profiles' => CvProfile::query()->where('user_id', $userId)->count(),
            'job_descriptions' => JobDescription::query()->where('user_id', $userId)->whereNull('deleted_at')->count(),
            'match_reports' => MatchReport::query()->where('user_id', $userId)->count(),
        ];
    }
}
