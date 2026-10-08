<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\OperationalSafety\OrchestrationMeasurementPlanValidator;
use Illuminate\Console\Command;

final class ValidateOrchestrationPlan extends Command
{
    protected $signature = 'safety:orchestration-plan
        {--plan= : Override the disposable orchestration measurement plan path}';

    protected $description = 'Validate the synthetic single-orchestrator measurement plan without executing it';

    public function handle(OrchestrationMeasurementPlanValidator $validator): int
    {
        $result = $validator->validate($this->option('plan') ?: null);
        $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return (int) $result['exit_code'];
    }
}
