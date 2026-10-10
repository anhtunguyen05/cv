<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv;

use App\Application\Cv\Contracts\TemplateCatalog;
use App\Application\Cv\Data\TemplateRecord;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\Template;

final class EloquentTemplateCatalog implements TemplateCatalog
{
    public function available(): array
    {
        return Template::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (Template $template): TemplateRecord => $this->record($template))
            ->all();
    }

    public function findAvailablePair(string $id, string $version): ?TemplateRecord
    {
        $template = Template::query()
            ->where('id', $id)
            ->where('version', $version)
            ->where('is_active', true)
            ->first();

        return $template instanceof Template ? $this->record($template) : null;
    }

    private function record(Template $template): TemplateRecord
    {
        return new TemplateRecord(
            id: (string) $template->getKey(),
            version: (string) $template->version,
            name: (string) $template->name,
            description: $template->description === null ? null : (string) $template->description,
            isActive: (bool) $template->is_active,
            supportedSnapshotSchemaVersions: is_array($template->supported_snapshot_schema_versions)
                ? array_values(array_map(static fn (mixed $version): string => (string) $version, $template->supported_snapshot_schema_versions))
                : [],
        );
    }
}
