<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Patch;

use App\Application\Cv\ApiProblem;
use App\Application\Patch\Contracts\PatchProposalContractValidator;
use App\Application\Patch\Contracts\PatchProviderClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class LaravelPatchProviderClient implements PatchProviderClient
{
    public function __construct(
        private readonly ?string $serviceUrl = null,
        private readonly ?string $serviceToken = null,
        private readonly ?int $timeoutSeconds = null,
    ) {}

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function propose(array $payload, string $correlationId): array
    {
        PatchProposalContractValidator::request($payload);
        $url = trim((string) ($this->serviceUrl ?? config('ai.service_url', '')));
        $token = (string) ($this->serviceToken ?? config('ai.service_token', ''));
        if ($url === '' || $token === '' || filter_var($url, FILTER_VALIDATE_URL) === false || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }
        if (preg_match('/^[a-f0-9]{32}$/', $correlationId) !== 1) {
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }
        $timeout = $this->timeoutSeconds ?? (int) config('ai.timeout_seconds', 10);
        if ($timeout < 1 || $timeout > 12) {
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }
        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withToken($token)
                ->withHeaders(['X-Correlation-ID' => $correlationId])
                ->connectTimeout(2)
                ->timeout($timeout)
                ->withOptions(['allow_redirects' => false])
                ->post(rtrim($url, '/').'/internal/v1/patch-proposals', $payload);
        } catch (ConnectionException) {
            throw new ApiProblem('PATCH_PROVIDER_TIMEOUT', 'The Patch provider timed out. Please retry.', 503);
        } catch (Throwable) {
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }
        $status = $response->status();
        if ($status === 429) {
            throw new ApiProblem('PATCH_RATE_LIMITED', 'Patch generation is temporarily rate limited. Please retry later.', 429);
        }
        if ($status === 504) {
            throw new ApiProblem('PATCH_PROVIDER_TIMEOUT', 'The Patch provider timed out. Please retry.', 503);
        }
        if (in_array($status, [400, 415, 422], true)) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch provider returned an invalid proposal.', 422);
        }
        if ($status < 200 || $status >= 300) {
            throw new ApiProblem('PATCH_PROVIDER_UNAVAILABLE', 'The Patch provider is unavailable. Please retry.', 503);
        }
        $body = $response->json();
        if (! is_array($body)) {
            throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch provider returned an invalid proposal.', 422);
        }
        PatchProposalContractValidator::assertBindings($payload, $body);

        return $body;
    }
}
