<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Cv\Contracts\IdempotencyStore;
use App\Domain\Shared\ValueObjects\IdempotencyKey;
use InvalidArgumentException;

final class CvIdempotency
{
    public static function validate(string $key): void
    {
        try {
            IdempotencyKey::fromString($key);
        } catch (InvalidArgumentException) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                'idempotency_key' => [['code' => 'INVALID', 'message' => 'The Idempotency-Key must be a lowercase UUID v4.']],
            ]);
        }
    }

    /** @return array{replayed: bool, body: array<string, mixed>|null, status: int|null} */
    public static function existing(IdempotencyStore $store, int $userId, string $operation, string $key, string $hash): array
    {
        $row = $store->find($userId, $operation, $key);
        if ($row === null) {
            return ['replayed' => false, 'body' => null, 'status' => null];
        }
        if ($row->expired) {
            $store->forget($userId, $operation, $key);

            return ['replayed' => false, 'body' => null, 'status' => null];
        }
        if (! hash_equals($row->requestHash, $hash)) {
            throw new ApiProblem('IDEMPOTENCY_KEY_REUSED', 'The idempotency key was already used for another request.', 409);
        }

        return ['replayed' => true, 'body' => $row->responseBody, 'status' => $row->responseStatus];
    }

    public static function record(IdempotencyStore $store, int $userId, string $operation, string $key, string $hash, array $body, int $status): void
    {
        $store->record($userId, $operation, $key, $hash, $body, $status);
    }
}
