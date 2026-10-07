<?php

declare(strict_types=1);

namespace Tests\Unit\Cv;

use App\Application\Cv\ProfileDocumentValidator;
use PHPUnit\Framework\TestCase;

final class ProfileDocumentValidatorTest extends TestCase
{
    public function test_the_minimal_profile_document_matches_the_contract(): void
    {
        $payload = [
            'title' => '  Backend CV  ',
            'personal_information' => [
                'full_name' => '  Nguyen Anh Tu ',
                'headline' => null,
                'email' => null,
                'phone' => null,
                'location' => null,
                'website_url' => null,
                'linkedin_url' => null,
                'github_url' => null,
            ],
        ];

        self::assertSame([], ProfileDocumentValidator::validateCreate($payload));
        self::assertSame('Backend CV', ProfileDocumentValidator::canonicalize($payload)['title']);
    }

    public function test_malformed_nested_values_report_exact_field_paths(): void
    {
        $errors = ProfileDocumentValidator::validateSection('projects', [[
            'name' => 'Project',
            'role' => null,
            'url' => 'javascript:alert(1)',
            'start_date' => '2026-02',
            'end_date' => '2025-01',
            'technologies' => [],
            'highlights' => ['ok'],
        ]]);

        self::assertArrayHasKey('projects.0.url', $errors);
        self::assertArrayHasKey('projects.0.end_date', $errors);
    }

    public function test_optional_start_dates_are_allowed_but_an_end_date_requires_one(): void
    {
        $item = [
            'name' => 'Project',
            'role' => null,
            'url' => null,
            'start_date' => null,
            'end_date' => null,
            'technologies' => [],
            'highlights' => [],
        ];
        self::assertArrayNotHasKey('projects.0.start_date', ProfileDocumentValidator::validateSection('projects', [$item]));

        $item['end_date'] = '2026-01';
        self::assertArrayHasKey('projects.0.start_date', ProfileDocumentValidator::validateSection('projects', [$item]));
    }

    public function test_project_technologies_are_unique_after_normalization(): void
    {
        $errors = ProfileDocumentValidator::validateSection('projects', [[
            'name' => 'Project',
            'role' => null,
            'url' => null,
            'start_date' => null,
            'end_date' => null,
            'technologies' => [' PHP ', 'php'],
            'highlights' => [],
        ]]);

        self::assertArrayHasKey('projects.0.technologies.1', $errors);
    }
}
