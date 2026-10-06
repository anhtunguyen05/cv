<?php

declare(strict_types=1);

namespace App\Application\Cv;

use Illuminate\Support\Str;

final class ProfileDocument
{
    public const SECTIONS = [
        'summary', 'skills', 'education', 'experience', 'projects',
        'certificates', 'languages', 'activities',
    ];

    public const PERSONAL_KEYS = [
        'full_name', 'headline', 'email', 'phone', 'location',
        'website_url', 'linkedin_url', 'github_url',
    ];

    public static function empty(string $fullName): array
    {
        return [
            'personal_information' => [
                'full_name' => $fullName,
                'headline' => null,
                'email' => null,
                'phone' => null,
                'location' => null,
                'website_url' => null,
                'linkedin_url' => null,
                'github_url' => null,
            ],
            'summary' => null,
            'skills' => [],
            'education' => [],
            'experience' => [],
            'projects' => [],
            'certificates' => [],
            'languages' => [],
            'activities' => [],
        ];
    }

    public static function isUlid(mixed $id): bool
    {
        return is_string($id) && preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) === 1;
    }

    public static function id(): string
    {
        return (string) Str::ulid();
    }
}
