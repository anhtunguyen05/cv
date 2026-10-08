<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\CheckpointedRetentionExecutor;
use App\Application\OperationalSafety\RetentionPolicyEvaluator;
use PHPUnit\Framework\TestCase;

final class RetentionPolicyEvaluatorTest extends TestCase
{
    public function test_policy_is_fixed_ordered_and_dry_run_only(): void
    {
        $plan = $this->evaluator()->evaluate($this->request());

        self::assertTrue($plan['accepted']);
        self::assertSame('preview', $plan['status']);
        self::assertTrue($plan['dry_run']);
        self::assertSame(
            ['trusted_product_records', 'derived_trusted_records', 'provider_job_attempts', 'audit_events', 'metrics_traces_logs', 'evaluation_artifacts', 'backups', 'external_processors'],
            array_column($plan['batches'], 'data_class'),
        );
        self::assertSame([10, 20, 30, 40, 50, 60, 70, 80], array_column($plan['batches'], 'dependency_order'));
    }

    public function test_checked_in_contract_fixture_matches_the_policy(): void
    {
        $fixturePath = dirname(__DIR__, 5).'/docs/contracts/operational-safety/fixtures/retention-policy-v1.json';
        $fixture = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(RetentionPolicyEvaluator::POLICY_VERSION, $fixture['policy_version']);
        self::assertSame(RetentionPolicyEvaluator::PLAN_VERSION, $fixture['plan_version']);
        self::assertSame(RetentionPolicyEvaluator::ENVIRONMENT, $fixture['environment']);
        self::assertSame(
            array_column($fixture['data_classes'], 'name'),
            array_column($this->evaluator()->policyRows(), 'data_class'),
        );
        self::assertSame(
            array_column($fixture['data_classes'], 'dependency_order'),
            array_column($this->evaluator()->policyRows(), 'dependency_order'),
        );
    }

    public function test_user_deletion_excludes_held_data_and_keeps_non_authoritative_data_safe(): void
    {
        $request = $this->request();
        $request['operation'] = RetentionPolicyEvaluator::OPERATION_USER_DELETION;
        $request['holds'] = [['data_class' => 'trusted_product_records', 'reason_code' => 'required_trusted_history']];

        $plan = $this->evaluator()->evaluate($request);

        self::assertTrue($plan['accepted']);
        self::assertSame(0, $plan['batches'][0]['planned_count']);
        self::assertSame(2, $plan['batches'][0]['held_count']);
        self::assertSame('retain_pending', $plan['batches'][7]['action']);
        self::assertSame(0, $plan['batches'][7]['planned_count']);
        self::assertSame(2, $plan['totals']['held_count']);
    }

    public function test_policy_hash_is_stable_and_changes_when_scope_changes(): void
    {
        $first = $this->evaluator()->evaluate($this->request());
        $second = $this->evaluator()->evaluate($this->request());
        $changed = $this->request();
        $changed['inventory'][2]['eligible_count'] = 4;
        $third = $this->evaluator()->evaluate($changed);

        self::assertSame($first['approval_hash'], $second['approval_hash']);
        self::assertNotSame($first['approval_hash'], $third['approval_hash']);
        self::assertTrue($this->evaluator()->verifyApprovalHash($first, $first['approval_hash']));
        self::assertFalse($this->evaluator()->verifyApprovalHash($first, $third['approval_hash']));
    }

    public function test_arbitrary_client_scope_and_production_environment_fail_closed(): void
    {
        $request = $this->request();
        $request['user_id'] = 42;
        self::assertSame('ARBITRARY_SCOPE_FORBIDDEN', $this->evaluator()->evaluate($request)['diagnostic']);

        $request = $this->request();
        $request['environment'] = 'production';
        self::assertSame('REQUEST_BOUNDARY_INVALID', $this->evaluator()->evaluate($request)['diagnostic']);

        $request = $this->request();
        $request['client_scope'] = 'all_users';
        self::assertSame('REQUEST_SHAPE_INVALID', $this->evaluator()->evaluate($request)['diagnostic']);
    }

    public function test_inventory_shape_and_policy_version_are_required(): void
    {
        $request = $this->request();
        $request['policy_version'] = '9.9.9';
        self::assertSame('REQUEST_BOUNDARY_INVALID', $this->evaluator()->evaluate($request)['diagnostic']);

        $request = $this->request();
        $request['inventory'][0]['table'] = 'users';
        self::assertSame('INVENTORY_INVALID', $this->evaluator()->evaluate($request)['diagnostic']);
    }

    public function test_checkpoint_resumes_after_disposable_failure_and_is_idempotent(): void
    {
        $plan = $this->evaluator()->evaluate($this->request());
        $executor = new CheckpointedRetentionExecutor($this->evaluator());
        $checkpoint = $executor->createCheckpoint($plan, $plan['approval_hash']);

        self::assertTrue($checkpoint['accepted']);
        $partial = $executor->run($checkpoint, 2);
        self::assertSame('partial_retryable', $partial['status']);
        self::assertSame(2, $partial['checkpoint']['next_batch_index']);
        self::assertSame(0, $partial['checkpoint']['processed_count']);

        $completed = $executor->run($partial);
        self::assertSame('completed_disposable', $completed['status']);
        self::assertFalse($completed['completion_claimable']);
        self::assertSame(3, $completed['checkpoint']['processed_count']);
        self::assertSame($completed, $executor->run($completed));
    }

    public function test_checkpoint_rejects_wrong_approval_and_tampered_plan(): void
    {
        $plan = $this->evaluator()->evaluate($this->request());
        $executor = new CheckpointedRetentionExecutor($this->evaluator());

        self::assertSame('APPROVAL_OR_ENVIRONMENT_INVALID', $executor->createCheckpoint($plan, str_repeat('0', 64))['diagnostic']);

        $plan['batches'][0]['planned_count'] = 999;
        self::assertSame('APPROVAL_OR_ENVIRONMENT_INVALID', $executor->createCheckpoint($plan, $plan['approval_hash'])['diagnostic']);
    }

    public function test_checkpoint_rejects_tampered_progress_state(): void
    {
        $plan = $this->evaluator()->evaluate($this->request());
        $executor = new CheckpointedRetentionExecutor($this->evaluator());
        $checkpoint = $executor->createCheckpoint($plan, $plan['approval_hash']);
        $checkpoint['checkpoint']['next_batch_index'] = 2;

        self::assertSame('CHECKPOINT_INVALID', $executor->run($checkpoint)['diagnostic']);
    }

    public function test_checkpoint_rejects_tampered_checkpoint_identity(): void
    {
        $plan = $this->evaluator()->evaluate($this->request());
        $executor = new CheckpointedRetentionExecutor($this->evaluator());
        $checkpoint = $executor->createCheckpoint($plan, $plan['approval_hash']);
        $checkpoint['checkpoint_key'] = str_repeat('0', 64);

        self::assertSame('CHECKPOINT_INVALID', $executor->run($checkpoint)['diagnostic']);
    }

    /** @return array<string,mixed> */
    private function request(): array
    {
        $inventory = [];
        foreach ($this->evaluator()->policyRows() as $row) {
            $inventory[] = [
                'data_class' => $row['data_class'],
                'eligible_count' => match ($row['data_class']) {
                    'trusted_product_records' => 2,
                    'provider_job_attempts' => 3,
                    default => 0,
                },
                'held_count' => 0,
            ];
        }

        return [
            'policy_version' => RetentionPolicyEvaluator::POLICY_VERSION,
            'environment' => RetentionPolicyEvaluator::ENVIRONMENT,
            'operation' => RetentionPolicyEvaluator::OPERATION_RETENTION,
            'subject_scope' => 'authenticated_subject',
            'cutoff' => '2026-10-08T00:00:00+00:00',
            'dry_run' => true,
            'holds' => [],
            'inventory' => $inventory,
        ];
    }

    private function evaluator(): RetentionPolicyEvaluator
    {
        return new RetentionPolicyEvaluator;
    }
}
