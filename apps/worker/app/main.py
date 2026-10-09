from typing import Any

from fastapi import FastAPI, Header, Request
from fastapi.exceptions import RequestValidationError
import uvicorn
from starlette.exceptions import HTTPException as StarletteHTTPException

from app.api.health import health_payload
from app.api.ready import readiness_payload
from app.api.patch_proposals import handle_patch_proposal
from app.core.config import Settings, get_settings
from app.core.errors import http_exception_handler, unhandled_exception_handler, validation_exception_handler
from app.core.middleware import CorrelationMiddleware, RequestBoundaryMiddleware, ResponseBoundaryMiddleware
from app.core.security import InternalIdentityMiddleware, require_internal_identity
from app.schemas.probe import ProbeRequest
from app.schemas.patch_proposal import PatchProposalRequestV1


def create_app(config: Settings | None = None) -> FastAPI:
    settings = config or get_settings()
    app = FastAPI(title=settings.service_name, docs_url=None, redoc_url=None, openapi_url=None)
    app.state.settings = settings
    app.state.patch_cache = {}
    app.add_middleware(ResponseBoundaryMiddleware, max_response_bytes=settings.max_response_bytes)
    protected_paths = {"/internal/v1/_probe", "/internal/v1/patch-proposals"}
    app.add_middleware(RequestBoundaryMiddleware, max_request_bytes=settings.max_request_bytes, json_path=protected_paths)
    app.add_middleware(InternalIdentityMiddleware, settings=settings, protected_paths=protected_paths)
    app.add_middleware(CorrelationMiddleware)
    app.add_exception_handler(RequestValidationError, validation_exception_handler)
    app.add_exception_handler(Exception, unhandled_exception_handler)
    app.add_exception_handler(StarletteHTTPException, http_exception_handler)

    @app.get("/health")
    @app.get("/api/health")
    async def health() -> dict:
        return health_payload(settings.service_name)

    @app.get("/ready")
    async def ready() -> dict[str, str]:
        return readiness_payload(settings)

    @app.get("/internal/v1/_probe")
    async def internal_probe_get(authorization: str | None = Header(default=None)) -> dict[str, str]:
        require_internal_identity(settings, authorization)
        return {"status": "ok", "component": "internal"}

    @app.post("/internal/v1/_probe")
    async def internal_probe_post(_: ProbeRequest, authorization: str | None = Header(default=None)) -> dict[str, str]:
        require_internal_identity(settings, authorization)
        return {"status": "ok", "component": "internal"}

    @app.post("/internal/v1/patch-proposals")
    async def patch_proposals(
        request: Request,
        payload: PatchProposalRequestV1,
        authorization: str | None = Header(default=None),
    ) -> Any:
        return await handle_patch_proposal(payload, settings, authorization, app.state.patch_cache, request.state.correlation_id)

    return app


app = create_app()


def main() -> None:
    settings = get_settings()
    uvicorn.run(app, host=settings.host, port=settings.port)


if __name__ == "__main__":
    main()
