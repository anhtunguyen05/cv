<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Cv;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\VersionPresenter;
use App\Application\Cv\VersionService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Cv\VersionCreateRequest;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VersionController extends Controller
{
    public function __construct(private readonly VersionService $versions) {}

    public function store(VersionCreateRequest $request, string $profile): JsonResponse
    {
        try {
            $result = $this->versions->create(
                $request->user(),
                $profile,
                (string) $request->input('name'),
                (string) $request->header('If-Match'),
                (string) $request->header('Idempotency-Key'),
                $request->path(),
            );

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $page = $this->positiveQuery($request, 'page', 1);
            $perPage = min(100, $this->positiveQuery($request, 'per_page', 20));
            $profileId = $request->query('profile_id');
            if ($profileId !== null && ! is_string($profileId)) {
                throw new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
            }
            $versions = $this->versions->list($request->user(), $page, $perPage, $profileId)->appends($request->query());

            $response = response()->json([
                'data' => array_map(static fn ($version): array => VersionPresenter::data($version), $versions->items()),
                'meta' => [
                    'current_page' => $versions->currentPage(),
                    'per_page' => $versions->perPage(),
                    'total' => $versions->total(),
                    'last_page' => $versions->lastPage(),
                ],
                'links' => [
                    'first' => $versions->url(1),
                    'last' => $versions->url($versions->lastPage()),
                    'prev' => $versions->previousPageUrl(),
                    'next' => $versions->nextPageUrl(),
                ],
            ]);
            $response->headers->set('Cache-Control', 'private, no-store');

            return $response;
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
        }
    }

    public function show(Request $request, string $version): JsonResponse
    {
        try {
            return ApiResponse::data(VersionPresenter::data($this->versions->findOwned($request->user(), $version)));
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
        if (! is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                $key => [['code' => 'INVALID', 'message' => 'The value must be a positive integer.']],
            ]);
        }

        return (int) $value;
    }
}
