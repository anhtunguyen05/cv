<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use Normalizer;

final class JobDescriptionValidator
{
    /** @return array{raw_text:string,company:?string,role:?string} */
    public static function create(array $payload): array
    {
        self::assertKeys($payload, ['raw_text', 'company', 'role']);
        $rawText = $payload['raw_text'] ?? null;
        if (! is_string($rawText) || self::trimUnicode($rawText) === '') {
            throw self::invalid(['raw_text' => ['The raw_text field is required and must not be blank.']]);
        }
        self::assertSourceBounds($rawText);

        return [
            'raw_text' => $rawText,
            'company' => self::metadata($payload['company'] ?? null, 'company'),
            'role' => self::metadata($payload['role'] ?? null, 'role'),
        ];
    }

    /** @return array{raw_text?:string,company:?string,role:?string} */
    public static function patch(array $payload): array
    {
        self::assertKeys($payload, ['raw_text', 'company', 'role']);
        if (array_key_exists('raw_text', $payload)) {
            if (! is_string($payload['raw_text'])) {
                throw self::invalid(['raw_text' => ['The raw_text field must be a string and cannot be null.']]);
            }
            self::assertSourceBounds($payload['raw_text']);
        }
        $result = [];
        if (array_key_exists('raw_text', $payload)) {
            $result['raw_text'] = $payload['raw_text'];
        }
        if (array_key_exists('company', $payload)) {
            $result['company'] = self::metadata($payload['company'], 'company');
        }
        if (array_key_exists('role', $payload)) {
            $result['role'] = self::metadata($payload['role'], 'role');
        }

        return $result;
    }

    public static function trimUnicode(string $value): string
    {
        return preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value) ?? trim($value);
    }

    public static function normalizedSource(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        if (class_exists(Normalizer::class)) {
            $value = Normalizer::normalize($value, Normalizer::FORM_C) ?: $value;
        }

        return $value;
    }

    private static function assertKeys(array $payload, array $allowed): void
    {
        $unknown = array_diff(array_keys($payload), $allowed);
        if ($unknown !== []) {
            throw self::invalid(['body' => ['The request contains unsupported fields.']]);
        }
    }

    private static function assertSourceBounds(string $value): void
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            throw self::invalid(['raw_text' => ['The raw_text field must be valid UTF-8.']]);
        }
        if (mb_strlen($value, 'UTF-8') > 50000) {
            throw self::invalid(['raw_text' => ['The raw_text field may not exceed 50,000 Unicode code points.']]);
        }
        if (strlen($value) > 204800) {
            throw self::invalid(['raw_text' => ['The raw_text field may not exceed 200 KiB in UTF-8.']]);
        }
        if (self::trimUnicode($value) === '') {
            throw self::invalid(['raw_text' => ['The raw_text field is required and must not be blank.']]);
        }
    }

    private static function metadata(mixed $value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! is_string($value)) {
            throw self::invalid([$field => [['code' => 'INVALID', 'message' => "The {$field} field must be a string or null."]]]);
        }
        if (! mb_check_encoding($value, 'UTF-8')) {
            throw self::invalid([$field => [['code' => 'INVALID_ENCODING', 'message' => "The {$field} field must be valid UTF-8."]]]);
        }
        $value = self::trimUnicode($value);
        if ($value === '') {
            throw self::invalid([$field => [['code' => 'INVALID', 'message' => "The {$field} field must not be blank."]]]);
        }
        if (mb_strlen($value, 'UTF-8') > 160) {
            throw self::invalid([$field => [['code' => 'MAX_LENGTH', 'message' => "The {$field} field may not exceed 160 Unicode code points."]]]);
        }

        return $value;
    }

    private static function invalid(array $details): ApiProblem
    {
        $normalized = [];
        foreach ($details as $field => $messages) {
            $normalized[$field] = [];
            foreach ($messages as $message) {
                $normalized[$field][] = is_array($message) ? $message : ['code' => 'INVALID', 'message' => $message];
            }
        }

        return new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, $normalized);
    }
}
