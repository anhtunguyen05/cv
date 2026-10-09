<?php

declare(strict_types=1);

namespace Tests\Unit\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Patch\Contracts\PatchProposalContractValidator;
use PHPUnit\Framework\TestCase;

final class PatchProposalContractTest extends TestCase
{
    /** @return array<string,mixed> */
    private function corpus(): array
    {
        return json_decode((string) file_get_contents(dirname(__DIR__, 5).'/docs/contracts/ai/fixtures/patch-proposal-v1.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_shared_valid_fixtures_are_accepted(): void
    {
        foreach ($this->corpus()['valid'] as $fixture) {
            self::assertSame($fixture['request'], PatchProposalContractValidator::request($fixture['request']));
            self::assertSame($fixture['response'], PatchProposalContractValidator::response($fixture['response']));
        }
    }

    public function test_shared_invalid_shape_fixtures_are_rejected_without_echoing_values(): void
    {
        foreach ($this->corpus()['invalid'] as $fixture) {
            if (in_array($fixture['name'], ['mismatched_execution_binding', 'foreign_evidence_reference'], true)) {
                continue;
            }
            try {
                if ($fixture['kind'] === 'request') {
                    PatchProposalContractValidator::request($fixture['case']);
                } else {
                    PatchProposalContractValidator::response($fixture['case']);
                }
                self::fail('Expected fixture to be rejected: '.$fixture['name']);
            } catch (ApiProblem $problem) {
                self::assertSame('PATCH_PROPOSAL_INVALID', $problem->errorCode);
                self::assertStringNotContainsString('CANARY', $problem->getMessage());
            }
        }
    }

    public function test_binding_mismatch_and_foreign_evidence_are_rejected(): void
    {
        $fixture = $this->corpus()['valid'][0];
        $response = $fixture['response'];
        $response['execution_id'] = '01J00000000000000000000009';
        try {
            PatchProposalContractValidator::assertBindings($fixture['request'], $response);
            self::fail('Expected execution binding mismatch to be rejected.');
        } catch (ApiProblem $problem) {
            self::assertSame('PATCH_PROPOSAL_INVALID', $problem->errorCode);
        }
        $response = $fixture['response'];
        $response['candidate']['evidence_source_ids'] = ['01J00000000000000000000006'];
        $this->expectException(ApiProblem::class);
        PatchProposalContractValidator::assertBindings($fixture['request'], $response);
    }

    public function test_request_hash_is_lowercase_sha256_and_utf8_byte_limits_are_applied(): void
    {
        $request = $this->corpus()['valid'][0]['request'];
        $request['request_hash'] = strtoupper($request['request_hash']);
        try {
            PatchProposalContractValidator::request($request);
            self::fail('Expected uppercase request hash to be rejected.');
        } catch (ApiProblem $problem) {
            self::assertSame('PATCH_PROPOSAL_INVALID', $problem->errorCode);
        }
        $request = $this->corpus()['valid'][0]['request'];
        $request['context']['source_fragment']['current_value'] = str_repeat('é', 1001);
        $this->expectException(ApiProblem::class);
        PatchProposalContractValidator::request($request);
    }
}
