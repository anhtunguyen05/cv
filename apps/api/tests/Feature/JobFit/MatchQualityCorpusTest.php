<?php

declare(strict_types=1);

namespace Tests\Feature\JobFit;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\ProfileDocument;
use App\Application\JobFit\JobDescriptionAnalyzer;
use App\Application\JobFit\MatchService;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvProfile;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescription;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescriptionAnalysis;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\JobDescriptionRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MatchQualityCorpusTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewed_and_held_out_match_corpus_is_executable_and_replayable(): void
    {
        $fixture = json_decode(
            (string) file_get_contents($this->fixturePath('match-report-v1.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $reviewed = $fixture['examples'] ?? [];
        $heldOut = $fixture['held_out_counterexamples'] ?? [];

        self::assertSame(MatchService::REPORT_SCHEMA_VERSION, $fixture['report_schema_version'] ?? null);
        self::assertSame(MatchService::RULE_VERSION, $fixture['matching_rule_version'] ?? null);
        self::assertSame(JobDescriptionAnalyzer::RULE_VERSION, $fixture['analysis_rule_version'] ?? null);
        self::assertGreaterThanOrEqual(40, count($reviewed));
        self::assertGreaterThanOrEqual(12, count($heldOut));
        self::assertCount(count($reviewed), array_unique(array_column($reviewed, 'id')));

        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'corpus-csrf-token']);

        foreach ([...$reviewed, ...$heldOut] as $case) {
            $this->assertCase($user, $case);
        }
    }

    public function test_persisted_optional_score_and_http_projection_match_the_report_contract(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'corpus-csrf-token']);
        $profile = CvProfile::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'HTTP corpus',
            'normalized_title' => 'HTTP corpus', 'revision' => 1, 'schema_version' => '1.0',
            'document' => ProfileDocument::empty('HTTP Candidate'),
        ]);
        $snapshot = array_replace_recursive($profile->document, [
            'projects' => [['id' => ProfileDocument::id(), 'name' => 'Frontend', 'highlights' => ['JavaScript']]],
        ]);
        $version = CvVersion::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1, 'name' => 'HTTP corpus version', 'snapshot_schema_version' => '1.0',
            'snapshot' => $snapshot, 'snapshot_hash' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR)), 'created_at' => now(),
        ]);
        $jobDescription = JobDescription::create([
            'id' => ProfileDocument::id(), 'user_id' => $user->getKey(), 'title' => 'Frontend Engineer', 'company' => 'HTTP Co',
        ]);
        $revision = JobDescriptionRevision::create([
            'id' => ProfileDocument::id(), 'job_description_id' => $jobDescription->getKey(), 'user_id' => $user->getKey(),
            'revision_number' => 1, 'title' => 'Frontend Engineer', 'company' => 'HTTP Co',
            'raw_text' => 'Requirements: JavaScript', 'created_at' => now(),
        ]);
        $jobDescription->forceFill(['current_revision_id' => $revision->getKey()])->save();
        $analysis = JobDescriptionAnalysis::create([
            'id' => ProfileDocument::id(), 'job_description_revision_id' => $revision->getKey(), 'user_id' => $user->getKey(),
            'status' => 'succeeded', 'analysis_schema_version' => JobDescriptionAnalyzer::SCHEMA_VERSION,
            'analysis_rule_version' => JobDescriptionAnalyzer::RULE_VERSION,
            'extracted_role' => ['state' => 'detected', 'value' => 'Frontend Engineer'],
            'required_skills' => $this->skillList(['javascript']), 'nice_to_have_skills' => ['state' => 'absent', 'items' => []],
            'responsibilities' => ['state' => 'absent', 'items' => []], 'keywords' => ['state' => 'absent', 'items' => []],
            'seniority' => null, 'seniority_state' => 'absent', 'soft_skills' => ['state' => 'absent', 'items' => []],
            'domain_context' => ['state' => 'absent', 'items' => []], 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/match-reports', [
            'cv_version_id' => $version->getKey(), 'job_description_id' => $jobDescription->getKey(),
        ], ['X-CSRF-TOKEN' => 'corpus-csrf-token', 'Idempotency-Key' => (string) Str::uuid()])
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id', 'cv_version_id', 'job_description_id', 'job_description_revision_id', 'analysis_id',
                'analysis_rule_version', 'matching_rule_version', 'report_schema_version', 'overall_score',
                'matched_skills', 'missing_skills', 'weak_evidence', 'recommendations', 'source_summary', 'created_at',
            ]]);
        $report = $response->json('data');
        self::assertSame((string) $analysis->getKey(), $report['analysis_id']);
        self::assertSame(93.75, (float) $report['overall_score']);
        self::assertSame($response->getContent(), $this->getJson('/api/v1/match-reports/'.$report['id'])->assertOk()->getContent());
    }

    /** @param array<string,mixed> $case */
    private function assertCase(User $user, array $case): void
    {
        $analysisSignals = $case['analysis'] ?? [];
        $profile = CvProfile::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => 'Corpus '.$case['id'],
            'normalized_title' => 'Corpus '.$case['id'],
            'revision' => 1,
            'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Corpus Candidate'),
        ]);
        $snapshot = array_replace_recursive($profile->document, $case['snapshot'] ?? []);
        $version = CvVersion::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1,
            'name' => 'Version '.$case['id'],
            'snapshot_schema_version' => '1.0',
            'snapshot' => $snapshot,
            'snapshot_hash' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR)),
            'created_at' => now(),
        ]);

        $jobDescription = JobDescription::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => 'Corpus Role',
            'company' => 'Corpus Company',
        ]);
        $revision = JobDescriptionRevision::create([
            'id' => ProfileDocument::id(),
            'job_description_id' => $jobDescription->getKey(),
            'user_id' => $user->getKey(),
            'revision_number' => 1,
            'title' => 'Corpus Role',
            'company' => 'Corpus Company',
            'raw_text' => 'Corpus source '.$case['id'],
            'created_at' => now(),
        ]);
        $jobDescription->forceFill(['current_revision_id' => $revision->getKey()])->save();
        $analysis = JobDescriptionAnalysis::create([
            'id' => ProfileDocument::id(),
            'job_description_revision_id' => $revision->getKey(),
            'user_id' => $user->getKey(),
            'status' => 'succeeded',
            'analysis_schema_version' => JobDescriptionAnalyzer::SCHEMA_VERSION,
            'analysis_rule_version' => '1.0.0',
            'extracted_role' => array_key_exists('role', $analysisSignals)
                ? ['state' => 'detected', 'value' => $analysisSignals['role']]
                : ['state' => 'absent', 'value' => null],
            'required_skills' => $this->skillList($analysisSignals['required'] ?? []),
            'nice_to_have_skills' => $this->skillList($analysisSignals['preferred'] ?? []),
            'responsibilities' => ['state' => 'absent', 'items' => []],
            'keywords' => ['state' => 'absent', 'items' => []],
            'seniority' => $analysisSignals['seniority'] ?? null,
            'seniority_state' => array_key_exists('seniority', $analysisSignals) ? 'detected' : 'absent',
            'soft_skills' => ['state' => 'absent', 'items' => []],
            'domain_context' => array_key_exists('domain_context', $analysisSignals)
                ? ['state' => 'detected', 'items' => $analysisSignals['domain_context']]
                : ['state' => 'absent', 'items' => []],
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $headers = [
            'X-CSRF-TOKEN' => 'corpus-csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ];
        $payload = [
            'cv_version_id' => $version->getKey(),
            'job_description_id' => $jobDescription->getKey(),
        ];
        $service = app(MatchService::class);
        $result = $service->create(new AuthenticatedUser((int) $user->getKey()), $payload, $headers['Idempotency-Key'], '/api/v1/match-reports');
        self::assertSame(201, $result['status'], $case['id'].' status');
        $report = $result['body']['data'];
        $expected = $case['expected'];

        self::assertSame($expected['matched'], array_column($report['matched_skills'], 'signal_id'), $case['id'].' matched');
        self::assertSame($expected['missing'], array_column($report['missing_skills'], 'signal_id'), $case['id'].' missing');
        self::assertSame($expected['weak'], array_column($report['weak_evidence'], 'signal_id'), $case['id'].' weak');
        self::assertEqualsWithDelta((float) $expected['score'], (float) $report['overall_score'], 0.01, $case['id'].' score');
        self::assertSame((string) $analysis->getKey(), $report['analysis_id']);
        self::assertSame(MatchService::REPORT_SCHEMA_VERSION, $report['report_schema_version']);
        self::assertSame(MatchService::RULE_VERSION, $report['matching_rule_version']);
        self::assertSame(JobDescriptionAnalyzer::RULE_VERSION, $report['analysis_rule_version']);
        self::assertSame((string) $revision->getKey(), $report['job_description_revision_id']);
        self::assertSame((string) $version->getKey(), $report['cv_version_id']);
        self::assertSame([
            'id', 'cv_version_id', 'job_description_id', 'job_description_revision_id',
            'analysis_id', 'analysis_rule_version', 'matching_rule_version',
            'report_schema_version', 'overall_score', 'matched_skills', 'missing_skills',
            'weak_evidence', 'recommendations', 'source_summary', 'created_at',
        ], array_keys($report));
        foreach ([...$report['matched_skills'], ...$report['weak_evidence']] as $item) {
            self::assertNotEmpty($item['source_references'], $case['id'].' references');
        }
        foreach ($report['recommendations'] as $recommendation) {
            self::assertMatchesRegularExpression('/^recommendation-[0-9]{3}$/', $recommendation['id']);
            self::assertContains($recommendation['priority'], ['high', 'medium', 'low']);
            self::assertContains($recommendation['target_cv_section'], ['projects_or_experience', 'skills_or_summary']);
            self::assertNotEmpty($recommendation['related_signal_ids']);
            self::assertNotEmpty($recommendation['rationale']);
            self::assertStringContainsString('unsupported claim', mb_strtolower($recommendation['action']));
        }
        $requiredIds = array_fill_keys($analysisSignals['required'] ?? [], true);
        $expectedRecommendationSignals = array_merge($expected['missing'], $expected['weak']);
        self::assertCount(count($expectedRecommendationSignals), $report['recommendations'], $case['id'].' recommendation count');
        foreach ($expectedRecommendationSignals as $index => $signalId) {
            $recommendation = $report['recommendations'][$index] ?? null;
            self::assertIsArray($recommendation, $case['id'].' recommendation '.$index);
            self::assertSame([$signalId], $recommendation['related_signal_ids'], $case['id'].' recommendation signal');
            self::assertSame(isset($requiredIds[$signalId]) ? 'high' : 'medium', $recommendation['priority'], $case['id'].' recommendation priority');
            self::assertSame(isset($requiredIds[$signalId]) ? 'projects_or_experience' : 'skills_or_summary', $recommendation['target_cv_section'], $case['id'].' recommendation section');
        }

        $replay = $service->create(new AuthenticatedUser((int) $user->getKey()), $payload, $headers['Idempotency-Key'], '/api/v1/match-reports');
        self::assertSame(CanonicalJson::encode($result['body']), CanonicalJson::encode($replay['body']), $case['id'].' replay');
    }

    /** @param array<int,string> $ids @return array{state:string,items:array<int,array{signal_id:string,label:string}>} */
    private function skillList(array $ids): array
    {
        $items = [];
        $analyzer = new JobDescriptionAnalyzer;
        foreach ($ids as $id) {
            $vocabulary = $analyzer->vocabulary($id);
            self::assertNotNull($vocabulary, 'Unknown corpus signal '.$id);
            $items[] = ['signal_id' => $id, 'label' => $vocabulary['label']];
        }
        usort($items, static fn (array $left, array $right): int => $left['signal_id'] <=> $right['signal_id']);

        return ['state' => $items === [] ? 'absent' : 'detected', 'items' => $items];
    }

    private function fixturePath(string $name): string
    {
        $candidates = [
            dirname(__DIR__, 3).'/../../docs/contracts/jd/fixtures/'.$name,
            '/workspace/docs/contracts/jd/fixtures/'.$name,
        ];
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        self::fail('Match fixture is unavailable. Checked: '.implode(', ', $candidates));
    }
}
