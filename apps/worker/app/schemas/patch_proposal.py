"""Strict v1 transport models for the private Patch Proposal boundary.

The models intentionally contain only transport data.  Laravel remains the
authority for ownership, source freshness, grounding, and persistence.
"""

from __future__ import annotations

import hashlib
import json
import re
import unicodedata
from typing import Annotated, Literal

from pydantic import BaseModel, ConfigDict, Field, field_validator, model_validator


ULID_PATTERN = re.compile(r"^[0-9A-HJKMNP-TV-Z]{26}$")
HASH_PATTERN = re.compile(r"^[a-f0-9]{64}$")
SAFE_IDENTIFIER = re.compile(r"^[A-Za-z0-9._-]{1,120}$")
SAFE_MODEL = re.compile(r"^[A-Za-z0-9._:-]{1,120}$")


def _utf8_size(value: str | None, field_name: str, maximum: int, minimum: int = 0) -> str | None:
    if value is None:
        if minimum:
            raise ValueError(f"{field_name} must not be empty")
        return value
    size = len(value.encode("utf-8"))
    if size < minimum or size > maximum or any(
        character in "<>" or ord(character) < 32 or ord(character) == 127 or unicodedata.category(character) == "Cf"
        for character in value
    ) or (minimum and not value.strip()):
        raise ValueError(f"{field_name} exceeds its UTF-8 byte limit")
    return value


def canonical_request_hash(value: dict[str, object]) -> str:
    unsigned = {key: item for key, item in value.items() if key != "request_hash"}
    encoded = json.dumps(unsigned, ensure_ascii=False, separators=(",", ":"), sort_keys=True).encode("utf-8")
    return hashlib.sha256(b"careerfit:patch-proposal-request:v1\n" + encoded).hexdigest()


class ContractModel(BaseModel):
    model_config = ConfigDict(extra="forbid", strict=True)


class ContractSource(ContractModel):
    cv_version_id: Annotated[str, Field(pattern=ULID_PATTERN.pattern)]
    snapshot_hash: Annotated[str, Field(pattern=HASH_PATTERN.pattern)]


class SourceFragment(ContractModel):
    kind: Literal["summary"]
    current_value: str | None

    @field_validator("current_value")
    @classmethod
    def validate_current_value(cls, value: str | None) -> str | None:
        return _utf8_size(value, "current_value", 2_000)


class PositiveEvidence(ContractModel):
    id: Annotated[str, Field(pattern=ULID_PATTERN.pattern)]
    area_signal_id: Annotated[str, Field(min_length=1, max_length=120)]
    answer: Annotated[str, Field(min_length=1, max_length=2_000)]

    @field_validator("area_signal_id")
    @classmethod
    def validate_area_signal_id(cls, value: str) -> str:
        return _utf8_size(value, "area_signal_id", 120, 1) or ""

    @field_validator("answer")
    @classmethod
    def validate_answer(cls, value: str) -> str:
        return _utf8_size(value, "answer", 2_000, 1) or ""


class PatchProposalContext(ContractModel):
    source_fragment: SourceFragment
    positive_evidence: Annotated[list[PositiveEvidence], Field(min_length=1, max_length=5)]


class PatchProposalConstraints(ContractModel):
    target_allowlist: list[Literal["summary"]]
    locale: Literal["en"]

    @model_validator(mode="after")
    def validate_target_allowlist(self) -> "PatchProposalConstraints":
        if self.target_allowlist != ["summary"]:
            raise ValueError("target_allowlist must contain only summary")
        return self


class PatchProposalRequestV1(ContractModel):
    contract_version: Literal["1.0"]
    execution_id: Annotated[str, Field(pattern=ULID_PATTERN.pattern)]
    request_hash: Annotated[str, Field(pattern=HASH_PATTERN.pattern)]
    source: ContractSource
    context: PatchProposalContext
    constraints: PatchProposalConstraints

    @model_validator(mode="after")
    def validate_request_hash(self) -> "PatchProposalRequestV1":
        if self.request_hash != canonical_request_hash(self.model_dump(mode="json")):
            raise ValueError("request_hash does not match canonical request")
        return self


class CandidateTarget(ContractModel):
    section: Literal["summary"]
    field: Literal["summary"]
    item_id: Literal[None]
    operation: Literal["replace"]


class PatchCandidate(ContractModel):
    target: CandidateTarget
    proposed_text: Annotated[str, Field(min_length=1, max_length=2_000)]
    reason: Annotated[str, Field(min_length=1, max_length=500)]
    evidence_source_ids: Annotated[list[Annotated[str, Field(pattern=ULID_PATTERN.pattern)]], Field(min_length=1, max_length=5)]

    @field_validator("proposed_text")
    @classmethod
    def validate_proposed_text(cls, value: str) -> str:
        return _utf8_size(value, "proposed_text", 2_000, 1) or ""

    @field_validator("reason")
    @classmethod
    def validate_reason(cls, value: str) -> str:
        return _utf8_size(value, "reason", 500, 1) or ""

    @field_validator("evidence_source_ids")
    @classmethod
    def validate_distinct_evidence_ids(cls, value: list[str]) -> list[str]:
        if len(set(value)) != len(value):
            raise ValueError("evidence_source_ids must be distinct")
        return value


class ProposalMetadata(ContractModel):
    provider: Annotated[str, Field(pattern=SAFE_IDENTIFIER.pattern)]
    model: Annotated[str, Field(pattern=SAFE_MODEL.pattern)]
    prompt_version: Annotated[str, Field(pattern=SAFE_IDENTIFIER.pattern)]
    input_tokens: Annotated[int, Field(ge=0, le=100_000)]
    output_tokens: Annotated[int, Field(ge=0, le=100_000)]
    latency_ms: Annotated[int, Field(ge=0, le=120_000)]


class PatchProposalResponseV1(ContractModel):
    contract_version: Literal["1.0"]
    execution_id: Annotated[str, Field(pattern=ULID_PATTERN.pattern)]
    request_hash: Annotated[str, Field(pattern=HASH_PATTERN.pattern)]
    source: ContractSource
    status: Literal["succeeded"]
    candidate: PatchCandidate
    metadata: ProposalMetadata


def validate_response_binding(request: PatchProposalRequestV1, response: PatchProposalResponseV1) -> PatchProposalResponseV1:
    """Apply the cross-message binding rules shared with Laravel."""
    if (
        request.contract_version != response.contract_version
        or request.execution_id != response.execution_id
        or request.request_hash != response.request_hash
        or request.source != response.source
    ):
        raise ValueError("response binding does not match request")
    allowed = {item.id for item in request.context.positive_evidence}
    if not set(response.candidate.evidence_source_ids).issubset(allowed):
        raise ValueError("response Evidence reference is not in the request")
    return response


__all__ = [
    "CandidateTarget",
    "ContractSource",
    "PatchCandidate",
    "PatchProposalConstraints",
    "PatchProposalContext",
    "PatchProposalRequestV1",
    "PatchProposalResponseV1",
    "PositiveEvidence",
    "ProposalMetadata",
    "SourceFragment",
    "validate_response_binding",
    "canonical_request_hash",
]
