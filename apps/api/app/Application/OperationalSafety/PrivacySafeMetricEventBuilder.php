<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

final class PrivacySafeMetricEventBuilder
{
    public const SCHEMA_VERSION = '1.0.0';

    public const METRIC_VERSION = '1.0.0';

    public const DATA_STATE_OBSERVED = 'observed';

    public const DATA_STATE_NO_DATA = 'no_data';

    public const DATA_STATE_TELEMETRY_UNAVAILABLE = 'telemetry_unavailable';

    /** @var array<string,array{type:string,unit:string,max:int|float}> */
    private const METRICS = [
        'operation_invocations_total' => ['type' => 'counter', 'unit' => 'count', 'max' => 1_000],
        'operation_duration_ms' => ['type' => 'histogram', 'unit' => 'milliseconds', 'max' => 60_000],
        'operation_outcomes_total' => ['type' => 'counter', 'unit' => 'count', 'max' => 1_000],
        'dependency_health_ratio' => ['type' => 'gauge', 'unit' => 'ratio', 'max' => 1.0],
    ];

    /** @var array<int,string> */
    private const OPERATIONS = ['matching', 'provider', 'patch', 'export', 'job', 'system'];

    /** @var array<int,string> */
    private const ENVIRONMENTS = ['development', 'test', 'staging', 'production', 'local-ci-disposable'];

    /** @var array<int,string> */
    private const STATUSES = ['succeeded', 'failed', 'timed_out', 'cancelled', 'no_data', 'unavailable'];

    /** @var array<int,string> */
    private const FAILURE_CATEGORIES = [
        'none', 'timeout', 'rate_limited', 'transport', 'malformed', 'validation', 'cancelled', 'dependency', 'unknown',
    ];

    /** @var array<int,string> */
    private const DATA_STATES = [self::DATA_STATE_OBSERVED, self::DATA_STATE_NO_DATA, self::DATA_STATE_TELEMETRY_UNAVAILABLE];

    /** @var array<int,string> */
    private const INPUT_FIELDS = ['metric_name', 'data_state', 'value', 'status', 'failure_category', 'labels'];

    /** @var array<int,string> */
    private const LABEL_FIELDS = ['operation', 'environment'];

    /**
     * Build a vendor-neutral metric event from server-owned taxonomy values.
     *
     * The builder is intentionally pure: it neither emits nor persists the
     * event. A later sink can consume the returned non-authoritative envelope.
     *
     * @param  array<string,mixed>  $input
     * @return array{accepted: true, event: array<string,mixed>}|array{accepted: false, diagnostic: string}
     */
    public function build(array $input): array
    {
        if (array_diff(array_keys($input), self::INPUT_FIELDS) !== []
            || array_diff(self::INPUT_FIELDS, array_keys($input)) !== []) {
            return $this->rejected('UNKNOWN_FIELD');
        }

        if (! is_string($input['metric_name']) || ! isset(self::METRICS[$input['metric_name']])) {
            return $this->rejected('METRIC_INVALID');
        }

        if (! is_string($input['data_state']) || ! in_array($input['data_state'], self::DATA_STATES, true)) {
            return $this->rejected('STATE_INVALID');
        }

        if (! is_string($input['status']) || ! in_array($input['status'], self::STATUSES, true)
            || ! is_string($input['failure_category']) || ! in_array($input['failure_category'], self::FAILURE_CATEGORIES, true)) {
            return $this->rejected('TAXONOMY_INVALID');
        }

        $labelsError = $this->validateLabels($input['labels']);
        if ($labelsError !== null) {
            return $this->rejected($labelsError);
        }

        $stateError = $this->validateState($input['data_state'], $input['status'], $input['failure_category'], $input['value']);
        if ($stateError !== null) {
            return $this->rejected($stateError);
        }

        $valueError = $this->validateValue(self::METRICS[$input['metric_name']], $input['data_state'], $input['value']);
        if ($valueError !== null) {
            return $this->rejected($valueError);
        }

        $metric = self::METRICS[$input['metric_name']];

        return [
            'accepted' => true,
            'event' => [
                'schema_version' => self::SCHEMA_VERSION,
                'metric_version' => self::METRIC_VERSION,
                'non_authoritative' => true,
                'metric_name' => $input['metric_name'],
                'metric_type' => $metric['type'],
                'unit' => $metric['unit'],
                'data_state' => $input['data_state'],
                'observation_present' => $input['data_state'] === self::DATA_STATE_OBSERVED,
                'value' => $input['value'],
                'status' => $input['status'],
                'failure_category' => $input['failure_category'],
                'labels' => [
                    'operation' => $input['labels']['operation'],
                    'environment' => $input['labels']['environment'],
                ],
            ],
        ];
    }

    /** @param array<string,mixed> $labels */
    private function validateLabels(mixed $labels): ?string
    {
        if (! is_array($labels)
            || array_diff(array_keys($labels), self::LABEL_FIELDS) !== []
            || array_diff(self::LABEL_FIELDS, array_keys($labels)) !== []) {
            return 'LABELS_INVALID';
        }

        if (! is_string($labels['operation']) || ! in_array($labels['operation'], self::OPERATIONS, true)
            || ! is_string($labels['environment']) || ! in_array($labels['environment'], self::ENVIRONMENTS, true)) {
            return 'LABELS_INVALID';
        }

        return null;
    }

    private function validateState(string $dataState, string $status, string $failureCategory, mixed $value): ?string
    {
        if ($dataState === self::DATA_STATE_NO_DATA) {
            return $status === 'no_data' && $failureCategory === 'none' && $value === null ? null : 'STATE_INVALID';
        }

        if ($dataState === self::DATA_STATE_TELEMETRY_UNAVAILABLE) {
            return $status === 'unavailable' && $failureCategory === 'dependency' && $value === null
                ? null
                : 'STATE_INVALID';
        }

        if ($value === null || in_array($status, ['no_data', 'unavailable'], true)) {
            return 'STATE_INVALID';
        }

        return match ($status) {
            'succeeded' => $failureCategory === 'none' ? null : 'STATE_INVALID',
            'timed_out' => $failureCategory === 'timeout' ? null : 'STATE_INVALID',
            'cancelled' => $failureCategory === 'cancelled' ? null : 'STATE_INVALID',
            'failed' => in_array($failureCategory, [
                'timeout', 'rate_limited', 'transport', 'malformed', 'validation', 'dependency', 'unknown',
            ], true) ? null : 'STATE_INVALID',
            default => 'STATE_INVALID',
        };
    }

    /** @param array{type:string,unit:string,max:int|float} $metric */
    private function validateValue(array $metric, string $dataState, mixed $value): ?string
    {
        if ($dataState !== self::DATA_STATE_OBSERVED) {
            return null;
        }

        if ((! is_int($value) && ! is_float($value)) || (is_float($value) && ! is_finite($value)) || $value < 0 || $value > $metric['max']) {
            return 'VALUE_INVALID';
        }

        if ($metric['type'] === 'counter' && ! is_int($value)) {
            return 'VALUE_INVALID';
        }

        return null;
    }

    /** @return array{accepted: false, diagnostic: string} */
    private function rejected(string $diagnostic): array
    {
        return ['accepted' => false, 'diagnostic' => $diagnostic];
    }
}
