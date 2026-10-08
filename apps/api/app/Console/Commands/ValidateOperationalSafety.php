<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\OperationalSafety\OperationalSafetyManifestValidator;
use Illuminate\Console\Command;

final class ValidateOperationalSafety extends Command
{
    protected $signature = 'safety:baseline
        {--manifest= : Override the disposable readiness manifest path}';

    protected $description = 'Validate the read-only operational safety evidence manifest';

    public function handle(OperationalSafetyManifestValidator $validator): int
    {
        $result = $validator->validate($this->option('manifest') ?: null);
        $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return (int) $result['exit_code'];
    }
}
