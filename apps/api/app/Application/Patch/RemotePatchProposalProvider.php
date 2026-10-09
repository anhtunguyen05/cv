<?php

declare(strict_types=1);

namespace App\Application\Patch;

use App\Application\Patch\Contracts\PatchProposalContractValidator;

final class RemotePatchProposalProvider implements PatchProposalProvider, PatchProviderMetadata
{
    /** @var array{provider:string,model:string,prompt_version:string,tool_schema_version:string,kind:string} */
    private array $metadata = [
        'provider' => 'remote-mock',
        'model' => 'deterministic-remote-mock-1.0',
        'prompt_version' => 'patch-v1',
        'tool_schema_version' => '1.0',
        'kind' => 'remote_mock',
        'input_tokens' => 0,
        'output_tokens' => 0,
        'latency_ms' => 0,
    ];

    public function __construct(private readonly AiServiceClient $client, private readonly PatchCandidateMapper $mapper) {}

    /** @param array<string,mixed> $request */
    public function propose(array $request): mixed
    {
        $payload = isset($request['context'])
            ? $request
            : self::contractPayload($request);
        unset($payload['_correlation_id']);
        PatchProposalContractValidator::request($payload);
        $response = $this->client->propose($payload, (string) ($request['_correlation_id'] ?? ''));
        PatchProposalContractValidator::assertBindings($payload, $response);
        $this->metadata = [
            ...$this->metadata,
            'model' => (string) $response['metadata']['model'],
            'prompt_version' => (string) $response['metadata']['prompt_version'],
            'input_tokens' => (int) $response['metadata']['input_tokens'],
            'output_tokens' => (int) $response['metadata']['output_tokens'],
            'latency_ms' => (int) $response['metadata']['latency_ms'],
        ];

        return $this->mapper->map($payload, $response);
    }

    /** @return array{provider:string,model:string,prompt_version:string,tool_schema_version:string,kind:string} */
    public function providerMetadata(): array
    {
        return $this->metadata;
    }

    /** @param array<string,mixed> $request @return array<string,mixed> */
    private static function contractPayload(array $request): array
    {
        $payload = [
            'contract_version' => '1.0',
            'execution_id' => $request['execution_id'],
            'request_hash' => $request['request_hash'],
            'source' => $request['source'],
            'context' => [
                'source_fragment' => [
                    'kind' => 'summary',
                    'current_value' => $request['source_fragments']['summary'] ?? null,
                ],
                'positive_evidence' => $request['positive_evidence'],
            ],
            'constraints' => [
                'target_allowlist' => ['summary'],
                'locale' => 'en',
            ],
        ];
        PatchProposalContractValidator::request($payload);

        return $payload;
    }
}
