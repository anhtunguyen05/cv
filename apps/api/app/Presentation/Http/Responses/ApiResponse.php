<?php

declare(strict_types=1);

namespace App\Presentation\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function data(array $data, int $status = 200): JsonResponse
    {
        $response = response()->json(['data' => $data], $status);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    public static function error(
        string $code,
        string $message,
        array $details = [],
        int $status = 400,
    ): JsonResponse {
        $payload = ['code' => $code, 'message' => $message];

        if ($details !== []) {
            $payload['details'] = $details;
        }

        $response = response()->json($payload, $status);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
