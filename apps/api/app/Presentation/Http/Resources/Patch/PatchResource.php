<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Patch;

use App\Application\Patch\PatchPresenter;

final class PatchResource
{
    /** @return array<string,mixed> */
    public static function data(object $patch): array
    {
        return PatchPresenter::data($patch);
    }
}
