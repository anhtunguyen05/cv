<?php

declare(strict_types=1);

namespace Tests\Feature\Patch;

use App\Application\Patch\Contracts\PatchProposalContractValidator;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\OperationalSafety\Eloquent\Models\OperationalAuditEvent;
use App\Infrastructure\Persistence\Patch\Eloquent\Models\Patch;
use App\Infrastructure\Persistence\Patch\Eloquent\Models\PatchGenerationReservation;
use App\Infrastructure\Persistence\Patch\Eloquent\Models\PatchProviderAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Evidence\CreatesEpic4Context;
use Tests\TestCase;

final class RemotePatchProposalTest extends TestCase
{
    use CreatesEpic4Context;
    use RefreshDatabase;

    public function test_remote_mock_creates_one_pending_patch_and_sends_minimum_contract(): void
    {
        config([
            'ai.patch_provider' => 'remote',
            'ai.service_url' => 'http://ai-service.test',
            'ai.service_token' => 'test-token',
        ]);
        Http::fake(function (Request $request) {
            $payload = $request->data();

            return Http::response([
                'contract_version' => '1.0',
                'execution_id' => $payload['execution_id'],
                'request_hash' => $payload['request_hash'],
                'source' => $payload['source'],
                'status' => 'succeeded',
                'candidate' => [
                    'target' => ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'],
                    'proposed_text' => $payload['context']['positive_evidence'][0]['answer'],
                    'reason' => 'Grounded remote mock candidate.',
                    'evidence_source_ids' => [$payload['context']['positive_evidence'][0]['id']],
                ],
                'metadata' => [
                    'provider' => 'remote_mock',
                    'model' => 'deterministic-remote-mock-1.0',
                    'prompt_version' => 'patch-v1',
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'latency_ms' => 0,
                ],
            ]);
        });
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);

        $response = $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.provenance.provider.kind', 'remote_mock');
        self::assertSame(1, Patch::query()->count());
        self::assertSame(1, OperationalAuditEvent::query()->count());
        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->url() === 'http://ai-service.test/internal/v1/patch-proposals'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && preg_match('/^[a-f0-9]{32}$/', (string) (($request->headers()['X-Correlation-ID'][0] ?? ''))) === 1
                && isset($payload['execution_id'], $payload['request_hash'], $payload['source'])
                && $payload['request_hash'] === PatchProposalContractValidator::canonicalRequestHash($payload)
                && ! isset($payload['old_value'], $payload['user_id'])
                && array_keys($payload) === ['contract_version', 'execution_id', 'request_hash', 'source', 'context', 'constraints']
                && array_keys($payload['context']) === ['source_fragment', 'positive_evidence']
                && array_keys($payload['context']['source_fragment']) === ['kind', 'current_value']
                && $payload['constraints'] === ['target_allowlist' => ['summary'], 'locale' => 'en'];
        });
    }

    public function test_remote_malformed_response_does_not_create_patch(): void
    {
        config([
            'ai.patch_provider' => 'remote',
            'ai.service_url' => 'http://ai-service.test',
            'ai.service_token' => 'test-token',
        ]);
        Http::fake(['*' => Http::response(['unsafe' => 'not-a-contract'], 200)]);
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);

        $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertStatus(422)->assertJsonPath('code', 'PATCH_PROPOSAL_INVALID');
        self::assertDatabaseCount('patches', 0);
    }

    public function test_same_logical_operation_replays_without_creating_a_second_patch(): void
    {
        config([
            'ai.patch_provider' => 'remote',
            'ai.service_url' => 'http://ai-service.test',
            'ai.service_token' => 'test-token',
        ]);
        Http::fake(function (Request $request) {
            $payload = $request->data();

            return Http::response([
                'contract_version' => '1.0',
                'execution_id' => $payload['execution_id'],
                'request_hash' => $payload['request_hash'],
                'source' => $payload['source'],
                'status' => 'succeeded',
                'candidate' => [
                    'target' => ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'],
                    'proposed_text' => $payload['context']['positive_evidence'][0]['answer'],
                    'reason' => 'Grounded remote mock candidate.',
                    'evidence_source_ids' => [$payload['context']['positive_evidence'][0]['id']],
                ],
                'metadata' => [
                    'provider' => 'remote_mock', 'model' => 'deterministic-remote-mock-1.0',
                    'prompt_version' => 'patch-v1', 'input_tokens' => 0, 'output_tokens' => 0, 'latency_ms' => 0,
                ],
            ]);
        });
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        $route = '/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches';
        $first = $this->postJson($route, [], ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => (string) Str::uuid()])->assertCreated();
        $second = $this->postJson($route, [], ['X-CSRF-TOKEN' => 'csrf-token', 'Idempotency-Key' => (string) Str::uuid()])->assertCreated();

        self::assertSame($first->json('data.id'), $second->json('data.id'));
        self::assertSame(1, Patch::query()->count());
    }

    public function test_remote_source_binding_mismatch_creates_no_patch(): void
    {
        config([
            'ai.patch_provider' => 'remote',
            'ai.service_url' => 'http://ai-service.test',
            'ai.service_token' => 'test-token',
        ]);
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);
        Http::fake(function (Request $request) {
            $payload = $request->data();
            $mismatchedSource = $payload['source'];
            $mismatchedSource['snapshot_hash'] = str_repeat('f', 64);

            return Http::response([
                'contract_version' => '1.0',
                'execution_id' => $payload['execution_id'],
                'request_hash' => $payload['request_hash'],
                'source' => $mismatchedSource,
                'status' => 'succeeded',
                'candidate' => [
                    'target' => ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'],
                    'proposed_text' => $payload['context']['positive_evidence'][0]['answer'],
                    'reason' => 'Grounded remote mock candidate.',
                    'evidence_source_ids' => [$payload['context']['positive_evidence'][0]['id']],
                ],
                'metadata' => [
                    'provider' => 'remote_mock',
                    'model' => 'deterministic-remote-mock-1.0',
                    'prompt_version' => 'patch-v1',
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'latency_ms' => 0,
                ],
            ]);
        });

        $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertStatus(422)->assertJsonPath('code', 'PATCH_PROPOSAL_INVALID');
        self::assertDatabaseCount('patches', 0);
    }

    /** @return array<string,array{int,int,string}> */
    public static function remoteFailures(): array
    {
        return [
            'unauthorized' => [401, 503, 'PATCH_PROVIDER_UNAVAILABLE'],
            'rate_limited' => [429, 429, 'PATCH_RATE_LIMITED'],
            'server_error' => [500, 503, 'PATCH_PROVIDER_UNAVAILABLE'],
            'timeout' => [504, 503, 'PATCH_PROVIDER_TIMEOUT'],
            'invalid_contract' => [422, 422, 'PATCH_PROPOSAL_INVALID'],
        ];
    }

    #[DataProvider('remoteFailures')]
    public function test_remote_transport_failures_create_no_patch(int $upstreamStatus, int $publicStatus, string $code): void
    {
        config([
            'ai.patch_provider' => 'remote',
            'ai.service_url' => 'http://ai-service.test',
            'ai.service_token' => 'test-token',
        ]);
        Http::fake(['*' => Http::response(['code' => 'safe-only'], $upstreamStatus)]);
        $user = User::factory()->create();
        $context = $this->startCompleteEpic4Interview($user);

        $this->postJson('/api/v1/evidence-interviews/'.$context['interview']->getKey().'/patches', [], [
            'X-CSRF-TOKEN' => 'csrf-token',
            'Idempotency-Key' => (string) Str::uuid(),
        ])->assertStatus($publicStatus)->assertJsonPath('code', $code);
        self::assertDatabaseCount('patches', 0);
        $expectedState = $code === 'PATCH_PROPOSAL_INVALID' ? 'terminal_failure' : 'retryable_failure';
        self::assertSame($expectedState, PatchProviderAttempt::query()->firstOrFail()->status->value);
        self::assertSame($expectedState, PatchGenerationReservation::query()->firstOrFail()->status->value);
    }
}
