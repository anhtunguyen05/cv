<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\JobFit;

use App\Application\JobFit\MatchReportPresenter;

final class MatchReportResource
{
    /** @return array<string,mixed> */
    public static function data(object $report): array
    {
        return MatchReportPresenter::data($report);
    }
}
