<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Patch;

use App\Application\Patch\PatchProposalProvider;
use App\Application\Patch\PatchProviderMetadata;

final class DeterministicFakePatchProposalProvider implements PatchProposalProvider, PatchProviderMetadata
{
    /** @param array<string,mixed> $request */
    public function propose(array $request): mixed
    {
        $snapshot = is_array($request['source_fragments'] ?? null) ? $request['source_fragments'] : [];
        $evidence = is_array($request['positive_evidence'] ?? null) ? $request['positive_evidence'] : [];
        $first = $evidence[0] ?? null;
        $answer = is_array($first) ? (string) ($first['answer'] ?? '') : '';
        $answer = trim($answer);
        if ($answer === '') {
            return [
                'target' => ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'],
                'old_value' => ['value' => $snapshot['summary'] ?? null],
                'new_value' => ['value' => ''],
                'reason' => 'No positive User Evidence was available.',
                'evidence_source_ids' => [],
            ];
        }

        $old = $snapshot['summary'] ?? null;

        return [
            'target' => ['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'],
            'old_value' => ['value' => $old],
            'new_value' => ['value' => mb_substr($answer, 0, 2000)],
            'reason' => 'This proposal is grounded in the User Evidence answer and is limited to the CV summary.',
            'evidence_source_ids' => array_values(array_filter(array_map(
                static fn (mixed $item): ?string => is_array($item) && is_string($item['id'] ?? null) ? $item['id'] : null,
                $evidence,
            ))),
        ];
    }

    /** @return array{provider:string,model:string,prompt_version:string,tool_schema_version:string,kind:string} */
    public function providerMetadata(): array
    {
        return [
            'provider' => 'deterministic-fake',
            'model' => 'deterministic-fake-1.0',
            'prompt_version' => 'fake-1.0',
            'tool_schema_version' => '1.0',
            'kind' => 'deterministic_fake',
        ];
    }
}
