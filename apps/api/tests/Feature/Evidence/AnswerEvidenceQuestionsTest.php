<?php

declare(strict_types=1);

namespace Tests\Feature\Evidence;

use App\Models\EvidenceAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AnswerEvidenceQuestionsTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_answer_is_immutable_and_interview_completes_after_all_outcomes(): void
    {
        $user = User::factory()->create();
        $context = $this->createEpic4Report($user);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $interview = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '11111111-1111-4111-8111-111111111111',
        ])->assertCreated()->json('data');

        $question = $interview['questions'][0];
        $this->postJson('/api/v1/evidence-interviews/'.$interview['id'].'/answers', [
            'question_id' => $question['id'],
            'question_version' => '1.0',
            'outcome' => 'answer',
            'answer' => "  Built\r\nreliable software.  ",
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '22222222-2222-4222-8222-222222222222',
        ])->assertCreated()->assertJsonPath('data.answer.answer', "  Built\r\nreliable software.  ");

        $this->postJson('/api/v1/evidence-interviews/'.$interview['id'].'/answers', [
            'question_id' => $question['id'],
            'question_version' => '1.0',
            'outcome' => 'answer',
            'answer' => 'A second answer',
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '33333333-3333-4333-8333-333333333333',
        ])->assertStatus(409)->assertJsonPath('code', 'EVIDENCE_SESSION_CONFLICT');

        foreach (array_slice($interview['questions'], 1) as $index => $remainingQuestion) {
            $this->postJson('/api/v1/evidence-interviews/'.$interview['id'].'/answers', [
                'question_id' => $remainingQuestion['id'],
                'question_version' => '1.0',
                'outcome' => $index === 0 ? 'cannot_provide' : 'answer',
                ...($index === 0 ? [] : ['answer' => 'Built reliable software for the stored signal.']),
            ], [
                'X-CSRF-TOKEN' => 'csrf-token',
                'Idempotency-Key' => (string) \Illuminate\Support\Str::uuid(),
            ])->assertCreated();
        }

        $completedInterview = \App\Models\EvidenceInterview::query()->findOrFail($interview['id']);
        self::assertSame('completed', $completedInterview->status);
        self::assertSame(2, EvidenceAnswer::query()->where('interview_id', $interview['id'])->count());
        self::assertSame(1, EvidenceAnswer::query()->where('outcome', 'cannot_provide')->count());
    }

    public function test_malformed_answer_does_not_create_evidence(): void
    {
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        $question = $context['interview']->questions[0];

        $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/answers', [
            'question_id' => $question['id'],
            'question_version' => '1.0',
            'outcome' => 'answer',
            'answer' => str_repeat('x', 17000),
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '44444444-4444-4444-8444-444444444444',
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');

        self::assertDatabaseCount('evidence_answers', 2);
    }
}
