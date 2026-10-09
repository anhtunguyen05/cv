<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv;

use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\Data\IdempotencyRecord;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvIdempotencyKey;
use Illuminate\Support\Carbon;

final class EloquentIdempotencyStore implements IdempotencyStore
{
    public function find(int $userId, string $operation, string $key): ?IdempotencyRecord
    {
        $row = CvIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('operation', $operation)
            ->where('key', $key)
            ->lockForUpdate()
            ->first();
        if ($row === null) {
            return null;
        }

        return new IdempotencyRecord(
            requestHash: (string) $row->request_hash,
            responseBody: is_array($row->response_body) ? $row->response_body : [],
            responseStatus: (int) $row->response_status,
            expired: $row->expires_at !== null && $row->expires_at->isPast(),
        );
    }

    public function forget(int $userId, string $operation, string $key): void
    {
        CvIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('operation', $operation)
            ->where('key', $key)
            ->delete();
    }

    public function record(int $userId, string $operation, string $key, string $requestHash, array $responseBody, int $responseStatus): void
    {
        CvIdempotencyKey::query()->create([
            'user_id' => $userId,
            'operation' => $operation,
            'key' => $key,
            'request_hash' => $requestHash,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'expires_at' => Carbon::now()->addDay(),
            'created_at' => Carbon::now(),
        ]);
    }
}
