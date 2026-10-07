<?php

declare(strict_types=1);

namespace App\Presentation\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function data(array $data, int $status = 200): JsonResponse
    {
        // JSONB does not preserve object-key order on PostgreSQL. Sorting
        // associative keys before encoding keeps idempotent first/replay
        // responses byte-identical across SQLite and PostgreSQL.
        $response = response()->json(['data' => self::canonicalize($data)], $status);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private static function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map([self::class, 'canonicalize'], $value);
        }

        $result = [];
        foreach ($value as $key => $item) {
            $result[(string) $key] = self::canonicalize($item);
        }
        ksort($result);

        return $result;
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
