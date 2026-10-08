<?php

declare(strict_types=1);

namespace App\Application\Patch;

interface PatchProposalProvider
{
    /** @param array<string,mixed> $request */
    public function propose(array $request): mixed;
}
