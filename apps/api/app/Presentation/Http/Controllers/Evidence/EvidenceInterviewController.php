<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Evidence;

use App\Application\Cv\ApiProblem;
use App\Application\Evidence\EvidenceAnswerService;
use App\Application\Evidence\EvidenceInterviewService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EvidenceInterviewController extends Controller
{
    public function __construct(
        private readonly EvidenceInterviewService $interviews,
        private readonly EvidenceAnswerService $answers,
    ) {}

    public function store(Request $request, string $matchReport): JsonResponse
    {
        try {
            $result = $this->interviews->start(
                $request->user(),
                $matchReport,
                (string) $request->header('Idempotency-Key'),
                $request->path(),
                $request->all(),
            );

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function show(Request $request, string $interview): JsonResponse
    {
        try {
            return ApiResponse::data($this->interviews->show($request->user(), $interview));
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function answer(Request $request, string $interview): JsonResponse
    {
        try {
            $result = $this->answers->answer(
                $request->user(),
                $interview,
                $request->all(),
                (string) $request->header('Idempotency-Key'),
                $request->path(),
            );

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }
}
