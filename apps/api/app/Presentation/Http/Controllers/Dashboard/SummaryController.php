<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Dashboard;

use App\Models\CvProfile;
use App\Models\JobDescription;
use App\Models\MatchReport;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->getKey();

        return ApiResponse::data([
            'cv_profiles' => CvProfile::query()->where('user_id', $userId)->count(),
            'job_descriptions' => JobDescription::query()->where('user_id', $userId)->whereNull('deleted_at')->count(),
            'match_reports' => MatchReport::query()->where('user_id', $userId)->count(),
        ]);
    }
}
