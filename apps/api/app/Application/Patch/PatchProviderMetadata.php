<?php

declare(strict_types=1);

namespace App\Application\Patch;

interface PatchProviderMetadata
{
    /** @return array{provider:string,model:string,prompt_version:string,tool_schema_version:string,kind:string} */
    public function providerMetadata(): array;
}
