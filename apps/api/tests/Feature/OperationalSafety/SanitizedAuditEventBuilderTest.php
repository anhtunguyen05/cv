<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\SanitizedAuditEventBuilder;
use PHPUnit\Framework\TestCase;

final class SanitizedAuditEventBuilderTest extends TestCase
{
    public function test_builds_a_bounded_non_authoritative_success_event(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertTrue($result['accepted']);
        self::assertSame(true, $result['event']['non_authoritative']);
        self::assertSame(SanitizedAuditEventBuilder::EVENT_VERSION, $result['event']['event_version']);
        self::assertSame(SanitizedAuditEventBuilder::REDACTION_VERSION, $result['event']['redaction_version']);
        self::assertSame([
            'event_version', 'redaction_version', 'non_authoritative', 'event_id', 'occurred_at', 'actor_type',
            'operation', 'tool', 'provider', 'model', 'contract_version', 'prompt_version', 'tool_schema_version',
            'resource_type', 'correlation_id', 'attempt_id', 'environment', 'status', 'failure_category',
            'duration_ms', 'retry_count', 'latency_class',
        ], array_keys($result['event']));
    }

    public function test_accepts_a_safe_timeout_failure_without_provider_content(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'timed_out',
            'failure_category' => 'timeout',
            'duration_ms' => 15_000,
            'retry_count' => 1,
            'latency_class' => 'timeout',
        ]);

        self::assertTrue($result['accepted']);
        self::assertSame('timed_out', $result['event']['status']);
        self::assertSame('timeout', $result['event']['failure_category']);
    }

    public function test_rejects_unknown_fields_without_echoing_input(): void
    {
        $outcome = [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
            'details' => 'should-not-cross-boundary',
        ];

        $result = $this->builder()->build($this->context(), $outcome);

        self::assertSame(['accepted' => false, 'diagnostic' => 'UNKNOWN_FIELD'], $result);
        self::assertStringNotContainsString('should-not-cross-boundary', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_nested_forbidden_payloads_and_canaries(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'failed',
            'failure_category' => 'transport',
            'duration_ms' => 120,
            'retry_count' => 1,
            'latency_class' => 'bounded_failure',
            'provider_error' => ['raw_output' => 'CV_CANARY'],
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'FORBIDDEN_CONTENT'], $result);
    }

    public function test_rejects_arbitrary_nested_metadata_even_without_a_known_canary(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'failed',
            'failure_category' => 'transport',
            'duration_ms' => 120,
            'retry_count' => 1,
            'latency_class' => 'bounded_failure',
            'details' => ['metadata' => 'not-allowed'],
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'FORBIDDEN_CONTENT'], $result);
    }

    public function test_rejects_malformed_and_oversized_values(): void
    {
        $context = $this->context();
        $context['correlation_id'] = str_repeat('x', 129);

        $result = $this->builder()->build($context, [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 60_001,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'CONTEXT_INVALID'], $result);
    }

    public function test_rejects_outcome_values_above_their_bounds(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 60_001,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'OUTCOME_INVALID'], $result);
    }

    public function test_rejects_missing_outcome_fields_without_a_runtime_warning(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'OUTCOME_INVALID'], $result);
    }

    public function test_rejects_inconsistent_outcome(): void
    {
        $result = $this->builder()->build($this->context(), [
            'status' => 'succeeded',
            'failure_category' => 'transport',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'OUTCOME_INCONSISTENT'], $result);
    }

    public function test_rejects_forbidden_content_in_context_before_echoing_it(): void
    {
        $context = $this->context();
        $context['model'] = 'provider@example.test';

        $result = $this->builder()->build($context, [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'FORBIDDEN_CONTENT'], $result);
        self::assertStringNotContainsString('provider@example.test', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_unsafe_identifier_characters(): void
    {
        $context = $this->context();
        $context['correlation_id'] = 'correlation id';

        $result = $this->builder()->build($context, [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'CONTEXT_INVALID'], $result);
    }

    public function test_rejects_credential_shaped_labels(): void
    {
        $context = $this->context();
        $context['model'] = 'ghp_examplecredential';

        $result = $this->builder()->build($context, [
            'status' => 'succeeded',
            'failure_category' => 'none',
            'duration_ms' => 120,
            'retry_count' => 0,
            'latency_class' => 'fast',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'FORBIDDEN_CONTENT'], $result);
    }

    public function test_same_valid_input_produces_the_same_event(): void
    {
        $outcome = [
            'status' => 'failed',
            'failure_category' => 'validation',
            'duration_ms' => 400,
            'retry_count' => 2,
            'latency_class' => 'bounded_failure',
        ];

        $first = $this->builder()->build($this->context(), $outcome);
        $second = $this->builder()->build($this->context(), $outcome);

        self::assertSame($first, $second);
    }

    private function builder(): SanitizedAuditEventBuilder
    {
        return new SanitizedAuditEventBuilder;
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
