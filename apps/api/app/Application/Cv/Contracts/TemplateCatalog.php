<?php

declare(strict_types=1);

namespace App\Application\Cv\Contracts;

use App\Application\Cv\Data\TemplateRecord;

interface TemplateCatalog
{
    /** @return list<TemplateRecord> */
    public function available(): array;

    public function findAvailablePair(string $id, string $version): ?TemplateRecord;
}
