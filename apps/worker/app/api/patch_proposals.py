from __future__ import annotations

import time
from typing import Any

from fastapi import Header, HTTPException
from fastapi.responses import JSONResponse

from app.core.config import Settings
from app.core.errors import safe_error
from app.core.security import require_internal_identity
from app.schemas.patch_proposal import (
    CandidateTarget,
    PatchCandidate,
    PatchProposalRequestV1,
    PatchProposalResponseV1,
    ProposalMetadata,
)


def _failure(outcome: str, correlation_id: str) -> JSONResponse | None:
    """Return a safe deterministic test outcome, never upstream details."""
    errors = {
        "unauthorized": (401, "AI_UNAUTHENTICATED", "The internal request could not be authenticated."),
        "timeout": (504, "AI_TIMEOUT", "The internal request timed out."),
        "rate_limited": (429, "AI_RATE_LIMITED", "The internal request was rate limited."),
        "unavailable": (503, "AI_UNAVAILABLE", "The internal service is unavailable."),
    }
    if outcome == "malformed":
        return JSONResponse(status_code=200, content={"contract_version": "1.0", "candidate": {"unsafe": "omitted"}})
    failure = errors.get(outcome)
    if failure is None:
        return None
    status, code, message = failure
    return JSONResponse(status_code=status, content=safe_error(code, message, correlation_id))


def deterministic_response(payload: PatchProposalRequestV1) -> PatchProposalResponseV1:
    first = payload.context.positive_evidence[0]
    proposed_text = first.answer.strip()
    if not proposed_text:
        raise HTTPException(status_code=422, detail="The request could not be processed.")
    return PatchProposalResponseV1(
        contract_version="1.0",
        execution_id=payload.execution_id,
        request_hash=payload.request_hash,
        source=payload.source,
        status="succeeded",
        candidate=PatchCandidate(
            target=CandidateTarget(section="summary", field="summary", item_id=None, operation="replace"),
            proposed_text=proposed_text,
            reason="Clarifies the summary using the first positive Evidence answer.",
            evidence_source_ids=[first.id],
        ),
        metadata=ProposalMetadata(
            provider="remote_mock",
            model="deterministic-remote-mock-1.0",
            prompt_version="patch-v1",
            input_tokens=0,
            output_tokens=0,
            latency_ms=0,
        ),
    )


async def handle_patch_proposal(
    payload: PatchProposalRequestV1,
    settings: Settings,
    authorization: str | None,
    cache: dict[tuple[str, str], tuple[PatchProposalRequestV1, dict[str, Any], float]] | None = None,
    correlation_id: str = "",
) -> Any:
    require_internal_identity(settings, authorization)
    failure = _failure(settings.mock_outcome or "", correlation_id)
    if failure is not None:
        return failure
    if cache is not None:
        now = time.monotonic()
        expired = [key for key, (_, _, expires_at) in cache.items() if expires_at <= now]
        for key in expired:
            del cache[key]
        key = (payload.execution_id, payload.request_hash)
        for (execution_id, request_hash), (cached_request, cached_response, _) in cache.items():
            if execution_id == payload.execution_id and request_hash != payload.request_hash:
                return JSONResponse(status_code=422, content=safe_error("AI_REQUEST_INVALID", "The internal request binding is invalid.", correlation_id))
            if (execution_id, request_hash) == key:
                if cached_request != payload:
                    return JSONResponse(status_code=422, content=safe_error("AI_REQUEST_INVALID", "The internal request binding is invalid.", correlation_id))
                return cached_response
    response = deterministic_response(payload).model_dump(mode="json")
    if cache is not None:
        cache[(payload.execution_id, payload.request_hash)] = (payload, response, time.monotonic() + settings.patch_cache_ttl_seconds)
        while len(cache) > 256:
            del cache[next(iter(cache))]
    return response
