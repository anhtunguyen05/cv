<?php

declare(strict_types=1);

namespace App\Application\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Application\Cv\ProfileDocumentValidator;
use App\Application\OperationalSafety\OperationalAuditEventStore;
use App\Application\OperationalSafety\ProviderFailureClassifier;
use App\Application\OperationalSafety\ProviderRetryPolicy;
use App\Application\Patch\Contracts\PatchProposalContractValidator;
use App\Models\CvVersion;
use App\Models\EvidenceAnswer;
use App\Models\EvidenceInterview;
use App\Models\JobDescription;
use App\Models\JobDescriptionAnalysis;
use App\Models\JobDescriptionRevision;
use App\Models\MatchReport;
use App\Models\Patch;
use App\Models\PatchGenerationReservation;
use App\Models\PatchProviderAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PatchService
{
    public const PATCH_SCHEMA_VERSION = '1.0';

    public const PROMPT_VERSION = 'fake-1.0';

    public const TOOL_SCHEMA_VERSION = '1.0';

    public const PROVIDER_MODEL_VERSION = 'deterministic-fake-1.0';

    private const GENERATION_DEADLINE_SECONDS = 15.0;

    public function __construct(
        private readonly PatchProposalProvider $provider,
        private readonly ProviderFailureClassifier $failureClassifier,
        private readonly ProviderRetryPolicy $retryPolicy,
        private readonly OperationalAuditEventStore $auditEventStore,
    ) {}

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function generate(User $user, string $interviewId, array $payload, string $idempotencyKey, string $route, ?string $predecessorId = null, string $operation = 'generate-patch', ?int $predecessorRevision = null): array
    {
        CvIdempotency::validate($idempotencyKey);
        if ($payload !== []) {
            throw new ApiProblem('VALIDATION_FAILED', 'The Patch generation request must not contain a body.', 422);
        }
        $hash = hash('sha256', CanonicalJson::encode([
            'interview_id' => $interviewId,
            'predecessor_patch_id' => $predecessorId,
        ])."\n".$route);
        $providerMetadata = $this->providerMetadata();
        $logicalOperationKey = hash('sha256', "patch-generation:v1\n".$interviewId."\n".($predecessorId ?? '')."\n".($predecessorRevision === null ? '' : (string) $predecessorRevision));

        $state = DB::transaction(function () use ($user, $interviewId, $idempotencyKey, $hash, $predecessorId, $operation, $predecessorRevision, $providerMetadata, $logicalOperationKey): array {
            $existing = CvIdempotency::existing($user, $operation, $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['replayed' => true, 'body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201];
            }
            $context = $this->context($user, $interviewId, true);
            $interview = $context['interview'];
            if ($interview->status !== 'completed') {
                throw new ApiProblem('EVIDENCE_SESSION_CONFLICT', 'Complete the active Evidence Interview before generating a Patch.', 409);
            }
            $active = PatchProviderAttempt::query()
                ->where('interview_id', $interview->getKey())
                ->whereIn('status', ['requested', 'running'])
                ->lockForUpdate()
                ->first();
            if ($active instanceof PatchProviderAttempt) {
                if ($active->created_at?->greaterThan(Carbon::now()->subSeconds(self::GENERATION_DEADLINE_SECONDS))) {
                    throw new ApiProblem('PATCH_STATE_CONFLICT', 'A Patch generation attempt is already active for this Interview.', 409);
                }
                $active->forceFill(['status' => 'retryable_failure', 'outcome_code' => 'PATCH_PROVIDER_TIMEOUT', 'completed_at' => Carbon::now()])->save();
                if ($active->logical_operation_key !== null) {
                    PatchGenerationReservation::query()
                        ->where('user_id', $user->getKey())
                        ->where('logical_operation_key', $active->logical_operation_key)
                        ->whereIn('status', ['requested', 'running'])
                        ->update(['status' => 'retryable_failure', 'updated_at' => Carbon::now()]);
                }
            }
            $attemptQuery = PatchProviderAttempt::query()
                ->where('user_id', $user->getKey())
                ->where('created_at', '>=', Carbon::now()->subSeconds(ProviderRetryPolicy::WINDOW_SECONDS));
            $attempts = $attemptQuery->count();
            $oldestAttempt = (clone $attemptQuery)->orderBy('created_at')->first();
            if (! $this->retryPolicy->allows(
                $attempts,
                $oldestAttempt?->created_at?->toDateTimeImmutable(),
                Carbon::now()->toDateTimeImmutable(),
            )) {
                throw new ApiProblem('PATCH_RATE_LIMITED', 'Patch generation is temporarily rate limited. Please retry later.', 429);
            }
            $predecessor = null;
            if ($predecessorId !== null) {
                $predecessor = Patch::query()->whereKey($predecessorId)->where('user_id', $user->getKey())->lockForUpdate()->first();
                if (! $predecessor instanceof Patch || ! in_array($predecessor->status, ['rejected', 'invalid'], true)) {
                    throw new ApiProblem('PATCH_STATE_CONFLICT', 'Only a rejected or invalid Patch can be regenerated.', 409);
                }
                if ($predecessorRevision !== null && (int) $predecessor->revision !== $predecessorRevision) {
                    throw new ApiProblem('PATCH_STATE_CONFLICT', 'The Patch has changed. Reload it before regenerating.', 409);
                }
                if ((string) $predecessor->interview_id !== (string) $interview->getKey()) {
                    throw $this->notFound();
                }
                if (Patch::query()->where('predecessor_patch_id', $predecessor->getKey())->exists()) {
                    throw new ApiProblem('PATCH_STATE_CONFLICT', 'This Patch already has a regeneration result.', 409);
                }
            }
            $answers = EvidenceAnswer::query()->where('interview_id', $interview->getKey())->orderBy('created_at')->get();
            $positive = $answers->filter(static fn (EvidenceAnswer $answer): bool => $answer->outcome === 'answer' && is_string($answer->answer_normalized) && trim($answer->answer_normalized) !== '')->values();
            if ($positive->isEmpty()) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'A Patch requires at least one positive User Evidence answer.', 422);
            }
            $reservation = PatchGenerationReservation::query()
                ->where('user_id', $user->getKey())
                ->where('logical_operation_key', $logicalOperationKey)
                ->lockForUpdate()
                ->first();
            if ($reservation instanceof PatchGenerationReservation && $reservation->status === 'succeeded' && is_array($reservation->response)) {
                CvIdempotency::record($user, $operation, $idempotencyKey, $hash, $reservation->response, 201);

                return ['replayed' => true, 'body' => $reservation->response, 'status' => 201];
            }
            if ($reservation instanceof PatchGenerationReservation && in_array($reservation->status, ['requested', 'running'], true)) {
                throw new ApiProblem('PATCH_STATE_CONFLICT', 'A Patch generation attempt is already active for this Interview.', 409);
            }
            if ($predecessorId === null && Patch::query()->where('interview_id', $interview->getKey())->whereNull('predecessor_patch_id')->exists()) {
                throw new ApiProblem('PATCH_STATE_CONFLICT', 'A Patch proposal already exists for this Interview.', 409);
            }
            $executionId = $reservation instanceof PatchGenerationReservation ? (string) $reservation->execution_id : ProfileDocument::id();
            $requestSource = [
                'cv_version_id' => (string) $context['version']->getKey(),
                'snapshot_hash' => (string) $context['version']->snapshot_hash,
            ];
            if ($reservation instanceof PatchGenerationReservation
                && ((string) $reservation->source_cv_version_id !== $requestSource['cv_version_id']
                    || ! hash_equals((string) $reservation->source_snapshot_hash, $requestSource['snapshot_hash']))) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The source CV Version has changed. Review the Patch again.', 409);
            }
            $contractRequest = $this->contractRequest($executionId, $requestSource, $context['version']->snapshot['summary'] ?? null, $positive->all());
            $requestHash = (string) $contractRequest['request_hash'];
            if ($reservation instanceof PatchGenerationReservation
                && ! hash_equals((string) $reservation->request_hash, $requestHash)) {
                throw new ApiProblem('PATCH_STATE_CONFLICT', 'The Patch generation input has changed. Retry with a new operation.', 409);
            }
            $correlationId = $reservation instanceof PatchGenerationReservation ? (string) $reservation->correlation_id : bin2hex(random_bytes(16));
            if (! $reservation instanceof PatchGenerationReservation) {
                $reservationValues = [
                    'id' => ProfileDocument::id(),
                    'user_id' => $user->getKey(),
                    'interview_id' => $interview->getKey(),
                    'predecessor_patch_id' => $predecessor?->getKey(),
                    'logical_operation_key' => $logicalOperationKey,
                    'execution_id' => $executionId,
                    'request_hash' => $requestHash,
                    'source_cv_version_id' => $requestSource['cv_version_id'],
                    'source_snapshot_hash' => $requestSource['snapshot_hash'],
                    'correlation_id' => $correlationId,
                    'status' => 'running',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $inserted = PatchGenerationReservation::query()->insertOrIgnore($reservationValues);
                $reservation = PatchGenerationReservation::query()
                    ->where('user_id', $user->getKey())
                    ->where('logical_operation_key', $logicalOperationKey)
                    ->lockForUpdate()
                    ->first();
                if (! $reservation instanceof PatchGenerationReservation) {
                    throw new ApiProblem('PATCH_STATE_CONFLICT', 'The Patch generation reservation could not be acquired. Retry the operation.', 409);
                }
                if ($inserted === 0) {
                    if ($reservation->status === 'succeeded' && is_array($reservation->response)) {
                        CvIdempotency::record($user, $operation, $idempotencyKey, $hash, $reservation->response, 201);

                        return ['replayed' => true, 'body' => $reservation->response, 'status' => 201];
                    }
                    if (in_array($reservation->status, ['requested', 'running'], true)) {
                        throw new ApiProblem('PATCH_STATE_CONFLICT', 'A Patch generation attempt is already active for this Interview.', 409);
                    }
                    $executionId = (string) $reservation->execution_id;
                    $correlationId = (string) $reservation->correlation_id;
                    if ((string) $reservation->source_cv_version_id !== $requestSource['cv_version_id']
                        || ! hash_equals((string) $reservation->source_snapshot_hash, $requestSource['snapshot_hash'])) {
                        throw new ApiProblem('PATCH_SOURCE_STALE', 'The source CV Version has changed. Review the Patch again.', 409);
                    }
                    $contractRequest = $this->contractRequest($executionId, $requestSource, $context['version']->snapshot['summary'] ?? null, $positive->all());
                    $requestHash = (string) $contractRequest['request_hash'];
                    if (! hash_equals((string) $reservation->request_hash, $requestHash)) {
                        throw new ApiProblem('PATCH_STATE_CONFLICT', 'The Patch generation input has changed. Retry with a new operation.', 409);
                    }
                }
            }
            if ($reservation instanceof PatchGenerationReservation && $reservation->status !== 'running') {
                $reservation->forceFill(['status' => 'running', 'updated_at' => Carbon::now()])->save();
            }
            $attempt = PatchProviderAttempt::query()->create([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'interview_id' => $interview->getKey(),
                'predecessor_patch_id' => $predecessor?->getKey(),
                'status' => 'running',
                'prompt_version' => $providerMetadata['prompt_version'],
                'tool_schema_version' => $providerMetadata['tool_schema_version'],
                'provider_model_version' => $providerMetadata['model'],
                'correlation_id' => $correlationId,
                'execution_id' => $executionId,
                'request_hash' => $requestHash,
                'source_cv_version_id' => $requestSource['cv_version_id'],
                'source_snapshot_hash' => $requestSource['snapshot_hash'],
                'logical_operation_key' => $logicalOperationKey,
                'created_at' => Carbon::now(),
            ]);

            return [
                'replayed' => false,
                'attempt_id' => (string) $attempt->getKey(),
                'correlation_id' => (string) $attempt->correlation_id,
                'context' => $context,
                'predecessor' => $predecessor,
                'positive' => $positive->all(),
                'contract_request' => $contractRequest,
                'reservation_id' => (string) $reservation->getKey(),
                'provider_metadata' => $providerMetadata,
                'hash' => $hash,
                'operation' => $operation,
                'idempotency_key' => $idempotencyKey,
            ];
        });
        if ($state['replayed'] ?? false) {
            return ['body' => $state['body'] ?? [], 'status' => $state['status'] ?? 201, 'replayed' => true];
        }

        $startedAt = hrtime(true);
        try {
            $context = $state['context'];
            $providerRequest = [
                'execution_id' => $state['contract_request']['execution_id'],
                'request_hash' => $state['contract_request']['request_hash'],
                'source' => $state['contract_request']['source'],
                'source_fragments' => ['summary' => $context['version']->snapshot['summary'] ?? null],
                'positive_evidence' => $state['contract_request']['context']['positive_evidence'],
                'target_allowlist' => ['summary'],
                'locale' => 'en',
                'prompt_version' => $state['provider_metadata']['prompt_version'],
                'tool_schema_version' => $state['provider_metadata']['tool_schema_version'],
                '_correlation_id' => $state['correlation_id'],
            ];
            $proposal = $this->provider->propose($providerRequest);
            $state['provider_metadata'] = $this->providerMetadata();
            PatchProviderAttempt::query()->whereKey($state['attempt_id'])->update([
                'prompt_version' => $state['provider_metadata']['prompt_version'],
                'provider_model_version' => $state['provider_metadata']['model'],
                'response_metadata' => [
                    'provider' => $state['provider_metadata']['provider'],
                    'model' => $state['provider_metadata']['model'],
                    'prompt_version' => $state['provider_metadata']['prompt_version'],
                    'input_tokens' => $state['provider_metadata']['input_tokens'] ?? null,
                    'output_tokens' => $state['provider_metadata']['output_tokens'] ?? null,
                    'latency_ms' => $state['provider_metadata']['latency_ms'] ?? null,
                ],
            ]);
            if ((hrtime(true) - $startedAt) / 1_000_000_000 > self::GENERATION_DEADLINE_SECONDS) {
                throw new ApiProblem('PATCH_PROVIDER_TIMEOUT', 'The Patch provider exceeded the synchronous deadline. Please retry.', 503);
            }
            $proposal = $this->validateProposal($proposal, $context['version']->snapshot, $state['positive']);
        } catch (ApiProblem $problem) {
            if (in_array($problem->errorCode, [
                'PATCH_PROVIDER_TIMEOUT',
                'PATCH_PROVIDER_UNAVAILABLE',
                'PATCH_RATE_LIMITED',
                'PATCH_PROPOSAL_INVALID',
                'PATCH_VALIDATION_FAILED',
                'PATCH_CANCELLED',
            ], true)) {
                $this->finishClassifiedAttempt((string) $state['attempt_id'], $problem->errorCode);
            } else {
                $this->finishAttempt((string) $state['attempt_id'], 'terminal_failure', $problem->errorCode);
            }
            $this->finishReservation($state, $problem->errorCode);
            $this->appendAuditEvent($state, $problem->errorCode, $startedAt);
            throw $problem;
        } catch (Throwable) {
            $this->finishClassifiedAttempt((string) $state['attempt_id'], 'PATCH_PROVIDER_UNAVAILABLE');
            $this->finishReservation($state, 'PATCH_PROVIDER_UNAVAILABLE');
            $this->appendAuditEvent($state, 'PATCH_PROVIDER_UNAVAILABLE', $startedAt);
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }

        try {
            $result = DB::transaction(function () use ($user, $state, $proposal): array {
                $latestVersion = CvVersion::query()->whereKey($state['context']['version']->getKey())->where('user_id', $user->getKey())->lockForUpdate()->first();
                if (! $latestVersion instanceof CvVersion || ! hash_equals((string) $state['contract_request']['source']['snapshot_hash'], (string) $latestVersion->snapshot_hash)) {
                    throw new ApiProblem('PATCH_SOURCE_STALE', 'The source CV Version has changed. Review the Patch again.', 409);
                }
                $patch = Patch::query()->create([
                    'id' => ProfileDocument::id(),
                    'user_id' => $user->getKey(),
                    'source_cv_version_id' => $state['context']['version']->getKey(),
                    'match_report_id' => $state['context']['report']->getKey(),
                    'interview_id' => $state['context']['interview']->getKey(),
                    'predecessor_patch_id' => $state['predecessor']?->getKey(),
                    'status' => 'pending',
                    'revision' => 1,
                    'patch_schema_version' => self::PATCH_SCHEMA_VERSION,
                    'prompt_version' => $state['provider_metadata']['prompt_version'],
                    'provider_model_version' => $state['provider_metadata']['model'],
                    'target' => $proposal['target'],
                    'old_value' => $proposal['old_value'],
                    'new_value' => $proposal['new_value'],
                    'reason' => $proposal['reason'],
                    'evidence_source_ids' => $proposal['evidence_source_ids'],
                    'provenance' => [
                        'source' => [
                            'cv_version_id' => (string) $state['context']['version']->getKey(),
                            'match_report_id' => (string) $state['context']['report']->getKey(),
                            'interview_id' => (string) $state['context']['interview']->getKey(),
                        ],
                        'provider' => ['kind' => $state['provider_metadata']['kind'], 'model_version' => $state['provider_metadata']['model']],
                        'contract' => [
                            'execution_id' => $state['contract_request']['execution_id'],
                            'request_hash' => $state['contract_request']['request_hash'],
                            'source_snapshot_hash' => $state['contract_request']['source']['snapshot_hash'],
                        ],
                        'evidence_source_ids' => $proposal['evidence_source_ids'],
                        'decisions' => [],
                    ],
                    'source_snapshot_hash' => (string) $state['context']['version']->snapshot_hash,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $body = ['data' => PatchPresenter::data($patch->fresh(['sourceVersion', 'interview.answers']))];
                CvIdempotency::record($user, $state['operation'], $state['idempotency_key'], $state['hash'], $body, 201);
                $this->finishClassifiedAttempt((string) $state['attempt_id'], 'PATCH_SUCCEEDED', true, (string) $patch->getKey());
                PatchGenerationReservation::query()->whereKey($state['reservation_id'])->update([
                    'status' => 'succeeded',
                    'patch_id' => (string) $patch->getKey(),
                    'response' => $body,
                    'updated_at' => Carbon::now(),
                ]);

                return ['body' => $body, 'status' => 201, 'replayed' => false];
            });
        } catch (ApiProblem $problem) {
            $this->finishAttempt((string) $state['attempt_id'], 'terminal_failure', $problem->errorCode);
            $this->finishReservation($state, $problem->errorCode);
            $this->appendAuditEvent($state, $problem->errorCode, $startedAt);
            throw $problem;
        } catch (Throwable $exception) {
            $this->finishAttempt((string) $state['attempt_id'], 'retryable_failure', 'PATCH_PERSISTENCE_FAILED');
            $this->finishReservation($state, 'PATCH_PERSISTENCE_FAILED');
            $this->appendAuditEvent($state, 'PATCH_PERSISTENCE_FAILED', $startedAt);
            throw $exception;
        }

        $this->appendAuditEvent($state, 'PATCH_SUCCEEDED', $startedAt);

        return $result;
    }

    /** @return array<string,mixed> */
    public function show(User $user, string $patchId): array
    {
        $patch = $this->ownedPatch($user, $patchId, false);

        return PatchPresenter::data($patch->load(['sourceVersion', 'interview.answers']));
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function edit(User $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        $newValue = $this->editValue($payload);

        return DB::transaction(function () use ($user, $patchId, $newValue, $ifMatch, $idempotencyKey, $route): array {
            CvIdempotency::validate($idempotencyKey);
            $hash = hash('sha256', CanonicalJson::encode(['new_value' => $newValue])."\n".$route."\n".$ifMatch);
            $existing = CvIdempotency::existing($user, 'edit-patch', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 200, 'replayed' => true];
            }
            $patch = $this->ownedPatch($user, $patchId, true);
            $this->assertPatchRevision($patch, $ifMatch);
            $this->assertPending($patch);
            $context = $this->context($user, (string) $patch->interview_id, false);
            $candidate = $patch->new_value;
            $candidate['value'] = $newValue;
            $this->validateProposal([
                'target' => $patch->target,
                'old_value' => $patch->old_value,
                'new_value' => $candidate,
                'reason' => $patch->reason,
                'evidence_source_ids' => $patch->evidence_source_ids,
            ], $context['version']->snapshot, EvidenceAnswer::query()->whereIn('id', $patch->evidence_source_ids)->where('outcome', 'answer')->get()->all(), true);
            $provenance = $patch->provenance;
            $provenance['revisions'][] = [
                'revision' => (int) $patch->revision,
                'new_value' => $patch->new_value['value'] ?? null,
                'source' => (int) $patch->revision === 1 ? 'provider' : 'user_edit',
                'at' => Carbon::now()->toISOString(),
            ];
            $provenance['decisions'][] = ['type' => 'edit', 'actor' => (string) $user->getKey(), 'from_revision' => (int) $patch->revision, 'at' => Carbon::now()->toISOString()];
            $patch->forceFill(['new_value' => $candidate, 'status' => 'pending', 'revision' => $patch->revision + 1, 'provenance' => $provenance, 'updated_at' => Carbon::now()])->save();
            $body = ['data' => PatchPresenter::data($patch->fresh(['sourceVersion', 'interview.answers']))];
            CvIdempotency::record($user, 'edit-patch', $idempotencyKey, $hash, $body, 200);

            return ['body' => $body, 'status' => 200, 'replayed' => false];
        });
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function reject(User $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        return $this->decide($user, $patchId, $payload, $ifMatch, $idempotencyKey, $route, 'reject');
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function approve(User $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $versionName = $this->approvalVersionName($payload);

        return DB::transaction(function () use ($user, $patchId, $ifMatch, $idempotencyKey, $route, $versionName): array {
            $hash = hash('sha256', CanonicalJson::encode(['confirmed' => true, 'name' => $versionName])."\n".$route."\n".$ifMatch);
            $existing = CvIdempotency::existing($user, 'approve-patch', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 200, 'replayed' => true];
            }
            $patch = $this->ownedPatch($user, $patchId, true);
            $this->assertPatchRevision($patch, $ifMatch);
            $this->assertPending($patch);
            $context = $this->context($user, (string) $patch->interview_id, true);
            if ((string) $patch->source_cv_version_id !== (string) $context['version']->getKey()
                || ! hash_equals((string) $patch->source_snapshot_hash, (string) $context['version']->snapshot_hash)) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The source CV Version has changed. Review the Patch again.', 409);
            }
            $positive = EvidenceAnswer::query()
                ->where('interview_id', $context['interview']->getKey())
                ->whereIn('id', $patch->evidence_source_ids)
                ->where('outcome', 'answer')
                ->get();
            if ($positive->count() !== count($patch->evidence_source_ids)) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The supporting User Evidence is no longer available.', 409);
            }
            $this->validateProposal([
                'target' => $patch->target,
                'old_value' => $patch->old_value,
                'new_value' => $patch->new_value,
                'reason' => $patch->reason,
                'evidence_source_ids' => $patch->evidence_source_ids,
            ], $context['version']->snapshot, $positive->all(), true);
            $snapshot = $this->applyPatch($patch, $context['version']->snapshot);
            $this->validateSnapshot($snapshot);
            $now = Carbon::now();
            $version = CvVersion::query()->create([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'source_profile_id' => $context['version']->source_profile_id,
                'source_profile_revision' => $context['version']->source_profile_revision,
                'source_cv_version_id' => $context['version']->getKey(),
                'source_match_report_id' => $context['report']->getKey(),
                'source_interview_id' => $context['interview']->getKey(),
                'source_patch_id' => $patch->getKey(),
                'name' => $versionName ?? 'Evidence revision '.(string) $patch->getKey(),
                'snapshot_schema_version' => '1.0',
                'snapshot' => $snapshot,
                'snapshot_hash' => hash('sha256', CanonicalJson::encode($snapshot)),
                'provenance' => [
                    'kind' => 'evidence_patch',
                    'source_cv_version_id' => (string) $context['version']->getKey(),
                    'source_match_report_id' => (string) $context['report']->getKey(),
                    'source_interview_id' => (string) $context['interview']->getKey(),
                    'source_patch_id' => (string) $patch->getKey(),
                    'evidence_source_ids' => $patch->evidence_source_ids,
                ],
                'created_at' => $now,
            ]);
            $provenance = $patch->provenance;
            $provenance['decisions'][] = ['type' => 'approve', 'actor' => (string) $user->getKey(), 'result_version_id' => (string) $version->getKey(), 'at' => $now->toISOString()];
            $patch->forceFill(['status' => 'applied', 'revision' => $patch->revision + 1, 'applied_version_id' => $version->getKey(), 'provenance' => $provenance, 'updated_at' => $now])->save();
            $body = ['data' => [
                ...PatchPresenter::data($patch->fresh(['sourceVersion', 'interview.answers'])),
                'result_version' => ['id' => (string) $version->getKey(), 'source_cv_version_id' => (string) $context['version']->getKey(), 'snapshot' => $version->snapshot],
            ]];
            CvIdempotency::record($user, 'approve-patch', $idempotencyKey, $hash, $body, 200);

            return ['body' => $body, 'status' => 200, 'replayed' => false];
        });
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function regenerate(User $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route): array
    {
        if ($payload !== []) {
            throw new ApiProblem('VALIDATION_FAILED', 'The Patch regeneration request must not contain a body.', 422);
        }
        $patch = $this->ownedPatch($user, $patchId, false);
        $predecessorRevision = $this->assertPatchRevision($patch, $ifMatch);
        if (! in_array($patch->status, ['rejected', 'invalid'], true)) {
            throw new ApiProblem('PATCH_STATE_CONFLICT', 'Only a rejected or invalid Patch can be regenerated.', 409);
        }

        return $this->generate($user, (string) $patch->interview_id, [], $idempotencyKey, $route.'|if-match:'.$ifMatch, (string) $patch->getKey(), 'regenerate-patch', $predecessorRevision);
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    private function decide(User $user, string $patchId, array $payload, string $ifMatch, string $idempotencyKey, string $route, string $decision): array
    {
        CvIdempotency::validate($idempotencyKey);
        $this->assertConfirmation($payload);

        return DB::transaction(function () use ($user, $patchId, $ifMatch, $idempotencyKey, $route, $decision): array {
            $hash = hash('sha256', CanonicalJson::encode(['confirmed' => true])."\n".$route."\n".$ifMatch);
            $operation = $decision === 'reject' ? 'reject-patch' : 'approve-patch';
            $existing = CvIdempotency::existing($user, $operation, $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 200, 'replayed' => true];
            }
            $patch = $this->ownedPatch($user, $patchId, true);
            $this->assertPatchRevision($patch, $ifMatch);
            $this->assertPending($patch);
            $provenance = $patch->provenance;
            $provenance['decisions'][] = ['type' => 'reject', 'actor' => (string) $user->getKey(), 'at' => Carbon::now()->toISOString()];
            $patch->forceFill(['status' => 'rejected', 'revision' => $patch->revision + 1, 'provenance' => $provenance, 'updated_at' => Carbon::now()])->save();
            $body = ['data' => PatchPresenter::data($patch->fresh(['sourceVersion', 'interview.answers']))];
            CvIdempotency::record($user, $operation, $idempotencyKey, $hash, $body, 200);

            return ['body' => $body, 'status' => 200, 'replayed' => false];
        });
    }

    /** @return array{interview:EvidenceInterview,report:MatchReport,version:CvVersion} */
    private function context(User $user, string $interviewId, bool $lock): array
    {
        if (! ProfileDocument::isUlid($interviewId)) {
            throw $this->notFound();
        }
        $query = EvidenceInterview::query()->whereKey($interviewId)->where('user_id', $user->getKey());
        if ($lock) {
            $query->lockForUpdate();
        }
        $interview = $query->first();
        if (! $interview instanceof EvidenceInterview) {
            throw $this->notFound();
        }
        $report = MatchReport::query()->whereKey($interview->match_report_id)->where('user_id', $user->getKey())->first();
        $version = CvVersion::query()->whereKey($interview->cv_version_id)->where('user_id', $user->getKey())->first();
        if (! $report instanceof MatchReport || ! $version instanceof CvVersion
            || (string) $report->cv_version_id !== (string) $version->getKey()
            || (string) $report->job_description_id !== (string) $interview->job_description_id
            || (string) $report->job_description_revision_id !== (string) $interview->job_description_revision_id
            || (string) $report->analysis_id !== (string) $interview->analysis_id) {
            throw $this->notFound();
        }
        $revision = JobDescriptionRevision::query()->whereKey($report->job_description_revision_id)->where('job_description_id', $report->job_description_id)->where('user_id', $user->getKey())->first();
        $job = JobDescription::query()->whereKey($report->job_description_id)->where('user_id', $user->getKey())->first();
        $analysis = JobDescriptionAnalysis::query()->whereKey($report->analysis_id)->where('job_description_revision_id', $report->job_description_revision_id)->where('user_id', $user->getKey())->first();
        if (! $revision instanceof JobDescriptionRevision
            || ! $job instanceof JobDescription
            || ! $analysis instanceof JobDescriptionAnalysis
            || $analysis->status !== 'succeeded'
            || (string) $analysis->analysis_rule_version !== (string) $report->analysis_rule_version) {
            throw $this->notFound();
        }

        return ['interview' => $interview, 'report' => $report, 'version' => $version];
    }

    private function ownedPatch(User $user, string $id, bool $lock): Patch
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $query = Patch::query()->whereKey($id)->where('user_id', $user->getKey());
        if ($lock) {
            $query->lockForUpdate();
        }
        $patch = $query->first();
        if (! $patch instanceof Patch) {
            throw $this->notFound();
        }
        $context = $this->context($user, (string) $patch->interview_id, false);
        if ((string) $patch->source_cv_version_id !== (string) $context['version']->getKey()
            || (string) $patch->match_report_id !== (string) $context['report']->getKey()) {
            throw $this->notFound();
        }

        return $patch;
    }

    /** @param array<string,mixed> $payload */
    private function editValue(array $payload): string
    {
        if (array_diff(array_keys($payload), ['new_value']) !== [] || ! is_string($payload['new_value'] ?? null)) {
            throw new ApiProblem('VALIDATION_FAILED', 'The edit must contain only a new_value string.', 422);
        }
        $value = ProfileDocument::normalizeText($payload['new_value']);
        if ($value === '' || mb_strlen($value) > 2000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}<>]/u', $value) === 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'The proposed value is invalid.', 422, ['new_value' => [['code' => 'INVALID', 'message' => 'The proposed value contains unsupported content.']]]);
        }

        return $value;
    }

    /** @param array<string,mixed> $payload */
    private function assertConfirmation(array $payload, array $optionalKeys = []): void
    {
        $confirmation = $payload['confirmed'] ?? $payload['confirmation'] ?? null;
        if (array_diff(array_keys($payload), ['confirmed', 'confirmation', ...$optionalKeys]) !== [] || $confirmation !== true) {
            throw new ApiProblem('VALIDATION_FAILED', 'Explicit confirmation is required for this Patch decision.', 422, ['confirmed' => [['code' => 'REQUIRED', 'message' => 'Set confirmed to true.']]]);
        }
    }

    /** @param array<string,mixed> $payload */
    private function approvalVersionName(array $payload): ?string
    {
        $this->assertConfirmation($payload, ['name']);
        if (! array_key_exists('name', $payload)) {
            return null;
        }
        if (! is_string($payload['name'])) {
            throw new ApiProblem('VALIDATION_FAILED', 'The approved Version name is invalid.', 422);
        }
        $name = ProfileDocument::normalizeText($payload['name']);
        if ($name === '' || mb_strlen($name) > 120 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}<>]/u', $name) === 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'The approved Version name is invalid.', 422);
        }

        return $name;
    }

    private function assertPatchRevision(Patch $patch, string $ifMatch): int
    {
        if (preg_match('/^"([1-9][0-9]*)"$/', $ifMatch, $matches) !== 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'One strong Patch revision is required in If-Match.', 422, ['if_match' => [['code' => 'INVALID', 'message' => 'The If-Match header must contain a strong Patch revision.']]]);
        }
        if ((int) $matches[1] !== (int) $patch->revision) {
            throw new ApiProblem('PATCH_STATE_CONFLICT', 'The Patch has changed. Reload it before deciding.', 409, ['revision' => [['code' => 'STALE', 'message' => 'The supplied Patch revision is stale.']]]);
        }

        return (int) $patch->revision;
    }

    private function assertPending(Patch $patch): void
    {
        if ($patch->status !== 'pending') {
            throw new ApiProblem('PATCH_STATE_CONFLICT', 'This Patch no longer accepts that action.', 409, ['status' => (string) $patch->status]);
        }
    }

    /** @param array<string,mixed> $proposal @param array<string,mixed> $snapshot @param array<int,mixed> $positive */
    private function validateProposal(mixed $proposal, array $snapshot, array $positive, bool $requireGrounding = true): array
    {
        if (! is_array($proposal)) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch provider returned an invalid proposal.', 422);
        }
        $target = $proposal['target'] ?? null;
        $old = $proposal['old_value'] ?? null;
        $new = $proposal['new_value'] ?? null;
        $reason = $proposal['reason'] ?? null;
        $ids = $proposal['evidence_source_ids'] ?? null;
        if (! is_array($target) || ! is_array($old) || ! is_array($new) || ! is_string($reason) || ! is_array($ids)
            || ! array_is_list($ids)
            || array_diff(array_keys($target), ['section', 'field', 'item_id', 'operation', 'highlight_index']) !== []
            || ! array_key_exists('section', $target)
            || ! array_key_exists('field', $target)
            || ! array_key_exists('item_id', $target)
            || ! array_key_exists('operation', $target)
            || ! in_array($target['operation'], ['replace', 'append'], true)) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch provider returned an invalid proposal.', 422);
        }
        $section = $target['section'] ?? null;
        $field = $target['field'] ?? null;
        $operation = $target['operation'];
        $itemId = $target['item_id'] ?? null;
        if ($section === 'summary' && $field === 'summary' && $itemId === null && $operation === 'replace') {
            if (array_diff(array_keys($old), ['value']) !== [] || array_diff(array_keys($new), ['value']) !== [] || array_key_exists('highlight_index', $target)) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch value shape is invalid.', 422);
            }
            $current = $snapshot['summary'] ?? null;
            if (! array_key_exists('value', $old) || $old['value'] !== $current) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch old-value precondition is invalid.', 422);
            }
            $this->validateTextValue($new['value'] ?? null, 2000);
        } elseif (in_array($section, ['experience', 'projects'], true) && $field === 'highlights' && ProfileDocument::isUlid($itemId) && in_array($operation, ['replace', 'append'], true)) {
            if (array_diff(array_keys($new), ['value']) !== []) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch value shape is invalid.', 422);
            }
            $item = $this->findItem($snapshot, $section, (string) $itemId);
            if ($item === null || ! is_array($item['highlights'] ?? null)) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch target item does not exist in the source Version.', 422);
            }
            if ($operation === 'replace') {
                $index = $target['highlight_index'] ?? null;
                if (array_diff(array_keys($old), ['value']) !== [] || ! is_int($index) || ! array_key_exists($index, $item['highlights']) || ($old['value'] ?? null) !== $item['highlights'][$index]) {
                    throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch highlight precondition is invalid.', 422);
                }
            } elseif (array_diff(array_keys($old), ['collection_hash']) !== [] || array_key_exists('highlight_index', $target) || ($old['collection_hash'] ?? null) !== hash('sha256', CanonicalJson::encode($item['highlights']))) {
                throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch highlight collection precondition is invalid.', 422);
            }
            $this->validateTextValue($new['value'] ?? null, 400);
        } else {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch target is outside the allowlist.', 422);
        }
        $positiveIds = [];
        foreach ($positive as $answer) {
            if ($answer instanceof EvidenceAnswer) {
                $positiveIds[] = (string) $answer->getKey();
            } elseif (is_array($answer) && is_string($answer['id'] ?? null)) {
                $positiveIds[] = $answer['id'];
            }
        }
        if ($ids === [] || array_filter($ids, static fn (mixed $id): bool => ! is_string($id) || ! ProfileDocument::isUlid($id)) !== [] || array_diff($ids, $positiveIds) !== [] || count(array_unique($ids)) !== count($ids)) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'Every Patch must cite positive User Evidence used to ground it.', 422);
        }
        $proposedText = mb_strtolower((string) ($new['value'] ?? ''), 'UTF-8');
        $grounded = false;
        foreach ($positive as $answer) {
            $answerId = $answer instanceof EvidenceAnswer ? (string) $answer->getKey() : (string) ($answer['id'] ?? '');
            if (! in_array($answerId, $ids, true)) {
                continue;
            }
            $answerText = mb_strtolower((string) ($answer instanceof EvidenceAnswer ? $answer->answer_normalized : ($answer['answer'] ?? '')), 'UTF-8');
            $tokens = preg_split('/[^\pL\pN]+/u', $answerText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($tokens as $token) {
                if (mb_strlen($token) >= 4 && ! in_array($token, ['with', 'from', 'that', 'this', 'have', 'your', 'were', 'been'], true) && mb_strpos($proposedText, $token) !== false) {
                    $grounded = true;
                    break 2;
                }
            }
            if ($tokens === [] && $answerText !== '' && mb_strpos($proposedText, $answerText) !== false) {
                $grounded = true;
                break;
            }
        }
        if ($requireGrounding && ! $grounded) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch proposal is not grounded in its cited User Evidence.', 422);
        }
        if (trim($reason) === '' || mb_strlen($reason) > 1000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}<>]/u', $reason) === 1) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch reason is unsafe.', 422);
        }

        return ['target' => $target, 'old_value' => $old, 'new_value' => $new, 'reason' => ProfileDocument::normalizeText($reason), 'evidence_source_ids' => array_values($ids)];
    }

    private function validateTextValue(mixed $value, int $max): void
    {
        if (! is_string($value) || trim($value) === '' || mb_strlen($value) > $max || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}<>]/u', $value) === 1) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch value is invalid.', 422);
        }
    }

    /** @param array<string,mixed> $snapshot @return array<string,mixed>|null */
    private function findItem(array $snapshot, string $section, string $itemId): ?array
    {
        foreach (($snapshot[$section] ?? []) as $item) {
            if (is_array($item) && ($item['id'] ?? null) === $itemId) {
                return $item;
            }
        }

        return null;
    }

    /** @param array<string,mixed> $snapshot @return array<string,mixed> */
    private function applyPatch(Patch $patch, array $snapshot): array
    {
        $target = $patch->target;
        $newValue = $patch->new_value['value'] ?? null;
        if (($target['section'] ?? null) === 'summary') {
            if (($patch->old_value['value'] ?? null) !== ($snapshot['summary'] ?? null)) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The source summary has changed. Review the Patch again.', 409);
            }
            $snapshot['summary'] = $newValue;

            return $snapshot;
        }
        $section = (string) ($target['section'] ?? '');
        $itemId = (string) ($target['item_id'] ?? '');
        $index = null;
        foreach (($snapshot[$section] ?? []) as $key => $item) {
            if (is_array($item) && ($item['id'] ?? null) === $itemId) {
                $index = $key;
                break;
            }
        }
        if ($index === null) {
            throw new ApiProblem('PATCH_SOURCE_STALE', 'The Patch target no longer exists.', 409);
        }
        $highlights = $snapshot[$section][$index]['highlights'] ?? null;
        if (! is_array($highlights)) {
            throw new ApiProblem('PATCH_SOURCE_STALE', 'The Patch target highlights are no longer valid.', 409);
        }
        if (($target['operation'] ?? 'replace') === 'replace') {
            $highlightIndex = $target['highlight_index'] ?? null;
            if (! is_int($highlightIndex) || ! array_key_exists($highlightIndex, $highlights) || ($patch->old_value['value'] ?? null) !== $highlights[$highlightIndex]) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The Patch highlight has changed. Review the Patch again.', 409);
            }
            $highlights[$highlightIndex] = $newValue;
        } else {
            if (($patch->old_value['collection_hash'] ?? null) !== hash('sha256', CanonicalJson::encode($highlights))) {
                throw new ApiProblem('PATCH_SOURCE_STALE', 'The Patch highlight collection has changed. Review the Patch again.', 409);
            }
            $highlights[] = $newValue;
        }
        $snapshot[$section][$index]['highlights'] = $highlights;

        return $snapshot;
    }

    /** @param array<string,mixed> $snapshot */
    private function validateSnapshot(array $snapshot): void
    {
        $errors = ProfileDocumentValidator::validateCreate([
            'title' => $snapshot['title'] ?? null,
            'personal_information' => $snapshot['personal_information'] ?? null,
        ]);
        foreach (ProfileDocument::SECTIONS as $section) {
            $errors = [...$errors, ...ProfileDocumentValidator::validateSection($section, $snapshot[$section] ?? null)];
        }
        if ($errors !== []) {
            throw new ApiProblem('PATCH_VALIDATION_FAILED', 'The complete CV Version snapshot is invalid.', 422, $errors);
        }
    }

    private function finishAttempt(string $id, string $status, ?string $code, ?string $patchId = null): void
    {
        $updates = [
            'status' => $status,
            'outcome_code' => $code,
            'latency_class' => $status === 'succeeded' ? 'fast' : 'bounded_failure',
            'completed_at' => Carbon::now(),
        ];
        if ($patchId !== null) {
            $updates['patch_id'] = $patchId;
        }
        PatchProviderAttempt::query()->whereKey($id)->update($updates);
    }

    /** @param array<string,mixed> $state */
    private function finishReservation(array $state, string $outcomeCode): void
    {
        $status = in_array($outcomeCode, ['PATCH_PROVIDER_TIMEOUT', 'PATCH_PROVIDER_UNAVAILABLE', 'PATCH_RATE_LIMITED', 'PATCH_PERSISTENCE_FAILED'], true)
            ? 'retryable_failure'
            : 'terminal_failure';
        PatchGenerationReservation::query()->whereKey($state['reservation_id'] ?? '')->update([
            'status' => $status,
            'updated_at' => Carbon::now(),
        ]);
    }

    /** @return array{provider:string,model:string,prompt_version:string,tool_schema_version:string,kind:string} */
    private function providerMetadata(): array
    {
        if ($this->provider instanceof PatchProviderMetadata) {
            return $this->provider->providerMetadata();
        }

        return [
            'provider' => 'deterministic-fake',
            'model' => self::PROVIDER_MODEL_VERSION,
            'prompt_version' => self::PROMPT_VERSION,
            'tool_schema_version' => self::TOOL_SCHEMA_VERSION,
            'kind' => 'deterministic_fake',
        ];
    }

    /** @param array<int,EvidenceAnswer> $positive @return array<string,mixed> */
    private function contractRequest(string $executionId, array $source, mixed $summary, array $positive): array
    {
        $request = [
            'contract_version' => '1.0',
            'execution_id' => $executionId,
            'request_hash' => '',
            'source' => $source,
            'context' => [
                'source_fragment' => ['kind' => 'summary', 'current_value' => $summary],
                'positive_evidence' => array_map(static fn (EvidenceAnswer $answer): array => [
                    'id' => (string) $answer->getKey(),
                    'area_signal_id' => (string) $answer->area_signal_id,
                    'answer' => (string) $answer->answer_normalized,
                ], array_slice($positive, 0, 5)),
            ],
            'constraints' => ['target_allowlist' => ['summary'], 'locale' => 'en'],
        ];
        $withoutHash = $request;
        unset($withoutHash['request_hash']);
        $request['request_hash'] = PatchProposalContractValidator::canonicalRequestHash($withoutHash);

        return $request;
    }

    private function finishClassifiedAttempt(string $id, string $outcomeCode, bool $validated = false, ?string $patchId = null): void
    {
        $result = $this->failureClassifier->classify($outcomeCode, $validated);
        if (($result['accepted'] ?? false) !== true) {
            $this->finishAttempt($id, 'terminal_failure', 'PATCH_UNKNOWN_OUTCOME', $patchId);

            return;
        }

        $status = match ($result['decision']['status']) {
            ProviderFailureClassifier::STATUS_SUCCEEDED => 'succeeded',
            ProviderFailureClassifier::STATUS_RETRYABLE_FAILED => 'retryable_failure',
            ProviderFailureClassifier::STATUS_TERMINAL_FAILED,
            ProviderFailureClassifier::STATUS_CANCELLED => 'terminal_failure',
            default => 'terminal_failure',
        };
        $this->finishAttempt($id, $status, $result['decision']['outcome_code'], $patchId);
    }

    /** @param array<string,mixed> $state */
    private function appendAuditEvent(array $state, string $outcomeCode, int $startedAt): void
    {
        $failureCategory = match ($outcomeCode) {
            'PATCH_SUCCEEDED' => 'none',
            'PATCH_PROVIDER_TIMEOUT' => 'timeout',
            'PATCH_RATE_LIMITED' => 'rate_limited',
            'PATCH_PROVIDER_UNAVAILABLE' => 'transport',
            'PATCH_PROPOSAL_INVALID' => 'malformed',
            'PATCH_VALIDATION_FAILED' => 'validation',
            'PATCH_CANCELLED' => 'cancelled',
            default => 'unknown',
        };
        $status = $outcomeCode === 'PATCH_SUCCEEDED'
            ? 'succeeded'
            : ($outcomeCode === 'PATCH_PROVIDER_TIMEOUT' ? 'timed_out' : 'failed');
        $latencyClass = $status === 'succeeded' ? 'fast' : ($status === 'timed_out' ? 'timeout' : 'bounded_failure');
        $durationMs = min(60_000, max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000)));

        try {
            $this->auditEventStore->append([
                'event_id' => ProfileDocument::id(),
                'occurred_at' => Carbon::now()->toIso8601String(),
                'actor_type' => 'system',
                'operation' => 'generate-patch',
                'tool' => 'patch-proposal',
                'provider' => (string) ($state['provider_metadata']['provider'] ?? 'deterministic-fake'),
                'model' => (string) ($state['provider_metadata']['model'] ?? self::PROVIDER_MODEL_VERSION),
                'contract_version' => 'patch-1.0',
                'prompt_version' => (string) ($state['provider_metadata']['prompt_version'] ?? self::PROMPT_VERSION),
                'tool_schema_version' => (string) ($state['provider_metadata']['tool_schema_version'] ?? self::TOOL_SCHEMA_VERSION),
                'resource_type' => 'patch',
                'correlation_id' => (string) ($state['correlation_id'] ?? ''),
                'attempt_id' => (string) ($state['attempt_id'] ?? ''),
                'environment' => 'local-ci-disposable',
            ], [
                'status' => $status,
                'failure_category' => $failureCategory,
                'duration_ms' => $durationMs,
                'retry_count' => 0,
                'latency_class' => $latencyClass,
            ]);
        } catch (Throwable) {
            // Audit failure never falls back to unsafe output or changes product truth.
        }
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
