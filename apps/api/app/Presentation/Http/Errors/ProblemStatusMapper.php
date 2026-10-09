<?php

declare(strict_types=1);

namespace App\Presentation\Http\Errors;

use App\Application\Cv\ApiProblem;

/**
 * Maps stable application error codes to the existing HTTP contract.
 *
 * Application services intentionally expose a machine code and details. HTTP
 * status selection belongs to this presentation adapter so transports other
 * than HTTP are not coupled to response semantics.
 */
final class ProblemStatusMapper
{
    public static function status(ApiProblem $problem): int
    {
        return match ($problem->errorCode) {
            'RESOURCE_NOT_FOUND', 'CV_VERSION_NOT_FOUND' => 404,
            'VALIDATION_FAILED', 'PATCH_PROPOSAL_INVALID',
            'PATCH_VALIDATION_FAILED', 'PREVIEW_SOURCE_INVALID',
            'RENDER_SOURCE_UNSUPPORTED' => 422,
            'PROFILE_NOT_VERSIONABLE', 'TEMPLATE_UNAVAILABLE' => 409,
            'PATCH_PROVIDER_UNAVAILABLE', 'PATCH_PROVIDER_TIMEOUT' => 503,
            'PATCH_RATE_LIMITED' => 429,
            'DERIVATION_TEMPORARILY_UNAVAILABLE' => 503,
            default => $problem->status,
        };
    }
}
