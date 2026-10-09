<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv;

use App\Application\Cv\Contracts\ProfileRepository;
use App\Application\Cv\Data\ProfileRecord;
use App\Application\Cv\Exceptions\ProfileTitleUnavailable;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvProfile;
use Illuminate\Database\QueryException;

final class EloquentProfileRepository implements ProfileRepository
{
    public function countOwned(int $userId): int
    {
        return CvProfile::query()->where('user_id', $userId)->count();
    }

    public function findOwned(int $userId, string $profileId): ?ProfileRecord
    {
        $profile = CvProfile::query()
            ->where('id', $profileId)
            ->where('user_id', $userId)
            ->first();

        return $profile instanceof CvProfile ? $this->record($profile) : null;
    }

    public function lockOwned(int $userId, string $profileId): ?ProfileRecord
    {
        $profile = CvProfile::query()
            ->where('id', $profileId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        return $profile instanceof CvProfile ? $this->record($profile) : null;
    }

    public function create(int $userId, string $id, string $title, string $normalizedTitle, array $document): ProfileRecord
    {
        try {
            $profile = CvProfile::query()->create([
                'id' => $id,
                'user_id' => $userId,
                'title' => $title,
                'normalized_title' => $normalizedTitle,
                'revision' => 1,
                'schema_version' => '1.0',
                'document' => $document,
            ]);
        } catch (QueryException $exception) {
            if ($this->isConstraint($exception)) {
                throw new ProfileTitleUnavailable(previous: $exception);
            }

            throw $exception;
        }

        return $this->record($profile->fresh() ?? $profile);
    }

    public function update(ProfileRecord $profile, array $attributes): ProfileRecord
    {
        $model = CvProfile::query()
            ->where('id', $profile->id)
            ->where('user_id', $profile->userId)
            ->lockForUpdate()
            ->first();
        if (! $model instanceof CvProfile) {
            return $profile;
        }

        try {
            $model->forceFill($attributes)->save();
        } catch (QueryException $exception) {
            if ($this->isConstraint($exception)) {
                throw new ProfileTitleUnavailable(previous: $exception);
            }

            throw $exception;
        }

        return $this->record($model->fresh() ?? $model);
    }

    private function record(CvProfile $profile): ProfileRecord
    {
        return new ProfileRecord(
            id: (string) $profile->getKey(),
            userId: (int) $profile->user_id,
            title: (string) $profile->title,
            revision: (int) $profile->revision,
            schemaVersion: (string) $profile->schema_version,
            document: is_array($profile->document) ? $profile->document : [],
            createdAt: $profile->created_at?->toISOString(),
            updatedAt: $profile->updated_at?->toISOString(),
        );
    }

    private function isConstraint(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23505', '23000', '19'], true);
    }
}
