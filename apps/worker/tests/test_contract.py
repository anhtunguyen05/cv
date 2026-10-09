import json
from pathlib import Path

import anyio
import httpx
import pytest
from pydantic import ValidationError
from pydantic import SecretStr

from app.core.config import Settings
from app.main import create_app
from app.schemas.patch_proposal import PatchProposalRequestV1, PatchProposalResponseV1, canonical_request_hash, validate_response_binding


FIXTURE = Path(__file__).parents[3] / "docs/contracts/ai/fixtures/patch-proposal-v1.json"


def request(app, method: str, path: str, **kwargs) -> httpx.Response:
    async def send() -> httpx.Response:
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://test") as client:
            return await client.request(method, path, **kwargs)

    return anyio.run(send)


def test_shared_valid_fixtures_validate_as_request_and_response() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    for item in corpus["valid"]:
        PatchProposalRequestV1.model_validate(item["request"])
        PatchProposalResponseV1.model_validate(item["response"])


def test_shared_invalid_shape_fixtures_are_rejected_without_canary_values() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    shape_invalid = {
        "unknown_top_level_request_field",
        "invalid_hash",
        "wrong_target_allowlist",
        "response_unknown_nested_field",
        "duplicate_evidence_reference",
        "empty_evidence_answer",
        "unsafe_metadata",
    }
    for item in corpus["invalid"]:
        if item["name"] not in shape_invalid:
            continue
        with pytest.raises(ValidationError) as error:
            if item["kind"] == "request":
                PatchProposalRequestV1.model_validate(item["case"])
            else:
                PatchProposalResponseV1.model_validate(item["case"])
        # The FastAPI boundary renders only field/type names; the exception
        # object itself is intentionally never serialized or logged.
        assert error.value.error_count() >= 1


def test_shared_binding_fixtures_are_rejected_against_their_request() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    valid_request = PatchProposalRequestV1.model_validate(corpus["valid"][0]["request"])
    for item in corpus["invalid"]:
        if item["name"] not in {"mismatched_execution_binding", "foreign_evidence_reference"}:
            continue
        response = PatchProposalResponseV1.model_validate(item["case"])
        with pytest.raises(ValueError):
            validate_response_binding(valid_request, response)


def test_request_models_are_strict_and_enforce_utf8_bytes() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    request_payload = corpus["valid"][0]["request"]
    with pytest.raises(ValidationError):
        PatchProposalRequestV1.model_validate({**request_payload, "execution_id": 1})
    too_long = {**request_payload, "context": {**request_payload["context"], "source_fragment": {"kind": "summary", "current_value": "é" * 1001}}}
    with pytest.raises(ValidationError):
        PatchProposalRequestV1.model_validate(too_long)


def test_authenticated_remote_mock_echoes_bindings_and_omits_trusted_fields() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    payload = corpus["valid"][0]["request"]
    app = create_app(Settings(internal_auth_token=SecretStr("test-token")))
    response = request(
        app,
        "POST",
        "/internal/v1/patch-proposals",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        json=payload,
    )
    assert response.status_code == 200
    body = response.json()
    assert body["execution_id"] == payload["execution_id"]
    assert body["request_hash"] == payload["request_hash"]
    assert body["source"] == payload["source"]
    assert "old_value" not in body
    assert "patch_id" not in body
    assert "user_id" not in body
    assert body["candidate"]["target"] == {"section": "summary", "field": "summary", "item_id": None, "operation": "replace"}
    assert body["candidate"]["proposed_text"] == payload["context"]["positive_evidence"][0]["answer"]
    assert body["candidate"]["evidence_source_ids"] == [payload["context"]["positive_evidence"][0]["id"]]


def test_remote_mock_uses_first_positive_evidence_for_candidate() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    payload = corpus["valid"][0]["request"]
    payload = {
        **payload,
        "execution_id": "01J00000000000000000000007",
        "context": {
            **payload["context"],
            "positive_evidence": [
                payload["context"]["positive_evidence"][0],
                {"id": "01J00000000000000000000008", "area_signal_id": "php", "answer": "Second evidence must not be selected."},
            ],
        },
    }
    payload["request_hash"] = canonical_request_hash(payload)
    app = create_app(Settings(internal_auth_token=SecretStr("test-token")))
    response = request(
        app,
        "POST",
        "/internal/v1/patch-proposals",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        json=payload,
    )
    assert response.status_code == 200
    assert response.json()["candidate"]["proposed_text"] == payload["context"]["positive_evidence"][0]["answer"]
    assert response.json()["candidate"]["evidence_source_ids"] == [payload["context"]["positive_evidence"][0]["id"]]


def test_remote_mock_replays_identical_binding_and_rejects_conflicting_execution_binding() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    payload = corpus["valid"][0]["request"]
    app = create_app(Settings(internal_auth_token=SecretStr("test-token")))
    headers = {"Authorization": "Bearer test-token", "Content-Type": "application/json"}
    first = request(app, "POST", "/internal/v1/patch-proposals", headers=headers, json=payload)
    replay = request(app, "POST", "/internal/v1/patch-proposals", headers=headers, json=payload)
    conflicting = request(app, "POST", "/internal/v1/patch-proposals", headers=headers, json={**payload, "request_hash": "f" * 64})
    assert first.status_code == replay.status_code == 200
    assert first.json() == replay.json()
    assert conflicting.status_code == 422
    assert conflicting.json()["code"] == "AI_REQUEST_INVALID"
    assert conflicting.json()["correlation_id"] == conflicting.headers["x-correlation-id"]


def test_remote_mock_requires_identity_and_rejects_invalid_wire() -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    app = create_app(Settings(internal_auth_token=SecretStr("test-token")))
    payload = corpus["valid"][0]["request"]
    missing = request(app, "POST", "/internal/v1/patch-proposals", json=payload)
    invalid = request(
        app,
        "POST",
        "/internal/v1/patch-proposals",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        content=b'{"request_hash": "SECRET_CANARY",',
    )
    assert missing.status_code == 401
    assert missing.json()["code"] == "AI_UNAUTHENTICATED"
    assert invalid.status_code == 422
    assert invalid.json()["code"] == "AI_REQUEST_INVALID"
    assert "SECRET_CANARY" not in invalid.text


@pytest.mark.parametrize(
    ("outcome", "status", "code"),
    [("timeout", 504, "AI_TIMEOUT"), ("rate_limited", 429, "AI_RATE_LIMITED"), ("unavailable", 503, "AI_UNAVAILABLE")],
)
def test_process_local_mock_failures_are_safe(outcome: str, status: int, code: str) -> None:
    corpus = json.loads(FIXTURE.read_text(encoding="utf-8"))
    app = create_app(Settings(internal_auth_token=SecretStr("test-token"), mock_outcome=outcome))
    response = request(
        app,
        "POST",
        "/internal/v1/patch-proposals",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        json=corpus["valid"][0]["request"],
    )
    assert response.status_code == status
    assert response.json()["code"] == code
    assert response.json()["correlation_id"] == response.headers["x-correlation-id"]
