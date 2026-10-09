<?php

declare(strict_types=1);

namespace App\Infrastructure\Workflow\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Application\JobFit\Contracts\MatchWorkflow;
use App\Application\JobFit\JobDescriptionAnalyzer;
use App\Application\JobFit\MatchEvaluator;
use App\Application\JobFit\MatchReportPresenter;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescription;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescriptionAnalysis;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\MatchReport;
use App\Shared\Application\Contracts\TransactionManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class EloquentMatchWorkflow implements MatchWorkflow
{
    private const DEADLINE_SECONDS = 5.0;

    public const REPORT_SCHEMA_VERSION = '1.0.0';

    public const RULE_VERSION = '1.0.0';

    public function __construct(
        private readonly TransactionManager $transactions,
        private readonly MatchEvaluator $evaluator,
        private readonly IdempotencyStore $idempotency,
    ) {}

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array
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

        return $this->transactions->run(function () use ($user, $idempotencyKey, $cvVersionId, $jobDescriptionId, $hash): array {
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
            $existingKey = CvIdempotency::existing($this->idempotency, $user->id, 'create-match-report', $idempotencyKey, $hash);
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
            $result = $this->evaluator->evaluate([
                'extracted_role' => $analysis->extracted_role,
                'required_skills' => $analysis->required_skills,
                'nice_to_have_skills' => $analysis->nice_to_have_skills,
                'seniority' => $analysis->seniority,
                'seniority_state' => $analysis->seniority_state,
                'domain_context' => $analysis->domain_context,
            ], is_array($cvVersion->snapshot) ? $cvVersion->snapshot : []);
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
            CvIdempotency::record($this->idempotency, $user->id, 'create-match-report', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(AuthenticatedUser $user, string $id): MatchReport
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

    public function list(AuthenticatedUser $user, int $page, int $perPage): LengthAwarePaginator
    {
        return MatchReport::query()->where('user_id', $user->getKey())->with(['revision', 'jobDescription'])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
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
