<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\PrivacySafeMetricEventBuilder;
use PHPUnit\Framework\TestCase;

final class PrivacySafeMetricEventBuilderTest extends TestCase
{
    public function test_builds_a_versioned_observed_event_with_bounded_labels(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'operation_duration_ms',
            'data_state' => 'observed',
            'value' => 120,
            'status' => 'succeeded',
            'failure_category' => 'none',
        ]));

        self::assertSame([
            'accepted' => true,
            'event' => [
                'schema_version' => PrivacySafeMetricEventBuilder::SCHEMA_VERSION,
                'metric_version' => PrivacySafeMetricEventBuilder::METRIC_VERSION,
                'non_authoritative' => true,
                'metric_name' => 'operation_duration_ms',
                'metric_type' => 'histogram',
                'unit' => 'milliseconds',
                'data_state' => 'observed',
                'observation_present' => true,
                'value' => 120,
                'status' => 'succeeded',
                'failure_category' => 'none',
                'labels' => [
                    'operation' => 'matching',
                    'environment' => 'test',
                ],
            ],
        ], $result);
    }

    public function test_accepts_a_failure_with_an_allowlisted_failure_category(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'operation_outcomes_total',
            'data_state' => 'observed',
            'value' => 1,
            'status' => 'failed',
            'failure_category' => 'validation',
        ]));

        self::assertTrue($result['accepted']);
        self::assertSame('failed', $result['event']['status']);
        self::assertSame('validation', $result['event']['failure_category']);
    }

    public function test_represents_no_data_without_fabricating_a_zero_observation(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'operation_invocations_total',
            'data_state' => 'no_data',
            'value' => null,
            'status' => 'no_data',
            'failure_category' => 'none',
        ]));

        self::assertTrue($result['accepted']);
        self::assertSame('no_data', $result['event']['data_state']);
        self::assertFalse($result['event']['observation_present']);
        self::assertNull($result['event']['value']);
    }

    public function test_represents_telemetry_outage_separately_from_no_data(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'dependency_health_ratio',
            'data_state' => 'telemetry_unavailable',
            'value' => null,
            'status' => 'unavailable',
            'failure_category' => 'dependency',
        ]));

        self::assertTrue($result['accepted']);
        self::assertSame('telemetry_unavailable', $result['event']['data_state']);
        self::assertSame('unavailable', $result['event']['status']);
        self::assertFalse($result['event']['observation_present']);
    }

    public function test_observed_zero_is_distinct_from_no_data(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'dependency_health_ratio',
            'data_state' => 'observed',
            'value' => 0.0,
            'status' => 'failed',
            'failure_category' => 'dependency',
        ]));

        self::assertTrue($result['accepted']);
        self::assertSame('observed', $result['event']['data_state']);
        self::assertTrue($result['event']['observation_present']);
        self::assertSame(0.0, $result['event']['value']);
    }

    public function test_rejects_unknown_metric_without_echoing_input(): void
    {
        $result = $this->builder()->build($this->input([
            'metric_name' => 'raw_provider_metric',
            'data_state' => 'observed',
            'value' => 1,
            'status' => 'succeeded',
            'failure_category' => 'none',
        ]));

        self::assertSame(['accepted' => false, 'diagnostic' => 'METRIC_INVALID'], $result);
        self::assertStringNotContainsString('raw_provider_metric', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_unbounded_or_sensitive_label_values_without_echoing_input(): void
    {
        $input = $this->input([
            'labels' => [
                'operation' => 'provider@example.test',
                'environment' => 'test',
            ],
        ]);

        $result = $this->builder()->build($input);

        self::assertSame(['accepted' => false, 'diagnostic' => 'LABELS_INVALID'], $result);
        self::assertStringNotContainsString('provider@example.test', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_unknown_or_sensitive_fields(): void
    {
        $input = $this->input();
        $input['raw_cv'] = 'CV_CANARY';

        $result = $this->builder()->build($input);

        self::assertSame(['accepted' => false, 'diagnostic' => 'UNKNOWN_FIELD'], $result);
        self::assertStringNotContainsString('CV_CANARY', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_inconsistent_no_data_and_observed_values(): void
    {
        $noDataWithZero = $this->input([
            'data_state' => 'no_data',
            'value' => 0,
            'status' => 'no_data',
            'failure_category' => 'none',
        ]);
        $observedWithoutValue = $this->input([
            'data_state' => 'observed',
            'value' => null,
            'status' => 'succeeded',
            'failure_category' => 'none',
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'STATE_INVALID'], $this->builder()->build($noDataWithZero));
        self::assertSame(['accepted' => false, 'diagnostic' => 'STATE_INVALID'], $this->builder()->build($observedWithoutValue));
    }

    public function test_rejects_values_outside_metric_bounds_or_wrong_types(): void
    {
        $durationTooLong = $this->input([
            'metric_name' => 'operation_duration_ms',
            'value' => 60_001,
        ]);
        $counterFraction = $this->input([
            'metric_name' => 'operation_invocations_total',
            'value' => 0.5,
        ]);

        self::assertSame(['accepted' => false, 'diagnostic' => 'VALUE_INVALID'], $this->builder()->build($durationTooLong));
        self::assertSame(['accepted' => false, 'diagnostic' => 'VALUE_INVALID'], $this->builder()->build($counterFraction));
    }

    public function test_same_input_produces_the_same_event(): void
    {
        $input = $this->input();
        $sameValuesWithDifferentLabelOrder = $input;
        $sameValuesWithDifferentLabelOrder['labels'] = [
            'environment' => 'test',
            'operation' => 'matching',
        ];

        self::assertSame($this->builder()->build($input), $this->builder()->build($input));
        self::assertSame($this->builder()->build($input), $this->builder()->build($sameValuesWithDifferentLabelOrder));
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function input(array $overrides = []): array
    {
        return array_replace([
            'metric_name' => 'operation_invocations_total',
            'data_state' => 'observed',
            'value' => 1,
            'status' => 'succeeded',
            'failure_category' => 'none',
            'labels' => [
                'operation' => 'matching',
                'environment' => 'test',
            ],
        ], $overrides);
    }

    private function builder(): PrivacySafeMetricEventBuilder
    {
        return new PrivacySafeMetricEventBuilder;
    }
}
