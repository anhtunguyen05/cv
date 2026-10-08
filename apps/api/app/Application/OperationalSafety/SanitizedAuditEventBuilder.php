<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

use DateTimeImmutable;
use DateTimeInterface;

final class SanitizedAuditEventBuilder
{
    public const EVENT_VERSION = '1.0.0';

    public const REDACTION_VERSION = '1.0.0';

    private const MAX_STRING_LENGTH = 128;

    private const MAX_DURATION_MS = 60_000;

    private const MAX_RETRY_COUNT = 5;

    /** @var array<int,string> */
    private const STATUSES = ['succeeded', 'failed', 'timed_out', 'cancelled'];

    /** @var array<int,string> */
    private const FAILURE_CATEGORIES = [
        'none', 'timeout', 'rate_limited', 'transport', 'malformed', 'validation', 'cancelled', 'unknown',
    ];

    /** @var array<int,string> */
    private const LATENCY_CLASSES = ['fast', 'bounded_failure', 'timeout', 'cancelled'];

    /** @var array<int,string> */
    private const ACTOR_TYPES = ['system', 'worker'];

    /** @var array<int,string> */
    private const OPERATIONS = ['generate-patch', 'analyze-job-description', 'validate-match'];

    /** @var array<int,string> */
    private const TOOLS = ['patch-proposal', 'job-description-analysis', 'deterministic-match'];

    /** @var array<int,string> */
    private const PROVIDERS = ['deterministic-fake', 'none'];

    /** @var array<int,string> */
    private const RESOURCE_TYPES = ['cv_version', 'job_description', 'match_report', 'evidence_interview', 'patch'];

    /** @var array<int,string> */
    private const CONTEXT_FIELDS = [
        'event_id', 'occurred_at', 'actor_type', 'operation', 'tool', 'provider', 'model',
        'contract_version', 'prompt_version', 'tool_schema_version', 'resource_type',
        'correlation_id', 'attempt_id', 'environment',
    ];

    /** @var array<int,string> */
    private const OUTCOME_FIELDS = ['status', 'failure_category', 'duration_ms', 'retry_count', 'latency_class'];

    /**
     * Build a safe operational event without logging, persisting, or calling
     * any application service.
     *
     * @param  array<string,mixed>  $serverContext
     * @param  array<string,mixed>  $outcome
     * @return array{accepted: true, event: array<string,mixed>}|array{accepted: false, diagnostic: string}
     */
    public function build(array $serverContext, array $outcome): array
    {
        $forbidden = $this->findForbiddenContent($serverContext, self::CONTEXT_FIELDS)
            ?? $this->findForbiddenContent($outcome, self::OUTCOME_FIELDS);
        if ($forbidden !== null) {
            return $this->rejected('FORBIDDEN_CONTENT');
        }

        $contextError = $this->validateExactFields($serverContext, self::CONTEXT_FIELDS);
        if ($contextError !== null) {
            return $this->rejected($contextError);
        }

        $outcomeError = $this->validateExactFields($outcome, self::OUTCOME_FIELDS);
        if ($outcomeError !== null) {
            return $this->rejected($outcomeError);
        }

        if ($this->validateContext($serverContext) !== null) {
            return $this->rejected('CONTEXT_INVALID');
        }

        if ($this->validateOutcome($outcome) !== null) {
            return $this->rejected('OUTCOME_INVALID');
        }

        if (! $this->isOutcomeConsistent($outcome)) {
            return $this->rejected('OUTCOME_INCONSISTENT');
        }

        return [
            'accepted' => true,
            'event' => [
                'event_version' => self::EVENT_VERSION,
                'redaction_version' => self::REDACTION_VERSION,
                'non_authoritative' => true,
                'event_id' => $serverContext['event_id'],
                'occurred_at' => $serverContext['occurred_at'],
                'actor_type' => $serverContext['actor_type'],
                'operation' => $serverContext['operation'],
                'tool' => $serverContext['tool'],
                'provider' => $serverContext['provider'],
                'model' => $serverContext['model'],
                'contract_version' => $serverContext['contract_version'],
                'prompt_version' => $serverContext['prompt_version'],
                'tool_schema_version' => $serverContext['tool_schema_version'],
                'resource_type' => $serverContext['resource_type'],
                'correlation_id' => $serverContext['correlation_id'],
                'attempt_id' => $serverContext['attempt_id'],
                'environment' => $serverContext['environment'],
                'status' => $outcome['status'],
                'failure_category' => $outcome['failure_category'],
                'duration_ms' => $outcome['duration_ms'],
                'retry_count' => $outcome['retry_count'],
                'latency_class' => $outcome['latency_class'],
            ],
        ];
    }

    /** @param array<string,mixed> $input @param array<int,string> $allowedFields */
    private function validateExactFields(array $input, array $allowedFields): ?string
    {
        $unknown = array_diff(array_keys($input), $allowedFields);

        return $unknown === [] ? null : 'UNKNOWN_FIELD';
    }

    /** @param array<string,mixed> $context */
    private function validateContext(array $context): ?string
    {
        foreach (self::CONTEXT_FIELDS as $field) {
            if (! array_key_exists($field, $context) || ! is_string($context[$field]) || $context[$field] === '') {
                return 'CONTEXT_INVALID';
            }
            if (mb_strlen($context[$field]) > self::MAX_STRING_LENGTH) {
                return 'CONTEXT_INVALID';
            }
        }

        if (! $this->isSafeIdentifier($context['event_id'])
            || ! $this->isSafeIdentifier($context['correlation_id'])
            || ! $this->isSafeIdentifier($context['attempt_id'])
            || ! $this->isSafeLabel($context['model'])
            || ! $this->isSafeLabel($context['contract_version'])
            || ! $this->isSafeLabel($context['prompt_version'])
            || ! $this->isSafeLabel($context['tool_schema_version'])
            || ! $this->isSafeLabel($context['environment'])) {
            return 'CONTEXT_INVALID';
        }

        if (! in_array($context['actor_type'], self::ACTOR_TYPES, true)
            || ! in_array($context['operation'], self::OPERATIONS, true)
            || ! in_array($context['tool'], self::TOOLS, true)
            || ! in_array($context['provider'], self::PROVIDERS, true)
            || ! in_array($context['resource_type'], self::RESOURCE_TYPES, true)) {
            return 'CONTEXT_INVALID';
        }

        try {
            $occurredAt = new DateTimeImmutable($context['occurred_at']);
        } catch (\Exception) {
            return 'CONTEXT_INVALID';
        }

        return $occurredAt->format(DateTimeInterface::ATOM) === $context['occurred_at'] ? null : 'CONTEXT_INVALID';
    }

    /** @param array<string,mixed> $outcome */
    private function validateOutcome(array $outcome): ?string
    {
        foreach (self::OUTCOME_FIELDS as $field) {
            if (! array_key_exists($field, $outcome)) {
                return 'OUTCOME_INVALID';
            }
        }

        if (! is_string($outcome['status']) || ! in_array($outcome['status'], self::STATUSES, true)
            || ! is_string($outcome['failure_category'])
            || ! in_array($outcome['failure_category'], self::FAILURE_CATEGORIES, true)
            || ! is_int($outcome['duration_ms']) || $outcome['duration_ms'] < 0 || $outcome['duration_ms'] > self::MAX_DURATION_MS
            || ! is_int($outcome['retry_count']) || $outcome['retry_count'] < 0 || $outcome['retry_count'] > self::MAX_RETRY_COUNT
            || ! is_string($outcome['latency_class']) || ! in_array($outcome['latency_class'], self::LATENCY_CLASSES, true)) {
            return 'OUTCOME_INVALID';
        }

        return null;
    }

    /** @param array<string,mixed> $outcome */
    private function isOutcomeConsistent(array $outcome): bool
    {
        return match ($outcome['status']) {
            'succeeded' => $outcome['failure_category'] === 'none' && $outcome['latency_class'] === 'fast',
            'timed_out' => $outcome['failure_category'] === 'timeout' && $outcome['latency_class'] === 'timeout',
            'cancelled' => $outcome['failure_category'] === 'cancelled' && $outcome['latency_class'] === 'cancelled',
            'failed' => $outcome['failure_category'] !== 'none' && $outcome['latency_class'] === 'bounded_failure',
            default => false,
        };
    }

    private function isSafeIdentifier(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/', $value) === 1;
    }

    private function isSafeLabel(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/', $value) === 1;
    }

    /**
     * @param  array<string,mixed>  $input
     * @param  array<int,string>  $allowedFields
     */
    private function findForbiddenContent(array $input, array $allowedFields): ?string
    {
        foreach ($input as $key => $value) {
            $key = (string) $key;
            if (! in_array($key, $allowedFields, true)
                && preg_match('/authorization|cookie|email|password|prompt|raw|secret|token|stack|trace|cv|jd|evidence|output|request|response|payload|error|message/i', $key) === 1) {
                return 'FORBIDDEN_CONTENT';
            }

            if (is_array($value)) {
                return 'FORBIDDEN_CONTENT';
            }

            if (is_string($value) && $this->containsCanary($value)) {
                return 'FORBIDDEN_CONTENT';
            }
        }

        return null;
    }

    private function containsCanary(string $value): bool
    {
        return preg_match('/canary|sk-[a-z0-9_-]+|gh[pousr]_[a-z0-9_]+|xox[baprs]-[a-z0-9-]+|akia[0-9a-z]{8,}|ya29\.[a-z0-9._-]+|bearer\s|-----begin|https?:\/\/|\S+@\S+/i', $value) === 1;
    }

    /** @return array{accepted: false, diagnostic: string} */
    private function rejected(string $diagnostic): array
    {
        return ['accepted' => false, 'diagnostic' => $diagnostic];
    }
}
