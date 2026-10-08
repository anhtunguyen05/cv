<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use DateTimeImmutable;
use Throwable;

final class OperationalSafetyManifestValidator
{
    public const ARTIFACT_VERSION = '1.0.0';

    public const MANIFEST_VERSION = '1.0.0';

    public const VERDICT_PASS = 'pass';

    public const VERDICT_PASS_WITH_GAPS = 'pass_with_gaps';

    public const VERDICT_FAIL = 'fail';

    public const VERDICT_INVALID = 'invalid';

    public const VERDICT_INFRASTRUCTURE_FAILURE = 'infrastructure_failure';

    public const EXIT_PASS = 0;

    public const EXIT_PASS_WITH_GAPS = 20;

    public const EXIT_FAIL = 21;

    public const EXIT_INVALID = 22;

    public const EXIT_INFRASTRUCTURE_FAILURE = 23;

    private const MANIFEST_RELATIVE_PATH = 'docs/contracts/operational-safety/fixtures/baseline-manifest-v1.json';

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
     * Validate an immutable, content-free readiness manifest without writing
     * product state or converting missing evidence into a pass.
     *
     * @return array<string,mixed>
     */
    public function validate(?string $manifestPath = null, ?DateTimeImmutable $now = null): array
    {
        if ($manifestPath !== null) {
            if (! app()->environment('testing') || ! $this->isAllowedTestingPath($manifestPath)) {
                return $this->failure(self::VERDICT_INVALID, 'PROHIBITED_SOURCE');
            }
        }

        $manifestPath ??= $this->projectRoot().'/'.self::MANIFEST_RELATIVE_PATH;
        $loaded = $this->readJson($manifestPath);
        if ($loaded['state'] === 'infrastructure') {
            return $this->failure(self::VERDICT_INFRASTRUCTURE_FAILURE, 'MANIFEST_UNAVAILABLE');
        }
        if ($loaded['state'] === 'invalid') {
            return $this->failure(self::VERDICT_INVALID, 'MANIFEST_INVALID');
        }

        $manifest = $loaded['data'];
        $shapeError = $this->validateShape($manifest);
        if ($shapeError !== null) {
            return $this->failure(self::VERDICT_INVALID, $shapeError);
        }

        $now ??= new DateTimeImmutable('now');
        $diagnostics = $this->validateIntegrity($manifest, $now);
        $computedVerdict = self::VERDICT_PASS;

        if ($diagnostics !== []) {
            $computedVerdict = self::VERDICT_FAIL;
        } elseif ($manifest['gaps'] !== []) {
            $computedVerdict = self::VERDICT_PASS_WITH_GAPS;
        }

        if ($manifest['verdict'] !== $computedVerdict) {
            return $this->failure(self::VERDICT_INVALID, 'VERDICT_MISMATCH', [
                'declared_verdict' => $manifest['verdict'],
                'computed_verdict' => $computedVerdict,
            ]);
        }

        return [
            'artifact_version' => self::ARTIFACT_VERSION,
            'manifest_version' => self::MANIFEST_VERSION,
            'baseline_id' => $manifest['baseline_id'],
            'source_sha' => $manifest['source_sha'],
            'environment' => $manifest['environment'],
            'verdict' => $computedVerdict,
            'exit_code' => self::exitCode($computedVerdict),
            'scope' => [
                'included_stories' => count($manifest['scope']['included_stories']),
                'excluded_stories' => count($manifest['scope']['excluded_stories']),
            ],
            'evidence' => [
                'count' => count($manifest['evidence']),
                'passing' => count(array_filter(
                    $manifest['evidence'],
                    static fn (array $evidence): bool => $evidence['result'] === 'pass',
                )),
            ],
            'unresolved_decisions' => count(array_filter(
                $manifest['decisions'],
                static fn (array $decision): bool => in_array($decision['status'], ['open', 'pending'], true),
            )),
            'gap_count' => count($manifest['gaps']),
            'waiver_count' => count($manifest['waivers']),
            'diagnostics' => $diagnostics,
        ];
    }

    public static function exitCode(string $verdict): int
    {
        return match ($verdict) {
            self::VERDICT_PASS => self::EXIT_PASS,
            self::VERDICT_PASS_WITH_GAPS => self::EXIT_PASS_WITH_GAPS,
            self::VERDICT_FAIL => self::EXIT_FAIL,
            self::VERDICT_INVALID => self::EXIT_INVALID,
            default => self::EXIT_INFRASTRUCTURE_FAILURE,
        };
    }

    /** @param array<string,mixed> $manifest */
    private function validateShape(array $manifest): ?string
    {
        foreach ([
            'manifest_version', 'baseline_id', 'source_sha', 'environment', 'freshness_window_days',
            'scope', 'sources', 'evidence', 'decisions', 'gaps', 'waivers', 'verdict',
        ] as $field) {
            if (! array_key_exists($field, $manifest)) {
                return 'MANIFEST_FIELD_MISSING';
            }
        }

        if ($manifest['manifest_version'] !== self::MANIFEST_VERSION
            || ! is_string($manifest['baseline_id'])
            || $manifest['baseline_id'] === ''
            || ! is_string($manifest['environment'])
            || $manifest['environment'] === ''
            || ! is_int($manifest['freshness_window_days'])
            || $manifest['freshness_window_days'] < 1
            || ! is_string($manifest['source_sha'])
            || preg_match('/^[a-f0-9]{40}$/', $manifest['source_sha']) !== 1
            || ! in_array($manifest['verdict'], [self::VERDICT_PASS, self::VERDICT_PASS_WITH_GAPS, self::VERDICT_FAIL], true)) {
            return 'MANIFEST_SCHEMA_INVALID';
        }

        $scope = $manifest['scope'];
        if (! is_array($scope)
            || ! is_array($scope['included_stories'] ?? null)
            || ! is_array($scope['excluded_stories'] ?? null)
            || ! is_array($scope['required_decisions'] ?? null)
            || $scope['included_stories'] === []) {
            return 'SCOPE_INVALID';
        }

        foreach (['included_stories', 'excluded_stories', 'required_decisions'] as $field) {
            foreach ($scope[$field] as $value) {
                if (! is_string($value) || $value === '') {
                    return 'SCOPE_INVALID';
                }
            }
        }

        foreach (['sources', 'evidence', 'decisions', 'gaps', 'waivers'] as $field) {
            if (! is_array($manifest[$field]) || ! array_is_list($manifest[$field])) {
                return 'MANIFEST_COLLECTION_INVALID';
            }
        }

        if ($this->containsForbiddenContent($manifest)) {
            return 'FORBIDDEN_CONTENT';
        }

        return null;
    }

    /** @param array<string,mixed> $manifest @return array<int,array<string,mixed>> */
    private function validateIntegrity(array $manifest, DateTimeImmutable $now): array
    {
        $diagnostics = [];
        $scope = $manifest['scope'];
        $requiredDecisions = array_fill_keys($scope['required_decisions'], false);
        $evidenceByStory = [];
        $activeWaivers = [];

        foreach ($manifest['sources'] as $source) {
            if (! is_array($source)
                || ! is_string($source['path'] ?? null)
                || ! is_string($source['sha256'] ?? null)
                || preg_match('/^[a-f0-9]{64}$/', $source['sha256']) !== 1
                || ! $this->isSafeRelativePath($source['path'])) {
                $diagnostics[] = ['code' => 'SOURCE_INVALID'];

                continue;
            }
            $actualHash = $this->hashRelativeFile($source['path']);
            if ($actualHash === null) {
                $diagnostics[] = ['code' => 'SOURCE_UNAVAILABLE', 'path' => $source['path']];
            } elseif (! hash_equals($source['sha256'], $actualHash)) {
                $diagnostics[] = ['code' => 'SOURCE_HASH_MISMATCH', 'path' => $source['path']];
            }
        }

        foreach ($manifest['decisions'] as $decision) {
            if (! is_array($decision)
                || ! is_string($decision['id'] ?? null)
                || ! is_string($decision['status'] ?? null)
                || ! in_array($decision['status'], ['approved', 'open', 'pending', 'not_applicable'], true)) {
                $diagnostics[] = ['code' => 'DECISION_INVALID'];

                continue;
            }
            if (array_key_exists($decision['id'], $requiredDecisions)) {
                $requiredDecisions[$decision['id']] = $decision['status'] === 'approved'
                    || $decision['status'] === 'not_applicable';
            }
        }
        foreach ($requiredDecisions as $decisionId => $satisfied) {
            if (! $satisfied) {
                $diagnostics[] = ['code' => 'DECISION_UNRESOLVED', 'decision_id' => $decisionId];
            }
        }

        foreach ($manifest['evidence'] as $evidence) {
            if (! is_array($evidence)
                || ! is_string($evidence['id'] ?? null)
                || ! is_string($evidence['story_key'] ?? null)
                || ! is_string($evidence['owner'] ?? null)
                || $evidence['owner'] === 'unassigned'
                || ! is_string($evidence['command'] ?? null)
                || ! in_array($evidence['result'] ?? null, ['pass', 'fail', 'not_run'], true)
                || ! is_string($evidence['recorded_at'] ?? null)
                || ! is_array($evidence['artifact'] ?? null)) {
                $diagnostics[] = ['code' => 'EVIDENCE_INVALID'];

                continue;
            }
            $evidenceByStory[$evidence['story_key']] = true;
            $recordedAt = $this->parseDate($evidence['recorded_at']);
            if ($recordedAt === null || $recordedAt > $now) {
                $diagnostics[] = ['code' => 'EVIDENCE_TIMESTAMP_INVALID', 'evidence_id' => $evidence['id']];
            } elseif (($now->getTimestamp() - $recordedAt->getTimestamp()) > $manifest['freshness_window_days'] * 86400) {
                $diagnostics[] = ['code' => 'EVIDENCE_STALE', 'evidence_id' => $evidence['id']];
            }
            if ($evidence['result'] !== 'pass') {
                $diagnostics[] = ['code' => 'EVIDENCE_NOT_PASSING', 'evidence_id' => $evidence['id']];
            }
            $this->validateArtifact($evidence['artifact'], $evidence['id'], $diagnostics);
        }

        foreach ($scope['included_stories'] as $storyKey) {
            if (! isset($evidenceByStory[$storyKey])) {
                $diagnostics[] = ['code' => 'EVIDENCE_MISSING', 'story_key' => $storyKey];
            }
        }

        foreach ($manifest['waivers'] as $waiver) {
            if (! is_array($waiver)
                || ! is_string($waiver['id'] ?? null)
                || ! is_string($waiver['gap_id'] ?? null)
                || ! is_string($waiver['owner'] ?? null)
                || $waiver['owner'] === 'unassigned'
                || ! is_string($waiver['expires_at'] ?? null)) {
                $diagnostics[] = ['code' => 'WAIVER_INVALID'];

                continue;
            }
            $expiresAt = $this->parseDate($waiver['expires_at']);
            if ($expiresAt === null || $expiresAt <= $now) {
                $diagnostics[] = ['code' => 'WAIVER_EXPIRED', 'waiver_id' => $waiver['id']];
            } else {
                $activeWaivers[$waiver['gap_id']] = true;
            }
        }

        foreach ($manifest['gaps'] as $gap) {
            if (! is_array($gap)
                || ! is_string($gap['id'] ?? null)
                || ! is_string($gap['owner'] ?? null)
                || $gap['owner'] === 'unassigned'
                || ! is_string($gap['risk'] ?? null)
                || ! is_string($gap['action'] ?? null)) {
                $diagnostics[] = ['code' => 'GAP_INVALID'];

                continue;
            }
            if (! isset($activeWaivers[$gap['id']])) {
                $diagnostics[] = ['code' => 'GAP_UNWAIVED', 'gap_id' => $gap['id']];
            }
        }

        return $diagnostics;
    }

    /** @param array<string,mixed> $artifact @param array<int,array<string,mixed>> $diagnostics */
    private function validateArtifact(array $artifact, string $evidenceId, array &$diagnostics): void
    {
        if (! is_string($artifact['path'] ?? null)
            || ! $this->isSafeRelativePath($artifact['path'])
            || ! is_string($artifact['sha256'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/', $artifact['sha256']) !== 1
            || ($artifact['access'] ?? null) !== 'private'
            || ($artifact['canary_scan'] ?? null) !== 'pass') {
            $diagnostics[] = ['code' => 'ARTIFACT_INVALID', 'evidence_id' => $evidenceId];

            return;
        }
        $actualHash = $this->hashRelativeFile($artifact['path']);
        if ($actualHash === null) {
            $diagnostics[] = ['code' => 'ARTIFACT_UNAVAILABLE', 'evidence_id' => $evidenceId];
        } elseif (! hash_equals($artifact['sha256'], $actualHash)) {
            $diagnostics[] = ['code' => 'ARTIFACT_HASH_MISMATCH', 'evidence_id' => $evidenceId];
        }
    }

    private function containsForbiddenContent(mixed $value, ?string $key = null): bool
    {
        if ($key !== null) {
            $normalizedKey = strtolower($key);
            foreach (self::FORBIDDEN_KEYS as $forbiddenKey) {
                if (str_contains($normalizedKey, $forbiddenKey)) {
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

    private function isSafeRelativePath(string $path): bool
    {
        return $path !== ''
            && ! str_starts_with($path, '/')
            && ! str_contains($path, '\\')
            && ! str_contains($path, '../')
            && ! str_contains($path, '..\\');
    }

    private function hashRelativeFile(string $path): ?string
    {
        $absolutePath = $this->projectRoot().'/'.$path;
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }
        $hash = hash_file('sha256', $absolutePath);

        return is_string($hash) ? $hash : null;
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
            'artifact_version' => self::ARTIFACT_VERSION,
            'manifest_version' => self::MANIFEST_VERSION,
            'verdict' => $verdict,
            'exit_code' => self::exitCode($verdict),
            'diagnostics' => [['code' => $code, ...$details]],
        ];
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function projectRoot(): string
    {
        return dirname(base_path(), 2);
    }
}
