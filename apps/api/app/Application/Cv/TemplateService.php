<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Cv\Contracts\TemplateCatalog;
use App\Application\Cv\Data\TemplateRecord;

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

    public function __construct(private readonly TemplateCatalog $catalog) {}

    /** @return list<TemplateRecord> */
    public function available(): array
    {
        return array_values(array_filter(
            $this->catalog->available(),
            fn (TemplateRecord $template): bool => $this->isAvailable($template),
        ));
    }

    public function findAvailablePair(string $id, string $version): TemplateRecord
    {
        if (! ProfileDocument::isUlid($id) || ! $this->isVersion($version)) {
            throw $this->unavailable();
        }

        $template = $this->catalog->findAvailablePair($id, $version);

        if ($template === null || ! $this->isAvailable($template)) {
            throw $this->unavailable();
        }

        return $template;
    }

    public function isAvailable(TemplateRecord $template): bool
    {
        $name = trim($template->name);
        $description = $template->description === null ? null : trim($template->description);
        $supportedSchemas = $template->supportedSnapshotSchemaVersions;

        return $template->isActive
            && $name !== ''
            && mb_strlen($name) <= 120
            && ($description === null || mb_strlen($description) <= 500)
            && $this->isVersion($template->version)
            && is_array($supportedSchemas)
            && $supportedSchemas !== []
            && in_array(self::SNAPSHOT_SCHEMA_VERSION, $supportedSchemas, true);
    }

    /** @return list<string> */
    public function supportedSections(TemplateRecord $template): array
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
