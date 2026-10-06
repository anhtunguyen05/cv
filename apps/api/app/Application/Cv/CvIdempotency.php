<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\CvIdempotencyKey;
use App\Models\User;
use Illuminate\Support\Carbon;

final class CvIdempotency
{
    public static function validate(string $key): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $key) !== 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                'idempotency_key' => [['code' => 'INVALID', 'message' => 'The Idempotency-Key must be a lowercase UUID v4.']],
            ]);
        }
    }

    /** @return array{replayed: bool, body: array<string, mixed>|null, status: int|null} */
    public static function existing(User $user, string $operation, string $key, string $hash): array
    {
        $row = CvIdempotencyKey::query()
            ->where('user_id', $user->getKey())
            ->where('operation', $operation)
            ->where('key', $key)
            ->lockForUpdate()
            ->first();

        if ($row === null) {
            return ['replayed' => false, 'body' => null, 'status' => null];
        }
        if ($row->expires_at !== null && $row->expires_at->isPast()) {
            $row->delete();

            return ['replayed' => false, 'body' => null, 'status' => null];
        }
        if (! hash_equals($row->request_hash, $hash)) {
            throw new ApiProblem('IDEMPOTENCY_KEY_REUSED', 'The idempotency key was already used for another request.', 409);
        }

        return ['replayed' => true, 'body' => $row->response_body, 'status' => $row->response_status];
    }

    public static function record(User $user, string $operation, string $key, string $hash, array $body, int $status): void
    {
        CvIdempotencyKey::query()->create([
            'user_id' => $user->getKey(),
            'operation' => $operation,
            'key' => $key,
            'request_hash' => $hash,
            'response_status' => $status,
            'response_body' => $body,
            'expires_at' => Carbon::now()->addDay(),
            'created_at' => Carbon::now(),
        ]);
    }
}
