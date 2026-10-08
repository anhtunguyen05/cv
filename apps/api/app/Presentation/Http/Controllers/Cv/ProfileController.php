<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Cv;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\ProfilePresenter;
use App\Application\Cv\ProfileService;
use App\Models\User;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Cv\ProfileCreateRequest;
use App\Presentation\Http\Requests\Cv\ProfilePersonalInformationRequest;
use App\Presentation\Http\Requests\Cv\ProfileSectionRequest;
use App\Presentation\Http\Requests\Cv\ProfileTitleRequest;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profiles) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        try {
            $page = $this->positiveQuery($request, 'page', 1);
            $perPage = min(100, $this->positiveQuery($request, 'per_page', 20));
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
        $profiles = $user->cvProfiles()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['id', 'user_id', 'title', 'revision', 'created_at', 'updated_at'], 'page', $page)
            ->appends($request->query());

        $response = response()->json([
            'data' => array_map(static fn ($profile): array => ProfilePresenter::summary($profile), $profiles->items()),
            'meta' => [
                'current_page' => $profiles->currentPage(),
                'per_page' => $profiles->perPage(),
                'total' => $profiles->total(),
                'last_page' => $profiles->lastPage(),
            ],
            'links' => [
                'first' => $profiles->url(1),
                'last' => $profiles->url($profiles->lastPage()),
                'prev' => $profiles->previousPageUrl(),
                'next' => $profiles->nextPageUrl(),
            ],
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    public function store(ProfileCreateRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $result = $this->profiles->create($user, $request->validated(), (string) $request->header('Idempotency-Key'), $request->path());

            return ApiResponse::data($result['body']['data'], $result['status']);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function show(Request $request, string $profile): JsonResponse
    {
        try {
            $model = $this->profiles->findOwned($request->user(), $profile);

            return ApiResponse::data(ProfilePresenter::data($model));
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function updatePersonal(ProfilePersonalInformationRequest $request, string $profile): JsonResponse
    {
        try {
            $result = $this->profiles->updatePersonal($request->user(), $profile, $request->input('personal_information'), (string) $request->header('If-Match'));

            return $this->updatedResponse($result);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function updateTitle(ProfileTitleRequest $request, string $profile): JsonResponse
    {
        try {
            $result = $this->profiles->updateTitle($request->user(), $profile, (string) $request->input('title'), (string) $request->header('If-Match'));

            return $this->updatedResponse($result);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    public function updateSection(ProfileSectionRequest $request, string $profile, string $section): JsonResponse
    {
        try {
            $result = $this->profiles->updateSection($request->user(), $profile, $section, $request->input($section), (string) $request->header('If-Match'));

            return $this->updatedResponse($result);
        } catch (ApiProblem $problem) {
            return $this->problem($problem);
        }
    }

    private function updatedResponse(array $result): JsonResponse
    {
        $response = ApiResponse::data($result['body']['data'], $result['status']);
        $response->headers->set('ETag', $result['etag']);

        return $response;
    }

    private function problem(ApiProblem $problem): JsonResponse
    {
        return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, $problem->status);
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
