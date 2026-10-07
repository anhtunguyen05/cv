<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\JobFit\AnalysisPresenter;
use App\Application\JobFit\AnalysisService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AnalysisController extends Controller
{
    public function __construct(private readonly AnalysisService $analyses) {}

    public function store(Request $request, string $jobDescription): JsonResponse
    {
        try {
            if ($request->all() !== []) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                    'body' => [['code' => 'INVALID', 'message' => 'The analysis request must not contain fields.']],
                ]);
            }
            $result = $this->analyses->create($request->user(), $jobDescription, (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function show(Request $request, string $jobDescription, string $analysis): JsonResponse
    {
        try {
            return ApiResponse::data(AnalysisPresenter::data($this->analyses->findOwned($request->user(), $jobDescription, $analysis)));
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }
}
