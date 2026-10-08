<?php

declare(strict_types=1);

namespace Tests\Feature\Evidence;

use App\Application\Cv\CanonicalJson;
use App\Application\Cv\ProfileDocument;
use App\Models\CvProfile;
use App\Models\CvVersion;
use App\Models\EvidenceInterview;
use App\Models\JobDescription;
use App\Models\JobDescriptionAnalysis;
use App\Models\JobDescriptionRevision;
use App\Models\MatchReport;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesEpic4Context
{
    /** @return array{report:MatchReport,version:CvVersion,job:JobDescription,revision:JobDescriptionRevision,analysis:JobDescriptionAnalysis} */
    protected function createEpic4Report(User $user): array
    {
        $profileId = ProfileDocument::id();
        $profileTitle = 'Epic 4 '.$profileId;
        $document = ProfileDocument::empty('Evidence User');
        $profile = CvProfile::create([
            'id' => $profileId,
            'user_id' => $user->getKey(),
            'title' => $profileTitle,
            'normalized_title' => $profileTitle,
            'revision' => 1,
            'schema_version' => '1.0',
            'document' => $document,
        ]);
        $snapshot = ['title' => $profileTitle, ...$document];
        $version = CvVersion::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'source_profile_id' => $profile->getKey(),
            'source_profile_revision' => 1,
            'name' => 'Baseline',
            'snapshot_schema_version' => '1.0',
            'snapshot' => $snapshot,
            'snapshot_hash' => hash('sha256', CanonicalJson::encode($snapshot)),
            'created_at' => now(),
        ]);
        $job = JobDescription::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => 'Engineer',
            'company' => 'Example',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $revision = JobDescriptionRevision::create([
            'id' => ProfileDocument::id(),
            'job_description_id' => $job->getKey(),
            'user_id' => $user->getKey(),
            'revision_number' => 1,
            'title' => 'Engineer',
            'company' => 'Example',
            'raw_text' => 'Build reliable software.',
            'created_at' => now(),
        ]);
        $job->forceFill(['current_revision_id' => $revision->getKey()])->save();
        $analysis = JobDescriptionAnalysis::create([
            'id' => ProfileDocument::id(),
            'job_description_revision_id' => $revision->getKey(),
            'user_id' => $user->getKey(),
            'status' => 'succeeded',
            'analysis_rule_version' => '1.0.0',
            'analysis_schema_version' => '1.0.0',
            'required_skills' => [],
            'nice_to_have_skills' => [],
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $signal = static fn (string $id, string $label, string $level): array => [
            'signal_id' => $id,
            'label' => $label,
            'importance' => 'required',
            'evidence_level' => $level,
            'source_references' => [],
        ];
        $report = MatchReport::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'cv_version_id' => $version->getKey(),
            'job_description_id' => $job->getKey(),
            'job_description_revision_id' => $revision->getKey(),
            'analysis_id' => $analysis->getKey(),
            'analysis_rule_version' => '1.0.0',
            'matching_rule_version' => '1.0.0',
            'report_schema_version' => '1.0.0',
            'overall_score' => 50,
            'matched_skills' => [],
            'missing_skills' => [$signal('missing-one', 'Missing One', 'missing')],
            'weak_evidence' => [$signal('weak-one', 'Weak One', 'weak')],
            'recommendations' => [],
            'created_at' => now(),
        ]);

        return compact('report', 'version', 'job', 'revision', 'analysis');
    }

    /** @return array{context:array<string,mixed>,interview:EvidenceInterview} */
    protected function startCompleteEpic4Interview(User $user): array
    {
        $context = $this->createEpic4Report($user);
        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $interviewResponse = $this->postJson('/api/v1/match-reports/'.$context['report']->getKey().'/evidence-interviews', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertCreated();
        $interview = EvidenceInterview::query()->findOrFail($interviewResponse->json('data.id'));
        foreach ($interview->questions as $question) {
            $this->postJson('/api/v1/evidence-interviews/'.$interview->getKey().'/answers', [
                'question_id' => $question['id'],
                'question_version' => '1.0',
                'outcome' => 'answer',
                'answer' => 'Built reliable software for '.$question['area_signal_id'].'.',
            ], [
                'X-CSRF-TOKEN' => 'csrf-token',
                'Idempotency-Key' => (string) Str::uuid(),
            ])->assertCreated();
        }

        return ['context' => $context, 'interview' => $interview->fresh()];
    }
}
