<?php

declare(strict_types=1);

namespace App\Application\Patch\Contracts;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\ProfileDocument;

final class PatchProposalContractValidator
{
    /** @param array<string,mixed> $value */
    public static function canonicalRequestHash(array $value): string
    {
        unset($value['request_hash']);

        return hash('sha256', "careerfit:patch-proposal-request:v1\n".CanonicalJson::encode($value));
    }

    /** @return array<string,mixed> */
    public static function request(array $value): array
    {
        self::keys($value, ['contract_version', 'execution_id', 'request_hash', 'source', 'context', 'constraints']);
        if (($value['contract_version'] ?? null) !== '1.0') {
            self::invalid();
        }
        self::ulid($value['execution_id'] ?? null);
        self::hash($value['request_hash'] ?? null);
        if (! hash_equals((string) $value['request_hash'], self::canonicalRequestHash($value))) {
            self::invalid();
        }
        self::source($value['source'] ?? null);
        $context = $value['context'] ?? null;
        if (! is_array($context)) {
            self::invalid();
        }
        self::keys($context, ['source_fragment', 'positive_evidence']);
        $fragment = $context['source_fragment'] ?? null;
        if (! is_array($fragment)) {
            self::invalid();
        }
        self::keys($fragment, ['kind', 'current_value']);
        if (($fragment['kind'] ?? null) !== 'summary' || (! is_string($fragment['current_value'] ?? null) && ! is_null($fragment['current_value'] ?? null))) {
            self::invalid();
        }
        if (is_string($fragment['current_value'] ?? null)) {
            self::text($fragment['current_value'], 2000, false);
        }
        $evidence = $context['positive_evidence'] ?? null;
        if (! is_array($evidence) || ! array_is_list($evidence) || count($evidence) < 1 || count($evidence) > 5) {
            self::invalid();
        }
        foreach ($evidence as $item) {
            if (! is_array($item)) {
                self::invalid();
            }
            self::keys($item, ['id', 'area_signal_id', 'answer']);
            self::ulid($item['id'] ?? null);
            if (! is_string($item['area_signal_id'] ?? null)) {
                self::invalid();
            }
            self::text($item['area_signal_id'], 120, true);
            if (! is_string($item['answer'] ?? null)) {
                self::invalid();
            }
            self::text($item['answer'], 2000, true);
        }
        $constraints = $value['constraints'] ?? null;
        if (! is_array($constraints)) {
            self::invalid();
        }
        self::keys($constraints, ['target_allowlist', 'locale']);
        if (($constraints['target_allowlist'] ?? null) !== ['summary'] || ($constraints['locale'] ?? null) !== 'en') {
            self::invalid();
        }

        return $value;
    }

    /** @return array<string,mixed> */
    public static function response(array $value): array
    {
        self::keys($value, ['contract_version', 'execution_id', 'request_hash', 'source', 'status', 'candidate', 'metadata']);
        if (($value['contract_version'] ?? null) !== '1.0' || ($value['status'] ?? null) !== 'succeeded') {
            self::invalid();
        }
        self::ulid($value['execution_id'] ?? null);
        self::hash($value['request_hash'] ?? null);
        self::source($value['source'] ?? null);
        $candidate = $value['candidate'] ?? null;
        if (! is_array($candidate)) {
            self::invalid();
        }
        self::keys($candidate, ['target', 'proposed_text', 'reason', 'evidence_source_ids']);
        $target = $candidate['target'] ?? null;
        if (! is_array($target)) {
            self::invalid();
        }
        self::keys($target, ['section', 'field', 'item_id', 'operation']);
        if (CanonicalJson::encode($target) !== CanonicalJson::encode(['section' => 'summary', 'field' => 'summary', 'item_id' => null, 'operation' => 'replace'])) {
            self::invalid();
        }
        if (! is_string($candidate['proposed_text'] ?? null) || ! is_string($candidate['reason'] ?? null)) {
            self::invalid();
        }
        self::text($candidate['proposed_text'], 2000, true);
        self::text($candidate['reason'], 500, true);
        $ids = $candidate['evidence_source_ids'] ?? null;
        if (! is_array($ids) || ! array_is_list($ids) || count($ids) < 1 || count($ids) > 5 || count(array_unique($ids)) !== count($ids)) {
            self::invalid();
        }
        foreach ($ids as $id) {
            self::ulid($id);
        }
        $metadata = $value['metadata'] ?? null;
        if (! is_array($metadata)) {
            self::invalid();
        }
        self::keys($metadata, ['provider', 'model', 'prompt_version', 'input_tokens', 'output_tokens', 'latency_ms']);
        foreach (['provider', 'prompt_version'] as $key) {
            if (! is_string($metadata[$key] ?? null) || preg_match('/^[A-Za-z0-9._-]{1,120}$/', $metadata[$key]) !== 1) {
                self::invalid();
            }
        }
        if (! is_string($metadata['model'] ?? null) || preg_match('/^[A-Za-z0-9._:-]{1,120}$/', $metadata['model']) !== 1) {
            self::invalid();
        }
        foreach (['input_tokens', 'output_tokens'] as $key) {
            if (! is_int($metadata[$key] ?? null) || $metadata[$key] < 0 || $metadata[$key] > 100000) {
                self::invalid();
            }
        }
        if (! is_int($metadata['latency_ms'] ?? null) || $metadata['latency_ms'] < 0 || $metadata['latency_ms'] > 120000) {
            self::invalid();
        }

        return $value;
    }

    /** @param array<string,mixed> $request @param array<string,mixed> $response */
    public static function assertBindings(array $request, array $response): void
    {
        self::request($request);
        self::response($response);
        if ($request['contract_version'] !== $response['contract_version']
            || $request['execution_id'] !== $response['execution_id']
            || $request['request_hash'] !== $response['request_hash']
            || CanonicalJson::encode($request['source']) !== CanonicalJson::encode($response['source'])) {
            self::invalid();
        }
        $allowed = array_map(static fn (array $item): string => $item['id'], $request['context']['positive_evidence']);
        if (array_diff($response['candidate']['evidence_source_ids'], $allowed) !== []) {
            self::invalid();
        }
    }

    private static function source(mixed $value): void
    {
        if (! is_array($value)) {
            self::invalid();
        }
        self::keys($value, ['cv_version_id', 'snapshot_hash']);
        self::ulid($value['cv_version_id'] ?? null);
        self::hash($value['snapshot_hash'] ?? null);
    }

    private static function ulid(mixed $value): void
    {
        if (! ProfileDocument::isUlid($value)) {
            self::invalid();
        }
    }

    private static function hash(mixed $value): void
    {
        if (! is_string($value) || preg_match('/^[a-f0-9]{64}$/', $value) !== 1) {
            self::invalid();
        }
    }

    private static function text(string $value, int $maximum, bool $required): void
    {
        if ($required && trim($value) === '') {
            self::invalid();
        }
        if (strlen($value) > $maximum || preg_match('//u', $value) !== 1 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}<>]/u', $value) === 1) {
            self::invalid();
        }
    }

    /** @param array<string,mixed> $value @param array<int,string> $expected */
    private static function keys(array $value, array $expected): void
    {
        if (array_diff(array_keys($value), $expected) !== [] || array_diff($expected, array_keys($value)) !== []) {
            self::invalid();
        }
    }

    private static function invalid(): never
    {
        throw new ApiProblem('PATCH_PROPOSAL_INVALID', 'The Patch proposal contract is invalid.', 422);
    }
}
