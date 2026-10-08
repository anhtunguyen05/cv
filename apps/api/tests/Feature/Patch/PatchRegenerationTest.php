<?php

declare(strict_types=1);

namespace Tests\Feature\Patch;

use App\Models\Patch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class PatchRegenerationTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_rejected_patch_regenerates_as_a_new_predecessor_linked_patch(): void
    {
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        $patch = $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated()->json('data');
        $this->postJson('/api/v1/patches/'.$patch['id'].'/reject', ['confirmed' => true], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertOk();

        $regenerated = $this->postJson('/api/v1/patches/'.$patch['id'].'/regenerate', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"2"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated()->json('data');

        self::assertSame($patch['id'], $regenerated['predecessor_patch_id']);
        self::assertNotSame($patch['id'], $regenerated['id']);
        self::assertSame('rejected', Patch::query()->findOrFail($patch['id'])->status);
        self::assertSame(2, Patch::query()->count());
    }
}
