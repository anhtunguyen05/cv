<?php

declare(strict_types=1);

namespace Tests\Feature\JobFit;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class JobDescriptionContractCorpusTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_description_fixture_cases_are_consumed_by_the_api(): void
    {
        $fixture = json_decode(
            (string) file_get_contents($this->fixturePath('job-description-v1.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->withSession(['_token' => 'corpus-csrf-token']);

        foreach ($fixture['examples'] as $case) {
            $response = $this->postJson('/api/v1/job-descriptions', [
                'raw_text' => $case['raw_text'],
                'company' => $case['company'] ?? null,
                'role' => $case['role'] ?? null,
            ], $this->headers());
            $response->assertCreated()->assertJsonPath('data.current_revision.revision_number', $case['expected_revision_number']);
            self::assertSame($case['raw_text'], $response->json('data.current_revision.raw_text'));
        }

        foreach ($fixture['validation_cases'] as $case) {
            $payload = ['raw_text' => $case['raw_text']];
            if (array_key_exists('extra', $case)) {
                $payload['extra'] = $case['extra'];
            }
            $this->postJson('/api/v1/job-descriptions', $payload, $this->headers())
                ->assertStatus(422)
                ->assertJsonPath('code', $case['expected_error']);
        }
    }

    /** @return array<string,string> */
    private function headers(): array
    {
        return ['X-CSRF-TOKEN' => 'corpus-csrf-token', 'Idempotency-Key' => (string) Str::uuid()];
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

        self::fail('Job Description fixture is unavailable. Checked: '.implode(', ', $candidates));
    }
}
