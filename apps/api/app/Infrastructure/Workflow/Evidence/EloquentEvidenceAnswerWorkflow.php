<?php

declare(strict_types=1);

namespace App\Infrastructure\Workflow\Evidence;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Application\Evidence\Contracts\EvidenceAnswerWorkflow;
use App\Application\Evidence\EvidenceAnswerPresenter;
use App\Application\Evidence\EvidenceInterviewPresenter;
use App\Domain\Evidence\Enums\EvidenceInterviewStatus;
use App\Domain\Evidence\Policies\EvidenceInterviewLifecycle;
use App\Infrastructure\Persistence\Evidence\Eloquent\Models\EvidenceAnswer;
use App\Infrastructure\Persistence\Evidence\Eloquent\Models\EvidenceInterview;
use App\Shared\Application\Contracts\TransactionManager;
use Illuminate\Support\Carbon;

final class EloquentEvidenceAnswerWorkflow implements EvidenceAnswerWorkflow
{
    public function __construct(
        private readonly TransactionManager $transactions,
        private readonly IdempotencyStore $idempotency,
    ) {}

    private const MAX_GRAPHEMES = 4000;

    private const MAX_BYTES = 16 * 1024;

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function answer(AuthenticatedUser $user, string $interviewId, array $payload, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $validated = $this->validatePayload($payload);
        $hash = hash('sha256', CanonicalJson::encode($validated)."\n".$route);

        return $this->transactions->run(function () use ($user, $interviewId, $validated, $idempotencyKey, $hash): array {
            $existing = CvIdempotency::existing($this->idempotency, $user->id, 'answer-evidence', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201, 'replayed' => true];
            }
            $interview = $this->ownedInterview($user, $interviewId, true);
            if ($interview->status !== EvidenceInterviewStatus::Active || $interview->expires_at?->isPast()) {
                if ($interview->status === EvidenceInterviewStatus::Active && $interview->expires_at?->isPast()) {
                    $interview->forceFill(['status' => 'expired'])->save();
                }
                throw $this->conflict($interview, 'The Evidence Interview is no longer accepting answers.');
            }
            $question = $this->question($interview, $validated['question_id']);
            if ($question === null || $question['question_version'] !== $validated['question_version']) {
                throw $this->conflict($interview, 'The question is stale or does not belong to this Interview.');
            }
            $existingAnswer = EvidenceAnswer::query()
                ->where('interview_id', $interview->getKey())
                ->where('question_id', $validated['question_id'])
                ->first();
            if ($existingAnswer instanceof EvidenceAnswer) {
                throw $this->conflict($interview, 'This question already has an immutable answer.');
            }

            $now = Carbon::now();
            $answer = EvidenceAnswer::query()->create([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'interview_id' => $interview->getKey(),
                'question_id' => $question['id'],
                'question_version' => $question['question_version'],
                'area_signal_id' => $question['area_signal_id'],
                'outcome' => $validated['outcome'],
                'answer_original' => $validated['answer_original'],
                'answer_normalized' => $validated['answer_normalized'],
                'provenance' => 'user',
                'created_at' => $now,
            ]);
            $answered = EvidenceAnswer::query()->where('interview_id', $interview->getKey())->count();
            $total = count($interview->questions ?? []);
            if ($answered === $total) {
                if (! EvidenceInterviewLifecycle::canTransition($interview->status, EvidenceInterviewStatus::Completed)) {
                    throw $this->conflict($interview, 'The Evidence Interview cannot be completed from its current state.');
                }
                $interview->forceFill(['status' => 'completed'])->save();
            }
            $interview->refresh();
            $interview->load('answers');
            $body = ['data' => [
                'answer' => EvidenceAnswerPresenter::data($answer),
                'interview' => EvidenceInterviewPresenter::data($interview),
                'progress' => ['answered' => $answered, 'total' => $total, 'remaining' => max(0, $total - $answered)],
            ]];
            CvIdempotency::record($this->idempotency, $user->id, 'answer-evidence', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    /** @return array{question_id:string,question_version:string,outcome:string,answer_original:?string,answer_normalized:?string} */
    private function validatePayload(array $payload): array
    {
        $unknown = array_diff(array_keys($payload), ['question_id', 'question_version', 'outcome', 'answer']);
        $errors = [];
        if ($unknown !== []) {
            $errors['body'][] = ['code' => 'UNSUPPORTED', 'message' => 'The request contains unsupported fields.'];
        }
        if (! is_string($payload['question_id'] ?? null) || ! ProfileDocument::isUlid($payload['question_id'])) {
            $errors['question_id'][] = ['code' => 'INVALID', 'message' => 'The question_id must be an uppercase ULID.'];
        }
        if (($payload['question_version'] ?? null) !== '1.0') {
            $errors['question_version'][] = ['code' => 'INVALID', 'message' => 'The question_version must be 1.0.'];
        }
        $outcome = $payload['outcome'] ?? null;
        if (! in_array($outcome, ['answer', 'cannot_provide'], true)) {
            $errors['outcome'][] = ['code' => 'INVALID', 'message' => 'The outcome must be answer or cannot_provide.'];
        }
        $answer = null;
        $normalized = null;
        if ($outcome === 'answer') {
            if (! is_string($payload['answer'] ?? null)) {
                $errors['answer'][] = ['code' => 'INVALID', 'message' => 'A text answer is required.'];
            } else {
                $answer = $payload['answer'];
                $normalized = ProfileDocument::normalizeText($answer);
                $length = function_exists('grapheme_strlen') ? grapheme_strlen($normalized) : mb_strlen($normalized);
                if ($length < 1 || $length > self::MAX_GRAPHEMES || strlen($normalized) > self::MAX_BYTES) {
                    $errors['answer'][] = ['code' => 'INVALID', 'message' => 'The answer must contain between 1 and 4,000 characters and be at most 16 KiB.'];
                }
                if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}]/u', $normalized) === 1) {
                    $errors['answer'][] = ['code' => 'INVALID', 'message' => 'The answer contains unsupported characters.'];
                }
            }
        } elseif (array_key_exists('answer', $payload) && $payload['answer'] !== null) {
            $errors['answer'][] = ['code' => 'INVALID', 'message' => 'cannot_provide cannot include answer text.'];
        }
        if ($errors !== []) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, $errors);
        }

        return [
            'question_id' => (string) $payload['question_id'],
            'question_version' => '1.0',
            'outcome' => (string) $outcome,
            'answer_original' => $answer,
            'answer_normalized' => $normalized,
        ];
    }

    private function ownedInterview(AuthenticatedUser $user, string $id, bool $lock): EvidenceInterview
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $query = EvidenceInterview::query()->whereKey($id)->where('user_id', $user->getKey());
        if ($lock) {
            $query->lockForUpdate();
        }
        $interview = $query->first();
        if (! $interview instanceof EvidenceInterview) {
            throw $this->notFound();
        }

        return $interview;
    }

    /** @return array<string,mixed>|null */
    private function question(EvidenceInterview $interview, string $id): ?array
    {
        foreach ($interview->questions ?? [] as $question) {
            if (is_array($question) && ($question['id'] ?? null) === $id) {
                return $question;
            }
        }

        return null;
    }

    private function conflict(EvidenceInterview $interview, string $message): ApiProblem
    {
        return new ApiProblem('EVIDENCE_SESSION_CONFLICT', $message, 409, [
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
