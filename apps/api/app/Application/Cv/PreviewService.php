<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\Data\TemplateRecord;

final class PreviewService
{
    public function __construct(
        private readonly VersionService $versions,
        private readonly TemplateService $templates,
    ) {}

    /** @return array<string, mixed> */
    public function resolve(AuthenticatedUser $user, string $versionId, string $templateId, string $templateVersion): array
    {
        try {
            $version = $this->versions->findOwned($user, $versionId);
        } catch (ApiProblem $problem) {
            throw new ApiProblem('CV_VERSION_NOT_FOUND', 'The requested CV Version was not found.', 404);
        }

        $template = $this->templates->findAvailablePair($templateId, $templateVersion);
        $snapshot = $version->snapshot;
        if (! is_array($snapshot)) {
            throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The saved CV Version cannot be rendered safely.', 422);
        }
        $this->assertSnapshotShape($snapshot);
        if ($version->snapshotSchemaVersion !== TemplateService::SNAPSHOT_SCHEMA_VERSION) {
            throw new ApiProblem('RENDER_SOURCE_UNSUPPORTED', 'This CV Version uses an unsupported snapshot schema.', 422);
        }

        $this->assertSafeLinks($snapshot);

        return [
            'cv_version_id' => $version->id,
            'template_id' => $template->id,
            'template_version' => $template->version,
            'template_name' => trim($template->name),
            'renderer_version' => '1.0.0',
            'version_name' => $version->name,
            'snapshot_schema_version' => $version->snapshotSchemaVersion,
            'source_profile_revision' => $version->sourceProfileRevision,
            'sections' => $this->sections($snapshot, $template),
            'rendered_at' => null,
        ];
    }

    /** @return list<array{key: string, data: mixed}> */
    private function sections(array $snapshot, TemplateRecord $template): array
    {
        $personal = $snapshot['personal_information'] ?? [];
        $all = [
            'identity' => [
                'title' => $snapshot['title'] ?? null,
                'personal_information' => is_array($personal) ? $personal : [],
            ],
            'summary' => $snapshot['summary'] ?? null,
            'experience' => $snapshot['experience'] ?? [],
            'projects' => $snapshot['projects'] ?? [],
            'education' => $snapshot['education'] ?? [],
            'skills' => $snapshot['skills'] ?? [],
            'certificates' => $snapshot['certificates'] ?? [],
            'languages' => $snapshot['languages'] ?? [],
            'activities' => $snapshot['activities'] ?? [],
        ];

        $sections = [];
        foreach ($this->templates->supportedSections($template) as $key) {
            $data = $all[$key] ?? null;
            if ($key !== 'identity' && $this->isEmpty($data)) {
                continue;
            }
            $sections[] = ['key' => $key, 'data' => $data];
        }

        return $sections;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return true;
        }

        return is_array($value) && array_filter($value, fn (mixed $item): bool => ! $this->isEmpty($item)) === [];
    }

    private function assertSafeLinks(array $snapshot): void
    {
        $personal = is_array($snapshot['personal_information'] ?? null) ? $snapshot['personal_information'] : [];
        $urls = [
            $personal['website_url'] ?? null,
            $personal['linkedin_url'] ?? null,
            $personal['github_url'] ?? null,
        ];
        foreach (['experience', 'projects', 'education', 'certificates', 'languages', 'activities'] as $section) {
            foreach ($snapshot[$section] as $item) {
                if (is_array($item)) {
                    foreach (['url', 'credential_url'] as $key) {
                        $urls[] = $item[$key] ?? null;
                    }
                }
            }
        }
        foreach ($urls as $url) {
            if ($url !== null && (! is_string($url) || ! $this->isSafeUrl($url))) {
                throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The saved CV Version contains an unsafe link.', 422);
            }
        }
    }

    private function isSafeUrl(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (in_array($scheme, ['http', 'https'], true) && empty($parts['host'])) {
            return false;
        }

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }

    /** @param array<string, mixed> $snapshot */
    private function assertSnapshotShape(array $snapshot): void
    {
        $required = [
            'title',
            'personal_information',
            'summary',
            'skills',
            'education',
            'experience',
            'projects',
            'certificates',
            'languages',
            'activities',
        ];
        foreach ($required as $key) {
            if (! array_key_exists($key, $snapshot)) {
                throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The saved CV Version contains an invalid snapshot.', 422);
            }
        }
        if (! is_array($snapshot['personal_information'])
            || ($snapshot['title'] !== null && ! is_string($snapshot['title']))
            || ($snapshot['summary'] !== null && ! is_string($snapshot['summary']))) {
            throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The saved CV Version contains an invalid snapshot.', 422);
        }
        foreach (['skills', 'education', 'experience', 'projects', 'certificates', 'languages', 'activities'] as $section) {
            if (! is_array($snapshot[$section])) {
                throw new ApiProblem('PREVIEW_SOURCE_INVALID', 'The saved CV Version contains an invalid snapshot.', 422);
            }
        }
    }
}
