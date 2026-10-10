<?php

declare(strict_types=1);

namespace App\Application\Cv\Contracts;

use App\Application\Cv\Data\ProfileView;

interface ProfileReader
{
    /** @return array{data:array<int,array<string,mixed>>,meta:array<string,int>,links:array<string,?string>} */
    public function summaries(int $userId, int $page, int $perPage, array $query): array;

    public function findOwned(int $userId, string $profileId): ProfileView;
}
