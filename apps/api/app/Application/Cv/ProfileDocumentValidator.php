<?php

declare(strict_types=1);

namespace App\Application\Cv;

final class ProfileDocumentValidator
{
    public static function sections(): array
    {
        return ProfileDocument::SECTIONS;
    }

    /** @return array<string, array<int, string>> */
    public static function validateCreate(array $payload): array
    {
        $errors = self::strictKeys($payload, ['title', 'personal_information'], '');
        self::string($errors, $payload['title'] ?? null, 'title', 1, 120, true);
        self::personal($errors, $payload['personal_information'] ?? null);

        return $errors;
    }

    /** @return array<string, array<int, string>> */
    public static function validateTitle(mixed $value, string $field = 'title'): array
    {
        $errors = [];
        self::string($errors, $value, $field, 1, 120, true);

        return $errors;
    }

    /** @return array<string, array<int, string>> */
    public static function validatePersonal(mixed $payload): array
    {
        $errors = [];
        if (! is_array($payload)) {
            return ['personal_information' => ['The personal_information field must be an object.']];
        }
        self::personal($errors, $payload);

        return $errors;
    }

    /** @return array<string, array<int, string>> */
    public static function validateSection(string $section, mixed $value): array
    {
        $errors = [];
        if (! in_array($section, ProfileDocument::SECTIONS, true)) {
            return ['section' => ['The selected section is invalid.']];
        }

        if ($section === 'summary') {
            if ($value !== null) {
                self::string($errors, $value, $section, 1, 2000, false);
            }

            return $errors;
        }

        if (! is_array($value) || ! array_is_list($value)) {
            return [$section => ['The section must be an array.']];
        }

        $limit = match ($section) {
            'skills' => 12,
            'languages' => 20,
            default => 30,
        };
        if (count($value) > $limit) {
            $errors[$section][] = "The section may not contain more than {$limit} items.";
        }

        foreach ($value as $index => $item) {
            $path = $section.'.'.$index;
            if (! is_array($item)) {
                $errors[$path][] = 'The item must be an object.';

                continue;
            }
            self::itemId($errors, $item, $path);

            match ($section) {
                'skills' => self::skill($errors, $item, $path),
                'education' => self::education($errors, $item, $path),
                'experience' => self::experience($errors, $item, $path),
                'projects' => self::project($errors, $item, $path),
                'certificates' => self::certificate($errors, $item, $path),
                'languages' => self::language($errors, $item, $path),
                'activities' => self::activity($errors, $item, $path),
                default => null,
            };
        }

        if ($section === 'skills') {
            $total = 0;
            foreach ($value as $category) {
                $total += is_array($category) && is_array($category['items'] ?? null) ? count($category['items']) : 0;
            }
            if ($total > 100) {
                $errors[$section][] = 'The total number of skill items may not exceed 100.';
            }
        }

        self::duplicates($errors, $value, $section);
        self::duplicateIds($errors, $value, $section);

        return $errors;
    }

    /** @return array<string, mixed> */
    public static function canonicalize(array $value): array
    {
        return self::normalizeMixed($value);
    }

    private static function personal(array &$errors, mixed $value): void
    {
        if (! is_array($value)) {
            $errors['personal_information'][] = 'The personal_information field must be an object.';

            return;
        }
        $errors += self::strictKeys($value, ProfileDocument::PERSONAL_KEYS, 'personal_information');
        self::string($errors, $value['full_name'] ?? null, 'personal_information.full_name', 1, 120, true);
        foreach (['headline' => 160, 'phone' => 32, 'location' => 160] as $key => $max) {
            if (($value[$key] ?? null) !== null) {
                self::string($errors, $value[$key], "personal_information.{$key}", 1, $max, true);
            }
        }
        if (($value['email'] ?? null) !== null) {
            self::string($errors, $value['email'], 'personal_information.email', 3, 254, true);
            if (is_string($value['email']) && filter_var($value['email'], FILTER_VALIDATE_EMAIL) === false) {
                $errors['personal_information.email'][] = 'The email must be a valid email address.';
            }
        }
        foreach (['website_url', 'linkedin_url', 'github_url'] as $key) {
            if (($value[$key] ?? null) !== null) {
                self::string($errors, $value[$key], "personal_information.{$key}", 1, 2048, true);
                if (is_string($value[$key])) {
                    $url = parse_url($value[$key]);
                    if ($url === false || ! in_array(strtolower((string) ($url['scheme'] ?? '')), ['http', 'https'], true) || empty($url['host'])) {
                        $errors["personal_information.{$key}"][] = 'The URL must be an absolute HTTP or HTTPS URL.';
                    }
                }
            }
        }
    }

    private static function skill(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'label', 'items'], $path);
        self::string($errors, $item['label'] ?? null, $path.'.label', 1, 60, true);
        if (! isset($item['items']) || ! is_array($item['items']) || ! array_is_list($item['items'])) {
            $errors[$path.'.items'][] = 'The items field must be an array.';

            return;
        }
        if (count($item['items']) < 1 || count($item['items']) > 30) {
            $errors[$path.'.items'][] = 'The items field must contain between 1 and 30 entries.';
        }
        foreach ($item['items'] as $index => $skill) {
            $skillPath = $path.'.items.'.$index;
            if (! is_array($skill)) {
                $errors[$skillPath][] = 'The skill item must be an object.';

                continue;
            }
            $errors += self::strictKeys($skill, ['id', 'name'], $skillPath);
            self::itemId($errors, $skill, $skillPath);
            self::string($errors, $skill['name'] ?? null, $skillPath.'.name', 1, 80, true);
        }
    }

    private static function education(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'institution', 'degree', 'field_of_study', 'location', 'start_date', 'end_date', 'description'], $path);
        self::string($errors, $item['institution'] ?? null, $path.'.institution', 1, 160, true);
        self::string($errors, $item['degree'] ?? null, $path.'.degree', 1, 160, true);
        self::optionalStrings($errors, $item, $path, ['field_of_study' => 160, 'location' => 160], ['description']);
        if (($item['description'] ?? null) !== null) {
            self::string($errors, $item['description'], $path.'.description', 1, 2000, false);
        }
        self::datePair($errors, $item, $path, 'start_date', 'end_date');
    }

    private static function experience(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'organization', 'role', 'employment_type', 'location', 'start_date', 'end_date', 'is_current', 'highlights'], $path);
        self::string($errors, $item['organization'] ?? null, $path.'.organization', 1, 160, true);
        self::string($errors, $item['role'] ?? null, $path.'.role', 1, 160, true);
        self::optionalStrings($errors, $item, $path, ['employment_type' => 40, 'location' => 160]);
        if (isset($item['employment_type']) && ! in_array($item['employment_type'], ['full_time', 'part_time', 'contract', 'internship', 'freelance', 'other'], true)) {
            $errors[$path.'.employment_type'][] = 'The employment type is invalid.';
        }
        self::datePair($errors, $item, $path, 'start_date', 'end_date', true);
        if (! is_bool($item['is_current'] ?? null)) {
            $errors[$path.'.is_current'][] = 'The is_current field must be boolean.';
        } elseif ($item['is_current'] && ($item['end_date'] ?? null) !== null) {
            $errors[$path.'.end_date'][] = 'The end date must be null for a current role.';
        } elseif (! $item['is_current'] && ($item['end_date'] ?? null) === null) {
            $errors[$path.'.end_date'][] = 'The end date is required for a non-current role.';
        }
        self::highlights($errors, $item['highlights'] ?? null, $path.'.highlights');
    }

    private static function project(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'name', 'role', 'url', 'start_date', 'end_date', 'technologies', 'highlights'], $path);
        self::string($errors, $item['name'] ?? null, $path.'.name', 1, 160, true);
        self::optionalStrings($errors, $item, $path, ['role' => 160]);
        if (($item['url'] ?? null) !== null) {
            self::string($errors, $item['url'], $path.'.url', 1, 2048, true);
            if (is_string($item['url']) && ! self::validUrl($item['url'])) {
                $errors[$path.'.url'][] = 'The URL must be an absolute HTTP or HTTPS URL.';
            }
        }
        self::datePair($errors, $item, $path, 'start_date', 'end_date');
        self::stringList($errors, $item['technologies'] ?? null, $path.'.technologies', 30, 80, true);
        self::highlights($errors, $item['highlights'] ?? null, $path.'.highlights');
    }

    private static function certificate(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'name', 'issuer', 'issued_on', 'expires_on', 'credential_url'], $path);
        self::string($errors, $item['name'] ?? null, $path.'.name', 1, 160, true);
        self::string($errors, $item['issuer'] ?? null, $path.'.issuer', 1, 160, true);
        self::datePair($errors, $item, $path, 'issued_on', 'expires_on');
        if (($item['credential_url'] ?? null) !== null && (! is_string($item['credential_url']) || ! self::validUrl($item['credential_url']))) {
            $errors[$path.'.credential_url'][] = 'The URL must be an absolute HTTP or HTTPS URL.';
        }
    }

    private static function language(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'language', 'proficiency'], $path);
        self::string($errors, $item['language'] ?? null, $path.'.language', 1, 80, true);
        if (! in_array($item['proficiency'] ?? null, ['native', 'fluent', 'professional_working', 'limited_working', 'elementary'], true)) {
            $errors[$path.'.proficiency'][] = 'The proficiency is invalid.';
        }
    }

    private static function activity(array &$errors, array $item, string $path): void
    {
        $errors += self::strictKeys($item, ['id', 'name', 'role', 'organization', 'start_date', 'end_date', 'description'], $path);
        self::string($errors, $item['name'] ?? null, $path.'.name', 1, 160, true);
        self::optionalStrings($errors, $item, $path, ['role' => 160, 'organization' => 160], ['description']);
        if (($item['description'] ?? null) !== null) {
            self::string($errors, $item['description'], $path.'.description', 1, 2000, false);
        }
        self::datePair($errors, $item, $path, 'start_date', 'end_date');
    }

    private static function itemId(array &$errors, array $item, string $path): void
    {
        if (array_key_exists('id', $item) && $item['id'] !== null && ! ProfileDocument::isUlid($item['id'])) {
            $errors[$path.'.id'][] = 'The id must be an uppercase ULID.';
        }
    }

    private static function optionalStrings(array &$errors, array $item, string $path, array $fields, array $multiline = []): void
    {
        foreach ($fields as $field => $max) {
            if (($item[$field] ?? null) !== null) {
                self::string($errors, $item[$field], $path.'.'.$field, 1, $max, ! in_array($field, $multiline, true));
            }
        }
    }

    private static function stringList(array &$errors, mixed $value, string $path, int $maxItems, int $maxLength, bool $unique = false): void
    {
        if (! is_array($value) || ! array_is_list($value)) {
            $errors[$path][] = 'The field must be an array.';

            return;
        }
        if (count($value) > $maxItems) {
            $errors[$path][] = "The field may not contain more than {$maxItems} entries.";
        }
        $seen = [];
        foreach ($value as $index => $entry) {
            self::string($errors, $entry, $path.'.'.$index, 1, $maxLength, true);
            if ($unique && is_string($entry)) {
                $normalized = mb_strtolower(self::normalize($entry));
                if (isset($seen[$normalized])) {
                    $errors[$path.'.'.$index][] = 'The value must be unique.';
                }
                $seen[$normalized] = true;
            }
        }
    }

    private static function highlights(array &$errors, mixed $value, string $path): void
    {
        if (! is_array($value) || ! array_is_list($value)) {
            $errors[$path][] = 'The highlights field must be an array.';

            return;
        }
        if (count($value) > 10) {
            $errors[$path][] = 'The highlights field may not contain more than 10 entries.';
        }
        foreach ($value as $index => $entry) {
            self::string($errors, $entry, $path.'.'.$index, 1, 400, false);
        }
    }

    private static function datePair(array &$errors, array $item, string $path, string $start, string $end, bool $startRequired = false): void
    {
        $startValue = $item[$start] ?? null;
        $endValue = $item[$end] ?? null;
        if ($startRequired && $startValue === null) {
            $errors[$path.'.'.$start][] = 'The start date is required.';
        }
        if (! $startRequired && $endValue !== null && $startValue === null) {
            $errors[$path.'.'.$start][] = 'The start date is required when an end date is provided.';
        }
        foreach ([$start => $startValue, $end => $endValue] as $field => $value) {
            if ($value !== null && (! is_string($value) || ! preg_match('/^(19\d{2}|20\d{2}|2100)-(0[1-9]|1[0-2])$/', $value))) {
                $errors[$path.'.'.$field][] = 'The date must use YYYY-MM between 1900-01 and 2100-12.';
            }
        }
        if ($startValue !== null && $endValue !== null && is_string($startValue) && is_string($endValue) && $endValue < $startValue) {
            $errors[$path.'.'.$end][] = 'The end date cannot precede the start date.';
        }
    }

    private static function duplicates(array &$errors, array $items, string $section): void
    {
        if ($section === 'skills') {
            self::duplicateBy($errors, $items, $section, 'label');
            foreach ($items as $index => $category) {
                if (! is_array($category) || ! is_array($category['items'] ?? null)) {
                    continue;
                }
                self::duplicateBy($errors, $category['items'], $section.'.'.$index.'.items', 'name');
            }
        } elseif ($section === 'languages') {
            self::duplicateBy($errors, $items, $section, 'language');
        }
    }

    private static function duplicateBy(array &$errors, array $items, string $path, string $key): void
    {
        $seen = [];
        foreach ($items as $index => $item) {
            if (! is_array($item) || ! is_string($item[$key] ?? null)) {
                continue;
            }
            $normalized = mb_strtolower(self::trim((string) $item[$key]));
            if (isset($seen[$normalized])) {
                $errors[$path.'.'.$index.'.'.$key][] = 'The value must be unique.';
            }
            $seen[$normalized] = true;
        }
    }

    private static function duplicateIds(array &$errors, array $items, string $section): void
    {
        $seen = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            if (isset($item['id']) && $item['id'] !== null) {
                if (isset($seen[$item['id']])) {
                    $errors[$section.'.'.$index.'.id'][] = 'The id must be unique within the section.';
                }
                $seen[$item['id']] = true;
            }
            if ($section === 'skills' && is_array($item['items'] ?? null)) {
                foreach ($item['items'] as $skillIndex => $skill) {
                    if (! is_array($skill) || ! isset($skill['id']) || $skill['id'] === null) {
                        continue;
                    }
                    if (isset($seen[$skill['id']])) {
                        $errors[$section.'.'.$index.'.items.'.$skillIndex.'.id'][] = 'The id must be unique within the section.';
                    }
                    $seen[$skill['id']] = true;
                }
            }
        }
    }

    private static function strictKeys(array $value, array $keys, string $path): array
    {
        $errors = [];
        $unknown = array_diff(array_keys($value), $keys);
        $missing = array_diff($keys, array_keys($value));
        if ($unknown !== []) {
            $errors[$path === '' ? 'body' : $path][] = 'The request contains unsupported fields.';
        }
        foreach ($missing as $key) {
            if ($key === 'id') {
                continue;
            }
            $errors[($path === '' ? '' : $path.'.').$key][] = 'The field is required.';
        }

        return $errors;
    }

    private static function string(array &$errors, mixed $value, string $path, int $min, int $max, bool $singleLine): void
    {
        if (! is_string($value)) {
            $errors[$path][] = 'The field must be a string.';

            return;
        }
        if (! mb_check_encoding($value, 'UTF-8')) {
            $errors[$path][] = 'The field must contain valid UTF-8.';

            return;
        }
        $normalized = self::normalize($value);
        $length = function_exists('grapheme_strlen') ? grapheme_strlen($normalized) : mb_strlen($normalized);
        if ($length < $min || $length > $max || strlen($normalized) > $max * 4) {
            $errors[$path][] = "The field must be between {$min} and {$max} characters.";
        }
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}]/u', $normalized) === 1 || ($singleLine && str_contains($normalized, "\n"))) {
            $errors[$path][] = 'The field contains unsupported characters.';
        }
    }

    private static function trim(string $value): string
    {
        return preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value) ?? $value;
    }

    private static function normalize(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = self::trim($value);
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        return $value;
    }

    private static function normalizeMixed(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::normalize($value);
        }
        if (! is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$key] = self::normalizeMixed($item);
        }

        return $normalized;
    }

    private static function validUrl(string $value): bool
    {
        $parts = parse_url($value);

        return $parts !== false && ! empty($parts['host']) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
    }
}
