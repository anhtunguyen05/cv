<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\JobFit;

use App\Application\JobFit\JobDescriptionPresenter;

final class JobDescriptionResource
{
    /** @return array<string,mixed> */
    public static function data(object $jobDescription): array
    {
        return JobDescriptionPresenter::data($jobDescription);
    }
}
