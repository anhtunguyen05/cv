<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;

final class RequireCsrfToken
{
    public function handle(Request $request, Closure $next): mixed
    {
        $token = (string) ($request->header('X-CSRF-TOKEN') ?: $request->input('_token', ''));
        $sessionToken = (string) $request->session()->token();

        if ($token === '' || $sessionToken === '' || ! hash_equals($sessionToken, $token)) {
            return ApiResponse::error('SESSION_EXPIRED', 'The session has expired.', status: 419);
        }

        return $next($request);
    }
}
