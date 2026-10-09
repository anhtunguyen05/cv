<?php

declare(strict_types=1);

namespace App\Application\Patch\Contracts;

final class PatchProposalRequestV1
{
    /** @param array<string,mixed> $value */
    private function __construct(public readonly array $value) {}

    /** @param array<string,mixed> $value */
    public static function fromArray(array $value): self
    {
        return new self(PatchProposalContractValidator::request($value));
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->value;
    }
}
