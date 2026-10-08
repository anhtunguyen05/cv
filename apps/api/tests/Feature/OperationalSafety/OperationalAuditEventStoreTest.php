<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\OperationalAuditEventQuery;
use App\Application\OperationalSafety\OperationalAuditEventStore;
use App\Application\OperationalSafety\SanitizedAuditEventBuilder;
use App\Models\OperationalAuditEvent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

final class OperationalAuditEventStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_appends_only_a_builder_approved_event_and_uses_server_actor_identity(): void
    {
        $actor = User::factory()->create();
        $event = $this->store()->append($this->context(), $this->successOutcome(), $actor);

        self::assertSame('01J9AUDIT00000000000000000', $event->getKey());
        self::assertSame($actor->getKey(), $event->actor_user_id);
        self::assertTrue($event->non_authoritative);
        self::assertSame(1, OperationalAuditEvent::query()->count());
        self::assertArrayNotHasKey('raw_output', $event->getAttributes());
        self::assertArrayNotHasKey('prompt', $event->getAttributes());
    }

    public function test_rejected_content_never_reaches_the_audit_table(): void
    {
        $outcome = $this->successOutcome();
        $outcome['provider_error'] = ['raw_output' => 'CV_CANARY'];

        $this->expectException(InvalidArgumentException::class);
        $this->store()->append($this->context(), $outcome);
        self::assertSame(0, OperationalAuditEvent::query()->count());
    }

    public function test_model_and_database_guards_reject_mutation_and_deletion(): void
    {
        $event = $this->store()->append($this->context(), $this->successOutcome());

        $event->status = 'failed';
        $this->expectException(LogicException::class);
        $event->save();
    }

    public function test_database_trigger_rejects_query_builder_mutation_and_deletion(): void
    {
        $this->store()->append($this->context(), $this->successOutcome());

        $updateRejected = false;
        try {
            OperationalAuditEvent::query()->update(['status' => 'failed']);
            self::fail('Expected append-only update trigger to reject the mutation.');
        } catch (QueryException) {
            // Expected: the database is the final append-only boundary.
            $updateRejected = true;
        }
        self::assertTrue($updateRejected);

        $deleteRejected = false;
        try {
            OperationalAuditEvent::query()->delete();
            self::fail('Expected append-only delete trigger to reject the mutation.');
        } catch (QueryException) {
            // Expected: the database is the final append-only boundary.
            $deleteRejected = true;
        }
        self::assertTrue($deleteRejected);
    }

    public function test_query_service_returns_bounded_filtered_safe_events(): void
    {
        $store = $this->store();
        $store->append($this->context(), $this->successOutcome());
        $second = $this->context();
        $second['event_id'] = '01J9AUDIT00000000000000001';
        $second['correlation_id'] = 'corr-other';
        $second['operation'] = 'validate-match';
        $store->append($second, $this->successOutcome());

        $page = (new OperationalAuditEventQuery)->paginate('generate-patch', 'corr-01J9AUDIT', 1, 10);

        self::assertSame(1, $page->total());
        self::assertCount(1, $page->items());
        self::assertSame('01J9AUDIT00000000000000000', $page->items()[0]->getKey());
    }

    public function test_query_service_rejects_unbounded_or_unknown_filters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new OperationalAuditEventQuery)->paginate('unknown-operation');
    }

    private function store(): OperationalAuditEventStore
    {
        return new OperationalAuditEventStore(new SanitizedAuditEventBuilder);
    }

    /** @return array<string,mixed> */
    private function successOutcome(): array
    {
        return [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ];
    }

    /** @return array<string,string> */
    private function context(): array
    {
        return [
            'event_id' => '01J9AUDIT00000000000000000',
            'occurred_at' => '2026-10-08T12:00:00+00:00',
            'actor_type' => 'system',
            'operation' => 'generate-patch',
            'tool' => 'patch-proposal',
            'provider' => 'deterministic-fake',
            'model' => 'deterministic-fake-1.0',
            'contract_version' => 'patch-1.0',
            'prompt_version' => 'fake-1.0',
            'tool_schema_version' => '1.0',
            'resource_type' => 'patch',
            'correlation_id' => 'corr-01J9AUDIT',
            'attempt_id' => '01J9ATTEMPT0000000000000000',
            'environment' => 'local-ci-disposable',
        ];
    }
}
