<?php

declare(strict_types=1);

namespace App\Application\Auth\Data;

use App\Domain\Shared\ValueObjects\OwnershipIdentity;

/**
 * The authenticated principal made available to application use cases.
 *
 * This deliberately contains identity data only. The HTTP/authentication
 * adapter is responsible for creating it from the current request user.
 */
final readonly class AuthenticatedUser
{
    public readonly int $id;

    public function __construct(int $id)
    {
        $this->id = OwnershipIdentity::fromInt($id)->userId;
    }

    /** Compatibility accessor for workflows migrating from Eloquent identities. */
    public function getKey(): int
    {
        return $this->id;
    }
}
