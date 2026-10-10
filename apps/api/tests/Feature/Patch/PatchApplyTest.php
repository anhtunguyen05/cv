<?php

declare(strict_types=1);

namespace Tests\Feature\Patch;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class PatchApplyTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_approval_creates_one_immutable_version_and_applies_patch(): void
    {
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        $patch = $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated()->json('data');

        $response = $this->postJson('/api/v1/patches/'.$patch['id'].'/approve', ['confirmed' => true], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertOk();

        $response->assertJsonPath('data.status', 'applied')
            ->assertJsonPath('data.result_version.source_cv_version_id', $context['context']['version']->getKey());
        self::assertSame(2, CvVersion::query()->count());
        self::assertSame(1, CvVersion::query()->where('source_patch_id', $patch['id'])->count());
    }
}
