<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Application\Cv\ProfileDocument;
use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class SummaryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesEpic4Context;

    public function test_summary_returns_authenticated_owner_scoped_counts_without_report_payload(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createEpic4Report($user);
        $this->createEpic4Report($other);
        JobDescription::create([
            'id' => ProfileDocument::id(),
            'user_id' => $user->getKey(),
            'title' => 'Owned role',
            'company' => null,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'web')->withSession(['_token' => 'csrf-token']);
        $this->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.cv_profiles', 1)
            ->assertJsonPath('data.job_descriptions', 1)
            ->assertJsonPath('data.match_reports', 1)
            ->assertJsonMissingPath('data.reports');
    }
}
