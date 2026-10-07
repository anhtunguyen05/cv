<?php

declare(strict_types=1);

namespace Tests\Feature\Cv;

use App\Application\Cv\CanonicalJson;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProfileVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_create_is_owner_scoped_and_idempotent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $headers = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'];

        $first = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $headers)->assertCreated();
        $second = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $headers)->assertCreated();

        self::assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('cv_profiles', 1);
        $this->getJson('/api/v1/cv-profiles', ['Accept' => 'application/json'])->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_guest_and_foreign_owner_cannot_discover_a_profile(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders('11111111-1111-4111-8111-111111111111'))
            ->assertCreated()->json('data');

        Auth::guard('web')->logout();
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/cv-profiles/'.$profile['id'])->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');

        $other = User::factory()->create();
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/cv-profiles/'.$profile['id'])->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    public function test_idempotency_key_reuse_with_a_different_payload_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $key = '12121212-1212-4121-8121-121212121212';

        $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders($key))->assertCreated();
        $changed = $this->createPayload();
        $changed['title'] = 'Different CV';
        $this->postJson('/api/v1/cv-profiles', $changed, $this->createHeaders($key))
            ->assertConflict()->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');
        $this->assertDatabaseCount('cv_profiles', 1);
    }

    public function test_invalid_section_replacement_does_not_mutate_the_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders('13131313-1313-4131-8131-131313131313'))
            ->assertCreated()->json('data');

        $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/projects', [
            'projects' => [[
                'name' => 'Project', 'role' => null, 'url' => null, 'start_date' => null, 'end_date' => '2026-01',
                'technologies' => [], 'highlights' => [],
            ]],
        ], ['X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"'])
            ->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_FAILED');
        $this->getJson('/api/v1/cv-profiles/'.$profile['id'])->assertOk()->assertJsonPath('data.revision', 1);
        $this->assertDatabaseCount('cv_versions', 0);
    }

    public function test_section_replacement_requires_the_current_revision_and_preserves_item_ids(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $headers = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'];
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $headers)->json('data');
        $section = ['summary' => 'A trusted source.'];

        $response = $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/summary', $section, [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"',
        ])->assertOk()->assertHeader('ETag', '"2"');
        $response->assertJsonPath('data.summary', 'A trusted source.');
        $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/summary', $section, [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"',
        ])->assertConflict()->assertJsonPath('code', 'PROFILE_UPDATE_CONFLICT');
    }

    public function test_versions_read_the_stored_snapshot_after_profile_edits(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        ])->json('data');
        $version = $this->postJson('/api/v1/cv-profiles/'.$profile['id'].'/versions', ['name' => 'Baseline'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"', 'Idempotency-Key' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        ])->assertCreated()->json('data');

        $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/summary', ['summary' => 'Later edit'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"',
        ])->assertOk();
        $this->getJson('/api/v1/cv-versions/'.$version['id'])->assertOk()->assertJsonPath('data.snapshot.summary', null);
    }

    public function test_repeatable_sections_receive_stable_ids_and_preserve_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders('14141414-1414-4141-8141-141414141414'))
            ->assertCreated()->json('data');
        $projects = [
            ['name' => 'First', 'role' => null, 'url' => null, 'start_date' => null, 'end_date' => null, 'technologies' => ['PHP'], 'highlights' => []],
            ['name' => 'Second', 'role' => null, 'url' => null, 'start_date' => '2025-01', 'end_date' => null, 'technologies' => ['Vue'], 'highlights' => []],
        ];

        $updated = $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/projects', ['projects' => $projects], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"',
        ])->assertOk()->json('data');

        self::assertNotEmpty($updated['projects'][0]['id']);
        self::assertNotEmpty($updated['projects'][1]['id']);
        self::assertSame('First', $updated['projects'][0]['name']);
        self::assertSame('Second', $updated['projects'][1]['name']);
        $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/projects', ['projects' => array_reverse($updated['projects'])], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"2"',
        ])->assertOk()->assertJsonPath('data.projects.0.name', 'Second');
    }

    public function test_version_create_is_idempotent_and_snapshot_hash_matches_canonical_json(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders('15151515-1515-4151-8151-151515151515'))
            ->assertCreated()->json('data');
        $headers = ['X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"', 'Idempotency-Key' => '16161616-1616-4161-8161-161616161616'];
        $first = $this->postJson('/api/v1/cv-profiles/'.$profile['id'].'/versions', ['name' => 'Baseline'], $headers)->assertCreated()->json('data');
        $second = $this->postJson('/api/v1/cv-profiles/'.$profile['id'].'/versions', ['name' => 'Baseline'], $headers)->assertCreated()->json('data');

        self::assertSame($first['id'], $second['id']);
        self::assertSame(hash('sha256', CanonicalJson::encode($first['snapshot'])), DB::table('cv_versions')->where('id', $first['id'])->value('snapshot_hash'));
        $this->assertDatabaseCount('cv_versions', 1);
    }

    public function test_postgres_trigger_rejects_version_mutation(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            self::markTestSkipped('The immutability trigger is PostgreSQL-specific.');
        }

        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->postJson('/api/v1/cv-profiles', $this->createPayload(), $this->createHeaders('17171717-1717-4171-8171-171717171717'))->json('data');
        $version = $this->postJson('/api/v1/cv-profiles/'.$profile['id'].'/versions', ['name' => 'Immutable'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"1"', 'Idempotency-Key' => '18181818-1818-4181-8181-181818181818',
        ])->assertCreated()->json('data');

        $this->expectException(QueryException::class);
        DB::table('cv_versions')->where('id', $version['id'])->update(['name' => 'Changed']);
    }

    /** @return array<string, mixed> */
    private function createPayload(): array
    {
        return [
            'title' => 'Backend CV',
            'personal_information' => [
                'full_name' => 'Nguyen Anh Tu', 'headline' => null, 'email' => null, 'phone' => null,
                'location' => null, 'website_url' => null, 'linkedin_url' => null, 'github_url' => null,
            ],
        ];
    }

    /** @return array<string, string> */
    private function createHeaders(string $key): array
    {
        return ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => $key];
    }
}
