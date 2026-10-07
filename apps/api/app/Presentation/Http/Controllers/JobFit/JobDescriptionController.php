<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\JobFit\JobDescriptionPresenter;
use App\Application\JobFit\JobDescriptionService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class JobDescriptionController extends Controller
{
    public function __construct(private readonly JobDescriptionService $jobDescriptions) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $page = $this->positiveQuery($request, 'page', 1);
            $perPage = min(100, $this->positiveQuery($request, 'per_page', 20));
            $jobDescriptions = $this->jobDescriptions->list($request->user(), $page, $perPage)->appends($request->query());

            return response()->json([
                'data' => array_map(static fn ($item): array => JobDescriptionPresenter::data($item), $jobDescriptions->items()),
                'meta' => [
                    'page' => $jobDescriptions->currentPage(),
                    'per_page' => $jobDescriptions->perPage(),
                    'total' => $jobDescriptions->total(),
                ],
                'links' => [
                    'self' => $jobDescriptions->url($jobDescriptions->currentPage()),
                    'first' => $jobDescriptions->url(1),
                    'last' => $jobDescriptions->url($jobDescriptions->lastPage()),
                    'previous' => $jobDescriptions->previousPageUrl(),
                    'next' => $jobDescriptions->nextPageUrl(),
                ],
            ])->header('Cache-Control', 'private, no-store');
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $result = $this->jobDescriptions->create($request->user(), $request->all(), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function show(Request $request, string $jobDescription): JsonResponse
    {
        try {
            return ApiResponse::data(JobDescriptionPresenter::data($this->jobDescriptions->findOwnedActive($request->user(), $jobDescription)));
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function update(Request $request, string $jobDescription): JsonResponse
    {
        try {
            $result = $this->jobDescriptions->update(
                $request->user(), $jobDescription, $request->all(),
                (string) $request->header('If-Match'), (string) $request->header('Idempotency-Key'), $request->path(),
            );
            $response = ApiResponse::data($result['body']['data'], $result['status']);
            $response->headers->set('ETag', $result['etag']);

            return $response;
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function destroy(Request $request, string $jobDescription): Response|JsonResponse
    {
        try {
            $result = $this->jobDescriptions->delete(
                $request->user(), $jobDescription, (string) $request->header('If-Match'),
                (string) $request->header('Idempotency-Key'), $request->path(),
            );

            return response()->noContent($result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    private function positiveQuery(Request $request, string $key, int $default): int
    {
        $value = $request->query($key);
        if ($value === null) {
            return $default;
        }
        if (! is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1 || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                $key => [['code' => 'INVALID', 'message' => 'The value must be a positive integer.']],
            ]);
        }

        return (int) $value;
    }

    private function problem(ApiProblem $problem): JsonResponse
    {
        return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
    }
}
