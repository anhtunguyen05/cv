<?php

declare(strict_types=1);

namespace Tests\Feature\Evidence;

use App\Application\Cv\ProfileDocument;
use App\Models\CvProfile;
use App\Models\CvVersion;
use App\Models\EvidenceInterview;
use App\Models\JobDescription;
use App\Models\JobDescriptionAnalysis;
use App\Models\JobDescriptionRevision;
use App\Models\MatchReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StartEvidenceInterviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owned_report_starts_one_source_pinned_interview_with_ordered_questions(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $snapshotBefore = $context['version']->snapshot;

        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $response = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '11111111-1111-4111-8111-111111111111',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.match_report_id', $context['report']->getKey())
            ->assertJsonPath('data.cv_version_id', $context['version']->getKey())
            ->assertJsonPath('data.job_description_id', $context['job']->getKey())
            ->assertJsonPath('data.job_description_revision_id', $context['revision']->getKey())
            ->assertJsonPath('data.analysis_id', $context['analysis']->getKey())
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.question_set_version', '1.0');
        self::assertCount(5, $response->json('data.areas'));
        self::assertSame(
            ['missing-one', 'missing-two', 'weak-one', 'weak-two', 'weak-three'],
            array_column($response->json('data.areas'), 'signal_id'),
        );
        self::assertSame(
            array_column($response->json('data.areas'), 'signal_id'),
            array_column($response->json('data.questions'), 'area_signal_id'),
        );
        self::assertSame($snapshotBefore, $context['version']->fresh()->snapshot);
        self::assertDatabaseCount('evidence_interviews', 1);
    }

    public function test_same_idempotency_key_replays_without_creating_a_second_session(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $headers = [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '22222222-2222-4222-8222-222222222222',
        ];
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $first = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], $headers)->assertCreated();
        $replay = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], $headers)->assertCreated();

        self::assertSame($first->getContent(), $replay->getContent());
        self::assertDatabaseCount('evidence_interviews', 1);
    }

    public function test_active_interview_is_reused_when_a_retry_uses_a_new_key(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $first = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '77777777-7777-4777-8777-777777777777',
        ])->assertCreated();
        $second = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '88888888-8888-4888-8888-888888888888',
        ])->assertCreated();

        self::assertSame($first->json('data.id'), $second->json('data.id'));
        self::assertDatabaseCount('evidence_interviews', 1);
    }

    public function test_changed_idempotency_fingerprint_returns_a_stable_conflict(): void
    {
        $user = User::factory()->create();
        $firstContext = $this->createReport($user, true);
        $secondContext = $this->createReport($user, true);
        $headers = [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '99999999-9999-4999-8999-999999999999',
        ];
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $this->postJson('/api/v1/match-reports/'.$firstContext['report']->getKey().'/evidence-interviews', [], $headers)
            ->assertCreated();
        $this->postJson('/api/v1/match-reports/'.$secondContext['report']->getKey().'/evidence-interviews', [], $headers)
            ->assertStatus(409)
            ->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED');
    }

    public function test_non_empty_start_body_is_rejected_without_creating_a_session(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [
            'areas' => ['client-controlled'],
        ], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');

        self::assertDatabaseCount('evidence_interviews', 0);
    }

    public function test_report_without_unresolved_areas_returns_not_needed_without_a_session(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, false);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);

        $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '33333333-3333-4333-8333-333333333333',
        ])->assertStatus(409)->assertJsonPath('code', 'INTERVIEW_NOT_NEEDED');

        self::assertDatabaseCount('evidence_interviews', 0);
    }

    public function test_foreign_and_malformed_reports_are_non_disclosing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $context = $this->createReport($owner, true);
        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);

        $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '44444444-4444-4444-8444-444444444444',
        ])->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
        $this->postJson('/api/v1/match-reports/not-a-ulid/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '55555555-5555-4555-8555-555555555555',
        ])->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

        self::assertDatabaseCount('evidence_interviews', 0);
    }

    public function test_foreign_interview_reads_as_not_found(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $context = $this->createReport($owner, true);
        $this->actingAs($owner, 'web')->withSession(['_token' => 'csrf-token']);
        $interview = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => '66666666-6666-4666-8666-666666666666',
        ])->assertCreated()->json('data');

        $this->actingAs($other, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/evidence-interviews/'.$interview['id'])
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    public function test_owned_interview_can_be_read_with_the_immutable_snapshot(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $interview = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ])->assertCreated()->json('data');

        $this->getJson('/api/v1/evidence-interviews/'.$interview['id'])
            ->assertOk()
            ->assertJsonPath('data.id', $interview['id'])
            ->assertJsonPath('data.areas.0.signal_id', 'missing-one')
            ->assertJsonPath('data.questions.0.area_signal_id', 'missing-one');
    }

    public function test_completed_interview_cannot_be_started_again(): void
    {
        $user = User::factory()->create();
        $context = $this->createReport($user, true);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $interview = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        ])->assertCreated()->json('data');
        EvidenceInterview::query()->whereKey($interview['id'])->update(['status' => 'completed']);

        $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        ])->assertStatus(409)->assertJsonPath('code', 'EVIDENCE_SESSION_CONFLICT');

        self::assertDatabaseCount('evidence_interviews', 1);
    }

    /** @return array{report:MatchReport,version:CvVersion,job:JobDescription,revision:JobDescriptionRevision,analysis:JobDescriptionAnalysis} */
    private function createReport(User $user, bool $withAreas): array
    {
        $profile = CvProfile::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'Primary',
            'normalized_title' => 'Primary', 'revision' => 1, 'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Evidence User'),
        ]);
        $version = CvVersion::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1, 'name' => 'Baseline', 'snapshot_schema_version' => '1.0',
            'snapshot' => $profile->document, 'snapshot_hash' => hash('sha256', json_encode($profile->document)), 'created_at' => now(),
        ]);
        $job = JobDescription::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'Engineer', 'company' => 'Example',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $revision = JobDescriptionRevision::create([
            'id' => ProfileDocument::id(), 'job_description_id' => $job->getKey(), 'user_id' => $user->getKey(),
            'revision_number' => 1, 'title' => 'Engineer', 'company' => 'Example', 'raw_text' => 'Build reliable software.', 'created_at' => now(),
        ]);
        $job->forceFill(['current_revision_id' => $revision->getKey()])->save();
        $analysis = JobDescriptionAnalysis::create([
            'id' => ProfileDocument::id(), 'job_description_revision_id' => $revision->getKey(), 'user_id' => $user->getKey(),
            'status' => 'succeeded', 'analysis_rule_version' => '1.0.0', 'analysis_schema_version' => '1.0.0',
            'required_skills' => [], 'nice_to_have_skills' => [], 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $makeSignal = static fn (string $id, string $label, string $level): array => [
            'signal_id' => $id, 'label' => $label, 'importance' => 'required', 'evidence_level' => $level, 'source_references' => [],
        ];
        $report = MatchReport::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'cv_version_id' => $version->getKey(),
            'job_description_id' => $job->getKey(), 'job_description_revision_id' => $revision->getKey(), 'analysis_id' => $analysis->getKey(),
            'analysis_rule_version' => '1.0.0', 'matching_rule_version' => '1.0.0', 'report_schema_version' => '1.0.0',
            'overall_score' => 50, 'matched_skills' => [],
            'missing_skills' => $withAreas ? [$makeSignal('missing-one', 'Missing One', 'missing'), $makeSignal('missing-two', 'Missing Two', 'missing')] : [],
            'weak_evidence' => $withAreas ? [$makeSignal('weak-one', 'Weak One', 'weak'), $makeSignal('weak-two', 'Weak Two', 'weak'), $makeSignal('weak-three', 'Weak Three', 'weak'), $makeSignal('weak-four', 'Weak Four', 'weak')] : [],
            'recommendations' => [], 'created_at' => now(),
        ]);

        return compact('report', 'version', 'job', 'revision', 'analysis');
    }
}
