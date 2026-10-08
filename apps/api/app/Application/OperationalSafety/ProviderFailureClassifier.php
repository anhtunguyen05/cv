<?php

declare(strict_types=1);

namespace App\Application\OperationalSafety;

final class ProviderFailureClassifier
{
    public const POLICY_VERSION = '1.0.0';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_RETRYABLE_FAILED = 'retryable_failed';

    public const STATUS_TERMINAL_FAILED = 'terminal_failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const CATEGORY_NONE = 'none';

    public const CATEGORY_TIMEOUT = 'timeout';

    public const CATEGORY_RATE_LIMITED = 'rate_limited';

    public const CATEGORY_TRANSPORT = 'transport';

    public const CATEGORY_MALFORMED = 'malformed';

    public const CATEGORY_VALIDATION = 'validation';

    public const CATEGORY_CANCELLED = 'cancelled';

    /** @var array<int,string> */
    private const ALLOWED_OUTCOME_CODES = [
        'PATCH_SUCCEEDED',
        'PATCH_PROVIDER_TIMEOUT',
        'PATCH_PROVIDER_UNAVAILABLE',
        'PATCH_RATE_LIMITED',
        'PATCH_PROPOSAL_INVALID',
        'PATCH_VALIDATION_FAILED',
        'PATCH_CANCELLED',
    ];

    /**
     * Classify a server-owned provider outcome without applying any state.
     *
     * @return array{accepted: true, decision: array{policy_version: string, status: string, failure_category: string, outcome_code: string, allowed_action: string, retry_candidate: bool, trusted_result_allowed: bool}}|array{accepted: false, diagnostic: string}
     */
    public function classify(string $outcomeCode, bool $validated): array
    {
        if (! in_array($outcomeCode, self::ALLOWED_OUTCOME_CODES, true)) {
            return ['accepted' => false, 'diagnostic' => 'UNKNOWN_OUTCOME'];
        }

        if ($outcomeCode === 'PATCH_SUCCEEDED') {
            if (! $validated) {
                return $this->decision(
                    self::STATUS_TERMINAL_FAILED,
                    self::CATEGORY_MALFORMED,
                    'UNVALIDATED_RESULT',
                    false,
                    false,
                );
            }

            return $this->decision(
                self::STATUS_SUCCEEDED,
                self::CATEGORY_NONE,
                $outcomeCode,
                false,
                true,
            );
        }

        return match ($outcomeCode) {
            'PATCH_PROVIDER_TIMEOUT' => $this->decision(
                self::STATUS_RETRYABLE_FAILED,
                self::CATEGORY_TIMEOUT,
                $outcomeCode,
                true,
                false,
            ),
            'PATCH_PROVIDER_UNAVAILABLE' => $this->decision(
                self::STATUS_RETRYABLE_FAILED,
                self::CATEGORY_TRANSPORT,
                $outcomeCode,
                true,
                false,
            ),
            'PATCH_RATE_LIMITED' => $this->decision(
                self::STATUS_RETRYABLE_FAILED,
                self::CATEGORY_RATE_LIMITED,
                $outcomeCode,
                true,
                false,
            ),
            'PATCH_PROPOSAL_INVALID' => $this->decision(
                self::STATUS_TERMINAL_FAILED,
                self::CATEGORY_MALFORMED,
                $outcomeCode,
                false,
                false,
            ),
            'PATCH_VALIDATION_FAILED' => $this->decision(
                self::STATUS_TERMINAL_FAILED,
                self::CATEGORY_VALIDATION,
                $outcomeCode,
                false,
                false,
            ),
            'PATCH_CANCELLED' => $this->decision(
                self::STATUS_CANCELLED,
                self::CATEGORY_CANCELLED,
                $outcomeCode,
                false,
                false,
            ),
        };
    }

    /**
     * @return array{accepted: true, decision: array{policy_version: string, status: string, failure_category: string, outcome_code: string, allowed_action: string, retry_candidate: bool, trusted_result_allowed: bool}}
     */
    private function decision(
        string $status,
        string $failureCategory,
        string $outcomeCode,
        bool $retryCandidate,
        bool $trustedResultAllowed,
    ): array {
        return [
            'accepted' => true,
            'decision' => [
                'policy_version' => self::POLICY_VERSION,
                'status' => $status,
                'failure_category' => $failureCategory,
                'outcome_code' => $outcomeCode,
                'allowed_action' => $retryCandidate ? 'retry_candidate' : 'none',
                'retry_candidate' => $retryCandidate,
                'trusted_result_allowed' => $trustedResultAllowed,
            ],
        ];
    }
}
