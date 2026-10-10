<?php

declare(strict_types=1);

namespace Tests\Feature\Patch;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\Patch\Eloquent\Models\Patch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class PatchDecisionTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_user_can_edit_with_revision_then_reject_with_explicit_confirmation(): void
    {
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        $patch = $this->generatePatch($context['interview']->getKey());

        $edited = $this->patchJson('/api/v1/patches/'.$patch['id'], ['new_value' => 'Built reliable systems.'], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertOk()->json('data');
        self::assertSame(2, $edited['revision']);

        $this->postJson('/api/v1/patches/'.$patch['id'].'/reject', ['confirmed' => true], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"2"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertOk()->assertJsonPath('data.status', 'rejected');

        $this->postJson('/api/v1/patches/'.$patch['id'].'/approve', ['confirmed' => true], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"2"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertStatus(409)->assertJsonPath('code', 'PATCH_STATE_CONFLICT');
        self::assertSame('rejected', Patch::query()->findOrFail($patch['id'])->status->value);
    }

    public function test_foreign_user_cannot_read_or_decide_a_patch(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($owner);
        $patch = $this->generatePatch($context['interview']->getKey());

        $this->app['auth']->forgetGuards();
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/patches/'.$patch['id'])
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->postJson('/api/v1/patches/'.$patch['id'].'/reject', ['confirmed' => true], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertNotFound();
    }

    /** @return array<string,mixed> */
    private function generatePatch(string $interviewId): array
    {
        return $this->postJson('/api/v1/evidence-interviews/'.$interviewId.'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated()->json('data');
    }
}
