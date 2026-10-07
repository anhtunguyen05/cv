<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Models\CvVersion;
use App\Models\JobDescription;
use App\Models\JobDescriptionAnalysis;
use App\Models\MatchReport;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class MatchService
{
    private const DEADLINE_SECONDS = 5.0;

    public const REPORT_SCHEMA_VERSION = '1.0.0';

    public const RULE_VERSION = '1.0.0';

    public function __construct(private readonly JobDescriptionAnalyzer $analyzer) {}

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function create(User $user, array $payload, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $this->assertPayload($payload);
        $cvVersionId = (string) $payload['cv_version_id'];
        $jobDescriptionId = (string) $payload['job_description_id'];
        $hash = hash('sha256', CanonicalJson::encode([
            'cv_version_id' => $cvVersionId,
            'job_description_id' => $jobDescriptionId,
            'matching_rule_version' => self::RULE_VERSION,
        ])."\n".$route);

        return DB::transaction(function () use ($user, $idempotencyKey, $cvVersionId, $jobDescriptionId, $hash): array {
            if (! ProfileDocument::isUlid($cvVersionId) || ! ProfileDocument::isUlid($jobDescriptionId)) {
                throw $this->notFound();
            }
            $jobDescription = JobDescription::query()->where('id', $jobDescriptionId)->where('user_id', $user->getKey())->lockForUpdate()->first();
            if (! $jobDescription instanceof JobDescription) {
                throw $this->notFound();
            }
            // Serialize requests for the same owned Job Description before
            // reading the receipt. This closes the race where two concurrent
            // retries both observe an empty ledger and attempt to write.
            $existingKey = CvIdempotency::existing($user, 'create-match-report', $idempotencyKey, $hash);
            if ($existingKey['replayed']) {
                return ['body' => $existingKey['body'] ?? [], 'status' => $existingKey['status'] ?? 201, 'replayed' => true];
            }
            $cvVersion = CvVersion::query()->where('id', $cvVersionId)->where('user_id', $user->getKey())->first();
            if (! $cvVersion instanceof CvVersion) {
                throw $this->notFound();
            }
            if ($jobDescription->isDeleted()) {
                throw new ApiProblem('JOB_DESCRIPTION_DELETED', 'The Job Description is deleted and cannot be matched.', 409);
            }
            $revision = $jobDescription->currentRevision()->first();
            if ($revision === null) {
                throw $this->notFound();
            }
            $analysis = JobDescriptionAnalysis::query()
                ->where('job_description_revision_id', $revision->getKey())
                ->where('analysis_schema_version', JobDescriptionAnalyzer::SCHEMA_VERSION)
                ->where('analysis_rule_version', JobDescriptionAnalyzer::RULE_VERSION)
                ->where('status', 'succeeded')->latest('created_at')->first();
            if (! $analysis instanceof JobDescriptionAnalysis) {
                throw new ApiProblem('JOB_DESCRIPTION_ANALYSIS_REQUIRED', 'Analyze the current Job Description before creating a Match Report.', 409);
            }
            $startedAt = hrtime(true);
            $result = $this->evaluate($analysis, $cvVersion, $revision);
            if ((hrtime(true) - $startedAt) / 1_000_000_000 > self::DEADLINE_SECONDS) {
                throw new ApiProblem('DERIVATION_TEMPORARILY_UNAVAILABLE', 'The match report exceeded the synchronous processing deadline. Please retry.', 503);
            }
            $report = new MatchReport([
                'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'cv_version_id' => $cvVersion->getKey(),
                'job_description_id' => $jobDescription->getKey(), 'job_description_revision_id' => $revision->getKey(),
                'analysis_id' => $analysis->getKey(), 'analysis_rule_version' => $analysis->analysis_rule_version,
                'matching_rule_version' => self::RULE_VERSION, 'report_schema_version' => self::REPORT_SCHEMA_VERSION,
                'overall_score' => $result['overall_score'], 'matched_skills' => $result['matched_skills'],
                'missing_skills' => $result['missing_skills'], 'weak_evidence' => $result['weak_evidence'],
                'recommendations' => $result['recommendations'], 'created_at' => Carbon::now(),
            ]);
            $report->save();
            $body = ['data' => MatchReportPresenter::data($report->fresh(['revision', 'jobDescription']))];
            CvIdempotency::record($user, 'create-match-report', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(User $user, string $id): MatchReport
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $report = MatchReport::query()->where('id', $id)->where('user_id', $user->getKey())
            ->with(['revision', 'jobDescription'])->first();
        if (! $report instanceof MatchReport) {
            throw $this->notFound();
        }

        return $report;
    }

    public function list(User $user, int $page, int $perPage): LengthAwarePaginator
    {
        return MatchReport::query()->where('user_id', $user->getKey())->with(['revision', 'jobDescription'])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }

    /** @return array<string,mixed> */
    private function evaluate(JobDescriptionAnalysis $analysis, CvVersion $version, object $revision): array
    {
        $required = $this->items($analysis->required_skills);
        $preferred = $this->items($analysis->nice_to_have_skills);
        $requiredIds = array_fill_keys(array_column($required, 'signal_id'), true);
        $preferred = array_values(array_filter($preferred, static fn (array $item): bool => ! isset($requiredIds[$item['signal_id']])));
        $snapshot = is_array($version->snapshot) ? $version->snapshot : [];
        $matched = [];
        $missing = [];
        $weak = [];
        $categoryValues = [];
        $all = array_merge(array_map(static fn (array $item): array => [$item, 'required'], $required), array_map(static fn (array $item): array => [$item, 'preferred'], $preferred));
        $evidenceTotal = 0.0;
        $evidenceCount = 0;
        foreach ($all as [$item, $importance]) {
            $evidence = $this->evidence($item['signal_id'], $snapshot);
            $classification = [
                'signal_id' => $item['signal_id'], 'label' => $item['label'], 'importance' => $importance,
                'evidence_level' => $evidence['level'], 'source_references' => $evidence['references'],
            ];
            if ($evidence['level'] === 'strong') {
                $matched[] = $classification;
            } elseif ($evidence['level'] === 'weak') {
                $weak[] = $classification;
            } else {
                $missing[] = $classification;
            }
            $value = $evidence['level'] === 'strong' ? 1.0 : ($evidence['level'] === 'weak' ? 0.5 : 0.0);
            $evidenceTotal += $value;
            $evidenceCount++;
            $categoryValues[$importance][] = $value;
        }
        foreach (['required' => 50.0, 'preferred' => 10.0] as $category => $weight) {
            if (($categoryValues[$category] ?? []) !== []) {
                $categoryValues['weighted'][] = [$weight, array_sum($categoryValues[$category]) / count($categoryValues[$category])];
            }
        }
        if ($evidenceCount > 0) {
            $categoryValues['weighted'][] = [25.0, $evidenceTotal / $evidenceCount];
        }

        $seniority = is_string($analysis->seniority) ? $analysis->seniority : null;
        $seniorityState = (string) ($analysis->seniority_state ?: ($seniority === null ? 'absent' : 'detected'));
        if ($seniorityState === 'detected' && $seniority !== null && $seniority !== '') {
            $categoryValues['weighted'][] = [10.0, $this->cvContains($snapshot, $seniority) ? 1.0 : 0.0];
        }
        $role = is_array($analysis->extracted_role) ? $analysis->extracted_role : null;
        $domains = $this->items($analysis->domain_context, 'domain');
        if ($role !== null && ($role['state'] ?? 'absent') === 'detected') {
            $roleFound = $this->cvContains($snapshot, (string) ($role['value'] ?? ''));
            $domainFound = $domains !== [] && $this->cvContainsAny($snapshot, $domains);
            $categoryValues['weighted'][] = [5.0, ($roleFound || $domainFound) ? 1.0 : 0.0];
        } elseif ($domains !== []) {
            $categoryValues['weighted'][] = [5.0, $this->cvContainsAny($snapshot, $domains) ? 1.0 : 0.0];
        }
        $weights = $categoryValues['weighted'] ?? [];
        $weightTotal = array_sum(array_column($weights, 0));
        $weightedScore = $weightTotal <= 0.0 ? 0.0 : array_sum(array_map(static fn (array $entry): float => $entry[0] * $entry[1], $weights)) / $weightTotal * 100;
        usort($missing, [$this, 'sortClassification']);
        usort($weak, [$this, 'sortClassification']);
        usort($matched, [$this, 'sortClassification']);
        $missing = array_merge(array_values(array_filter($missing, static fn (array $item): bool => $item['importance'] === 'required')), array_values(array_filter($missing, static fn (array $item): bool => $item['importance'] === 'preferred')));
        $weak = array_merge(array_values(array_filter($weak, static fn (array $item): bool => $item['importance'] === 'required')), array_values(array_filter($weak, static fn (array $item): bool => $item['importance'] === 'preferred')));
        $recommendations = [];
        foreach (array_merge($missing, $weak) as $index => $item) {
            $recommendations[] = [
                'id' => 'recommendation-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'priority' => $item['importance'] === 'required' ? 'high' : 'medium',
                'target_cv_section' => $item['importance'] === 'required' ? 'projects_or_experience' : 'skills_or_summary',
                'related_signal_ids' => [$item['signal_id']],
                'rationale' => $item['evidence_level'] === 'weak' ? 'The signal appears without strong project or experience evidence.' : 'The current CV Version contains no supported evidence for this signal.',
                'action' => 'Add truthful, source-backed evidence if this experience is accurate; do not add an unsupported claim.',
            ];
        }

        return [
            'overall_score' => round(max(0.0, min(100.0, $weightedScore)), 2), 'matched_skills' => $matched,
            'missing_skills' => $missing, 'weak_evidence' => $weak, 'recommendations' => $recommendations,
        ];
    }

    /** @return array<int,array{signal_id:string,label:string}> */
    private function items(mixed $value, string $kind = 'skill'): array
    {
        if (! is_array($value) || ! is_array($value['items'] ?? null)) {
            return [];
        }
        if (($value['state'] ?? 'detected') !== 'detected') {
            return [];
        }

        if ($kind === 'domain') {
            return array_values(array_filter($value['items'], static fn (mixed $item): bool => is_string($item) && $item !== ''));
        }

        return array_values(array_filter($value['items'], static fn (mixed $item): bool => is_array($item) && isset($item['signal_id'], $item['label'])));
    }

    /** @param array<int,string> $domains */
    private function cvContainsAny(array $snapshot, array $domains): bool
    {
        foreach ($domains as $domain) {
            if ($this->cvContains($snapshot, $domain)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{level:string,references:array<int,string>} */
    private function evidence(string $signalId, array $snapshot): array
    {
        $definition = $this->analyzer->vocabulary($signalId);
        $aliases = $definition['aliases'] ?? [$signalId];
        $result = ['strong' => [], 'weak' => []];
        $walk = function (mixed $value, string $path, string $section) use (&$walk, &$result, $aliases): void {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    if (in_array((string) $key, ['id', 'url', 'website_url', 'linkedin_url', 'github_url', 'start_date', 'end_date', 'metadata'], true)) {
                        continue;
                    }
                    $childPath = $path === '' ? (string) $key : $path.'.'.$key;
                    $childSection = $section;
                    if (in_array((string) $key, ['projects', 'experience'], true)) {
                        $childSection = 'strong';
                    } elseif (in_array((string) $key, ['skills', 'summary'], true)) {
                        $childSection = 'weak';
                    }
                    $walk($child, $childPath, $childSection);
                }

                return;
            }
            if (! is_string($value) || $value === '') {
                return;
            }
            $lower = mb_strtolower($value, 'UTF-8');
            foreach ($aliases as $alias) {
                if ($this->containsPositiveAlias($lower, $alias)) {
                    if (in_array($section, ['strong', 'weak'], true)) {
                        $result[$section][] = $path;
                    }
                    break;
                }
            }
        };
        $walk($snapshot, '', 'unsupported');
        $strong = array_values(array_unique($result['strong']));
        $weak = array_values(array_unique($result['weak']));
        if ($strong !== []) {
            return ['level' => 'strong', 'references' => $strong];
        }
        if ($weak !== []) {
            return ['level' => 'weak', 'references' => $weak];
        }

        return ['level' => 'missing', 'references' => []];
    }

    private function containsPositiveAlias(string $text, string $alias): bool
    {
        $quoted = preg_quote($alias, '/');
        $positive = '/(?<![\pL\pN.])'.$quoted.'(?!(?:[\pL\pN]|\.(?=[\pL\pN])))/iu';
        preg_match_all($positive, $text, $occurrences, PREG_OFFSET_CAPTURE);
        foreach ($occurrences[0] ?? [] as [$match, $offset]) {
            $before = substr($text, 0, (int) $offset);
            $after = substr($text, (int) $offset + strlen((string) $match));
            if (preg_match('/(?:\bno\b|\bnot\b|\bwithout\b|\bkhông\b|\bkhong\b)[^\n.;]{0,32}$/iu', $before) !== 1
                && preg_match('/^[^\n.;]{0,24}\b(?:is\s+not|not\s+required|not\s+needed|not\s+mandatory|không\s+cần|khong\s+can)\b/iu', $after) !== 1) {
                return true;
            }
        }

        return false;
    }

    private function cvContains(array $snapshot, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }
        $found = false;
        $walk = function (mixed $value, string $key) use (&$walk, &$found, $needle): void {
            if ($found) {
                return;
            }
            if (is_array($value)) {
                foreach ($value as $childKey => $child) {
                    if (in_array((string) $childKey, ['id', 'url', 'website_url', 'linkedin_url', 'github_url', 'start_date', 'end_date', 'metadata'], true)) {
                        continue;
                    }
                    $walk($child, (string) $childKey);
                }

                return;
            }
            if (! is_string($value) || $value === '') {
                return;
            }
            $quoted = preg_quote(mb_strtolower($needle, 'UTF-8'), '/');
            if (preg_match('/(?<![\pL\pN.])'.$quoted.'(?!(?:[\pL\pN]|\.(?=[\pL\pN])))/iu', mb_strtolower($value, 'UTF-8')) === 1) {
                $found = true;
            }
        };
        $walk($snapshot, '');

        return $found;
    }

    private function sortClassification(array $left, array $right): int
    {
        return $left['signal_id'] <=> $right['signal_id'];
    }

    private function assertPayload(array $payload): void
    {
        if (array_key_exists('analysis_id', $payload)) {
            throw new ApiProblem('MATCH_SOURCE_CONFLICT', 'The server resolves the current Analysis; retry without an analysis override.', 409);
        }
        $unknown = array_diff(array_keys($payload), ['cv_version_id', 'job_description_id']);
        if ($unknown !== [] || ! is_string($payload['cv_version_id'] ?? null) || ! is_string($payload['job_description_id'] ?? null)) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                'body' => [['code' => 'INVALID', 'message' => 'cv_version_id and job_description_id are required.']],
            ]);
        }
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
