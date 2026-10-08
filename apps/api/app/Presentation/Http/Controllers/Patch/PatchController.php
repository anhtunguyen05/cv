<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Patch\PatchService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatchController extends Controller
{
    public function __construct(private readonly PatchService $patches) {}

    public function generate(Request $request, string $interview): JsonResponse
    {
        try {
            $result = $this->patches->generate($request->user(), $interview, $request->all(), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function show(Request $request, string $patch): JsonResponse
    {
        try {
            return ApiResponse::data($this->patches->show($request->user(), $patch));
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function edit(Request $request, string $patch): JsonResponse
    {
        try {
            $result = $this->patches->edit($request->user(), $patch, $request->all(), (string) $request->header('If-Match'), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function reject(Request $request, string $patch): JsonResponse
    {
        try {
            $result = $this->patches->reject($request->user(), $patch, $request->all(), (string) $request->header('If-Match'), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function approve(Request $request, string $patch): JsonResponse
    {
        try {
            $result = $this->patches->approve($request->user(), $patch, $request->all(), (string) $request->header('If-Match'), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function regenerate(Request $request, string $patch): JsonResponse
    {
        try {
            $result = $this->patches->regenerate($request->user(), $patch, $request->all(), (string) $request->header('If-Match'), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    private function problem(ApiProblem $problem): JsonResponse
    {
        return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
    }
}
