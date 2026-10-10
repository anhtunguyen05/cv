<?php

declare(strict_types=1);

namespace App\Application\Cv\Contracts;

use App\Application\Cv\Data\ProfileRecord;

interface ProfileRepository
{
    public function countOwned(int $userId): int;

    public function findOwned(int $userId, string $profileId): ?ProfileRecord;

    public function lockOwned(int $userId, string $profileId): ?ProfileRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(int $userId, string $id, string $title, string $normalizedTitle, array $document): ProfileRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ProfileRecord $profile, array $attributes): ProfileRecord;
}
