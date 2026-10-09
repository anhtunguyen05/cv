from collections.abc import Mapping
from typing import Any

from fastapi import Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from starlette.exceptions import HTTPException as StarletteHTTPException


def safe_error(code: str, message: str, correlation_id: str, details: Mapping[str, Any] | None = None) -> dict[str, Any]:
    payload: dict[str, Any] = {
        "code": code,
        "message": message,
        "correlation_id": correlation_id,
    }
    if details:
        payload["details"] = dict(details)
    return payload


def correlation_id(request: Request) -> str:
    return getattr(request.state, "correlation_id", "")


async def http_exception_handler(request: Request, exc: StarletteHTTPException) -> JSONResponse:
    status = exc.status_code
    code_by_status = {
        404: "NOT_FOUND",
        405: "METHOD_NOT_ALLOWED",
        413: "REQUEST_TOO_LARGE",
        415: "UNSUPPORTED_MEDIA_TYPE",
        401: "UNAUTHENTICATED",
        403: "FORBIDDEN",
        429: "RATE_LIMITED",
    }
    code = code_by_status.get(status, "REQUEST_FAILED")
    message = {
        404: "The requested route was not found.",
        405: "The HTTP method is not supported.",
        413: "The request body is too large.",
        415: "The request media type is not supported.",
        401: "Authentication is required.",
        403: "The request is not permitted.",
        429: "The request was rate limited.",
    }.get(status, "The request could not be processed.")
    return JSONResponse(status_code=status, content=safe_error(code, message, correlation_id(request)))


async def validation_exception_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    fields = {
        ".".join(str(part) for part in error.get("loc", ()) if part != "body"): [error.get("type", "invalid")]
        for error in exc.errors()
    }
    return JSONResponse(
        status_code=422,
        content=safe_error("VALIDATION_FAILED", "The request could not be validated.", correlation_id(request), {"fields": fields}),
    )


async def unhandled_exception_handler(request: Request, exc: Exception) -> JSONResponse:
    return JSONResponse(
        status_code=500,
        content=safe_error("INTERNAL_ERROR", "The service could not process the request.", correlation_id(request)),
    )
