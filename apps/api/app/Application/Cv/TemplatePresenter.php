<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Cv\Data\TemplateRecord;

final class TemplatePresenter
{
    /** @return array<string, mixed> */
    public static function summary(TemplateRecord $template, TemplateService $templates): array
    {
        return [
            'id' => $template->id,
            'version' => $template->version,
            'name' => trim($template->name),
            'description' => $template->description === null ? null : trim($template->description),
            'status' => 'active',
            'supported_sections' => $templates->supportedSections($template),
            'preview_metadata' => [],
        ];
    }
}
