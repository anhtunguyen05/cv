<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\JobFit\MatchQualityEvaluator;
use Illuminate\Console\Command;

final class ValidateMatchQuality extends Command
{
    protected $signature = 'match:quality
        {--manifest= : Override the synthetic evaluation manifest path}
        {--corpus= : Override the synthetic corpus path}
        {--json : Emit the safe result as JSON}';

    protected $description = 'Validate deterministic matching quality against the pinned synthetic corpus';

    public function handle(MatchQualityEvaluator $evaluator): int
    {
        $result = $evaluator->evaluate(
            $this->option('manifest') ?: null,
            $this->option('corpus') ?: null,
        );

        $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return (int) $result['exit_code'];
    }
}
