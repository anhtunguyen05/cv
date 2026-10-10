<?php

declare(strict_types=1);

namespace Tests\Feature\Cv;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class Epic3PreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_returns_only_active_compatible_templates_in_a_private_envelope(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/v1/templates');

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['data' => [['id', 'version', 'name', 'status', 'supported_sections', 'preview_metadata']]])
            ->assertJsonPath('data.0.status', 'active');
    }

    public function test_catalog_filters_inactive_and_incompatible_pairs_in_name_order(): void
    {
        DB::table('templates')->insert([
            [
                'id' => (string) Str::ulid(),
                'name' => 'A Compatible Layout',
                'slug' => 'a-compatible-layout',
                'version' => '1.0.0',
                'is_active' => true,
                'description' => 'Compatible',
                'layout_config' => json_encode([]),
                'supported_snapshot_schema_versions' => json_encode(['1.0']),
            ],
            [
                'id' => (string) Str::ulid(),
                'name' => 'B Incompatible Layout',
                'slug' => 'b-incompatible-layout',
                'version' => '1.0.0',
                'is_active' => true,
                'description' => 'Incompatible',
                'layout_config' => json_encode([]),
                'supported_snapshot_schema_versions' => json_encode(['2.0']),
            ],
            [
                'id' => (string) Str::ulid(),
                'name' => 'C Inactive Layout',
                'slug' => 'c-inactive-layout',
                'version' => '1.0.0',
                'is_active' => false,
                'description' => 'Inactive',
                'layout_config' => json_encode([]),
                'supported_snapshot_schema_versions' => json_encode(['1.0']),
            ],
        ]);

        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/v1/templates')->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();

        self::assertSame(['A Compatible Layout', 'Clean Modern'], $names);
    }

    public function test_preview_uses_the_owned_immutable_version_snapshot_after_profile_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->createProfile();
        $version = $this->createVersion($profile['id'], 'Baseline');
        $template = DB::table('templates')->where('is_active', true)->first();

        $this->putJson('/api/v1/cv-profiles/'.$profile['id'].'/summary', ['summary' => 'Later edit'], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
        ])->assertOk();

        $this->getJson('/api/v1/cv-versions/'.$version['id'].'/preview?template_id='.$template->id.'&template_version='.$template->version)
            ->assertOk()
            ->assertJsonPath('data.cv_version_id', $version['id'])
            ->assertJsonPath('data.template_version', $template->version)
            ->assertJsonPath('data.version_name', 'Baseline')
            ->assertJsonPath('data.sections.0.key', 'identity')
            ->assertJsonCount(1, 'data.sections');
    }

    public function test_foreign_version_is_not_disclosed(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->createProfile();
        $version = $this->createVersion($profile['id'], 'Private');
        $template = DB::table('templates')->where('is_active', true)->first();

        auth()->guard('web')->logout();
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $other = User::factory()->create();
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/cv-versions/'.$version['id'].'/preview?template_id='.$template->id.'&template_version='.$template->version)
            ->assertNotFound()
            ->assertJsonPath('code', 'CV_VERSION_NOT_FOUND');
    }

    public function test_unavailable_template_and_invalid_source_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->createProfile();
        $version = $this->createVersion($profile['id'], 'Version');
        $template = DB::table('templates')->where('is_active', true)->first();

        DB::table('templates')->where('id', $template->id)->update(['is_active' => false]);
        $this->getJson('/api/v1/cv-versions/'.$version['id'].'/preview?template_id='.$template->id.'&template_version='.$template->version)
            ->assertConflict()
            ->assertJsonPath('code', 'TEMPLATE_UNAVAILABLE');

        $this->getJson('/api/v1/cv-versions/'.$version['id'].'/preview?template_id=bad&template_version=')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PREVIEW_SOURCE_INVALID');
    }

    public function test_preview_rejects_unsafe_saved_links_before_projection(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $profile = $this->createProfile();
        $snapshot = [
            'title' => 'Epic 3 CV',
            'personal_information' => [
                'full_name' => 'Epic Three User',
                'headline' => null,
                'email' => null,
                'phone' => null,
                'location' => null,
                'website_url' => null,
                'linkedin_url' => null,
                'github_url' => null,
            ],
            'summary' => null,
            'skills' => [],
            'education' => [],
            'experience' => [],
            'projects' => [],
            'certificates' => [[
                'id' => (string) Str::ulid(),
                'name' => 'Unsafe certificate',
                'issuer' => 'Issuer',
                'issued_on' => null,
                'expires_on' => null,
                'credential_url' => 'javascript:alert(1)',
            ]],
            'languages' => [],
            'activities' => [],
        ];
        $versionId = (string) Str::ulid();
        DB::table('cv_versions')->insert([
            'id' => $versionId,
            'user_id' => $user->getKey(),
            'source_profile_id' => $profile['id'],
            'source_profile_revision' => 1,
            'name' => 'Unsafe source',
            'snapshot_schema_version' => '1.0',
            'snapshot' => json_encode($snapshot),
            'snapshot_hash' => hash('sha256', json_encode($snapshot)),
            'created_at' => now(),
        ]);
        $template = DB::table('templates')->where('is_active', true)->first();

        $this->getJson('/api/v1/cv-versions/'.$versionId.'/preview?template_id='.$template->id.'&template_version='.$template->version)
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PREVIEW_SOURCE_INVALID');
    }

    /** @return array<string, mixed> */
    private function createProfile(): array
    {
        return $this->postJson('/api/v1/cv-profiles', [
            'title' => 'Epic 3 CV',
            'personal_information' => [
                'full_name' => 'Epic Three User',
                'headline' => null,
                'email' => null,
                'phone' => null,
                'location' => null,
                'website_url' => null,
                'linkedin_url' => null,
                'github_url' => null,
            ],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ])->assertCreated()->json('data');
    }

    /** @return array<string, mixed> */
    private function createVersion(string $profileId, string $name): array
    {
        return $this->postJson('/api/v1/cv-profiles/'.$profileId.'/versions', ['name' => $name], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"1"',
            'Idempotency-Key' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ])->assertCreated()->json('data');
    }
}
