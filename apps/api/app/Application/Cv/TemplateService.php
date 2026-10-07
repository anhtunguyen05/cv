<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\Template;
use Illuminate\Database\Eloquent\Collection;

final class TemplateService
{
    public const SNAPSHOT_SCHEMA_VERSION = '1.0';

    /** @var list<string> */
    public const SECTION_KEYS = [
        'identity',
        'summary',
        'experience',
        'projects',
        'education',
        'skills',
        'certificates',
        'languages',
        'activities',
    ];

    /** @return Collection<int, Template> */
    public function available(): Collection
    {
        return Template::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->filter(fn (Template $template): bool => $this->isAvailable($template))
            ->values();
    }

    public function findAvailablePair(string $id, string $version): Template
    {
        if (! ProfileDocument::isUlid($id) || ! $this->isVersion($version)) {
            throw $this->unavailable();
        }

        $template = Template::query()
            ->where('id', $id)
            ->where('version', $version)
            ->where('is_active', true)
            ->first();

        if (! $template instanceof Template || ! $this->isAvailable($template)) {
            throw $this->unavailable();
        }

        return $template;
    }

    public function isAvailable(Template $template): bool
    {
        $name = trim((string) $template->name);
        $description = $template->description === null ? null : trim((string) $template->description);
        $supportedSchemas = $template->supported_snapshot_schema_versions;

        return $template->is_active
            && $name !== ''
            && mb_strlen($name) <= 120
            && ($description === null || mb_strlen($description) <= 500)
            && $this->isVersion((string) $template->version)
            && is_array($supportedSchemas)
            && $supportedSchemas !== []
            && in_array(self::SNAPSHOT_SCHEMA_VERSION, $supportedSchemas, true);
    }

    /** @return list<string> */
    public function supportedSections(Template $template): array
    {
        // Renderer order is application-owned. Template JSON is inert metadata,
        // never executable renderer configuration supplied by a catalog row.
        return self::SECTION_KEYS;
    }

    private function isVersion(string $version): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,19}$/', $version) === 1;
    }

    private function unavailable(): ApiProblem
    {
        return new ApiProblem('TEMPLATE_UNAVAILABLE', 'The selected template is no longer available.', 409);
    }
}
