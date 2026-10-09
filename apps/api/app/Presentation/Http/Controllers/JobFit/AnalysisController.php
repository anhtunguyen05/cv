<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\ApiProblem;
use App\Application\JobFit\AnalysisService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Errors\ProblemStatusMapper;
use App\Presentation\Http\Resources\JobFit\AnalysisResource;
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
            $result = $this->analyses->create(new AuthenticatedUser((int) $request->user()->getAuthIdentifier()), $jobDescription, (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, ProblemStatusMapper::status($problem));
        }
    }

    public function show(Request $request, string $jobDescription, string $analysis): JsonResponse
    {
        try {
            return ApiResponse::data(AnalysisResource::data($this->analyses->findOwned(new AuthenticatedUser((int) $request->user()->getAuthIdentifier()), $jobDescription, $analysis)));
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, ProblemStatusMapper::status($problem));
        }
    }
}
