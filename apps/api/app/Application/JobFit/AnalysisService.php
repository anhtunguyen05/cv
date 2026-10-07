<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Models\JobDescription;
use App\Models\JobDescriptionAnalysis;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class AnalysisService
{
    private const DEADLINE_SECONDS = 5.0;

    public function __construct(private readonly JobDescriptionAnalyzer $analyzer) {}

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function create(User $user, string $jobDescriptionId, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);

        return DB::transaction(function () use ($user, $jobDescriptionId, $idempotencyKey, $route): array {
            // Serialize requests for the same owned Job Description before
            // reading the receipt. This closes the race where two concurrent
            // retries both observe an empty ledger and attempt to write.
            $hash = hash('sha256', CanonicalJson::encode([
                'job_description_id' => $jobDescriptionId,
                'rule_version' => JobDescriptionAnalyzer::RULE_VERSION,
            ])."\n".$route);
            $jobDescription = JobDescription::query()->where('id', $jobDescriptionId)->where('user_id', $user->getKey())->lockForUpdate()->first();
            if (! $jobDescription instanceof JobDescription) {
                throw $this->notFound();
            }
            $existingKey = CvIdempotency::existing($user, 'analyze-job-description', $idempotencyKey, $hash);
            if ($existingKey['replayed']) {
                return ['body' => $existingKey['body'] ?? [], 'status' => $existingKey['status'] ?? 200, 'replayed' => true];
            }
            $revision = $jobDescription->currentRevision()->first();
            if ($revision === null) {
                throw new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
            }
            if ($jobDescription->isDeleted()) {
                throw new ApiProblem('JOB_DESCRIPTION_DELETED', 'The Job Description is deleted and cannot be analyzed.', 409);
            }
            $existing = JobDescriptionAnalysis::query()
                ->where('job_description_revision_id', $revision->getKey())
                ->where('analysis_schema_version', JobDescriptionAnalyzer::SCHEMA_VERSION)
                ->where('analysis_rule_version', JobDescriptionAnalyzer::RULE_VERSION)
                ->where('status', 'succeeded')->first();
            if ($existing instanceof JobDescriptionAnalysis) {
                $body = ['data' => AnalysisPresenter::data($existing)];
                CvIdempotency::record($user, 'analyze-job-description', $idempotencyKey, $hash, $body, 200);

                return ['body' => $body, 'status' => 200, 'replayed' => false];
            }
            $startedAt = hrtime(true);
            $signals = $this->analyzer->analyze($revision->raw_text, $revision->title);
            if ((hrtime(true) - $startedAt) / 1_000_000_000 > self::DEADLINE_SECONDS) {
                throw new ApiProblem('DERIVATION_TEMPORARILY_UNAVAILABLE', 'The analysis exceeded the synchronous processing deadline. Please retry.', 503);
            }
            $analysis = new JobDescriptionAnalysis([
                'id' => ProfileDocument::id(), 'job_description_revision_id' => $revision->getKey(),
                'user_id' => $user->getKey(), 'status' => 'succeeded', 'analysis_schema_version' => JobDescriptionAnalyzer::SCHEMA_VERSION,
                'analysis_rule_version' => JobDescriptionAnalyzer::RULE_VERSION,
                'extracted_role' => $signals['role'], 'required_skills' => $signals['required_skills'],
                'nice_to_have_skills' => $signals['nice_to_have_skills'], 'responsibilities' => $signals['responsibilities'],
                'keywords' => $signals['keywords'], 'seniority' => $signals['seniority']['value'],
                'seniority_state' => $signals['seniority']['state'],
                'soft_skills' => $signals['soft_skills'], 'domain_context' => $signals['domain_context'],
                'completed_at' => Carbon::now(), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ]);
            $analysis->save();
            $body = ['data' => AnalysisPresenter::data($analysis->fresh())];
            CvIdempotency::record($user, 'analyze-job-description', $idempotencyKey, $hash, $body, 200);

            return ['body' => $body, 'status' => 200, 'replayed' => false];
        });
    }

    public function findOwned(User $user, string $jobDescriptionId, string $analysisId): JobDescriptionAnalysis
    {
        if (! ProfileDocument::isUlid($jobDescriptionId) || ! ProfileDocument::isUlid($analysisId)) {
            throw $this->notFound();
        }
        $analysis = JobDescriptionAnalysis::query()->where('id', $analysisId)->where('user_id', $user->getKey())
            ->whereHas('revision', static fn ($query) => $query->where('job_description_id', $jobDescriptionId))->first();
        if (! $analysis instanceof JobDescriptionAnalysis) {
            throw $this->notFound();
        }

        return $analysis;
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
