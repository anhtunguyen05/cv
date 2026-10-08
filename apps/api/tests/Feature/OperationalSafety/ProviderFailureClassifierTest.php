<?php

declare(strict_types=1);

namespace Tests\Feature\OperationalSafety;

use App\Application\OperationalSafety\ProviderFailureClassifier;
use PHPUnit\Framework\TestCase;

final class ProviderFailureClassifierTest extends TestCase
{
    public function test_validated_success_is_the_only_trusted_result(): void
    {
        $result = $this->classifier()->classify('PATCH_SUCCEEDED', true);

        self::assertSame([
            'accepted' => true,
            'decision' => [
                'policy_version' => ProviderFailureClassifier::POLICY_VERSION,
                'status' => 'succeeded',
                'failure_category' => 'none',
                'outcome_code' => 'PATCH_SUCCEEDED',
                'allowed_action' => 'none',
                'retry_candidate' => false,
                'trusted_result_allowed' => true,
            ],
        ], $result);
    }

    public function test_timeout_rate_limit_and_transport_are_retry_candidates_without_trusted_results(): void
    {
        foreach ([
            'PATCH_PROVIDER_TIMEOUT' => 'timeout',
            'PATCH_RATE_LIMITED' => 'rate_limited',
            'PATCH_PROVIDER_UNAVAILABLE' => 'transport',
        ] as $code => $category) {
            $result = $this->classifier()->classify($code, false);

            self::assertSame(true, $result['accepted']);
            self::assertSame('retryable_failed', $result['decision']['status']);
            self::assertSame($category, $result['decision']['failure_category']);
            self::assertSame($code, $result['decision']['outcome_code']);
            self::assertSame('retry_candidate', $result['decision']['allowed_action']);
            self::assertTrue($result['decision']['retry_candidate']);
            self::assertFalse($result['decision']['trusted_result_allowed']);
        }
    }

    public function test_malformed_and_validation_outcomes_are_terminal(): void
    {
        foreach ([
            'PATCH_PROPOSAL_INVALID' => 'malformed',
            'PATCH_VALIDATION_FAILED' => 'validation',
        ] as $code => $category) {
            $result = $this->classifier()->classify($code, false);

            self::assertSame(true, $result['accepted']);
            self::assertSame('terminal_failed', $result['decision']['status']);
            self::assertSame($category, $result['decision']['failure_category']);
            self::assertSame($code, $result['decision']['outcome_code']);
            self::assertSame('none', $result['decision']['allowed_action']);
            self::assertFalse($result['decision']['retry_candidate']);
            self::assertFalse($result['decision']['trusted_result_allowed']);
        }
    }

    public function test_unvalidated_success_is_terminal_malformed_output(): void
    {
        $result = $this->classifier()->classify('PATCH_SUCCEEDED', false);

        self::assertSame(true, $result['accepted']);
        self::assertSame('terminal_failed', $result['decision']['status']);
        self::assertSame('malformed', $result['decision']['failure_category']);
        self::assertSame('UNVALIDATED_RESULT', $result['decision']['outcome_code']);
        self::assertSame('none', $result['decision']['allowed_action']);
        self::assertFalse($result['decision']['retry_candidate']);
        self::assertFalse($result['decision']['trusted_result_allowed']);
    }

    public function test_non_success_outcomes_never_become_trusted_regardless_of_validation_flag(): void
    {
        foreach ([
            'PATCH_PROVIDER_TIMEOUT',
            'PATCH_PROVIDER_UNAVAILABLE',
            'PATCH_RATE_LIMITED',
            'PATCH_PROPOSAL_INVALID',
            'PATCH_VALIDATION_FAILED',
            'PATCH_CANCELLED',
        ] as $code) {
            foreach ([false, true] as $validated) {
                $result = $this->classifier()->classify($code, $validated);

                self::assertSame(true, $result['accepted']);
                self::assertFalse($result['decision']['trusted_result_allowed']);
            }
        }
    }

    public function test_cancellation_never_becomes_success_or_retry(): void
    {
        $result = $this->classifier()->classify('PATCH_CANCELLED', false);

        self::assertSame([
            'accepted' => true,
            'decision' => [
                'policy_version' => ProviderFailureClassifier::POLICY_VERSION,
                'status' => 'cancelled',
                'failure_category' => 'cancelled',
                'outcome_code' => 'PATCH_CANCELLED',
                'allowed_action' => 'none',
                'retry_candidate' => false,
                'trusted_result_allowed' => false,
            ],
        ], $result);
    }

    public function test_unknown_outcome_is_rejected_without_echoing_input(): void
    {
        $result = $this->classifier()->classify('provider message contains a secret', false);

        self::assertSame(['accepted' => false, 'diagnostic' => 'UNKNOWN_OUTCOME'], $result);
        self::assertStringNotContainsString('secret', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_same_input_produces_the_same_safe_decision(): void
    {
        $first = $this->classifier()->classify('PATCH_PROVIDER_TIMEOUT', false);
        $second = $this->classifier()->classify('PATCH_PROVIDER_TIMEOUT', false);

        self::assertSame($first, $second);
        self::assertSame([
            'accepted', 'decision',
        ], array_keys($first));
        self::assertSame([
            'policy_version', 'status', 'failure_category', 'outcome_code', 'allowed_action', 'retry_candidate', 'trusted_result_allowed',
        ], array_keys($first['decision']));
    }

    private function classifier(): ProviderFailureClassifier
    {
        return new ProviderFailureClassifier;
    }
}
