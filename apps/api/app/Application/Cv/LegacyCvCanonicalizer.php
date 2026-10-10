<?php

declare(strict_types=1);

namespace App\Application\Cv;

/**
 * Converts the preliminary scaffold's section names/shapes into Profile v1.
 * It is deliberately additive and is only used when the aligned JSON column is empty.
 */
final class LegacyCvCanonicalizer
{
    public static function profileDocument(mixed $legacy, string $fallbackFullName): array
    {
        $legacy = is_array($legacy) ? $legacy : [];
        $personal = is_array($legacy['personal_info'] ?? null) ? $legacy['personal_info'] : [];
        $first = self::text($personal['first_name'] ?? null);
        $last = self::text($personal['last_name'] ?? null);
        $fullName = self::text($personal['full_name'] ?? null) ?: trim(implode(' ', array_filter([$first, $last]))) ?: self::text($fallbackFullName);
        $summary = $legacy['summary'] ?? null;
        if (is_array($summary)) {
            $summary = $summary['text'] ?? null;
        }

        return [
            'personal_information' => [
                'full_name' => $fullName,
                'headline' => self::text($personal['headline'] ?? null),
                'email' => self::text($personal['email'] ?? null),
                'phone' => self::text($personal['phone'] ?? null),
                'location' => self::text($personal['location'] ?? ($personal['city'] ?? null)),
                'website_url' => self::text($personal['website_url'] ?? ($personal['website'] ?? ($personal['portfolio'] ?? null))),
                'linkedin_url' => self::text($personal['linkedin_url'] ?? ($personal['linkedin'] ?? null)),
                'github_url' => self::text($personal['github_url'] ?? ($personal['github'] ?? null)),
            ],
            'summary' => self::text($summary),
            'skills' => self::skills($legacy['skills'] ?? []),
            'education' => self::education($legacy['education'] ?? []),
            'experience' => self::experience($legacy['experience'] ?? []),
            'projects' => self::projects($legacy['projects'] ?? []),
            'certificates' => self::certificates($legacy['certificates'] ?? []),
            'languages' => self::languages($legacy['languages'] ?? []),
            'activities' => self::activities($legacy['activities'] ?? []),
        ];
    }

    public static function snapshotFromLegacy(string $title, array $sections): array
    {
        return [
            'title' => self::text($title),
            ...self::profileDocument($sections, self::text($title)),
        ];
    }

    private static function skills(mixed $value): array
    {
        $items = is_array($value) ? $value : [];
        $result = [];
        foreach (array_values($items) as $category) {
            if (! is_array($category)) {
                continue;
            }
            $skills = [];
            foreach (array_values(is_array($category['items'] ?? null) ? $category['items'] : []) as $skill) {
                $name = self::text(is_array($skill) ? ($skill['name'] ?? null) : $skill);
                if ($name !== null) {
                    $skills[] = ['id' => self::id(is_array($skill) ? ($skill['id'] ?? null) : null), 'name' => $name];
                }
            }
            $result[] = ['id' => self::id($category['id'] ?? null), 'label' => self::text($category['label'] ?? ($category['category'] ?? 'Skills')), 'items' => $skills];
        }

        return $result;
    }

    private static function education(mixed $value): array
    {
        return self::mapList($value, static function (array $item): array {
            return [
                'id' => self::id($item['id'] ?? null), 'institution' => self::text($item['institution'] ?? ($item['school'] ?? '')),
                'degree' => self::text($item['degree'] ?? ($item['major'] ?? '')), 'field_of_study' => self::text($item['field_of_study'] ?? ($item['field'] ?? null)),
                'location' => self::text($item['location'] ?? null), 'start_date' => self::date($item['start_date'] ?? null),
                'end_date' => self::date($item['end_date'] ?? null), 'description' => self::text($item['description'] ?? null),
            ];
        });
    }

    private static function experience(mixed $value): array
    {
        return self::mapList($value, static function (array $item): array {
            $current = (bool) ($item['is_current'] ?? false);

            return [
                'id' => self::id($item['id'] ?? null), 'organization' => self::text($item['organization'] ?? ($item['company'] ?? '')),
                'role' => self::text($item['role'] ?? ($item['title'] ?? '')), 'employment_type' => self::text($item['employment_type'] ?? null),
                'location' => self::text($item['location'] ?? null), 'start_date' => self::date($item['start_date'] ?? null),
                'end_date' => $current ? null : self::date($item['end_date'] ?? null), 'is_current' => $current,
                'highlights' => self::textList($item['highlights'] ?? ($item['bullets'] ?? []), 400),
            ];
        });
    }

    private static function projects(mixed $value): array
    {
        return self::mapList($value, static function (array $item): array {
            return [
                'id' => self::id($item['id'] ?? null), 'name' => self::text($item['name'] ?? ''), 'role' => self::text($item['role'] ?? null),
                'url' => self::text($item['url'] ?? null), 'start_date' => self::date($item['start_date'] ?? null), 'end_date' => self::date($item['end_date'] ?? null),
                'technologies' => self::textList($item['technologies'] ?? ($item['tech_stack'] ?? []), 80),
                'highlights' => self::textList($item['highlights'] ?? ($item['bullets'] ?? []), 400),
            ];
        });
    }

    private static function certificates(mixed $value): array
    {
        return self::mapList($value, static fn (array $item): array => [
            'id' => self::id($item['id'] ?? null), 'name' => self::text($item['name'] ?? ''), 'issuer' => self::text($item['issuer'] ?? ''),
            'issued_on' => self::date($item['issued_on'] ?? ($item['issued_date'] ?? ($item['date'] ?? null))),
            'expires_on' => self::date($item['expires_on'] ?? ($item['expires_date'] ?? null)), 'credential_url' => self::text($item['credential_url'] ?? ($item['url'] ?? null)),
        ]);
    }

    private static function languages(mixed $value): array
    {
        $map = ['advanced' => 'professional_working', 'intermediate' => 'limited_working', 'basic' => 'elementary'];

        return self::mapList($value, static function (array $item) use ($map): array {
            $proficiency = strtolower((string) ($item['proficiency'] ?? ($item['level'] ?? 'elementary')));

            return ['id' => self::id($item['id'] ?? null), 'language' => self::text($item['language'] ?? ($item['name'] ?? '')), 'proficiency' => $map[$proficiency] ?? $proficiency];
        });
    }

    private static function activities(mixed $value): array
    {
        return self::mapList($value, static fn (array $item): array => [
            'id' => self::id($item['id'] ?? null), 'name' => self::text($item['name'] ?? ''), 'role' => self::text($item['role'] ?? null),
            'organization' => self::text($item['organization'] ?? null), 'start_date' => self::date($item['start_date'] ?? null),
            'end_date' => self::date($item['end_date'] ?? null), 'description' => self::text($item['description'] ?? null),
        ]);
    }

    private static function mapList(mixed $value, callable $mapper): array
    {
        $result = [];
        foreach (array_values(is_array($value) ? $value : []) as $item) {
            if (is_array($item)) {
                $result[] = $mapper($item);
            }
        }

        return $result;
    }

    private static function textList(mixed $value, int $max): array
    {
        $result = [];
        foreach (array_values(is_array($value) ? $value : []) as $item) {
            $value = self::text($item);
            if ($value !== null) {
                $result[] = mb_substr($value, 0, $max);
            }
        }

        return $result;
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = str_replace(["\r\n", "\r"], "\n", trim($value));
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        return $value === '' ? null : $value;
    }

    private static function date(mixed $value): ?string
    {
        $value = self::text($value);

        return is_string($value) && preg_match('/^(19\d{2}|20\d{2}|2100)-(0[1-9]|1[0-2])$/', $value) === 1 ? $value : null;
    }

    private static function id(mixed $value): string
    {
        return ProfileDocument::isUlid($value) ? $value : ProfileDocument::id();
    }
}
