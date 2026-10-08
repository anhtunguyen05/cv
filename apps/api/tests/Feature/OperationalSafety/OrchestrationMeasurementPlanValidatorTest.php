<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\Cv\CanonicalJson;
use App\Application\OperationalSafety\OrchestrationMeasurementPlanValidator;
use App\Models\MatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrchestrationMeasurementPlanValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_approved_plan_validates_without_executing_measurement(): void
    {
        $usersBefore = User::count();
        $reportsBefore = MatchReport::count();

        $result = app(OrchestrationMeasurementPlanValidator::class)->validate();

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_PASS, $result['verdict']);
        self::assertSame(OrchestrationMeasurementPlanValidator::EXIT_PASS, $result['exit_code']);
        self::assertSame('not_started', $result['execution']);
        self::assertSame([], $result['pending_decisions']);
        self::assertSame($usersBefore, User::count());
        self::assertSame($reportsBefore, MatchReport::count());
    }

    public function test_complete_approved_plan_passes_validation_only(): void
    {
        $plan = $this->approvedPlan();
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_PASS, $result['verdict']);
        self::assertSame(OrchestrationMeasurementPlanValidator::EXIT_PASS, $result['exit_code']);
        self::assertSame('not_started', $result['execution']);
        self::assertSame(3, $result['run_repetitions']);
    }

    public function test_missing_metric_is_rejected_before_any_execution(): void
    {
        $plan = $this->basePlan();
        array_pop($plan['metrics']);
        $plan['plan_sha256'] = $this->planHash($plan);
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame(OrchestrationMeasurementPlanValidator::EXIT_INVALID, $result['exit_code']);
        self::assertSame([['code' => 'METRICS_INVALID']], $result['diagnostics']);
    }

    public function test_fewer_than_three_repetitions_is_rejected(): void
    {
        $plan = $this->basePlan();
        $plan['run_repetitions'] = 2;
        $plan['plan_sha256'] = $this->planHash($plan);
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'PLAN_SCHEMA_INVALID']], $result['diagnostics']);
    }

    public function test_source_pin_drift_is_rejected(): void
    {
        $plan = $this->basePlan();
        $plan['source_sha'] = str_repeat('0', 40);
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'PLAN_SCHEMA_INVALID']], $result['diagnostics']);
    }

    public function test_prohibited_plan_source_is_rejected_before_reading(): void
    {
        $result = app(OrchestrationMeasurementPlanValidator::class)->validate(dirname(__DIR__, 5).'/.env');

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'PROHIBITED_SOURCE']], $result['diagnostics']);
    }

    public function test_approved_plan_requires_dated_approval_and_approvers(): void
    {
        $plan = $this->approvedPlan();
        $plan['approval']['approved_at'] = null;
        $plan['plan_sha256'] = $this->planHash($plan);
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'APPROVAL_EVIDENCE_INVALID']], $result['diagnostics']);
    }

    public function test_command_forwards_a_custom_plan_path_and_exit_code(): void
    {
        $plan = $this->approvedPlan();
        $plan['approval']['approved_at'] = null;
        $plan['plan_sha256'] = $this->planHash($plan);
        $path = $this->writePlan($plan);

        try {
            $this->artisan('safety:orchestration-plan', ['--plan' => $path])
                ->expectsOutputToContain('APPROVAL_EVIDENCE_INVALID')
                ->assertExitCode(OrchestrationMeasurementPlanValidator::EXIT_INVALID);
            $this->artisan('validate:orchestration-plan', ['--plan' => $path])
                ->expectsOutputToContain('APPROVAL_EVIDENCE_INVALID')
                ->assertExitCode(OrchestrationMeasurementPlanValidator::EXIT_INVALID);
        } finally {
            unlink($path);
        }
    }

    public function test_forbidden_unknown_plan_field_is_rejected(): void
    {
        $plan = $this->basePlan();
        $plan['raw_cv'] = 'synthetic canary';
        $plan['plan_sha256'] = $this->planHash($plan);
        $path = $this->writePlan($plan);

        try {
            $result = app(OrchestrationMeasurementPlanValidator::class)->validate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(OrchestrationMeasurementPlanValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'FORBIDDEN_CONTENT']], $result['diagnostics']);
    }

    public function test_artisan_commands_propagate_approved_and_alias_exit_codes(): void
    {
        $this->artisan('safety:orchestration-plan')
            ->expectsOutputToContain('"verdict":"pass"')
            ->assertExitCode(OrchestrationMeasurementPlanValidator::EXIT_PASS);

        $this->artisan('validate:orchestration-plan')
            ->expectsOutputToContain('"verdict":"pass"')
            ->assertExitCode(OrchestrationMeasurementPlanValidator::EXIT_PASS);
    }

    /** @return array<string,mixed> */
    private function basePlan(): array
    {
        $plan = [
            'plan_version' => OrchestrationMeasurementPlanValidator::PLAN_VERSION,
            'plan_id' => 'single-orchestrator-measurement-test',
            'status' => 'proposed',
            'source_sha' => 'e27910f1e3f760ec87e6773c31310b955b195780',
            'environment' => 'local-ci-disposable',
            'workload_source' => 'synthetic',
            'data_classification' => 'synthetic-no-user-content',
            'provider_mode' => 'not_configured',
            'config_version' => '1.0.0',
            'workload_version' => '1.0.0',
            'metric_version' => '1.0.0',
            'orchestrator_version' => 'single-orchestrator-v1',
            'plan_sha256' => '',
            'run_repetitions' => 3,
            'metrics' => [
                'quality_contract',
                'latency_ms',
                'cost_units',
                'reliability_rate',
                'operability_failure_rate',
                'variance',
            ],
            'metric_definitions' => [
                ['name' => 'quality_contract', 'unit' => 'boolean', 'aggregation' => 'per_workload', 'sampling' => 'every_run', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
                ['name' => 'latency_ms', 'unit' => 'milliseconds', 'aggregation' => 'p50_p95', 'sampling' => 'every_run', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
                ['name' => 'cost_units', 'unit' => 'abstract_units', 'aggregation' => 'sum_and_mean', 'sampling' => 'every_run', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
                ['name' => 'reliability_rate', 'unit' => 'ratio', 'aggregation' => 'mean', 'sampling' => 'every_run', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
                ['name' => 'operability_failure_rate', 'unit' => 'ratio', 'aggregation' => 'mean', 'sampling' => 'every_run', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
                ['name' => 'variance', 'unit' => 'ratio', 'aggregation' => 'distribution', 'sampling' => 'all_runs', 'threshold_ref' => 'E5-DEC-007-recommendation-v1'],
            ],
            'alternatives' => [
                'single_orchestrator_tuning',
                'deterministic_workflow_split',
                'bounded_queue',
                'multi_agent',
            ],
            'alternative_reviews' => [
                ['id' => 'single_orchestrator_tuning', 'quality_risk' => 'risk', 'operational_risk' => 'risk', 'safety_boundary' => 'preserved', 'requires_runtime_change' => false],
                ['id' => 'deterministic_workflow_split', 'quality_risk' => 'risk', 'operational_risk' => 'risk', 'safety_boundary' => 'preserved', 'requires_runtime_change' => true],
                ['id' => 'bounded_queue', 'quality_risk' => 'risk', 'operational_risk' => 'risk', 'safety_boundary' => 'preserved', 'requires_runtime_change' => true],
                ['id' => 'multi_agent', 'quality_risk' => 'risk', 'operational_risk' => 'risk', 'safety_boundary' => 'preserved', 'requires_runtime_change' => true],
            ],
            'limitation_rule' => 'A named workload must fail its contract after deterministic tuning and a bounded queue are assessed before multi-agent adoption.',
            'limitation_rule_version' => 'E5-DEC-007-recommendation-v1',
            'baseline_config' => [
                'version' => '1.0.0',
                'orchestrator' => 'single',
                'tool_allowlist' => [],
                'write_permissions' => [],
                'human_approval_required' => true,
                'runtime_components_added' => false,
            ],
            'config_sha256' => '',
            'workload_path' => 'docs/contracts/operational-safety/fixtures/orchestration-workloads-v1.json',
            'workload_ids' => ['synthetic-match-quality-001', 'synthetic-patch-boundary-001', 'synthetic-failure-recovery-001'],
            'workload_sha256' => 'fe2de21e45f09c918ad5f982aec41a49d74b375cbd9ec281b08cdd00bdb639d8',
            'privacy' => [
                'synthetic_only' => true,
                'forbidden_sources' => ['production_db', 'provider_credentials', 'raw_cv', 'raw_jd'],
            ],
            'approval' => [
                'required_decisions' => ['E5-PREREQ-AI-001', 'E5-DEC-007', 'E5-DEC-008'],
                'decisions' => [
                    ['id' => 'E5-PREREQ-AI-001', 'status' => 'pending'],
                    ['id' => 'E5-DEC-007', 'status' => 'pending'],
                    ['id' => 'E5-DEC-008', 'status' => 'pending'],
                ],
                'owner' => 'unassigned',
                'approved_at' => null,
                'approvers' => [],
            ],
        ];

        $plan['config_sha256'] = hash('sha256', CanonicalJson::encode($plan['baseline_config']));
        $plan['plan_sha256'] = $this->planHash($plan);

        return $plan;
    }

    /** @return array<string,mixed> */
    private function approvedPlan(): array
    {
        $plan = $this->basePlan();
        $plan['status'] = 'approved';
        $plan['approval']['decisions'] = array_map(
            static fn (array $decision): array => [...$decision, 'status' => 'approved', 'evidence_ref' => 'decision-evidence-'.$decision['id']],
            $plan['approval']['decisions'],
        );
        $plan['approval']['owner'] = 'architecture-owner';
        $plan['approval']['approved_at'] = '2026-10-07T12:00:00+00:00';
        $plan['approval']['approvers'] = ['security-owner', 'operations-owner'];
        $plan['plan_sha256'] = $this->planHash($plan);

        return $plan;
    }

    /** @param array<string,mixed> $plan */
    private function planHash(array $plan): string
    {
        unset($plan['plan_sha256']);

        return hash('sha256', CanonicalJson::encode($plan));
    }

    /** @param array<string,mixed> $plan */
    private function writePlan(array $plan): string
    {
        $path = tempnam(sys_get_temp_dir(), 'orchestration-plan-');
        self::assertIsString($path);
        file_put_contents($path, json_encode($plan, JSON_THROW_ON_ERROR));

        return $path;
    }
}
