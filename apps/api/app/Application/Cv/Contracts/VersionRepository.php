<?php

declare(strict_types=1);

namespace App\Application\Cv\Contracts;

use App\Application\Cv\Data\VersionPage;
use App\Application\Cv\Data\VersionRecord;

interface VersionRepository
{
    public function findOwned(int $userId, string $versionId): ?VersionRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): VersionRecord;

    /**
     * @param  array<string, mixed>  $query
     */
    public function listOwned(int $userId, int $page, int $perPage, ?string $profileId, array $query = []): VersionPage;
}
