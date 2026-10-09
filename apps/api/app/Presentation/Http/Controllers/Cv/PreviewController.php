<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Cv;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\ApiProblem;
use App\Application\Cv\PreviewPresenter;
use App\Application\Cv\PreviewService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Errors\ProblemStatusMapper;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PreviewController extends Controller
{
    public function __construct(private readonly PreviewService $previews) {}

    public function show(Request $request, string $cvVersion): JsonResponse
    {
        try {
            [$templateId, $templateVersion] = $this->sourceQuery($request);

            return ApiResponse::data(PreviewPresenter::data($this->previews->resolve(
                new AuthenticatedUser((int) $request->user()->getAuthIdentifier()),
                $cvVersion,
                $templateId,
                $templateVersion,
            )));
        } catch (ApiProblem $problem) {
            return ApiResponse::error($problem->errorCode, $problem->getMessage(), $problem->details, ProblemStatusMapper::status($problem));
        } catch (\Throwable) {
            return ApiResponse::error(
                'PREVIEW_PROJECTION_FAILED',
                'The Preview could not be built. Try again.',
                [],
                500,
            );
        }
    }

    /** @return array{string, string} */
    private function sourceQuery(Request $request): array
    {
        $templateId = $request->query('template_id');
        $templateVersion = $request->query('template_version');
        if (! is_string($templateId) || ! is_string($templateVersion)
            || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $templateId) !== 1
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,19}$/', $templateVersion) !== 1) {
            throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The preview source is invalid.', 422, [
                'template_id' => [['code' => 'INVALID', 'message' => 'A single valid template_id is required.']],
                'template_version' => [['code' => 'INVALID', 'message' => 'A single non-empty template_version is required.']],
            ]);
        }

        return [$templateId, $templateVersion];
    }
}
