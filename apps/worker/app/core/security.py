import secrets

from fastapi import Header, HTTPException

from app.core.config import Settings


class InternalIdentityError(Exception):
    def __init__(self, status_code: int, code: str, message: str) -> None:
        self.status_code = status_code
        self.code = code
        self.message = message
        super().__init__(message)


def check_internal_identity(settings: Settings, authorization: str | None) -> None:
    if settings.internal_auth_token is None:
        raise InternalIdentityError(503, "IDENTITY_NOT_CONFIGURED", "Internal identity is not configured.")
    if not authorization or not authorization.startswith("Bearer "):
        raise InternalIdentityError(401, "UNAUTHENTICATED", "Authentication is required.")
    presented = authorization.removeprefix("Bearer ")
    if not secrets.compare_digest(presented, settings.internal_auth_token.get_secret_value()):
        raise InternalIdentityError(403, "FORBIDDEN", "The request is not permitted.")


def require_internal_identity(settings: Settings, authorization: str | None = Header(default=None)) -> None:
    try:
        check_internal_identity(settings, authorization)
    except InternalIdentityError as error:
        raise HTTPException(status_code=error.status_code, detail=error.message) from None


class InternalIdentityMiddleware:
    def __init__(self, app, settings: Settings, protected_path: str | None = None, protected_paths: set[str] | None = None) -> None:
        self.app = app
        self.settings = settings
        self.protected_paths = set(protected_paths or ())
        if protected_path is not None:
            self.protected_paths.add(protected_path)

    async def __call__(self, scope, receive, send) -> None:
        if scope["type"] != "http" or scope.get("path") not in self.protected_paths:
            await self.app(scope, receive, send)
            return

        headers = {key.lower(): value for key, value in scope.get("headers", [])}
        authorization = headers.get(b"authorization", b"").decode("latin-1") or None
        try:
            check_internal_identity(self.settings, authorization)
        except InternalIdentityError as error:
            correlation = scope.get("state", {}).get("correlation_id", "")
            code = error.code
            message = error.message
            if scope.get("path") == "/internal/v1/patch-proposals" and error.status_code in {401, 403}:
                code = "AI_UNAUTHENTICATED"
                message = "The internal request could not be authenticated."
            elif scope.get("path") == "/internal/v1/patch-proposals" and error.status_code == 503:
                code = "AI_UNAVAILABLE"
                message = "The internal service is unavailable."
            body = (
                '{"code":"'
                + code
                + '","message":"'
                + message
                + '","correlation_id":"'
                + correlation
                + '"}'
            ).encode("utf-8")
            await send(
                {
                    "type": "http.response.start",
                    "status": error.status_code,
                    "headers": [(b"content-type", b"application/json")],
                }
            )
            await send({"type": "http.response.body", "body": body})
            return

        await self.app(scope, receive, send)
