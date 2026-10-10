<?php

declare(strict_types=1);

namespace Tests\Feature\JobFit;

use App\Application\JobFit\JobDescriptionAnalyzer;
use App\Application\JobFit\MatchEvaluator;
use App\Application\JobFit\MatchQualityEvaluator;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\MatchReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MatchQualityEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pinned_corpus_passes_without_product_writes(): void
    {
        $manifestPath = dirname(__DIR__, 5).'/docs/contracts/jd/fixtures/match-quality-evaluation-v1.json';
        $corpusPath = dirname(__DIR__, 5).'/docs/contracts/jd/fixtures/match-report-v1.json';
        $manifestHashBefore = hash_file('sha256', $manifestPath);
        $corpusHashBefore = hash_file('sha256', $corpusPath);
        $usersBefore = User::count();
        $reportsBefore = MatchReport::count();

        $result = app(MatchQualityEvaluator::class)->evaluate();

        self::assertSame(MatchQualityEvaluator::VERDICT_PASS, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::ARTIFACT_VERSION, $result['artifact_version']);
        self::assertSame(0, $result['exit_code']);
        self::assertSame(52, $result['metrics']['case_count']);
        self::assertTrue($result['metrics']['repeatability_exact']);
        self::assertGreaterThanOrEqual(0, $result['metrics']['duration_ms']);
        self::assertSame($usersBefore, User::count());
        self::assertSame($reportsBefore, MatchReport::count());
        self::assertSame($manifestHashBefore, hash_file('sha256', $manifestPath));
        self::assertSame($corpusHashBefore, hash_file('sha256', $corpusPath));
    }

    public function test_changed_manifest_version_is_rejected_before_matching(): void
    {
        $manifest = $this->readManifest();
        $manifest['matching_rule_version'] = '9.9.9';
        $path = $this->writeTemporaryJson($manifest);

        try {
            $result = app(MatchQualityEvaluator::class)->evaluate($path);
        } finally {
            unlink($path);
        }

        self::assertSame(MatchQualityEvaluator::VERDICT_INVALID_INPUT, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::ARTIFACT_VERSION, $result['artifact_version']);
        self::assertSame(MatchQualityEvaluator::EXIT_INVALID_INPUT, $result['exit_code']);
        self::assertSame([['code' => 'MANIFEST_VERSION_MISMATCH']], $result['diagnostics']);
    }

    public function test_malformed_corpus_is_rejected_without_baseline_rewrite(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'match-quality-');
        self::assertIsString($path);
        file_put_contents($path, '{"examples":');
        $manifestPath = $this->writeTemporaryJson($this->readManifest());

        try {
            $result = app(MatchQualityEvaluator::class)->evaluate($manifestPath, $path);
        } finally {
            unlink($manifestPath);
            unlink($path);
        }

        self::assertSame(MatchQualityEvaluator::VERDICT_INVALID_INPUT, $result['verdict']);
        self::assertSame([['code' => 'CORPUS_INVALID']], $result['diagnostics']);
    }

    public function test_prohibited_corpus_source_is_rejected_before_reading_it(): void
    {
        $result = app(MatchQualityEvaluator::class)->evaluate(null, dirname(__DIR__, 5).'/.env');

        self::assertSame(MatchQualityEvaluator::VERDICT_INVALID_INPUT, $result['verdict']);
        self::assertSame([['code' => 'PROHIBITED_SOURCE']], $result['diagnostics']);
    }

    public function test_seeded_score_regression_returns_quality_exit_class_and_safe_diagnostic(): void
    {
        $matcher = new MatchEvaluator(new JobDescriptionAnalyzer);
        $result = app(MatchQualityEvaluator::class)->evaluate(
            matcher: function (array $analysis, array $snapshot) use ($matcher): array {
                $output = $matcher->evaluate($analysis, $snapshot);
                $output['overall_score'] = max(0, (float) $output['overall_score'] - 1.0);

                return $output;
            },
        );

        self::assertSame(MatchQualityEvaluator::VERDICT_QUALITY_REGRESSION, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::EXIT_QUALITY_REGRESSION, $result['exit_code']);
        self::assertContains(['code' => 'SCORE_OUT_OF_TOLERANCE', 'case_id' => 'match-reviewed-001'], $result['diagnostics']);
        self::assertStringNotContainsString('JavaScript', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_unsupported_claim_counterexample_is_a_quality_regression(): void
    {
        $matcher = new MatchEvaluator(new JobDescriptionAnalyzer);
        $result = app(MatchQualityEvaluator::class)->evaluate(
            matcher: function (array $analysis, array $snapshot) use ($matcher): array {
                $output = $matcher->evaluate($analysis, $snapshot);
                $output['matched_skills'][] = [
                    'signal_id' => 'javascript',
                    'label' => 'JavaScript',
                    'importance' => 'required',
                    'evidence_level' => 'strong',
                    'source_references' => [],
                ];

                return $output;
            },
        );

        self::assertSame(MatchQualityEvaluator::VERDICT_QUALITY_REGRESSION, $result['verdict']);
        self::assertGreaterThan(0, $result['metrics']['unsupported_claim_counterexamples']);
        self::assertContains('UNSUPPORTED_CLAIM_COUNTEREXAMPLE', array_column($result['diagnostics'], 'code'));
    }

    public function test_non_repeatable_matcher_returns_repeatability_exit_class(): void
    {
        $matcher = new MatchEvaluator(new JobDescriptionAnalyzer);
        $invocation = 0;
        $result = app(MatchQualityEvaluator::class)->evaluate(
            matcher: function (array $analysis, array $snapshot) use ($matcher, &$invocation): array {
                $output = $matcher->evaluate($analysis, $snapshot);
                $invocation++;
                if ($invocation === 1) {
                    $output['overall_score'] = max(0, (float) $output['overall_score'] - 1.0);
                }

                return $output;
            },
        );

        self::assertSame(MatchQualityEvaluator::VERDICT_REPEATABILITY_FAILURE, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::EXIT_REPEATABILITY_FAILURE, $result['exit_code']);
        self::assertTrue($result['metrics']['repeatability_exact'] === false);
        self::assertContains('RUN_HASH_MISMATCH', array_column($result['diagnostics'], 'code'));
    }

    public function test_classification_regression_returns_quality_exit_class(): void
    {
        $matcher = new MatchEvaluator(new JobDescriptionAnalyzer);
        $result = app(MatchQualityEvaluator::class)->evaluate(
            matcher: function (array $analysis, array $snapshot) use ($matcher): array {
                $output = $matcher->evaluate($analysis, $snapshot);
                if (($analysis['required_skills']['items'][0]['signal_id'] ?? null) === 'javascript'
                    && $output['matched_skills'] !== []) {
                    $output['missing_skills'][] = $output['matched_skills'][0];
                    $output['matched_skills'] = [];
                }

                return $output;
            },
        );

        self::assertSame(MatchQualityEvaluator::VERDICT_QUALITY_REGRESSION, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::EXIT_QUALITY_REGRESSION, $result['exit_code']);
        self::assertContains('CLASSIFICATION_MISMATCH', array_column($result['diagnostics'], 'code'));
    }

    public function test_order_regression_returns_quality_exit_class(): void
    {
        $corpus = $this->readCorpus();
        foreach ($corpus['examples'] as &$case) {
            if (($case['id'] ?? null) === 'match-reviewed-001') {
                $case['analysis']['required'] = ['javascript', 'typescript'];
                $case['snapshot'] = ['projects' => [['highlights' => ['JavaScript', 'TypeScript']]]];
                $case['expected']['matched'] = ['typescript', 'javascript'];
                $case['expected']['missing'] = [];
                $case['expected']['weak'] = [];
                break;
            }
        }
        unset($case);

        $corpusPath = $this->writeTemporaryJson($corpus);
        $manifest = $this->readManifest();
        $manifest['corpus_sha256'] = hash_file('sha256', $corpusPath);
        $manifestPath = $this->writeTemporaryJson($manifest);

        try {
            $result = app(MatchQualityEvaluator::class)->evaluate($manifestPath, $corpusPath);
        } finally {
            unlink($manifestPath);
            unlink($corpusPath);
        }

        self::assertSame(MatchQualityEvaluator::VERDICT_QUALITY_REGRESSION, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::EXIT_QUALITY_REGRESSION, $result['exit_code']);
        self::assertContains('ORDER_MISMATCH', array_column($result['diagnostics'], 'code'));
    }

    public function test_duration_budget_failure_returns_quality_exit_class(): void
    {
        $result = app(MatchQualityEvaluator::class)->evaluate(maxDurationSeconds: 0.0);

        self::assertSame(MatchQualityEvaluator::VERDICT_QUALITY_REGRESSION, $result['verdict']);
        self::assertSame(MatchQualityEvaluator::EXIT_QUALITY_REGRESSION, $result['exit_code']);
        self::assertContains('DURATION_EXCEEDED', array_column($result['diagnostics'], 'code'));
    }

    public function test_artisan_command_returns_invalid_exit_code_for_invalid_manifest(): void
    {
        $manifest = $this->readManifest();
        $manifest['matching_rule_version'] = '9.9.9';
        $path = $this->writeTemporaryJson($manifest);

        try {
            $this->artisan('match:quality', ['--manifest' => $path])
                ->expectsOutputToContain('MANIFEST_VERSION_MISMATCH')
                ->assertExitCode(MatchQualityEvaluator::EXIT_INVALID_INPUT);
        } finally {
            unlink($path);
        }
    }

    public function test_alias_command_preserves_success_exit_code(): void
    {
        $this->artisan('validate:match-quality')->assertExitCode(MatchQualityEvaluator::EXIT_PASS);
    }

    public function test_artisan_command_returns_pass_exit_code_and_json_contract(): void
    {
        $this->artisan('match:quality', ['--json' => true])
            ->expectsOutputToContain('"verdict":"PASS"')
            ->assertExitCode(MatchQualityEvaluator::EXIT_PASS);
    }

    /** @return array<string,mixed> */
    private function readManifest(): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 5).'/docs/contracts/jd/fixtures/match-quality-evaluation-v1.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }

    /** @return array<string,mixed> */
    private function readCorpus(): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 5).'/docs/contracts/jd/fixtures/match-report-v1.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }

    /** @param array<string,mixed> $payload */
    private function writeTemporaryJson(array $payload): string
    {
        $path = tempnam(sys_get_temp_dir(), 'match-quality-');
        self::assertIsString($path);
        file_put_contents($path, json_encode($payload, JSON_THROW_ON_ERROR));

        return $path;
    }
}
