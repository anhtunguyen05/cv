<?php

use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->trustProxies(at: env('TRUSTED_PROXIES'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::error('UNAUTHENTICATED', 'Authentication is required.', status: 401);
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::error('SESSION_EXPIRED', 'The session has expired.', status: 419);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            $details = [];
            foreach ($exception->errors() as $field => $messages) {
                $details[$field] = array_map(
                    static fn (string $message): array => ['code' => 'INVALID', 'message' => $message],
                    $messages,
                );
            }

            return ApiResponse::error('VALIDATION_FAILED', 'One or more fields are invalid.', $details, 422);
        });

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            $response = ApiResponse::error('THROTTLED', 'Too many attempts. Try again later.', status: 429);
            foreach ($exception->getHeaders() as $header => $value) {
                $response->header($header, $value);
            }

            return $response;
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            return ApiResponse::error('INTERNAL_ERROR', 'An unexpected error occurred.', status: 500);
        });
    })->create();
