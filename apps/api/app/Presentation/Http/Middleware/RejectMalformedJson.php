<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use JsonException;

final class RejectMalformedJson
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->isJson() && trim($request->getContent()) !== '') {
            try {
                json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return ApiResponse::error('INVALID_REQUEST_BODY', 'The request body is malformed.', status: 400);
            }
        }

        return $next($request);
    }
}
