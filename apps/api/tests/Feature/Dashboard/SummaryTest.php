<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Application\Cv\ProfileDocument;
use App\Models\CvProfile;
use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_authenticated_owner_scoped_counts_without_report_rows(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createProfile($user, 'Owned CV');
        $this->createProfile($other, 'Foreign CV');
        JobDescription::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => 'Owned role',
            'company' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        JobDescription::create([
            'id' => ProfileDocument::id(),
            'user_id' => $other->getKey(),
            'title' => 'Foreign role',
            'company' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.cv_profiles', 1)
            ->assertJsonPath('data.job_descriptions', 1)
            ->assertJsonPath('data.match_reports', 0)
            ->assertJsonMissingPath('data.reports');
    }

    private function createProfile(User $user, string $title): void
    {
        CvProfile::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => $title,
            'normalized_title' => $title,
            'revision' => 1,
            'schema_version' => '1.0',
            'document' => ProfileDocument::empty('Candidate'),
        ]);
    }
}
