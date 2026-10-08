<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\CanonicalJson;
use Throwable;

final class MatchQualityEvaluator
{
    public const ARTIFACT_VERSION = '1.0.0';

    public const MANIFEST_VERSION = '1.0.0';

    public const METRIC_VERSION = '1.0.0';

    public const TOOL_VERSION = '1.0.0';

    public const ENGINE_VERSION = MatchEvaluator::ENGINE_VERSION;

    public const APPROVED_SOURCE_SHA = 'e27910f1e3f760ec87e6773c31310b955b195780';

    public const VERDICT_PASS = 'PASS';

    public const VERDICT_QUALITY_REGRESSION = 'QUALITY_REGRESSION';

    public const VERDICT_REPEATABILITY_FAILURE = 'REPEATABILITY_FAILURE';

    public const VERDICT_INVALID_INPUT = 'INVALID_INPUT';

    public const VERDICT_INFRASTRUCTURE_FAILURE = 'INFRASTRUCTURE_FAILURE';

    public const EXIT_PASS = 0;

    public const EXIT_QUALITY_REGRESSION = 10;

    public const EXIT_REPEATABILITY_FAILURE = 11;

    public const EXIT_INVALID_INPUT = 12;

    public const EXIT_INFRASTRUCTURE_FAILURE = 13;

    private const MANIFEST_RELATIVE_PATH = 'docs/contracts/jd/fixtures/match-quality-evaluation-v1.json';

    private const CORPUS_RELATIVE_PATH = 'docs/contracts/jd/fixtures/match-report-v1.json';

    /** @var array<int,string> */
    private const APPROVED_METRICS = [
        'classification_exact',
        'ordering_exact',
        'score_within_delta',
        'repeatability_exact',
        'unsupported_claim_counterexamples',
    ];

    public function __construct(private readonly MatchEvaluator $matcher) {}

    /**
     * Evaluate the pinned synthetic corpus without opening a persistence path.
     *
     * The optional matcher callback exists for deterministic fault-injection
     * tests. Production callers use the extracted MatchEvaluator boundary.
     *
     * @return array<string,mixed>
     */
    public function evaluate(
        ?string $manifestPath = null,
        ?string $corpusPath = null,
        ?callable $matcher = null,
        ?float $maxDurationSeconds = null,
    ): array {
        if (($manifestPath !== null || $corpusPath !== null) && ! app()->environment('testing')) {
            return $this->failure(self::VERDICT_INVALID_INPUT, 'PROHIBITED_SOURCE');
        }
        foreach ([$manifestPath, $corpusPath] as $customPath) {
            if ($customPath !== null && ! $this->isAllowedTestingPath($customPath)) {
                return $this->failure(self::VERDICT_INVALID_INPUT, 'PROHIBITED_SOURCE');
            }
        }
        $manifestPath ??= $this->projectRoot().'/'.self::MANIFEST_RELATIVE_PATH;
        $manifest = $this->readJson($manifestPath);
        if ($manifest['state'] === 'infrastructure') {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'MANIFEST_UNAVAILABLE');
        }
        if ($manifest['state'] === 'invalid') {
            return $this->failure(self::VERDICT_INVALID_INPUT, 'MANIFEST_INVALID');
        }
        $manifestData = $manifest['data'];
        $compatibility = $this->validateManifest($manifestData);
        if ($compatibility !== null) {
            return $this->failure(self::VERDICT_INVALID_INPUT, $compatibility);
        }

        $declaredCorpus = (string) $manifestData['fixture_path'];
        if ($corpusPath !== null && $this->isDefaultManifest($manifestPath)
            && $this->normalizePath($corpusPath) !== $this->normalizePath($this->projectRoot().'/'.$declaredCorpus)) {
            return $this->failure(self::VERDICT_INVALID_INPUT, 'PROHIBITED_SOURCE');
        }
        $corpusPath ??= $this->projectRoot().'/'.$declaredCorpus;
        $corpus = $this->readJson($corpusPath);
        if ($corpus['state'] === 'infrastructure') {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'CORPUS_UNAVAILABLE');
        }
        if ($corpus['state'] === 'invalid') {
            return $this->failure(self::VERDICT_INVALID_INPUT, 'CORPUS_INVALID');
        }
        $corpusData = $corpus['data'];
        $corpusCompatibility = $this->validateCorpusMetadata($corpusData, $manifestData);
        if ($corpusCompatibility !== null) {
            return $this->failure(self::VERDICT_INVALID_INPUT, $corpusCompatibility);
        }
        $corpusHash = $corpus['sha256'] ?? null;
        if (! is_string($corpusHash)) {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'CORPUS_HASH_UNAVAILABLE');
        }
        if (! hash_equals((string) $manifestData['corpus_sha256'], $corpusHash)) {
            return $this->failure(self::VERDICT_INVALID_INPUT, 'CORPUS_HASH_MISMATCH');
        }
        $cases = $this->selectCases($manifestData, $corpusData);
        if ($cases['error'] !== null) {
            return $this->failure(self::VERDICT_INVALID_INPUT, $cases['error']);
        }

        $thresholds = $manifestData['thresholds'];
        $runCount = (int) $thresholds['repeatability_runs'];
        $scoreDelta = (float) $thresholds['score_delta'];
        $outputs = [];
        $diagnostics = [];
        $startedAt = hrtime(true);
        $metricTotals = [
            'classification_exact_cases' => 0,
            'ordering_exact_cases' => 0,
            'score_within_delta_cases' => 0,
            'unsupported_claim_counterexamples' => 0,
        ];

        try {
            for ($run = 1; $run <= $runCount; $run++) {
                $runCases = $run === 1 ? $cases['cases'] : array_reverse($cases['cases']);
                $runOutputs = [];
                foreach ($runCases as $case) {
                    $input = $this->caseInput($case);
                    $result = $matcher === null
                        ? $this->matcher->evaluate($input['analysis'], $input['snapshot'])
                        : $matcher($input['analysis'], $input['snapshot']);
                    $canonical = $this->canonicalResult($result);
                    $runOutputs[$case['id']] = [
                        'hash' => hash('sha256', CanonicalJson::encode($canonical)),
                        'result' => $canonical,
                    ];
                    if ($run === 1) {
                        $comparison = $this->compareCase($case, $canonical, $scoreDelta);
                        $metricTotals['classification_exact_cases'] += $comparison['classification_exact'] ? 1 : 0;
                        $metricTotals['ordering_exact_cases'] += $comparison['ordering_exact'] ? 1 : 0;
                        $metricTotals['score_within_delta_cases'] += $comparison['score_within_delta'] ? 1 : 0;
                        $metricTotals['unsupported_claim_counterexamples'] += $comparison['unsupported_claim'] ? 1 : 0;
                        $diagnostics = array_merge($diagnostics, $comparison['diagnostics']);
                    }
                }
                ksort($runOutputs, SORT_STRING);
                $outputs[] = [
                    'run' => $run,
                    'hash' => hash('sha256', CanonicalJson::encode($runOutputs)),
                    'cases' => $runOutputs,
                ];
            }
        } catch (Throwable) {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'MATCHER_FAILED');
        }

        $durationSeconds = max(0.0, (hrtime(true) - $startedAt) / 1_000_000_000);
        $durationLimit = $maxDurationSeconds ?? (float) $thresholds['max_duration_seconds'];
        if ($durationSeconds > $durationLimit) {
            $diagnostics[] = ['code' => 'DURATION_EXCEEDED'];
        }

        $repeatability = true;
        $repeatabilityDiagnostics = [];
        $firstRun = $outputs[0]['cases'] ?? [];
        foreach ($outputs as $output) {
            if ($output['hash'] !== $outputs[0]['hash']) {
                $repeatability = false;
                $repeatabilityDiagnostics[] = [
                    'code' => 'RUN_HASH_MISMATCH',
                    'run' => $output['run'],
                ];
            }
            foreach ($output['cases'] as $caseId => $caseOutput) {
                if (isset($firstRun[$caseId]) && $caseOutput['hash'] !== $firstRun[$caseId]['hash']) {
                    $repeatability = false;
                    $repeatabilityDiagnostics[] = [
                        'code' => 'CASE_HASH_MISMATCH',
                        'case_id' => $caseId,
                        'run' => $output['run'],
                    ];
                }
            }
        }
        $diagnostics = $this->uniqueDiagnostics(array_merge($diagnostics, $repeatabilityDiagnostics));
        $qualityRegression = $diagnostics !== [] && ! $this->onlyRepeatabilityDiagnostics($diagnostics);
        $unsupportedLimit = (int) $thresholds['max_unsupported_claim_counterexamples'];
        if ($metricTotals['unsupported_claim_counterexamples'] > $unsupportedLimit) {
            $qualityRegression = true;
        }
        $verdict = ! $repeatability
            ? self::VERDICT_REPEATABILITY_FAILURE
            : ($qualityRegression ? self::VERDICT_QUALITY_REGRESSION : self::VERDICT_PASS);
        $caseCount = count($cases['cases']);

        return [
            'artifact_version' => self::ARTIFACT_VERSION,
            'verdict' => $verdict,
            'exit_code' => self::exitCode($verdict),
            'manifest_version' => $manifestData['manifest_version'],
            'tool_version' => $manifestData['tool_version'],
            'metric_version' => $manifestData['metric_version'],
            'engine_version' => $manifestData['engine_version'],
            'source_sha' => $manifestData['source_sha'],
            'corpus_sha256' => $corpusHash,
            'metrics' => [
                'case_count' => $caseCount,
                'reviewed_case_count' => count($manifestData['reviewed_case_ids']),
                'held_out_case_count' => count($manifestData['held_out_case_ids']),
                'classification_exact_cases' => $metricTotals['classification_exact_cases'],
                'ordering_exact_cases' => $metricTotals['ordering_exact_cases'],
                'score_within_delta_cases' => $metricTotals['score_within_delta_cases'],
                'classification_exact' => $metricTotals['classification_exact_cases'] === $caseCount,
                'ordering_exact' => $metricTotals['ordering_exact_cases'] === $caseCount,
                'score_within_delta' => $metricTotals['score_within_delta_cases'] === $caseCount,
                'unsupported_claim_counterexamples' => $metricTotals['unsupported_claim_counterexamples'],
                'repeatability_exact' => $repeatability,
                'repeatability_runs' => $runCount,
                'run_hashes' => array_column($outputs, 'hash'),
                'duration_ms' => round($durationSeconds * 1000, 3),
                'max_duration_seconds' => $durationLimit,
                'case_hashes' => array_map(
                    static fn (array $caseOutput): string => $caseOutput['hash'],
                    $outputs[0]['cases'] ?? [],
                ),
            ],
            'diagnostics' => $diagnostics,
        ];
    }

    public static function exitCode(string $verdict): int
    {
        return match ($verdict) {
            self::VERDICT_PASS => self::EXIT_PASS,
            self::VERDICT_QUALITY_REGRESSION => self::EXIT_QUALITY_REGRESSION,
            self::VERDICT_REPEATABILITY_FAILURE => self::EXIT_REPEATABILITY_FAILURE,
            self::VERDICT_INVALID_INPUT => self::EXIT_INVALID_INPUT,
            default => self::EXIT_INFRASTRUCTURE_FAILURE,
        };
    }

    /** @return array<string,mixed> */
    private function validateManifest(array $manifest): ?string
    {
        $required = [
            'manifest_version', 'fixture_path', 'corpus_sha256', 'source_sha',
            'matching_rule_version', 'analysis_rule_version', 'report_schema_version',
            'metric_version', 'tool_version', 'engine_version', 'reviewed_case_ids', 'held_out_case_ids', 'thresholds', 'metrics',
        ];
        foreach ($required as $field) {
            if (! array_key_exists($field, $manifest)) {
                return 'MANIFEST_FIELD_MISSING';
            }
        }
        if ($manifest['manifest_version'] !== self::MANIFEST_VERSION
            || $manifest['metric_version'] !== self::METRIC_VERSION
            || $manifest['tool_version'] !== self::TOOL_VERSION
            || $manifest['engine_version'] !== self::ENGINE_VERSION
            || $manifest['matching_rule_version'] !== MatchService::RULE_VERSION
            || $manifest['analysis_rule_version'] !== JobDescriptionAnalyzer::RULE_VERSION
            || $manifest['report_schema_version'] !== MatchService::REPORT_SCHEMA_VERSION
            || $manifest['source_sha'] !== self::APPROVED_SOURCE_SHA
            || $manifest['fixture_path'] !== self::CORPUS_RELATIVE_PATH
        ) {
            return 'MANIFEST_VERSION_MISMATCH';
        }
        if (! is_array($manifest['reviewed_case_ids']) || ! array_is_list($manifest['reviewed_case_ids']) || count($manifest['reviewed_case_ids']) !== 40
            || ! is_array($manifest['held_out_case_ids']) || ! array_is_list($manifest['held_out_case_ids']) || count($manifest['held_out_case_ids']) !== 12
            || count(array_filter($manifest['reviewed_case_ids'], static fn (mixed $caseId): bool => is_string($caseId))) !== 40
            || count(array_filter($manifest['held_out_case_ids'], static fn (mixed $caseId): bool => is_string($caseId))) !== 12
            || count(array_unique($manifest['reviewed_case_ids'])) !== 40
            || count(array_unique($manifest['held_out_case_ids'])) !== 12
            || array_intersect($manifest['reviewed_case_ids'], $manifest['held_out_case_ids']) !== []) {
            return 'MANIFEST_CASE_INVENTORY_INVALID';
        }
        foreach ([...$manifest['reviewed_case_ids'], ...$manifest['held_out_case_ids']] as $caseId) {
            if (! is_string($caseId) || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $caseId) !== 1) {
                return 'MANIFEST_CASE_INVENTORY_INVALID';
            }
        }
        $thresholds = $manifest['thresholds'] ?? null;
        if (! is_array($thresholds)
            || ($thresholds['score_delta'] ?? null) !== 0.01
            || ($thresholds['repeatability_runs'] ?? null) !== 2
            || ($thresholds['max_unsupported_claim_counterexamples'] ?? null) !== 0
            || ($thresholds['max_duration_seconds'] ?? null) !== 60
        ) {
            return 'MANIFEST_THRESHOLDS_INVALID';
        }
        if (! is_string($manifest['corpus_sha256']) || preg_match('/^[a-f0-9]{64}$/', $manifest['corpus_sha256']) !== 1
            || ! is_string($manifest['source_sha']) || preg_match('/^[a-f0-9]{40}$/', $manifest['source_sha']) !== 1) {
            return 'MANIFEST_HASH_INVALID';
        }
        if (! is_array($manifest['metrics']) || ! array_is_list($manifest['metrics']) || $manifest['metrics'] !== self::APPROVED_METRICS) {
            return 'MANIFEST_METRICS_INVALID';
        }

        return null;
    }

    /** @param array<string,mixed> $corpus @param array<string,mixed> $manifest */
    private function validateCorpusMetadata(array $corpus, array $manifest): ?string
    {
        foreach (['report_schema_version', 'matching_rule_version', 'analysis_rule_version'] as $field) {
            if (! array_key_exists($field, $corpus) || $corpus[$field] !== $manifest[$field]) {
                return 'CORPUS_VERSION_MISMATCH';
            }
        }

        return null;
    }

    /** @return array{cases:array<int,array<string,mixed>>,error:string|null} */
    private function selectCases(array $manifest, array $corpus): array
    {
        if (! is_array($corpus['examples'] ?? null) || ! is_array($corpus['held_out_counterexamples'] ?? null)) {
            return ['cases' => [], 'error' => 'CORPUS_CASES_INVALID'];
        }
        $available = [];
        foreach ([...$corpus['examples'], ...$corpus['held_out_counterexamples']] as $case) {
            if (! is_array($case) || ! is_string($case['id'] ?? null) || isset($available[$case['id']])) {
                return ['cases' => [], 'error' => 'CORPUS_CASE_INVALID'];
            }
            $available[$case['id']] = $case;
        }
        $cases = [];
        foreach ([...$manifest['reviewed_case_ids'], ...$manifest['held_out_case_ids']] as $caseId) {
            if (! isset($available[$caseId])) {
                return ['cases' => [], 'error' => 'MANIFEST_CASE_NOT_FOUND'];
            }
            $case = $available[$caseId];
            if ($this->validateCase($case) !== null) {
                return ['cases' => [], 'error' => 'CORPUS_CASE_SCHEMA_INVALID'];
            }
            $case['held_out'] = in_array($caseId, $manifest['held_out_case_ids'], true);
            $cases[] = $case;
        }

        return ['cases' => $cases, 'error' => null];
    }

    /** @param array<string,mixed> $case */
    private function validateCase(array $case): ?string
    {
        if (! is_array($case['analysis'] ?? null) || ! is_array($case['snapshot'] ?? null) || ! is_array($case['expected'] ?? null)) {
            return 'CASE_FIELDS_INVALID';
        }
        foreach (['matched', 'missing', 'weak'] as $field) {
            if (! is_array($case['expected'][$field] ?? null)) {
                return 'CASE_EXPECTED_INVALID';
            }
            if (count(array_filter($case['expected'][$field], static fn (mixed $signalId): bool => is_string($signalId))) !== count($case['expected'][$field])) {
                return 'CASE_EXPECTED_INVALID';
            }
            if (count(array_unique($case['expected'][$field])) !== count($case['expected'][$field])) {
                return 'CASE_EXPECTED_INVALID';
            }
            foreach ($case['expected'][$field] as $signalId) {
                if (! is_string($signalId) || $this->vocabulary($signalId) === null) {
                    return 'CASE_EXPECTED_INVALID';
                }
            }
        }
        $expectedSignals = [
            ...$case['expected']['matched'],
            ...$case['expected']['missing'],
            ...$case['expected']['weak'],
        ];
        if (count(array_unique($expectedSignals)) !== count($expectedSignals)) {
            return 'CASE_EXPECTED_PARTITION_INVALID';
        }
        if (! is_int($case['expected']['score'] ?? null) && ! is_float($case['expected']['score'] ?? null)) {
            return 'CASE_SCORE_INVALID';
        }
        foreach (['role', 'seniority'] as $field) {
            if (array_key_exists($field, $case['analysis']) && ! is_string($case['analysis'][$field])) {
                return 'CASE_ANALYSIS_INVALID';
            }
        }
        foreach (['required', 'preferred'] as $field) {
            if (array_key_exists($field, $case['analysis']) && ! is_array($case['analysis'][$field])) {
                return 'CASE_ANALYSIS_INVALID';
            }
            foreach ($case['analysis'][$field] ?? [] as $signalId) {
                if (! is_string($signalId) || $this->vocabulary($signalId) === null) {
                    return 'CASE_SIGNAL_INVALID';
                }
            }
        }
        $requestedSignals = [
            ...($case['analysis']['required'] ?? []),
            ...($case['analysis']['preferred'] ?? []),
        ];
        if (array_diff($expectedSignals, $requestedSignals) !== []) {
            return 'CASE_EXPECTED_SIGNAL_NOT_REQUESTED';
        }
        if (array_key_exists('domain_context', $case['analysis']) && ! is_array($case['analysis']['domain_context'])) {
            return 'CASE_ANALYSIS_INVALID';
        }
        foreach ($case['analysis']['domain_context'] ?? [] as $domain) {
            if (! is_string($domain) || $domain === '') {
                return 'CASE_ANALYSIS_INVALID';
            }
        }

        return null;
    }

    /** @param array<string,mixed> $case @return array{analysis:array<string,mixed>,snapshot:array<string,mixed>} */
    private function caseInput(array $case): array
    {
        $signals = $case['analysis'];
        $required = $this->skillList($signals['required'] ?? []);
        $preferred = $this->skillList($signals['preferred'] ?? []);

        return [
            'analysis' => [
                'extracted_role' => array_key_exists('role', $signals)
                    ? ['state' => 'detected', 'value' => $signals['role']]
                    : ['state' => 'absent', 'value' => null],
                'required_skills' => $this->stateful($required),
                'nice_to_have_skills' => $this->stateful($preferred),
                'seniority' => $signals['seniority'] ?? null,
                'seniority_state' => array_key_exists('seniority', $signals) ? 'detected' : 'absent',
                'domain_context' => array_key_exists('domain_context', $signals)
                    ? ['state' => 'detected', 'items' => $signals['domain_context']]
                    : ['state' => 'absent', 'items' => []],
            ],
            'snapshot' => $case['snapshot'],
        ];
    }

    /** @param array<int,string> $ids @return array<int,array{signal_id:string,label:string}> */
    private function skillList(array $ids): array
    {
        $items = [];
        foreach ($ids as $id) {
            $vocabulary = $this->vocabulary($id);
            if ($vocabulary === null) {
                throw new \InvalidArgumentException('Unknown signal');
            }
            $items[] = ['signal_id' => $id, 'label' => $vocabulary['label']];
        }
        usort($items, static fn (array $left, array $right): int => $left['signal_id'] <=> $right['signal_id']);

        return $items;
    }

    /** @param array<int,mixed> $items @return array{state:string,items:array<int,mixed>} */
    private function stateful(array $items): array
    {
        return ['state' => $items === [] ? 'absent' : 'detected', 'items' => array_values($items)];
    }

    /** @param array<string,mixed> $result @return array<string,mixed> */
    private function canonicalResult(array $result): array
    {
        foreach (['matched_skills', 'missing_skills', 'weak_evidence', 'recommendations'] as $field) {
            if (! is_array($result[$field] ?? null)) {
                throw new \InvalidArgumentException('Matcher result invalid');
            }
        }
        foreach (['matched_skills', 'missing_skills', 'weak_evidence'] as $field) {
            foreach ($result[$field] as $classification) {
                if (! is_array($classification)
                    || ! is_string($classification['signal_id'] ?? null)
                    || ! is_string($classification['label'] ?? null)
                    || ! is_string($classification['importance'] ?? null)
                    || ! is_string($classification['evidence_level'] ?? null)
                    || ! is_array($classification['source_references'] ?? null)
                    || array_filter($classification['source_references'], static fn (mixed $reference): bool => ! is_string($reference)) !== []) {
                    throw new \InvalidArgumentException('Matcher classification invalid');
                }
            }
        }
        if (! is_int($result['overall_score'] ?? null) && ! is_float($result['overall_score'] ?? null)) {
            throw new \InvalidArgumentException('Matcher score invalid');
        }

        return [
            'overall_score' => (float) $result['overall_score'],
            'matched_skills' => array_values($result['matched_skills']),
            'missing_skills' => array_values($result['missing_skills']),
            'weak_evidence' => array_values($result['weak_evidence']),
            'recommendations' => array_values($result['recommendations']),
        ];
    }

    /** @param array<string,mixed> $case @param array<string,mixed> $actual @return array<string,mixed> */
    private function compareCase(array $case, array $actual, float $scoreDelta): array
    {
        $expected = $case['expected'];
        $fields = ['matched' => 'matched_skills', 'missing' => 'missing_skills', 'weak' => 'weak_evidence'];
        $classificationExact = true;
        $orderingExact = true;
        $diagnostics = [];
        foreach ($fields as $expectedField => $actualField) {
            $expectedIds = $expected[$expectedField];
            $actualIds = array_column($actual[$actualField], 'signal_id');
            if ($expectedIds !== $actualIds) {
                $orderingExact = false;
                if ($this->sameMembers($expectedIds, $actualIds)) {
                    $diagnostics[] = ['code' => 'ORDER_MISMATCH', 'case_id' => $case['id'], 'field' => $expectedField];
                } else {
                    $classificationExact = false;
                    $diagnostics[] = ['code' => 'CLASSIFICATION_MISMATCH', 'case_id' => $case['id'], 'field' => $expectedField];
                }
            }
        }
        $scoreWithinDelta = abs((float) $expected['score'] - (float) $actual['overall_score']) <= $scoreDelta;
        if (! $scoreWithinDelta) {
            $diagnostics[] = ['code' => 'SCORE_OUT_OF_TOLERANCE', 'case_id' => $case['id']];
        }
        $expectedMatched = $expected['matched'];
        $actualMatched = array_column($actual['matched_skills'], 'signal_id');
        $unsupportedClaim = $case['held_out'] && count(array_diff($actualMatched, $expectedMatched)) > 0;
        if ($unsupportedClaim) {
            $diagnostics[] = ['code' => 'UNSUPPORTED_CLAIM_COUNTEREXAMPLE', 'case_id' => $case['id']];
        }

        return [
            'classification_exact' => $classificationExact,
            'ordering_exact' => $orderingExact,
            'score_within_delta' => $scoreWithinDelta,
            'unsupported_claim' => $unsupportedClaim,
            'diagnostics' => $diagnostics,
        ];
    }

    /** @param array<int,string> $left @param array<int,string> $right */
    private function sameMembers(array $left, array $right): bool
    {
        sort($left);
        sort($right);

        return $left === $right;
    }

    /** @param array<int,array<string,mixed>> $diagnostics @return array<int,array<string,mixed>> */
    private function uniqueDiagnostics(array $diagnostics): array
    {
        $unique = [];
        foreach ($diagnostics as $diagnostic) {
            $unique[CanonicalJson::encode($diagnostic)] = $diagnostic;
        }

        return array_values($unique);
    }

    /** @param array<int,array<string,mixed>> $diagnostics */
    private function onlyRepeatabilityDiagnostics(array $diagnostics): bool
    {
        foreach ($diagnostics as $diagnostic) {
            if (! in_array($diagnostic['code'] ?? null, ['RUN_HASH_MISMATCH', 'CASE_HASH_MISMATCH'], true)) {
                return false;
            }
        }

        return $diagnostics !== [];
    }

    /** @return array{state:string,data:array<string,mixed>|null,sha256:string|null} */
    private function readJson(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ['state' => 'infrastructure', 'data' => null, 'sha256' => null];
        }
        try {
            $contents = @file_get_contents($path);
        } catch (Throwable) {
            $contents = false;
        }
        if ($contents === false) {
            return ['state' => 'infrastructure', 'data' => null, 'sha256' => null];
        }
        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ['state' => 'invalid', 'data' => null, 'sha256' => hash('sha256', $contents)];
        }
        if (! is_array($decoded)) {
            return ['state' => 'invalid', 'data' => null, 'sha256' => hash('sha256', $contents)];
        }

        return ['state' => 'valid', 'data' => $decoded, 'sha256' => hash('sha256', $contents)];
    }

    /** @return array<string,mixed> */
    private function failure(string $verdict, string $code): array
    {
        return [
            'artifact_version' => self::ARTIFACT_VERSION,
            'verdict' => $verdict,
            'exit_code' => self::exitCode($verdict),
            'diagnostics' => [['code' => $code]],
        ];
    }

    private function vocabulary(string $signalId): ?array
    {
        return (new JobDescriptionAnalyzer)->vocabulary($signalId);
    }

    private function projectRoot(): string
    {
        return dirname(base_path(), 2);
    }

    private function isDefaultManifest(string $path): bool
    {
        return $this->normalizePath($path) === $this->normalizePath($this->projectRoot().'/'.self::MANIFEST_RELATIVE_PATH);
    }

    private function normalizePath(string $path): string
    {
        return realpath($path) ?: $path;
    }

    private function isAllowedTestingPath(string $path): bool
    {
        $normalized = $this->normalizePath($path);
        $temporaryRoot = rtrim($this->normalizePath(sys_get_temp_dir()), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $fixtureRoot = rtrim($this->normalizePath($this->projectRoot().'/docs/contracts/jd/fixtures'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($normalized, $temporaryRoot) || str_starts_with($normalized, $fixtureRoot);
    }
}
