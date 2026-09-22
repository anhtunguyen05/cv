<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;

final class RejectBearerToken
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->bearerToken() !== null) {
            return ApiResponse::error('UNAUTHENTICATED', 'Authentication is required.', status: 401);
        }

        return $next($request);
    }
}
