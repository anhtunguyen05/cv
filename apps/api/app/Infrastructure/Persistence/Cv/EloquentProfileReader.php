<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\Contracts\ProfileReader;
use App\Application\Cv\Data\ProfileView;
use App\Application\Cv\ProfileDocument;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvProfile;

final class EloquentProfileReader implements ProfileReader
{
    /** @return array{data:array<int,array<string,mixed>>,meta:array<string,int>,links:array<string,?string>} */
    public function summaries(int $userId, int $page, int $perPage, array $query): array
    {
        $profiles = CvProfile::query()
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['id', 'user_id', 'title', 'revision', 'created_at', 'updated_at'], 'page', $page)
            ->appends($query);

        return [
            'data' => array_map(static fn (CvProfile $profile): array => [
                'id' => (string) $profile->getKey(),
                'title' => (string) $profile->title,
                'revision' => (int) $profile->revision,
                'created_at' => $profile->created_at?->toISOString(),
                'updated_at' => $profile->updated_at?->toISOString(),
            ], $profiles->items()),
            'meta' => [
                'current_page' => $profiles->currentPage(),
                'per_page' => $profiles->perPage(),
                'total' => $profiles->total(),
                'last_page' => $profiles->lastPage(),
            ],
            'links' => [
                'first' => $profiles->url(1),
                'last' => $profiles->url($profiles->lastPage()),
                'prev' => $profiles->previousPageUrl(),
                'next' => $profiles->nextPageUrl(),
            ],
        ];
    }

    public function findOwned(int $userId, string $profileId): ProfileView
    {
        if (! ProfileDocument::isUlid($profileId)) {
            throw new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
        }

        $profile = CvProfile::query()->where('id', $profileId)->where('user_id', $userId)->first();
        if (! $profile instanceof CvProfile) {
            throw new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
        }

        return new ProfileView(
            id: (string) $profile->getKey(),
            title: (string) $profile->title,
            revision: (int) $profile->revision,
            document: is_array($profile->document) ? $profile->document : [],
            createdAt: $profile->created_at?->toISOString(),
            updatedAt: $profile->updated_at?->toISOString(),
        );
    }
}
