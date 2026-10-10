<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Cv\Data\ProfileRecord;

final class ProfilePresenter
{
    /** @return array<string,mixed> */
    public static function summary(ProfileRecord $profile): array
    {
        return [
            'id' => $profile->id,
            'title' => $profile->title,
            'revision' => $profile->revision,
            'created_at' => $profile->createdAt,
            'updated_at' => $profile->updatedAt,
        ];
    }

    public static function data(ProfileRecord $profile): array
    {
        $document = $profile->document;

        return [
            'id' => $profile->id,
            'title' => $profile->title,
            'revision' => $profile->revision,
            ...$document,
            'created_at' => $profile->createdAt,
            'updated_at' => $profile->updatedAt,
        ];
    }
}
