<?php

declare(strict_types=1);

namespace App\Infrastructure\Workflow\Evidence;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Application\Evidence\Contracts\EvidenceInterviewWorkflow;
use App\Application\Evidence\EvidenceInterviewPresenter;
use App\Domain\Evidence\Enums\EvidenceInterviewStatus;
use App\Domain\JobFit\Enums\AnalysisStatus;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;
use App\Infrastructure\Persistence\Evidence\Eloquent\Models\EvidenceInterview;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescription;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescriptionAnalysis;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescriptionRevision;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\MatchReport;
use App\Shared\Application\Contracts\TransactionManager;
use Illuminate\Support\Carbon;

final class EloquentEvidenceInterviewWorkflow implements EvidenceInterviewWorkflow
{
    public function __construct(
        private readonly TransactionManager $transactions,
        private readonly IdempotencyStore $idempotency,
    ) {}

    public const QUESTION_SET_VERSION = '1.0';

    private const MAX_AREAS = 5;

    private const EXPIRY_DAYS = 7;

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function start(AuthenticatedUser $user, string $matchReportId, string $idempotencyKey, string $route, array $requestPayload = []): array
    {
        CvIdempotency::validate($idempotencyKey);
        if ($requestPayload !== []) {
            throw new ApiProblem(
                'VALIDATION_FAILED',
                'The Evidence Interview start request must not contain a body.',
                422,
            );
        }
        $hash = hash('sha256', CanonicalJson::encode([
            'match_report_id' => $matchReportId,
            'request_payload' => [],
        ])."\n".$route);

        return $this->transactions->run(function () use ($user, $matchReportId, $idempotencyKey, $hash): array {
            $report = $this->findOwnedReport($user, $matchReportId, true);
            $existingKey = CvIdempotency::existing($this->idempotency, $user->id, 'start-evidence-interview', $idempotencyKey, $hash);
            if ($existingKey['replayed']) {
                return [
                    'body' => $existingKey['body'] ?? [],
                    'status' => $existingKey['status'] ?? 201,
                    'replayed' => true,
                ];
            }

            $sources = $this->resolveSources($user, $report);
            $areas = $this->unresolvedAreas($report);
            if ($areas === []) {
                throw new ApiProblem(
                    'INTERVIEW_NOT_NEEDED',
                    'This Match Report has no unresolved evidence areas, so an interview is not needed.',
                    409,
                );
            }

            $existingInterviews = EvidenceInterview::query()
                ->where('user_id', $user->getKey())
                ->where('match_report_id', $report->getKey())
                ->lockForUpdate()
                ->orderByDesc('created_at')
                ->get();

            foreach ($existingInterviews as $existingInterview) {
                if ($existingInterview->status === EvidenceInterviewStatus::Active && $existingInterview->expires_at?->isPast()) {
                    $existingInterview->forceFill(['status' => 'expired'])->save();
                }
                if ($existingInterview->status === EvidenceInterviewStatus::Active) {
                    if (! $this->sameSources($existingInterview, $sources)) {
                        throw $this->sessionConflict($existingInterview);
                    }

                    $body = ['data' => EvidenceInterviewPresenter::data($existingInterview->fresh())];
                    CvIdempotency::record($this->idempotency, $user->id, 'start-evidence-interview', $idempotencyKey, $hash, $body, 201);

                    return ['body' => $body, 'status' => 201, 'replayed' => false];
                }

                throw $this->sessionConflict($existingInterview);
            }

            $questions = array_map(
                fn (array $area): array => [
                    'id' => ProfileDocument::id(),
                    'area_signal_id' => $area['signal_id'],
                    'question_version' => self::QUESTION_SET_VERSION,
                    'question' => $this->questionFor($area['label']),
                ],
                $areas,
            );
            $now = Carbon::now();
            $interview = EvidenceInterview::query()->create([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'match_report_id' => $report->getKey(),
                'cv_version_id' => $sources['cv_version_id'],
                'job_description_id' => $sources['job_description_id'],
                'job_description_revision_id' => $sources['job_description_revision_id'],
                'analysis_id' => $sources['analysis_id'],
                'areas' => $areas,
                'questions' => $questions,
                'question_set_version' => self::QUESTION_SET_VERSION,
                'status' => 'active',
                'expires_at' => $now->copy()->addDays(self::EXPIRY_DAYS),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $body = ['data' => EvidenceInterviewPresenter::data($interview)];
            CvIdempotency::record($this->idempotency, $user->id, 'start-evidence-interview', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    /** @return array<string,mixed> */
    public function show(AuthenticatedUser $user, string $interviewId): array
    {
        if (! ProfileDocument::isUlid($interviewId)) {
            throw $this->notFound();
        }

        return $this->transactions->run(function () use ($user, $interviewId): array {
            $interview = EvidenceInterview::query()
                ->where('id', $interviewId)
                ->where('user_id', $user->getKey())
                ->with('answers')
                ->lockForUpdate()
                ->first();
            if (! $interview instanceof EvidenceInterview) {
                throw $this->notFound();
            }
            $report = $this->findOwnedReport($user, (string) $interview->match_report_id, false);
            $sources = $this->resolveSources($user, $report);
            if (! $this->sameSources($interview, $sources)) {
                throw $this->sessionConflict($interview);
            }

            if ($interview->status === EvidenceInterviewStatus::Active && $interview->expires_at?->isPast()) {
                $interview->forceFill(['status' => 'expired'])->save();
                $interview->refresh();
            }

            return EvidenceInterviewPresenter::data($interview);
        });
    }

    /** @return array{report:MatchReport,cv_version_id:string,job_description_id:string,job_description_revision_id:string,analysis_id:string} */
    private function resolveSources(AuthenticatedUser $user, MatchReport $report): array
    {
        $sourceIds = [
            'cv_version_id' => (string) $report->cv_version_id,
            'job_description_id' => (string) $report->job_description_id,
            'job_description_revision_id' => (string) $report->job_description_revision_id,
            'analysis_id' => (string) $report->analysis_id,
        ];
        foreach ($sourceIds as $sourceId) {
            if (! ProfileDocument::isUlid($sourceId)) {
                throw $this->notFound();
            }
        }

        $cvVersion = CvVersion::query()->whereKey($sourceIds['cv_version_id'])->where('user_id', $user->getKey())->first();
        $jobDescription = JobDescription::query()->whereKey($sourceIds['job_description_id'])->where('user_id', $user->getKey())->first();
        $revision = JobDescriptionRevision::query()
            ->whereKey($sourceIds['job_description_revision_id'])
            ->where('job_description_id', $sourceIds['job_description_id'])
            ->where('user_id', $user->getKey())
            ->first();
        $analysis = JobDescriptionAnalysis::query()
            ->whereKey($sourceIds['analysis_id'])
            ->where('job_description_revision_id', $sourceIds['job_description_revision_id'])
            ->where('user_id', $user->getKey())
            ->first();
        if (! $cvVersion instanceof CvVersion
            || ! $jobDescription instanceof JobDescription
            || ! $revision instanceof JobDescriptionRevision
            || ! $analysis instanceof JobDescriptionAnalysis
            || $analysis->status !== AnalysisStatus::Succeeded
            || (string) $analysis->analysis_rule_version !== (string) $report->analysis_rule_version) {
            throw $this->notFound();
        }

        return [
            'report' => $report,
            'cv_version_id' => $sourceIds['cv_version_id'],
            'job_description_id' => $sourceIds['job_description_id'],
            'job_description_revision_id' => $sourceIds['job_description_revision_id'],
            'analysis_id' => $sourceIds['analysis_id'],
        ];
    }

    /** @return array<int,array{signal_id:string,label:string,importance:string,evidence_level:string,source_references:array<int,string>}> */
    private function unresolvedAreas(MatchReport $report): array
    {
        $missing = $report->missing_skills;
        $weak = $report->weak_evidence;
        if (! is_array($missing) || ! is_array($weak)) {
            throw new ApiProblem('DERIVED_RESULT_INVALID', 'The stored Match Report is invalid and cannot be used for an interview.', 500);
        }

        $areas = [];
        $signalIds = [];
        foreach (array_merge($missing, $weak) as $signal) {
            if (! is_array($signal)
                || ! is_string($signal['signal_id'] ?? null)
                || trim($signal['signal_id']) === ''
                || ! is_string($signal['label'] ?? null)
                || trim($signal['label']) === ''
                || ! in_array($signal['importance'] ?? null, ['required', 'preferred'], true)
                || ! in_array($signal['evidence_level'] ?? null, ['missing', 'weak'], true)
                || ! is_array($signal['source_references'] ?? null)
                || array_filter($signal['source_references'], static fn (mixed $reference): bool => ! is_string($reference) || trim($reference) === '') !== []) {
                throw new ApiProblem('DERIVED_RESULT_INVALID', 'The stored Match Report is invalid and cannot be used for an interview.', 500);
            }
            if (in_array($signal['signal_id'], $signalIds, true)) {
                throw new ApiProblem('DERIVED_RESULT_INVALID', 'The stored Match Report is invalid and cannot be used for an interview.', 500);
            }
            $signalIds[] = $signal['signal_id'];
            $areas[] = [
                'signal_id' => $signal['signal_id'],
                'label' => $signal['label'],
                'importance' => $signal['importance'],
                'evidence_level' => $signal['evidence_level'],
                'source_references' => array_values($signal['source_references']),
            ];
        }

        return array_slice($areas, 0, self::MAX_AREAS);
    }

    private function findOwnedReport(AuthenticatedUser $user, string $matchReportId, bool $lock): MatchReport
    {
        if (! ProfileDocument::isUlid($matchReportId)) {
            throw $this->notFound();
        }
        $query = MatchReport::query()->whereKey($matchReportId)->where('user_id', $user->getKey());
        if ($lock) {
            $query->lockForUpdate();
        }
        $report = $query->first();
        if (! $report instanceof MatchReport) {
            throw $this->notFound();
        }

        return $report;
    }

    /** @param array{report:MatchReport,cv_version_id:string,job_description_id:string,job_description_revision_id:string,analysis_id:string} $sources */
    private function sameSources(EvidenceInterview $interview, array $sources): bool
    {
        return (string) $interview->match_report_id === (string) $sources['report']->getKey()
            && (string) $interview->cv_version_id === $sources['cv_version_id']
            && (string) $interview->job_description_id === $sources['job_description_id']
            && (string) $interview->job_description_revision_id === $sources['job_description_revision_id']
            && (string) $interview->analysis_id === $sources['analysis_id'];
    }

    private function questionFor(string $label): string
    {
        return "What specific experience can you share that demonstrates your work with {$label}?";
    }

    private function sessionConflict(EvidenceInterview $interview): ApiProblem
    {
        return new ApiProblem('EVIDENCE_SESSION_CONFLICT', 'This Evidence Interview is no longer available for a new start.', 409, [
            'interview_id' => (string) $interview->getKey(),
            'status' => $interview->status->value,
            'next_action' => 'refresh_interview',
        ]);
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
