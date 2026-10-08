<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use App\Application\Cv\CanonicalJson;
use DateTimeImmutable;
use Throwable;

final class OrchestrationMeasurementPlanValidator
{
    public const PLAN_VERSION = '1.0.0';

    public const VERDICT_PASS = 'pass';

    public const VERDICT_BLOCKED = 'blocked';

    public const VERDICT_INVALID = 'invalid';

    public const VERDICT_INFRASTRUCTURE_FAILURE = 'infrastructure_failure';

    public const EXIT_PASS = 0;

    public const EXIT_BLOCKED = 30;

    public const EXIT_INVALID = 31;

    public const EXIT_INFRASTRUCTURE_FAILURE = 32;

    private const CONFIG_VERSION = '1.0.0';

    private const WORKLOAD_VERSION = '1.0.0';

    private const METRIC_VERSION = '1.0.0';

    private const MAX_RUN_REPETITIONS = 100;

    private const PLAN_RELATIVE_PATH = 'docs/contracts/operational-safety/fixtures/orchestration-measurement-plan-v1.json';

    private const WORKLOAD_RELATIVE_PATH = 'docs/contracts/operational-safety/fixtures/orchestration-workloads-v1.json';

    private const SOURCE_SHA = 'e27910f1e3f760ec87e6773c31310b955b195780';

    /** @var array<int,string> */
    private const METRICS = [
        'quality_contract',
        'latency_ms',
        'cost_units',
        'reliability_rate',
        'operability_failure_rate',
        'variance',
    ];

    /** @var array<int,string> */
    private const ALTERNATIVES = [
        'single_orchestrator_tuning',
        'deterministic_workflow_split',
        'bounded_queue',
        'multi_agent',
    ];

    /** @var array<int,string> */
    private const REQUIRED_DECISIONS = [
        'E5-PREREQ-AI-001',
        'E5-DEC-007',
        'E5-DEC-008',
    ];

    /** @var array<int,string> */
    private const REQUIRED_FORBIDDEN_SOURCES = [
        'production_db',
        'provider_credentials',
        'raw_cv',
        'raw_jd',
    ];

    private const LIMITATION_RULE = 'A named workload must fail its contract after deterministic tuning and a bounded queue are assessed before multi-agent adoption.';

    /** @var array<int,string> */
    private const FORBIDDEN_KEYS = [
        'authorization',
        'cookie',
        'email',
        'password',
        'prompt',
        'raw_cv',
        'raw_jd',
        'secret',
        'token',
    ];

    /**
     * Validate a synthetic measurement plan without running a workload.
     *
     * @return array<string,mixed>
     */
    public function validate(?string $planPath = null): array
    {
        if ($planPath !== null) {
            if (! app()->environment('testing') || ! $this->isAllowedTestingPath($planPath)) {
                return $this->failure(self::VERDICT_INVALID, 'PROHIBITED_SOURCE');
            }
        }

        $planPath ??= $this->projectRoot().'/'.self::PLAN_RELATIVE_PATH;
        $resolvedPlanPath = realpath($planPath);
        if ($resolvedPlanPath === false || ! $this->isAllowedResolvedPath($resolvedPlanPath)) {
            return $this->failure(self::VERDICT_INVALID, 'PROHIBITED_SOURCE');
        }
        $loaded = $this->readJson($resolvedPlanPath);
        if ($loaded['state'] === 'infrastructure') {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'PLAN_UNAVAILABLE');
        }
        if ($loaded['state'] === 'invalid') {
            return $this->failure(self::VERDICT_INVALID, 'PLAN_INVALID');
        }

        $plan = $loaded['data'];
        $error = $this->validateShape($plan);
        if ($error !== null) {
            return $this->failure(self::VERDICT_INVALID, $error);
        }

        $pending = [];
        foreach ($plan['approval']['decisions'] as $decision) {
            if ($decision['status'] !== 'approved') {
                $pending[] = $decision['id'];
            }
        }
        if ($plan['status'] !== 'approved' || $pending !== [] || $plan['approval']['owner'] === 'unassigned') {
            return [
                'plan_version' => self::PLAN_VERSION,
                'plan_id' => $plan['plan_id'],
                'source_sha' => $plan['source_sha'],
                'environment' => $plan['environment'],
                'verdict' => self::VERDICT_BLOCKED,
                'exit_code' => self::EXIT_BLOCKED,
                'execution' => 'not_started',
                'pending_decisions' => $pending,
                'diagnostics' => [['code' => 'APPROVAL_PENDING']],
            ];
        }

        if ($plan['approval']['approved_at'] === null || $plan['approval']['approvers'] === []) {
            return $this->failure(self::VERDICT_INVALID, 'APPROVAL_EVIDENCE_INVALID');
        }

        return [
            'plan_version' => self::PLAN_VERSION,
            'plan_id' => $plan['plan_id'],
            'source_sha' => $plan['source_sha'],
            'environment' => $plan['environment'],
            'verdict' => self::VERDICT_PASS,
            'exit_code' => self::EXIT_PASS,
            'execution' => 'not_started',
            'pending_decisions' => [],
            'metrics' => self::METRICS,
            'alternatives' => self::ALTERNATIVES,
            'run_repetitions' => $plan['run_repetitions'],
            'diagnostics' => [],
        ];
    }

    public static function exitCode(string $verdict): int
    {
        return match ($verdict) {
            self::VERDICT_PASS => self::EXIT_PASS,
            self::VERDICT_BLOCKED => self::EXIT_BLOCKED,
            self::VERDICT_INVALID => self::EXIT_INVALID,
            default => self::EXIT_INFRASTRUCTURE_FAILURE,
        };
    }

    /** @param array<string,mixed> $plan */
    private function validateShape(array $plan): ?string
    {
        foreach ([
            'plan_version', 'plan_id', 'status', 'source_sha', 'environment',
            'workload_source', 'data_classification', 'provider_mode', 'run_repetitions',
            'config_version', 'workload_version', 'metric_version', 'orchestrator_version',
            'plan_sha256', 'metrics', 'metric_definitions', 'alternatives', 'alternative_reviews',
            'limitation_rule', 'limitation_rule_version', 'baseline_config', 'config_sha256',
            'workload_path', 'workload_ids', 'workload_sha256', 'privacy', 'approval',
        ] as $field) {
            if (! array_key_exists($field, $plan)) {
                return 'PLAN_FIELD_MISSING';
            }
        }
        if ($plan['plan_version'] !== self::PLAN_VERSION
            || ! is_string($plan['plan_id'])
            || $plan['plan_id'] === ''
            || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $plan['plan_id']) !== 1
            || strlen($plan['plan_id']) > 80
            || ! in_array($plan['status'], ['proposed', 'approved'], true)
            || $plan['source_sha'] !== self::SOURCE_SHA
            || $plan['environment'] !== 'local-ci-disposable'
            || $plan['workload_source'] !== 'synthetic'
            || $plan['data_classification'] !== 'synthetic-no-user-content'
            || ! in_array($plan['provider_mode'], ['not_configured', 'approved_sandbox'], true)
            || ! is_int($plan['run_repetitions'])
            || $plan['run_repetitions'] < 3
            || $plan['run_repetitions'] > self::MAX_RUN_REPETITIONS
            || ($plan['config_version'] ?? null) !== self::CONFIG_VERSION
            || ($plan['workload_version'] ?? null) !== self::WORKLOAD_VERSION
            || ($plan['metric_version'] ?? null) !== self::METRIC_VERSION
            || ! is_string($plan['orchestrator_version'] ?? null)
            || $plan['orchestrator_version'] === ''
            || ! is_string($plan['plan_sha256'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/', $plan['plan_sha256']) !== 1
            || ! is_string($plan['limitation_rule'])
            || $plan['limitation_rule'] !== self::LIMITATION_RULE
            || ($plan['limitation_rule_version'] ?? null) !== 'E5-DEC-007-recommendation-v1') {
            return 'PLAN_SCHEMA_INVALID';
        }
        if (! $this->matchesPlanHash($plan)) {
            return 'PLAN_HASH_MISMATCH';
        }
        if (! is_array($plan['metrics']) || ! array_is_list($plan['metrics']) || $plan['metrics'] !== self::METRICS) {
            return 'METRICS_INVALID';
        }
        if (! is_array($plan['metric_definitions']) || ! array_is_list($plan['metric_definitions'])) {
            return 'METRIC_DEFINITIONS_INVALID';
        }
        foreach ($plan['metric_definitions'] as $definition) {
            if (! is_array($definition)
                || ! is_string($definition['name'] ?? null)
                || ! in_array($definition['name'], self::METRICS, true)
                || ! is_string($definition['unit'] ?? null)
                || $definition['unit'] === ''
                || ! is_string($definition['aggregation'] ?? null)
                || $definition['aggregation'] === ''
                || ! is_string($definition['sampling'] ?? null)
                || $definition['sampling'] === ''
                || ($definition['threshold_ref'] ?? null) !== 'E5-DEC-007-recommendation-v1') {
                return 'METRIC_DEFINITIONS_INVALID';
            }
        }
        if (array_values(array_unique(array_column($plan['metric_definitions'], 'name'))) !== self::METRICS) {
            return 'METRIC_DEFINITIONS_INVALID';
        }
        if (! is_array($plan['alternatives']) || ! array_is_list($plan['alternatives']) || $plan['alternatives'] !== self::ALTERNATIVES) {
            return 'ALTERNATIVES_INVALID';
        }
        if (! is_array($plan['alternative_reviews']) || ! array_is_list($plan['alternative_reviews'])) {
            return 'ALTERNATIVE_REVIEWS_INVALID';
        }
        foreach ($plan['alternative_reviews'] as $review) {
            if (! is_array($review)
                || ! is_string($review['id'] ?? null)
                || ! in_array($review['id'], self::ALTERNATIVES, true)
                || ! is_string($review['quality_risk'] ?? null)
                || $review['quality_risk'] === ''
                || ! is_string($review['operational_risk'] ?? null)
                || $review['operational_risk'] === ''
                || ! is_string($review['safety_boundary'] ?? null)
                || $review['safety_boundary'] === ''
                || ! is_bool($review['requires_runtime_change'] ?? null)) {
                return 'ALTERNATIVE_REVIEWS_INVALID';
            }
        }
        if (array_values(array_unique(array_column($plan['alternative_reviews'], 'id'))) !== self::ALTERNATIVES) {
            return 'ALTERNATIVE_REVIEWS_INVALID';
        }
        if (! is_array($plan['baseline_config'] ?? null)
            || ($plan['baseline_config']['version'] ?? null) !== self::CONFIG_VERSION
            || ($plan['baseline_config']['orchestrator'] ?? null) !== 'single'
            || ($plan['baseline_config']['write_permissions'] ?? null) !== []
            || ($plan['baseline_config']['human_approval_required'] ?? null) !== true
            || ($plan['baseline_config']['runtime_components_added'] ?? null) !== false
            || ! is_array($plan['baseline_config']['tool_allowlist'] ?? null)
            || ! is_string($plan['config_sha256'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/', $plan['config_sha256']) !== 1
            || ! $this->matchesConfigHash($plan)) {
            return 'BASELINE_CONFIG_INVALID';
        }
        if (! is_string($plan['workload_path'] ?? null)
            || ! $this->isSafeRelativePath($plan['workload_path'])
            || ! str_starts_with($plan['workload_path'], 'docs/contracts/operational-safety/fixtures/')
            || ! is_array($plan['workload_ids'] ?? null)
            || ! array_is_list($plan['workload_ids'])
            || count(array_filter($plan['workload_ids'], static fn (mixed $id): bool => is_string($id) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id) === 1)) !== count($plan['workload_ids'])
            || count(array_unique($plan['workload_ids'])) !== count($plan['workload_ids'])
            || $plan['workload_ids'] === []
            || ! is_string($plan['workload_sha256'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/', $plan['workload_sha256']) !== 1
            || ! $this->matchesWorkloadHash($plan)
            || ! $this->matchesWorkloadIds($plan)) {
            return 'WORKLOAD_INVALID';
        }
        if ($plan['provider_mode'] === 'approved_sandbox'
            && (! is_array($plan['sandbox'] ?? null)
                || ! is_string($plan['sandbox']['id'] ?? null)
                || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $plan['sandbox']['id']) !== 1
                || ($plan['sandbox']['credential_source'] ?? null) !== 'secret-manager'
                || ($plan['sandbox']['external_fake'] ?? null) !== true
                || ($plan['sandbox']['config_version'] ?? null) !== self::CONFIG_VERSION)) {
            return 'SANDBOX_BOUNDARY_INVALID';
        }
        if (! is_array($plan['privacy'])
            || ($plan['privacy']['synthetic_only'] ?? null) !== true
            || ! is_array($plan['privacy']['forbidden_sources'] ?? null)
            || ! array_is_list($plan['privacy']['forbidden_sources'])
            || count(array_filter($plan['privacy']['forbidden_sources'], static fn (mixed $source): bool => is_string($source))) !== count($plan['privacy']['forbidden_sources'])
            || count(array_unique($plan['privacy']['forbidden_sources'])) !== count($plan['privacy']['forbidden_sources'])
            || array_diff(self::REQUIRED_FORBIDDEN_SOURCES, $plan['privacy']['forbidden_sources']) !== []) {
            return 'PRIVACY_BOUNDARY_INVALID';
        }
        $approval = $plan['approval'];
        if (! is_array($approval)
            || ! is_string($approval['owner'] ?? null)
            || trim($approval['owner']) === ''
            || ! is_array($approval['required_decisions'] ?? null)
            || $approval['required_decisions'] !== self::REQUIRED_DECISIONS
            || ! is_array($approval['decisions'] ?? null)
            || ! array_is_list($approval['decisions'])
            || ! array_key_exists('approved_at', $approval)
            || ! is_array($approval['approvers'] ?? null)) {
            return 'APPROVAL_SCHEMA_INVALID';
        }
        $decisionIds = [];
        foreach ($approval['decisions'] as $decision) {
            if (! is_array($decision)
                || ! is_string($decision['id'] ?? null)
                || ! in_array($decision['id'], self::REQUIRED_DECISIONS, true)
                || ! is_string($decision['status'] ?? null)
                || ! in_array($decision['status'], ['pending', 'approved'], true)) {
                return 'APPROVAL_DECISION_INVALID';
            }
            if ($decision['status'] === 'approved'
                && (! is_string($decision['evidence_ref'] ?? null) || $decision['evidence_ref'] === '')) {
                return 'APPROVAL_EVIDENCE_INVALID';
            }
            $decisionIds[] = $decision['id'];
        }
        sort($decisionIds);
        $requiredDecisions = self::REQUIRED_DECISIONS;
        sort($requiredDecisions);
        if ($decisionIds !== $requiredDecisions) {
            return 'APPROVAL_DECISION_SET_INVALID';
        }
        if ($approval['approved_at'] !== null
            && (! is_string($approval['approved_at']) || $this->parseDate($approval['approved_at']) === null || $this->parseDate($approval['approved_at']) > new DateTimeImmutable('now'))) {
            return 'APPROVAL_EVIDENCE_INVALID';
        }
        foreach ($approval['approvers'] as $approver) {
            if (! is_string($approver) || $approver === '') {
                return 'APPROVAL_EVIDENCE_INVALID';
            }
        }

        if ($this->containsForbiddenContent($plan)) {
            return 'FORBIDDEN_CONTENT';
        }

        return null;
    }

    /** @return array{state:string,data:array<string,mixed>|null} */
    private function readJson(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ['state' => 'infrastructure', 'data' => null];
        }
        try {
            $contents = file_get_contents($path);
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ['state' => 'invalid', 'data' => null];
        }
        if (! is_array($decoded)) {
            return ['state' => 'invalid', 'data' => null];
        }

        return ['state' => 'valid', 'data' => $decoded];
    }

    /** @param array<string,mixed> $details @return array<string,mixed> */
    private function failure(string $verdict, string $code, array $details = []): array
    {
        return [
            'plan_version' => self::PLAN_VERSION,
            'verdict' => $verdict,
            'exit_code' => self::exitCode($verdict),
            'execution' => 'not_started',
            'diagnostics' => [['code' => $code, ...$details]],
        ];
    }

    private function isAllowedTestingPath(string $path): bool
    {
        $realPath = realpath($path);
        $temporaryRoot = realpath(sys_get_temp_dir());
        $fixtureRoot = realpath($this->projectRoot().'/docs/contracts/operational-safety/fixtures');

        return $realPath !== false && $temporaryRoot !== false
            && ($realPath === $temporaryRoot
                || str_starts_with($realPath, $temporaryRoot.DIRECTORY_SEPARATOR)
                || ($fixtureRoot !== false && str_starts_with($realPath, $fixtureRoot.DIRECTORY_SEPARATOR)));
    }

    private function isAllowedResolvedPath(string $path): bool
    {
        $temporaryRoot = realpath(sys_get_temp_dir());
        $fixtureRoot = realpath($this->projectRoot().'/docs/contracts/operational-safety/fixtures');

        return ($temporaryRoot !== false
                && ($path === $temporaryRoot || str_starts_with($path, $temporaryRoot.DIRECTORY_SEPARATOR)))
            || ($fixtureRoot !== false
                && ($path === $fixtureRoot || str_starts_with($path, $fixtureRoot.DIRECTORY_SEPARATOR)));
    }

    private function projectRoot(): string
    {
        return dirname(base_path(), 2);
    }

    /** @param array<string,mixed> $plan */
    private function matchesPlanHash(array $plan): bool
    {
        $declared = $plan['plan_sha256'];
        unset($plan['plan_sha256']);

        return hash_equals($declared, hash('sha256', CanonicalJson::encode($plan)));
    }

    /** @param array<string,mixed> $plan */
    private function matchesConfigHash(array $plan): bool
    {
        return hash_equals($plan['config_sha256'], hash('sha256', CanonicalJson::encode($plan['baseline_config'])));
    }

    /** @param array<string,mixed> $plan */
    private function matchesWorkloadHash(array $plan): bool
    {
        $path = $this->projectRoot().'/'.$plan['workload_path'];
        if (! is_file($path) || ! is_readable($path)) {
            return false;
        }

        $actualHash = hash_file('sha256', $path);

        return is_string($actualHash) && hash_equals($plan['workload_sha256'], $actualHash);
    }

    /** @param array<string,mixed> $plan */
    private function matchesWorkloadIds(array $plan): bool
    {
        $loaded = $this->readJson($this->projectRoot().'/'.$plan['workload_path']);
        if ($loaded['state'] !== 'valid' || ! is_array($loaded['data']['workloads'] ?? null)) {
            return false;
        }
        $actualIds = [];
        foreach ($loaded['data']['workloads'] as $workload) {
            if (! is_array($workload) || ! is_string($workload['id'] ?? null)) {
                return false;
            }
            $actualIds[] = $workload['id'];
        }
        $expectedIds = $plan['workload_ids'];
        sort($actualIds);
        sort($expectedIds);

        return $actualIds === $expectedIds;
    }

    private function isSafeRelativePath(string $path): bool
    {
        return $path !== ''
            && ! str_starts_with($path, '/')
            && ! str_contains($path, '\\')
            && ! str_contains($path, '../')
            && ! str_contains($path, '..\\');
    }

    private function containsForbiddenContent(mixed $value, ?string $key = null): bool
    {
        if ($key !== null) {
            foreach (self::FORBIDDEN_KEYS as $forbiddenKey) {
                if (str_contains(strtolower($key), $forbiddenKey)) {
                    return true;
                }
            }
        }
        if (is_array($value)) {
            foreach ($value as $childKey => $childValue) {
                if ($this->containsForbiddenContent($childValue, is_string($childKey) ? $childKey : null)) {
                    return true;
                }
            }

            return false;
        }
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/-----BEGIN|\bBearer\s+|\bsk-[A-Za-z0-9_-]+/', $value) === 1;
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
