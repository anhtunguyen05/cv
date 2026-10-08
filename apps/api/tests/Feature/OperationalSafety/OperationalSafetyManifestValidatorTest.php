<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\OperationalSafetyManifestValidator;
use DateTimeImmutable;
use Tests\TestCase;

final class OperationalSafetyManifestValidatorTest extends TestCase
{
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new DateTimeImmutable('2026-10-08T12:00:00+00:00');
    }

    public function test_default_manifest_returns_explicit_pass_with_gaps_for_disposable_mvp(): void
    {
        $result = app(OperationalSafetyManifestValidator::class)->validate(now: $this->now);

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_PASS_WITH_GAPS, $result['verdict']);
        self::assertSame(OperationalSafetyManifestValidator::EXIT_PASS_WITH_GAPS, $result['exit_code']);
        self::assertSame(0, $result['unresolved_decisions']);
        self::assertSame(1, $result['gap_count']);
    }

    public function test_complete_scoped_manifest_passes_with_integrity_and_freshness_checks(): void
    {
        $path = $this->writeManifest($this->baseManifest());

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_PASS, $result['verdict']);
        self::assertSame(OperationalSafetyManifestValidator::EXIT_PASS, $result['exit_code']);
        self::assertSame(1, $result['evidence']['passing']);
        self::assertSame([], $result['diagnostics']);
    }

    public function test_stale_evidence_fails_even_when_declared_pass(): void
    {
        $manifest = $this->baseManifest();
        $manifest['evidence'][0]['recorded_at'] = '2026-09-01T00:00:00+00:00';
        $manifest['verdict'] = OperationalSafetyManifestValidator::VERDICT_FAIL;
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_FAIL, $result['verdict']);
        self::assertContains('EVIDENCE_STALE', array_column($result['diagnostics'], 'code'));
    }

    public function test_tampered_artifact_fails_integrity_gate(): void
    {
        $manifest = $this->baseManifest();
        $manifest['evidence'][0]['artifact']['sha256'] = str_repeat('0', 64);
        $manifest['verdict'] = OperationalSafetyManifestValidator::VERDICT_FAIL;
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_FAIL, $result['verdict']);
        self::assertContains('ARTIFACT_HASH_MISMATCH', array_column($result['diagnostics'], 'code'));
    }

    public function test_forbidden_content_is_rejected_before_verdict_calculation(): void
    {
        $manifest = $this->baseManifest();
        $manifest['evidence'][0]['raw_cv'] = 'synthetic canary';
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame([['code' => 'FORBIDDEN_CONTENT']], $result['diagnostics']);
    }

    public function test_active_waiver_allows_explicit_pass_with_gaps(): void
    {
        $manifest = $this->baseManifest();
        $manifest['gaps'] = [[
            'id' => 'E5-GAP-001',
            'owner' => 'environment-owner',
            'risk' => 'Production-like boundary is not approved.',
            'action' => 'Obtain E5-DEC-008 approval.',
        ]];
        $manifest['waivers'] = [[
            'id' => 'E5-WAIVER-001',
            'gap_id' => 'E5-GAP-001',
            'owner' => 'release-owner',
            'expires_at' => '2026-10-15T00:00:00+00:00',
        ]];
        $manifest['verdict'] = OperationalSafetyManifestValidator::VERDICT_PASS_WITH_GAPS;
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_PASS_WITH_GAPS, $result['verdict']);
        self::assertSame(OperationalSafetyManifestValidator::EXIT_PASS_WITH_GAPS, $result['exit_code']);
    }

    public function test_expired_waiver_cannot_launder_a_gap(): void
    {
        $manifest = $this->baseManifest();
        $manifest['gaps'] = [[
            'id' => 'E5-GAP-001',
            'owner' => 'environment-owner',
            'risk' => 'Production-like boundary is not approved.',
            'action' => 'Obtain E5-DEC-008 approval.',
        ]];
        $manifest['waivers'] = [[
            'id' => 'E5-WAIVER-001',
            'gap_id' => 'E5-GAP-001',
            'owner' => 'release-owner',
            'expires_at' => '2026-10-01T00:00:00+00:00',
        ]];
        $manifest['verdict'] = OperationalSafetyManifestValidator::VERDICT_FAIL;
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_FAIL, $result['verdict']);
        self::assertContains('WAIVER_EXPIRED', array_column($result['diagnostics'], 'code'));
        self::assertContains('GAP_UNWAIVED', array_column($result['diagnostics'], 'code'));
    }

    public function test_declared_verdict_cannot_disagree_with_recomputed_verdict(): void
    {
        $manifest = $this->baseManifest();
        $manifest['verdict'] = OperationalSafetyManifestValidator::VERDICT_FAIL;
        $path = $this->writeManifest($manifest);

        try {
            $result = app(OperationalSafetyManifestValidator::class)->validate($path, $this->now);
        } finally {
            unlink($path);
        }

        self::assertSame(OperationalSafetyManifestValidator::VERDICT_INVALID, $result['verdict']);
        self::assertSame('VERDICT_MISMATCH', $result['diagnostics'][0]['code']);
    }

    public function test_artisan_command_returns_pass_with_gaps_exit_for_default_disposable_manifest(): void
    {
        $this->artisan('safety:baseline')
            ->expectsOutputToContain('pass_with_gaps')
            ->assertExitCode(OperationalSafetyManifestValidator::EXIT_PASS_WITH_GAPS);
    }

    public function test_artisan_command_returns_invalid_exit_for_malformed_manifest(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'safety-manifest-');
        self::assertIsString($path);
        file_put_contents($path, '{"manifest_version":');

        try {
            $this->artisan('safety:baseline', ['--manifest' => $path])
                ->expectsOutputToContain('MANIFEST_INVALID')
                ->assertExitCode(OperationalSafetyManifestValidator::EXIT_INVALID);
        } finally {
            unlink($path);
        }
    }

    /** @return array<string,mixed> */
    private function baseManifest(): array
    {
        $sourcePath = 'docs/contracts/jd/fixtures/match-quality-evaluation-v1.json';
        $artifactPath = 'docs/contracts/jd/fixtures/match-report-v1.json';

        return [
            'manifest_version' => OperationalSafetyManifestValidator::MANIFEST_VERSION,
            'baseline_id' => 'scoped-match-quality-v1',
            'source_sha' => 'e27910f1e3f760ec87e6773c31310b955b195780',
            'environment' => 'local-ci-disposable',
            'freshness_window_days' => 7,
            'scope' => [
                'included_stories' => ['5-5-validate-deterministic-matching-quality'],
                'excluded_stories' => [],
                'required_decisions' => ['E5-DEC-006'],
            ],
            'sources' => [[
                'path' => $sourcePath,
                'sha256' => hash_file('sha256', dirname(__DIR__, 5).'/'.$sourcePath),
            ]],
            'evidence' => [[
                'id' => 'match-quality-pinned-corpus',
                'story_key' => '5-5-validate-deterministic-matching-quality',
                'owner' => 'matching-quality-owner',
                'command' => 'php artisan match:quality --json',
                'result' => 'pass',
                'recorded_at' => '2026-10-08T11:00:00+00:00',
                'artifact' => [
                    'path' => $artifactPath,
                    'sha256' => hash_file('sha256', dirname(__DIR__, 5).'/'.$artifactPath),
                    'access' => 'private',
                    'canary_scan' => 'pass',
                ],
            ]],
            'decisions' => [['id' => 'E5-DEC-006', 'status' => 'approved']],
            'gaps' => [],
            'waivers' => [],
            'verdict' => OperationalSafetyManifestValidator::VERDICT_PASS,
        ];
    }

    /** @param array<string,mixed> $manifest */
    private function writeManifest(array $manifest): string
    {
        $path = tempnam(sys_get_temp_dir(), 'safety-manifest-');
        self::assertIsString($path);
        file_put_contents($path, json_encode($manifest, JSON_THROW_ON_ERROR));

        return $path;
    }
}
