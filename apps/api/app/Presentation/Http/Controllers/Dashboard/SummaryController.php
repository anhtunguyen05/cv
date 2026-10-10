<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Dashboard;

use App\Application\Dashboard\Contracts\DashboardSummaryReader;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SummaryController extends Controller
{
    public function __construct(private readonly DashboardSummaryReader $summaries) {}

    public function __invoke(Request $request): JsonResponse
    {
        return ApiResponse::data($this->summaries->forUser((int) $request->user()->getKey()));
    }
}
