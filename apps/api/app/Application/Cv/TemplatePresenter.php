<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\Template;

final class TemplatePresenter
{
    /** @return array<string, mixed> */
    public static function summary(Template $template, TemplateService $templates): array
    {
        return [
            'id' => (string) $template->getKey(),
            'version' => (string) $template->version,
            'name' => trim((string) $template->name),
            'description' => $template->description === null ? null : trim((string) $template->description),
            'status' => 'active',
            'supported_sections' => $templates->supportedSections($template),
            'preview_metadata' => [],
        ];
    }
}
