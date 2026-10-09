import re

import anyio
import httpx
import pytest
from pydantic import SecretStr, ValidationError

from app.core.config import Settings
from app.main import create_app


class ChunkedBody(httpx.AsyncByteStream):
    def __init__(self, *chunks: bytes) -> None:
        self.chunks = chunks

    async def __aiter__(self):
        for chunk in self.chunks:
            yield chunk


def request(app, method: str, path: str, **kwargs) -> httpx.Response:
    async def send() -> httpx.Response:
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://test") as client:
            return await client.request(method, path, **kwargs)

    return anyio.run(send)


@pytest.fixture()
def app():
    return create_app(Settings(internal_auth_token=SecretStr("test-token")))


def assert_correlation(response: httpx.Response, expected: str | None = None) -> None:
    value = response.headers.get("x-correlation-id")
    assert value is not None
    assert re.fullmatch(r"[0-9a-f]{32}", value)
    if expected is not None:
        assert value == expected


def test_health_endpoint_preserves_existing_payload(app) -> None:
    response = request(app, "GET", "/health")

    assert response.status_code == 200
    assert response.json()["status"] == "ok"
    assert response.json()["component"] == "worker"
    assert set(response.json()) == {"status", "service", "component", "timestamp"}
    assert_correlation(response)


def test_api_health_alias_returns_ok(app) -> None:
    response = request(app, "GET", "/api/health")

    assert response.status_code == 200
    assert response.json()["status"] == "ok"
    assert_correlation(response)


def test_ready_is_content_safe(app) -> None:
    response = request(app, "GET", "/ready")

    assert response.status_code == 200
    assert response.json() == {"status": "ready", "service": "CareerFit Worker", "component": "worker"}
    assert_correlation(response)


def test_unknown_route_and_unsupported_method_use_safe_json(app) -> None:
    not_found = request(app, "GET", "/unknown")
    method_not_allowed = request(app, "POST", "/health")

    assert not_found.status_code == 404
    assert not_found.json()["code"] == "NOT_FOUND"
    assert "detail" not in not_found.json()
    assert method_not_allowed.status_code == 405
    assert method_not_allowed.json()["code"] == "METHOD_NOT_ALLOWED"


def test_internal_probe_fails_closed_without_or_with_wrong_identity(app) -> None:
    missing = request(app, "GET", "/internal/v1/_probe")
    wrong = request(app, "GET", "/internal/v1/_probe", headers={"Authorization": "Bearer wrong"})

    assert missing.status_code == 401
    assert wrong.status_code == 403
    assert "detail" not in missing.json()
    assert "detail" not in wrong.json()


def test_internal_probe_fails_closed_when_identity_is_not_configured() -> None:
    app = create_app(Settings(internal_auth_token=None))

    response = request(app, "GET", "/internal/v1/_probe")

    assert response.status_code == 503
    assert response.json()["code"] == "IDENTITY_NOT_CONFIGURED"
    assert "detail" not in response.json()


def test_internal_probe_accepts_test_identity(app) -> None:
    response = request(app, "GET", "/internal/v1/_probe", headers={"Authorization": "Bearer test-token"})

    assert response.status_code == 200
    assert response.json() == {"status": "ok", "component": "internal"}


def test_internal_probe_rejects_non_json_content_type(app) -> None:
    response = request(
        app,
        "POST",
        "/internal/v1/_probe",
        headers={"Authorization": "Bearer test-token", "Content-Type": "text/plain"},
        content=b'{"probe":true}',
    )

    assert response.status_code == 415
    assert response.json()["code"] == "UNSUPPORTED_MEDIA_TYPE"


def test_internal_probe_rejects_bad_content_encoding_and_oversized_body(app) -> None:
    headers = {"Authorization": "Bearer test-token", "Content-Type": "application/json"}
    encoded = request(app, "POST", "/internal/v1/_probe", headers={**headers, "Content-Encoding": "gzip"}, content=b"{}")
    oversized = request(app, "POST", "/internal/v1/_probe", headers=headers, content=b"x" * (64 * 1024 + 1))

    assert encoded.status_code == 415
    assert encoded.json()["code"] == "UNSUPPORTED_MEDIA_TYPE"
    assert oversized.status_code == 413
    assert oversized.json()["code"] == "REQUEST_TOO_LARGE"


def test_chunked_body_is_bounded_without_content_length(app) -> None:
    response = request(
        app,
        "POST",
        "/internal/v1/_probe",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        content=ChunkedBody(b"x" * (32 * 1024), b"y" * (32 * 1024 + 1)),
    )

    assert response.status_code == 413
    assert response.json()["code"] == "REQUEST_TOO_LARGE"


def test_internal_probe_rejects_invalid_json_without_echoing_body(app) -> None:
    response = request(
        app,
        "POST",
        "/internal/v1/_probe",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        content=b'{"secret":"do-not-echo",',
    )

    assert response.status_code == 422
    assert response.json()["code"] == "VALIDATION_FAILED"
    assert "do-not-echo" not in response.text


def test_invalid_correlation_is_replaced_and_valid_correlation_is_preserved(app) -> None:
    replaced = request(app, "GET", "/health", headers={"X-Correlation-ID": "not-valid"})
    preserved = request(app, "GET", "/health", headers={"X-Correlation-ID": "0123456789abcdef0123456789abcdef"})

    assert_correlation(replaced)
    assert_correlation(preserved, "0123456789abcdef0123456789abcdef")


def test_response_limit_is_enforced_without_leaking_payload() -> None:
    app = create_app(Settings(internal_auth_token=SecretStr("test-token"), max_response_bytes=1))
    limited = request(app, "GET", "/health", headers={"X-Correlation-ID": "0123456789abcdef0123456789abcdef"})

    assert limited.status_code == 500
    assert limited.json()["code"] == "RESPONSE_TOO_LARGE"
    assert "CareerFit Worker" not in limited.text
    assert limited.json()["correlation_id"] == "0123456789abcdef0123456789abcdef"
    assert_correlation(limited, "0123456789abcdef0123456789abcdef")


def test_error_envelope_preserves_correlation_id(app) -> None:
    response = request(app, "GET", "/internal/v1/_probe", headers={"X-Correlation-ID": "0123456789abcdef0123456789abcdef"})

    assert response.status_code == 401
    assert response.json()["correlation_id"] == "0123456789abcdef0123456789abcdef"
    assert_correlation(response, "0123456789abcdef0123456789abcdef")


def test_probe_rejects_unknown_fields(app) -> None:
    response = request(
        app,
        "POST",
        "/internal/v1/_probe",
        headers={"Authorization": "Bearer test-token", "Content-Type": "application/json"},
        json={"probe": True, "secret": "do-not-accept"},
    )

    assert response.status_code == 422
    assert response.json()["code"] == "VALIDATION_FAILED"
    assert "do-not-accept" not in response.text


def test_unhandled_exception_uses_safe_error_envelope(app) -> None:
    @app.get("/test-boom")
    async def test_boom():
        raise RuntimeError("secret implementation detail")

    async def send() -> httpx.Response:
        transport = httpx.ASGITransport(app=app, raise_app_exceptions=False)
        async with httpx.AsyncClient(transport=transport, base_url="http://test") as client:
            return await client.get("/test-boom")

    response = anyio.run(send)

    assert response.status_code == 500
    assert response.json()["code"] == "INTERNAL_ERROR"
    assert "secret implementation detail" not in response.text
    assert re.fullmatch(r"[0-9a-f]{32}", response.json()["correlation_id"])


def test_settings_reject_invalid_port() -> None:
    with pytest.raises(ValidationError):
        Settings(port=0)


def test_settings_reject_limits_above_security_caps() -> None:
    with pytest.raises(ValidationError):
        Settings(max_request_bytes=(64 * 1024) + 1)
    with pytest.raises(ValidationError):
        Settings(max_response_bytes=(16 * 1024) + 1)
