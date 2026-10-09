<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\Contracts\AdvisoryLock;
use Illuminate\Support\Facades\DB;

final class LaravelAdvisoryLock implements AdvisoryLock
{
    public function forUser(int $userId): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('SELECT pg_advisory_xact_lock(?)', [$userId]);
        }
    }
}
