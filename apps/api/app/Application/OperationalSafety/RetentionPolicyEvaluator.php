<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use App\Application\Cv\CanonicalJson;

final class RetentionPolicyEvaluator
{
    public const POLICY_VERSION = '1.0.0';

    public const PLAN_VERSION = '1.0.0';

    public const ENVIRONMENT = 'local-ci-disposable';

    public const OPERATION_RETENTION = 'retention';

    public const OPERATION_USER_DELETION = 'user_deletion';

    /**
     * The order is part of the policy contract. It is deliberately expressed
     * in data-class terms instead of accepting table or record identifiers.
     *
     * @var array<int,array{data_class:string,system:string,authority:string,retention_action:string,deletion_action:string,dependency_order:int,retention_days:int|null}>
     */
    private const POLICY_ROWS = [
        [
            'data_class' => 'trusted_product_records',
            'system' => 'product',
            'authority' => 'laravel_domain',
            'retention_action' => 'retain',
            'deletion_action' => 'delete',
            'dependency_order' => 10,
            'retention_days' => null,
        ],
        [
            'data_class' => 'derived_trusted_records',
            'system' => 'product',
            'authority' => 'laravel_domain',
            'retention_action' => 'retain',
            'deletion_action' => 'delete',
            'dependency_order' => 20,
            'retention_days' => null,
        ],
        [
            'data_class' => 'provider_job_attempts',
            'system' => 'operational',
            'authority' => 'application',
            'retention_action' => 'delete',
            'deletion_action' => 'delete',
            'dependency_order' => 30,
            'retention_days' => 30,
        ],
        [
            'data_class' => 'audit_events',
            'system' => 'audit',
            'authority' => 'operational',
            'retention_action' => 'delete',
            'deletion_action' => 'delete',
            'dependency_order' => 40,
            'retention_days' => 30,
        ],
        [
            'data_class' => 'metrics_traces_logs',
            'system' => 'observability',
            'authority' => 'observability',
            'retention_action' => 'aggregate',
            'deletion_action' => 'aggregate',
            'dependency_order' => 50,
            'retention_days' => 30,
        ],
        [
            'data_class' => 'evaluation_artifacts',
            'system' => 'ci_artifact',
            'authority' => 'version_control',
            'retention_action' => 'retain',
            'deletion_action' => 'retain',
            'dependency_order' => 60,
            'retention_days' => 7,
        ],
        [
            'data_class' => 'backups',
            'system' => 'backup',
            'authority' => 'environment_owner',
            'retention_action' => 'retain_until_expiry',
            'deletion_action' => 'retain_until_expiry',
            'dependency_order' => 70,
            'retention_days' => 30,
        ],
        [
            'data_class' => 'external_processors',
            'system' => 'external',
            'authority' => 'processor_owner',
            'retention_action' => 'retain_pending',
            'deletion_action' => 'retain_pending',
            'dependency_order' => 80,
            'retention_days' => 30,
        ],
    ];

    /** @var array<int,string> */
    private const FORBIDDEN_SCOPE_KEYS = [
        'id',
        'ids',
        'record_id',
        'record_ids',
        'table',
        'tables',
        'user_id',
        'user_ids',
    ];

    /** @var array<int,string> */
    private const HOLD_REASON_CODES = [
        'legal_hold',
        'required_trusted_history',
        'pending_external_expiry',
    ];

    /**
     * Evaluate a server-derived, content-free inventory into a mutation-free
     * retention plan. This method never reads or writes product persistence.
     *
     * @param  array<string,mixed>  $request
     * @return array<string,mixed>
     */
    public function evaluate(array $request): array
    {
        if ($this->containsForbiddenScopeKeys($request)) {
            return $this->failure('ARBITRARY_SCOPE_FORBIDDEN');
        }

        $allowedRequestKeys = [
            'policy_version',
            'environment',
            'operation',
            'subject_scope',
            'cutoff',
            'dry_run',
            'holds',
            'inventory',
        ];
        if (array_diff(array_keys($request), $allowedRequestKeys) !== []) {
            return $this->failure('REQUEST_SHAPE_INVALID');
        }

        if (($request['policy_version'] ?? null) !== self::POLICY_VERSION
            || ($request['environment'] ?? null) !== self::ENVIRONMENT
            || ! in_array($request['operation'] ?? null, [self::OPERATION_RETENTION, self::OPERATION_USER_DELETION], true)
            || ($request['subject_scope'] ?? null) !== 'authenticated_subject'
            || ($request['dry_run'] ?? null) !== true
            || ! is_string($request['cutoff'] ?? null)
            || ! $this->isCanonicalDate($request['cutoff'])) {
            return $this->failure('REQUEST_BOUNDARY_INVALID');
        }

        $holds = $this->validateHolds($request['holds'] ?? null);
        if ($holds === null) {
            return $this->failure('HOLDS_INVALID');
        }

        $inventory = $this->validateInventory($request['inventory'] ?? null);
        if ($inventory === null) {
            return $this->failure('INVENTORY_INVALID');
        }

        $rowsByClass = [];
        foreach ($inventory as $row) {
            $rowsByClass[$row['data_class']] = $row;
        }

        $planRows = [];
        $totals = [
            'eligible_count' => 0,
            'held_count' => 0,
            'planned_count' => 0,
            'retained_count' => 0,
        ];

        foreach (self::POLICY_ROWS as $policyRow) {
            $inventoryRow = $rowsByClass[$policyRow['data_class']];
            $holdActive = isset($holds[$policyRow['data_class']]);
            $eligibleCount = $inventoryRow['eligible_count'];
            $heldCount = $holdActive ? $eligibleCount : $inventoryRow['held_count'];
            $plannedCount = max(0, $eligibleCount - $heldCount);
            $action = $request['operation'] === self::OPERATION_USER_DELETION
                ? $policyRow['deletion_action']
                : $policyRow['retention_action'];

            if (in_array($action, ['retain', 'retain_until_expiry', 'retain_pending'], true)) {
                $plannedCount = 0;
            }

            $retainedCount = $eligibleCount - $plannedCount;
            $planRows[] = [
                'data_class' => $policyRow['data_class'],
                'system' => $policyRow['system'],
                'authority' => $policyRow['authority'],
                'action' => $action,
                'dependency_order' => $policyRow['dependency_order'],
                'retention_days' => $policyRow['retention_days'],
                'eligible_count' => $eligibleCount,
                'held_count' => $heldCount,
                'planned_count' => $plannedCount,
                'retained_count' => $retainedCount,
                'hold_active' => $holdActive,
                'verification' => 'safe_counts_only',
            ];
            $totals['eligible_count'] += $eligibleCount;
            $totals['held_count'] += $heldCount;
            $totals['planned_count'] += $plannedCount;
            $totals['retained_count'] += $retainedCount;
        }

        $payload = [
            'policy_version' => self::POLICY_VERSION,
            'plan_version' => self::PLAN_VERSION,
            'environment' => self::ENVIRONMENT,
            'operation' => $request['operation'],
            'subject_scope' => 'authenticated_subject',
            'cutoff' => $request['cutoff'],
            'dry_run' => true,
            'holds' => array_values($holds),
            'batches' => $planRows,
            'totals' => $totals,
        ];

        return [
            'accepted' => true,
            ...$payload,
            'status' => 'preview',
            'approval_hash' => hash('sha256', CanonicalJson::encode($payload)),
            'diagnostics' => [],
        ];
    }

    /**
     * Verify that an approval refers to exactly this unchanged preview.
     *
     * @param  array<string,mixed>  $plan
     */
    public function verifyApprovalHash(array $plan, string $approvalHash): bool
    {
        if (($plan['accepted'] ?? null) !== true
            || ($plan['approval_hash'] ?? null) !== $approvalHash
            || ! is_array($plan['batches'] ?? null)
            || ! is_array($plan['totals'] ?? null)) {
            return false;
        }

        $payload = $plan;
        unset($payload['accepted'], $payload['status'], $payload['approval_hash'], $payload['diagnostics']);

        return hash_equals($approvalHash, hash('sha256', CanonicalJson::encode($payload)));
    }

    /**
     * @return array<int,array{data_class:string,system:string,authority:string,retention_action:string,deletion_action:string,dependency_order:int,retention_days:int|null}>
     */
    public function policyRows(): array
    {
        return self::POLICY_ROWS;
    }

    /** @param array<string,mixed> $request */
    private function containsForbiddenScopeKeys(array $request): bool
    {
        foreach (self::FORBIDDEN_SCOPE_KEYS as $key) {
            if (array_key_exists($key, $request)) {
                return true;
            }
        }

        return false;
    }

    private function isCanonicalDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && $date->format(\DATE_ATOM) === $value
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }

    /**
     * @return array<string,array{data_class:string,reason_code:string}>|null
     */
    private function validateHolds(mixed $holds): ?array
    {
        if (! is_array($holds) || ! array_is_list($holds)) {
            return null;
        }

        $knownClasses = array_column(self::POLICY_ROWS, 'data_class');
        $result = [];
        foreach ($holds as $hold) {
            if (! is_array($hold)
                || array_keys($hold) !== ['data_class', 'reason_code']
                || ! is_string($hold['data_class'])
                || ! in_array($hold['data_class'], $knownClasses, true)
                || ! is_string($hold['reason_code'])
                || ! in_array($hold['reason_code'], self::HOLD_REASON_CODES, true)
                || isset($result[$hold['data_class']])) {
                return null;
            }
            $result[$hold['data_class']] = [
                'data_class' => $hold['data_class'],
                'reason_code' => $hold['reason_code'],
            ];
        }

        return $result;
    }

    /**
     * @return array<int,array{data_class:string,eligible_count:int,held_count:int}>|null
     */
    private function validateInventory(mixed $inventory): ?array
    {
        if (! is_array($inventory) || ! array_is_list($inventory) || count($inventory) !== count(self::POLICY_ROWS)) {
            return null;
        }

        $knownClasses = array_column(self::POLICY_ROWS, 'data_class');
        $seen = [];
        foreach ($inventory as $row) {
            if (! is_array($row)
                || array_keys($row) !== ['data_class', 'eligible_count', 'held_count']
                || ! is_string($row['data_class'])
                || ! in_array($row['data_class'], $knownClasses, true)
                || isset($seen[$row['data_class']])
                || ! is_int($row['eligible_count'])
                || $row['eligible_count'] < 0
                || ! is_int($row['held_count'])
                || $row['held_count'] < 0
                || $row['held_count'] > $row['eligible_count']) {
                return null;
            }
            $seen[$row['data_class']] = true;
        }

        return count($seen) === count(self::POLICY_ROWS) ? $inventory : null;
    }

    /** @return array<string,mixed> */
    private function failure(string $diagnostic): array
    {
        return [
            'accepted' => false,
            'policy_version' => self::POLICY_VERSION,
            'plan_version' => self::PLAN_VERSION,
            'status' => 'blocked',
            'diagnostic' => $diagnostic,
        ];
    }
}
