<?php

declare(strict_types=1);

namespace Tests\Feature\Patch;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\OperationalSafety\Eloquent\Models\OperationalAuditEvent;
use App\Infrastructure\Persistence\Patch\Eloquent\Models\Patch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class GeneratePatchProposalTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_completed_interview_generates_one_bounded_patch_from_positive_evidence(): void
    {
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);

        $response = $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.allowed_actions.0', 'edit')
            ->assertJsonPath('data.target.section', 'summary')
            ->assertJsonPath('data.provenance.provider.kind', 'deterministic_fake');
        self::assertSame(1, Patch::query()->count());
        self::assertSame(1, Patch::query()->where('interview_id', $context['interview']->getKey())->count());
        self::assertSame(1, OperationalAuditEvent::query()->count());
        self::assertSame('succeeded', OperationalAuditEvent::query()->firstOrFail()->status->value);
    }

    public function test_generation_requires_completed_interview_and_does_not_create_patch(): void
    {
        $user = User::factory()->create();
        $context = $this->createEpic4Report($user);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $interview = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated()->json('data');

        $this->postJson('/api/v1/evidence-interviews/'.$interview['id'].'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertStatus(409)->assertJsonPath('code', 'EVIDENCE_SESSION_CONFLICT');
        self::assertDatabaseCount('patches', 0);
    }
}
