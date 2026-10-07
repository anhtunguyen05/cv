<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\CvProfile;

final class ProfilePresenter
{
    public static function data(CvProfile $profile): array
    {
        $document = is_array($profile->document) ? $profile->document : [];

        return [
            'id' => (string) $profile->getKey(),
            'title' => $profile->title,
            'revision' => (int) $profile->revision,
            ...$document,
            'created_at' => $profile->created_at?->toISOString(),
            'updated_at' => $profile->updated_at?->toISOString(),
        ];
    }
}
