<?php

declare(strict_types=1);

namespace App\Presentation\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
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

        return response()->json($payload, $status);
    }
}
