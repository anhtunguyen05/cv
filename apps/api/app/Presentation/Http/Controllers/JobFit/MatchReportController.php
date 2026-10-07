<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\JobFit\MatchReportPresenter;
use App\Application\JobFit\MatchService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MatchReportController extends Controller
{
    public function __construct(private readonly MatchService $reports) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $page = $this->positiveQuery($request, 'page', 1);
            $perPage = min(100, $this->positiveQuery($request, 'per_page', 20));
            $reports = $this->reports->list($request->user(), $page, $perPage)->appends($request->query());

            return response()->json([
                'data' => array_map(static fn ($item): array => MatchReportPresenter::data($item), $reports->items()),
                'meta' => [
                    'page' => $reports->currentPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
                'links' => [
                    'self' => $reports->url($reports->currentPage()),
                    'first' => $reports->url(1),
                    'last' => $reports->url($reports->lastPage()),
                    'previous' => $reports->previousPageUrl(),
                    'next' => $reports->nextPageUrl(),
                ],
            ])->header('Cache-Control', 'private, no-store');
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $result = $this->reports->create($request->user(), $request->all(), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function show(Request $request, string $matchReport): JsonResponse
    {
        try {
            return ApiResponse::data(MatchReportPresenter::data($this->reports->findOwned($request->user(), $matchReport)));
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
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
}
