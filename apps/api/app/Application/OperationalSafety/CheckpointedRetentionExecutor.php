<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use App\Application\Cv\CanonicalJson;

final class CheckpointedRetentionExecutor
{
    public const EXECUTION_VERSION = '1.0.0';

    private RetentionPolicyEvaluator $policyEvaluator;

    public function __construct(?RetentionPolicyEvaluator $policyEvaluator = null)
    {
        $this->policyEvaluator = $policyEvaluator ?? new RetentionPolicyEvaluator;
    }

    /**
     * Create a disposable checkpoint from an approved, mutation-free preview.
     * The returned checkpoint contains counts only and cannot target a table,
     * record, User ID, production environment, or external processor.
     *
     * @param  array<string,mixed>  $plan
     * @return array<string,mixed>
     */
    public function createCheckpoint(array $plan, string $approvalHash): array
    {
        if (($plan['environment'] ?? null) !== RetentionPolicyEvaluator::ENVIRONMENT
            || ($plan['dry_run'] ?? null) !== true
            || ($plan['status'] ?? null) !== 'preview'
            || ! $this->policyEvaluator->verifyApprovalHash($plan, $approvalHash)) {
            return $this->failure('APPROVAL_OR_ENVIRONMENT_INVALID');
        }

        $checkpointKey = hash('sha256', CanonicalJson::encode([
            'approval_hash' => $approvalHash,
            'execution_version' => self::EXECUTION_VERSION,
        ]));

        return [
            'accepted' => true,
            'execution_version' => self::EXECUTION_VERSION,
            'execution' => 'disposable_no_mutation',
            'status' => 'ready',
            'completion_claimable' => false,
            'approval_hash' => $approvalHash,
            'checkpoint_key' => $checkpointKey,
            'plan' => $plan,
            'checkpoint' => [
                'next_batch_index' => 0,
                'completed_batch_keys' => [],
                'processed_count' => 0,
            ],
            'diagnostics' => [],
        ];
    }

    /**
     * Advance a checkpoint without mutating any persistence. The optional
     * failure index is a deterministic disposable fault-injection seam.
     *
     * @param  array<string,mixed>  $checkpoint
     * @return array<string,mixed>
     */
    public function run(array $checkpoint, ?int $failAtBatchIndex = null): array
    {
        if (! $this->isValidCheckpoint($checkpoint)) {
            return $this->failure('CHECKPOINT_INVALID');
        }

        if ($checkpoint['status'] === 'completed_disposable') {
            return $checkpoint;
        }

        $plan = $checkpoint['plan'];
        $batches = $plan['batches'];
        $state = $checkpoint['checkpoint'];
        $start = $state['next_batch_index'];

        for ($index = $start; $index < count($batches); $index++) {
            if ($failAtBatchIndex === $index) {
                $checkpoint['checkpoint'] = $state;
                $checkpoint['status'] = 'partial_retryable';
                $checkpoint['diagnostics'] = [[
                    'code' => 'DISPOSABLE_BATCH_FAILURE',
                    'batch_index' => $index,
                    'next_batch_index' => $index,
                ]];

                return $checkpoint;
            }

            $batch = $batches[$index];
            $batchKey = $this->batchKey($batch);
            if (! in_array($batchKey, $state['completed_batch_keys'], true)) {
                $state['completed_batch_keys'][] = $batchKey;
                $state['processed_count'] += $batch['planned_count'];
            }
            $state['next_batch_index'] = $index + 1;
        }

        $checkpoint['checkpoint'] = $state;
        $checkpoint['status'] = 'completed_disposable';
        $checkpoint['diagnostics'] = [];

        return $checkpoint;
    }

    /** @param array<string,mixed> $checkpoint */
    private function isValidCheckpoint(array $checkpoint): bool
    {
        if (($checkpoint['accepted'] ?? null) !== true
            || ($checkpoint['execution_version'] ?? null) !== self::EXECUTION_VERSION
            || ($checkpoint['execution'] ?? null) !== 'disposable_no_mutation'
            || ($checkpoint['completion_claimable'] ?? null) !== false
            || ! in_array($checkpoint['status'] ?? null, ['ready', 'partial_retryable', 'completed_disposable'], true)
            || ! is_string($checkpoint['approval_hash'] ?? null)
            || ! is_string($checkpoint['checkpoint_key'] ?? null)
            || ! is_array($checkpoint['plan'] ?? null)
            || ! $this->policyEvaluator->verifyApprovalHash($checkpoint['plan'], $checkpoint['approval_hash'])) {
            return false;
        }

        $expectedCheckpointKey = hash('sha256', CanonicalJson::encode([
            'approval_hash' => $checkpoint['approval_hash'],
            'execution_version' => self::EXECUTION_VERSION,
        ]));
        if (! hash_equals($expectedCheckpointKey, $checkpoint['checkpoint_key'])) {
            return false;
        }

        $state = $checkpoint['checkpoint'] ?? null;
        $batches = $checkpoint['plan']['batches'] ?? null;
        if (! is_array($batches) || ! array_is_list($batches)
            || ! is_array($state)
            || ! is_int($state['next_batch_index'] ?? null)
            || $state['next_batch_index'] < 0
            || $state['next_batch_index'] > count($batches)
            || ! is_int($state['processed_count'] ?? null)
            || $state['processed_count'] < 0
            || ! is_array($state['completed_batch_keys'] ?? null)
            || ! array_is_list($state['completed_batch_keys'])) {
            return false;
        }

        $expectedKeys = [];
        $expectedProcessedCount = 0;
        foreach ($batches as $batch) {
            if (! is_array($batch)) {
                return false;
            }
            $expectedKeys[] = $this->batchKey($batch);
        }
        foreach (array_slice($batches, 0, $state['next_batch_index']) as $batch) {
            $expectedProcessedCount += $batch['planned_count'];
        }

        return $state['completed_batch_keys'] === array_slice($expectedKeys, 0, $state['next_batch_index'])
            && $state['processed_count'] === $expectedProcessedCount
            && ($checkpoint['status'] !== 'completed_disposable' || $state['next_batch_index'] === count($batches));
    }

    /** @param array<string,mixed> $batch */
    private function batchKey(array $batch): string
    {
        return hash('sha256', CanonicalJson::encode([
            'data_class' => $batch['data_class'],
            'dependency_order' => $batch['dependency_order'],
            'action' => $batch['action'],
        ]));
    }

    /** @return array<string,mixed> */
    private function failure(string $diagnostic): array
    {
        return [
            'accepted' => false,
            'execution_version' => self::EXECUTION_VERSION,
            'status' => 'blocked',
            'completion_claimable' => false,
            'diagnostic' => $diagnostic,
        ];
    }
}
