<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\JobFit;

use App\Application\JobFit\AnalysisPresenter;

final class AnalysisResource
{
    /** @return array<string,mixed> */
    public static function data(object $analysis): array
    {
        return AnalysisPresenter::data($analysis);
    }
}
