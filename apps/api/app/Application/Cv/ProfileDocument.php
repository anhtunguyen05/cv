<?php

declare(strict_types=1);

namespace App\Application\Cv;

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
        $timestamp = (int) floor(microtime(true) * 1000);
        $bytes = pack('N', ($timestamp >> 32) & 0xFFFFFFFF)
            .pack('N', $timestamp & 0xFFFFFFFF)
            .random_bytes(10);
        $bits = '00';
        foreach (unpack('C*', $bytes) as $byte) {
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $id = '';
        for ($index = 0; $index < 26; $index++) {
            $id .= $alphabet[bindec(substr($bits, $index * 5, 5))];
        }

        return $id;
    }

    public static function normalizeText(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value) ?? $value;
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        return $value;
    }
}
