<?php

declare(strict_types=1);

namespace App\Application\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\ProfileDocument;
use App\Application\Patch\Contracts\PatchProposalContractValidator;

final class PatchCandidateMapper
{
    /** @param array<string,mixed> $request @param array<string,mixed> $response @return array<string,mixed> */
    public function map(array $request, array $response): array
    {
        PatchProposalContractValidator::assertBindings($request, $response);
        $candidate = $response['candidate'];
        if ($candidate['target'] !== ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace']) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch candidate target is not allowed.', 422);
        }
        $evidenceIds = $candidate['evidence_source_ids'];
        if ($evidenceIds === []) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch candidate has no Evidence reference.', 422);
        }

        $newValue = ProfileDocument::normalizeText($candidate['proposed_text']);
        $reason = ProfileDocument::normalizeText($candidate['reason']);
        if (strlen($newValue) > 2000 || strlen($reason) > 500) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch candidate exceeds its byte limit.', 422);
        }

        return [
            'target' => $candidate['target'],
            'old_value' => ['value' => $request['context']['source_fragment']['current_value']],
            'new_value' => ['value' => $newValue],
            'reason' => $reason,
            'evidence_source_ids' => array_values($evidenceIds),
        ];
    }
}
