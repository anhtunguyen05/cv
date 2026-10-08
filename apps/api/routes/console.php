<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('validate:match-quality', function (): int {
    return $this->call('match:quality', ['--json' => true]);
})->purpose('Validate deterministic matching quality against the pinned synthetic corpus');

Artisan::command('validate:orchestration-plan {--plan=}', function (): int {
    return $this->call('safety:orchestration-plan', ['--plan' => $this->option('plan')]);
})->purpose('Validate the synthetic single-orchestrator measurement plan without executing it');
