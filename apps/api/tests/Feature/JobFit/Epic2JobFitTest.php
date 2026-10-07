<?php

declare(strict_types=1);

namespace Tests\Feature\JobFit;

use App\Application\Cv\ProfileDocument;
use App\Models\CvProfile;
use App\Models\CvVersion;
use App\Models\MatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

final class Epic2JobFitTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ThrottleRequests::shouldHashKeys(true);
        parent::tearDown();
    }

    public function test_job_description_analysis_and_match_report_pin_sources_and_replay(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $jdHeaders = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '11111111-1111-4111-8111-111111111111'];
        $payload = [
            'company' => 'Example', 'role' => 'Frontend Engineer',
            'raw_text' => "Responsibilities:\n- Build Vue 3 interfaces.\nRequirements:\n- TypeScript and REST API.\nNice to have:\n- Docker.",
        ];
        $created = $this->postJson('/api/v1/job-descriptions', $payload, $jdHeaders)->assertCreated();
        $replayed = $this->postJson('/api/v1/job-descriptions', $payload, $jdHeaders)->assertCreated();
        self::assertSame($created->getContent(), $replayed->getContent());
        $this->postJson('/api/v1/job-descriptions', [...$payload, 'role' => 'Changed role'], $jdHeaders)
            ->assertStatus(409)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');
        $jd = $created->json('data');
        $this->getJson('/api/v1/job-descriptions?per_page=20')
            ->assertOk()
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure(['data', 'meta' => ['page', 'per_page', 'total'], 'links' => ['self', 'first', 'last', 'previous', 'next']]);

        $analysis = $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '22222222-2222-4222-8222-222222222222',
        ])->assertOk()->json('data');
        self::assertSame('1.0.0', $analysis['analysis_schema_version']);
        self::assertSame('1.0.0', $analysis['analysis_rule_version']);
        self::assertSame($jd['current_revision']['id'], $analysis['job_description_revision_id']);
        self::assertSame('detected', $analysis['signals']['required_skills']['state']);
        self::assertSame(['rest-api', 'typescript'], array_column($analysis['signals']['required_skills']['items'], 'signal_id'));
        self::assertSame('detected', $analysis['signals']['nice_to_have_skills']['state']);
        self::assertSame(['docker'], array_column($analysis['signals']['nice_to_have_skills']['items'], 'signal_id'));

        $profile = CvProfile::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'Primary',
            'normalized_title' => 'Primary', 'revision' => 1, 'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Example User'),
        ]);
        $snapshot = $profile->document;
        $snapshot['projects'] = [[
            'id' => ProfileDocument::id(), 'name' => 'Web app', 'role' => 'Engineer', 'highlights' => ['Built Vue 3 with TypeScript'],
            'technologies' => ['Vue 3', 'TypeScript'],
        ]];
        $version = CvVersion::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1, 'name' => 'Baseline', 'snapshot_schema_version' => '1.0',
            'snapshot' => $snapshot, 'snapshot_hash' => hash('sha256', json_encode($snapshot)), 'created_at' => now(),
        ]);
        $matchHeaders = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '33333333-3333-4333-8333-333333333333'];
        $report = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], $matchHeaders)->assertCreated();
        $reportReplay = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], $matchHeaders)->assertCreated();
        self::assertSame($report->getContent(), $reportReplay->getContent());
        $reportData = $report->json('data');
        self::assertSame($analysis['id'], $reportData['analysis_id']);
        self::assertSame('1.0.0', $reportData['matching_rule_version']);
        self::assertGreaterThanOrEqual(0, $reportData['overall_score']);
        self::assertLessThanOrEqual(100, $reportData['overall_score']);
        self::assertNotEmpty($reportData['matched_skills']);
        self::assertNotEmpty($reportData['missing_skills']);
        self::assertNotEmpty($reportData['matched_skills'][0]['source_references']);
        self::assertIsArray($reportData['weak_evidence']);
        self::assertIsArray($reportData['recommendations']);
        $this->getJson('/api/v1/match-reports')
            ->assertOk()->assertJsonPath('meta.page', 1)->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $reportData['id']);
    }

    public function test_stale_update_and_foreign_lookup_are_safe(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($owner, 'web')->withSession(['_token' => 'csrf-token']);
        $jd = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'A short but valid source.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '44444444-4444-4444-8444-444444444444',
        ])->assertCreated()->json('data');
        $this->app['auth']->forgetGuards();
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/job-descriptions/'.$jd['id'])->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    public function test_revisions_use_if_match_and_deleted_sources_are_non_disclosing(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $created = $this->postJson('/api/v1/job-descriptions', [
            'company' => 'Example',
            'role' => 'Backend Engineer',
            'raw_text' => 'Build and maintain a reliable API platform.',
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '55555555-5555-4555-8555-555555555555',
        ])->assertCreated()->json('data');
        $firstRevision = $created['current_revision']['id'];

        $updated = $this->patchJson('/api/v1/job-descriptions/'.$created['id'], [
            'raw_text' => 'Build and maintain a reliable API platform with Laravel.',
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$firstRevision.'"',
            'Idempotency-Key' => '66666666-6666-4666-8666-666666666666',
        ])->assertOk()->json('data');
        self::assertSame(2, $updated['current_revision']['revision_number']);

        $this->patchJson('/api/v1/job-descriptions/'.$created['id'], [
            'raw_text' => 'A stale write must not replace the current revision.',
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$firstRevision.'"',
            'Idempotency-Key' => '77777777-7777-4777-8777-777777777777',
        ])->assertStatus(409)->assertJsonPath('code', 'JOB_DESCRIPTION_UPDATE_CONFLICT');

        $currentRevision = $updated['current_revision']['id'];
        $this->patchJson('/api/v1/job-descriptions/'.$created['id'], [
            'raw_text' => $updated['current_revision']['raw_text'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$currentRevision.'"',
            'Idempotency-Key' => '88888888-8888-4888-8888-888888888888',
        ])->assertStatus(422)->assertJsonPath('code', 'JOB_DESCRIPTION_NO_CHANGES');

        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$currentRevision.'"',
            'Idempotency-Key' => '99999999-9999-4999-8999-999999999999',
        ])->assertNoContent();

        $this->getJson('/api/v1/job-descriptions/'.$created['id'])
            ->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->getJson('/api/v1/job-descriptions')
            ->assertOk()->assertJsonPath('meta.total', 0)->assertJsonPath('data', []);
        $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ])->assertStatus(409)->assertJsonPath('code', 'JOB_DESCRIPTION_DELETED');
    }

    public function test_match_requires_current_analysis_and_keeps_deleted_source_history(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $jd = $this->postJson('/api/v1/job-descriptions', [
            'raw_text' => "Requirements:\n- Vue and TypeScript",
            'role' => 'Frontend Engineer',
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ])->assertCreated()->json('data');

        $profile = CvProfile::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'Primary',
            'normalized_title' => 'Primary', 'revision' => 1, 'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Example User'),
        ]);
        $version = CvVersion::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1, 'name' => 'Baseline', 'snapshot_schema_version' => '1.0',
            'snapshot' => $profile->document, 'snapshot_hash' => hash('sha256', json_encode($profile->document)),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        ])->assertStatus(409)->assertJsonPath('code', 'JOB_DESCRIPTION_ANALYSIS_REQUIRED');

        $analysis = $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        ])->assertOk()->json('data');
        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
            'analysis_id' => ProfileDocument::id(),
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
        ])->assertStatus(409)->assertJsonPath('code', 'MATCH_SOURCE_CONFLICT');

        $updated = $this->patchJson('/api/v1/job-descriptions/'.$jd['id'], [
            'raw_text' => "Requirements:\n- Vue, TypeScript, and Laravel",
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$jd['current_revision']['id'].'"',
            'Idempotency-Key' => 'abababab-abab-4aba-8aba-abababababab',
        ])->assertOk()->json('data');
        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'acacacac-acac-4aca-8aca-acacacacacac',
        ])->assertStatus(409)->assertJsonPath('code', 'JOB_DESCRIPTION_ANALYSIS_REQUIRED');
        $analysis = $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => 'adadadad-adad-4ada-8ada-adadadadadad',
        ])->assertOk()->json('data');

        $report = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        ])->assertCreated()->json('data');

        $other = User::factory()->create();
        $this->app['auth']->forgetGuards();
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses/'.$analysis['id'])
            ->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->getJson('/api/v1/match-reports/'.$report['id'])
            ->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->getJson('/api/v1/match-reports')->assertOk()->assertJsonPath('meta.total', 0);
        $this->app['auth']->forgetGuards();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
    
        $this->deleteJson('/api/v1/job-descriptions/'.$jd['id'], [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'If-Match' => '"'.$updated['current_revision']['id'].'"',
            'Idempotency-Key' => '12121212-1212-4121-8121-121212121212',
        ])->assertNoContent();
        $this->getJson('/api/v1/match-reports/'.$report['id'])
            ->assertOk()->assertJsonPath('data.source_summary.source_deleted', true);
    }

    public function test_job_description_validation_rejects_blank_and_oversized_source(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $this->postJson('/api/v1/job-descriptions', ['raw_text' => " \n\t "], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '13131313-1313-4131-8131-131313131313',
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');
        $this->postJson('/api/v1/job-descriptions', ['raw_text' => str_repeat('x', 204801)], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '14141414-1414-4141-8141-141414141414',
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');
        $this->getJson('/api/v1/job-descriptions?page=999999999999999999999999')
            ->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');
    }

    public function test_analysis_update_delete_replay_and_stale_delete_are_idempotent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $created = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Requirements: Vue.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '15151515-1515-4151-8151-151515151515',
        ])->assertCreated()->json('data');
        $analysisHeaders = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '16161616-1616-4161-8161-161616161616'];
        $analysis = $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], $analysisHeaders)->assertOk();
        $analysisReplay = $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], $analysisHeaders)->assertOk();
        self::assertSame($analysis->getContent(), $analysisReplay->getContent());
        $freshKeyAnalysis = $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '16161616-1616-4161-8161-161616161617',
        ])->assertOk();
        self::assertSame($analysis->json('data.id'), $freshKeyAnalysis->json('data.id'));
        $this->assertDatabaseCount('job_description_analyses', 1);

        $updated = $this->patchJson('/api/v1/job-descriptions/'.$created['id'], ['raw_text' => 'Requirements: Vue and TypeScript.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '17171717-1717-4171-8171-171717171717',
        ])->assertOk();
        $updatedReplay = $this->patchJson('/api/v1/job-descriptions/'.$created['id'], ['raw_text' => 'Requirements: Vue and TypeScript.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '17171717-1717-4171-8171-171717171717',
        ])->assertOk();
        self::assertSame($updated->getContent(), $updatedReplay->getContent());
        $this->patchJson('/api/v1/job-descriptions/'.$created['id'], ['raw_text' => 'A different payload must not replay.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '17171717-1717-4171-8171-171717171717',
        ])->assertStatus(409)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');

        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '18181818-1818-4181-8181-181818181818',
        ])->assertStatus(409)->assertJsonPath('code', 'JOB_DESCRIPTION_UPDATE_CONFLICT');
        $deleteHeaders = [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$updated->json('data.current_revision.id').'"', 'Idempotency-Key' => '19191919-1919-4191-8191-191919191919',
        ];
        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], $deleteHeaders)->assertNoContent();
        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], $deleteHeaders)->assertNoContent();
        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '19191919-1919-4191-8191-191919191919',
        ])->assertStatus(409)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');
    }

    public function test_analysis_replay_conflict_and_match_replay_survive_source_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $created = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Requirements: Vue.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '20202020-2020-4020-8020-202020202020',
        ])->assertCreated()->json('data');
        $second = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Requirements: TypeScript.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '21212121-2121-4121-8121-212121212121',
        ])->assertCreated()->json('data');

        $analysisHeaders = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '22222222-2222-4222-8222-222222222222'];
        $analysis = $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], $analysisHeaders)->assertOk();
        $updated = $this->patchJson('/api/v1/job-descriptions/'.$created['id'], ['raw_text' => 'Requirements: Laravel.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$created['current_revision']['id'].'"', 'Idempotency-Key' => '23232323-2323-4232-8232-232323232323',
        ])->assertOk();
        $this->postJson('/api/v1/job-descriptions/'.$second['id'].'/analyses', [], $analysisHeaders)
            ->assertStatus(409)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');
        $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], $analysisHeaders)
            ->assertOk()->assertExactJson($analysis->json());

        $version = $this->createCvVersion($user, 'Replay source');
        $this->postJson('/api/v1/job-descriptions/'.$created['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '24242424-2424-4242-8242-242424242424',
        ])->assertOk();
        $matchHeaders = ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '25252525-2525-4252-8252-252525252525'];
        $report = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $created['id'],
        ], $matchHeaders)->assertCreated();
        CvProfile::query()->whereKey($version->source_profile_id)->update([
            'document' => array_replace($version->snapshot, ['summary' => 'Changed after version pinning']),
        ]);
        $this->getJson('/api/v1/match-reports/'.$report->json('data.id'))
            ->assertOk()->assertExactJson($report->json());
        $this->deleteJson('/api/v1/job-descriptions/'.$created['id'], [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'If-Match' => '"'.$updated->json('data.current_revision.id').'"', 'Idempotency-Key' => '26262626-2626-4262-8262-262626262626',
        ])->assertNoContent();
        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $created['id'],
        ], $matchHeaders)->assertCreated()->assertExactJson($report->json());
    }

    public function test_mixed_owner_derivation_is_non_disclosing_and_writes_nothing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownerVersion = $this->createCvVersion($owner, 'Owner');
        $otherVersion = $this->createCvVersion($other, 'Other');
        $ownerJd = $this->createJobDescription($owner, 'Owner JD', '30303030-3030-4030-8030-303030303030');
        $otherJd = $this->createJobDescription($other, 'Other JD', '31313131-3131-4131-8131-313131313131');
        $this->actingAs($owner, 'web')->withSession(['_token' => 'csrf-token']);

        $before = (int) \DB::table('match_reports')->count();
        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $otherVersion->getKey(), 'job_description_id' => $ownerJd['id'],
        ], ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '32323232-3232-4232-8232-323232323232'])
            ->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $ownerVersion->getKey(), 'job_description_id' => $otherJd['id'],
        ], ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '33333333-3333-4333-8333-333333333333'])
            ->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        self::assertSame($before, (int) \DB::table('match_reports')->count());
    }

    public function test_derived_work_is_throttled_without_creating_partial_analysis(): void
    {
        ThrottleRequests::shouldHashKeys(false);
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $jd = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Requirements: Vue.'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '34343434-3434-4434-8434-343434343434',
        ])->assertCreated()->json('data');

        for ($index = 0; $index < 10; $index++) {
            $key = sprintf('35%06d-3535-4353-8353-353535353535', $index);
            $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
                'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => $key,
            ])->assertOk();
        }
        $response = $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '36363636-3636-4363-8363-363636363636',
        ]);
        $response->assertTooManyRequests()->assertJsonPath('code', 'THROTTLED')->assertHeader('Retry-After');
        $this->assertDatabaseCount('job_description_analyses', 1);
    }

    public function test_corrupt_persisted_match_report_is_rejected_by_detail_and_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $jd = $this->createJobDescription($user, 'Corruptible source', '37373737-3737-4373-8373-373737373737');
        $this->postJson('/api/v1/job-descriptions/'.$jd['id'].'/analyses', [], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '38383838-3838-4383-8383-383838383838',
        ])->assertOk();
        $version = $this->createCvVersion($user, 'Corruptible version');
        $report = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jd['id'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '39393939-3939-4393-8393-393939393939',
        ])->assertCreated()->json('data');
        MatchReport::query()->whereKey($report['id'])->update(['report_schema_version' => '0.0.0']);

        $this->getJson('/api/v1/match-reports/'.$report['id'])
            ->assertStatus(500)->assertJsonPath('code', 'DERIVED_RESULT_INVALID');
        $this->getJson('/api/v1/match-reports')
            ->assertStatus(500)->assertJsonPath('code', 'DERIVED_RESULT_INVALID');
    }

    public function test_job_description_mutation_route_is_throttled_without_extra_state(): void
    {
        ThrottleRequests::shouldHashKeys(false);
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        for ($index = 0; $index < 30; $index++) {
            $key = sprintf('40%06d-4040-4404-8404-404040404040', $index);
            $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Mutation '.$index], [
                'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => $key,
            ])->assertCreated();
        }
        $response = $this->postJson('/api/v1/job-descriptions', ['raw_text' => 'Blocked mutation'], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => '41414141-4141-4414-8414-414141414141',
        ]);
        $response->assertTooManyRequests()->assertJsonPath('code', 'THROTTLED')->assertHeader('Retry-After');
        $this->getJson('/api/v1/job-descriptions')->assertOk()->assertJsonPath('meta.total', 30);
    }

    /** @return array{id:string,current_revision:array<string,mixed>} */
    private function createJobDescription(User $user, string $source, string $key): array
    {
        $this->app['auth']->forgetGuards();
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        return $this->postJson('/api/v1/job-descriptions', ['raw_text' => $source], [
            'X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => $key,
        ])->assertCreated()->json('data');
    }

    private function createCvVersion(User $user, string $name): CvVersion
    {
        $profile = CvProfile::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => $name,
            'normalized_title' => $name, 'revision' => 1, 'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Corpus Candidate'),
        ]);

        return CvVersion::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1, 'name' => $name, 'snapshot_schema_version' => '1.0',
            'snapshot' => $profile->document, 'snapshot_hash' => hash('sha256', json_encode($profile->document)), 'created_at' => now(),
        ]);
    }
}
