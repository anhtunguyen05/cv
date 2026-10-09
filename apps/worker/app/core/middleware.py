import secrets

from starlette.types import ASGIApp, Message, Receive, Scope, Send


def _correlation_for(scope: Scope) -> str:
    return scope.get("state", {}).get("correlation_id", "") or secrets.token_hex(16)


class RequestBoundaryMiddleware:
    def __init__(self, app: ASGIApp, max_request_bytes: int, json_path: str) -> None:
        self.app = app
        self.max_request_bytes = max_request_bytes
        self.json_path = json_path

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return

        header_values: dict[bytes, list[bytes]] = {}
        for key, value in scope.get("headers", []):
            header_values.setdefault(key.lower(), []).append(value)
        headers = {key: values[-1] for key, values in header_values.items()}
        encodings = header_values.get(b"content-encoding", [b"identity"])
        if len(encodings) != 1 or encodings[0].lower() != b"identity":
            await self._send_error(scope, send, 415, "UNSUPPORTED_MEDIA_TYPE", "Only identity content encoding is supported.")
            return

        if scope.get("path") == self.json_path and scope.get("method") == "POST":
            content_type = headers.get(b"content-type", b"").split(b";", 1)[0].strip().lower()
            if content_type != b"application/json" and not content_type.endswith(b"+json"):
                await self._send_error(scope, send, 415, "UNSUPPORTED_MEDIA_TYPE", "The request media type is not supported.")
                return

        content_lengths = header_values.get(b"content-length", [])
        if len(content_lengths) > 1:
            await self._send_error(scope, send, 400, "INVALID_CONTENT_LENGTH", "The request could not be processed.")
            return
        content_length = content_lengths[0] if content_lengths else None
        if content_length is not None:
            try:
                if int(content_length) > self.max_request_bytes:
                    await self._send_error(scope, send, 413, "REQUEST_TOO_LARGE", "The request body is too large.")
                    return
            except ValueError:
                await self._send_error(scope, send, 400, "INVALID_CONTENT_LENGTH", "The request could not be processed.")
                return

        messages: list[Message] = []
        total = 0
        while True:
            message = await receive()
            if message["type"] == "http.disconnect":
                messages.append(message)
                break
            if message["type"] != "http.request":
                messages.append(message)
                break
            body = message.get("body", b"")
            total += len(body)
            if total > self.max_request_bytes:
                await self._send_error(scope, send, 413, "REQUEST_TOO_LARGE", "The request body is too large.")
                return
            messages.append(message)
            if not message.get("more_body", False):
                break

        index = 0

        async def replay_receive() -> Message:
            nonlocal index
            if index < len(messages):
                message = messages[index]
                index += 1
                return message
            return {"type": "http.request", "body": b"", "more_body": False}

        await self.app(scope, replay_receive, send)

    async def _send_error(self, scope: Scope, send: Send, status: int, code: str, message: str) -> None:
        correlation = _correlation_for(scope)
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
                "status": status,
                "headers": [(b"content-type", b"application/json"), (b"x-correlation-id", correlation.encode("ascii"))],
            }
        )
        await send({"type": "http.response.body", "body": body})


class CorrelationMiddleware:
    def __init__(self, app: ASGIApp) -> None:
        self.app = app

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return

        headers = {key.lower(): value for key, value in scope.get("headers", [])}
        raw_incoming = headers.get(b"x-correlation-id", b"")
        try:
            incoming = raw_incoming.decode("ascii")
        except UnicodeDecodeError:
            incoming = ""
        value = incoming if len(incoming) == 32 and all(char in "0123456789abcdef" for char in incoming) else secrets.token_hex(16)
        scope.setdefault("state", {})["correlation_id"] = value

        async def send_with_correlation(message: Message) -> None:
            if message["type"] == "http.response.start":
                response_headers = [(key, item) for key, item in message.get("headers", []) if key.lower() != b"x-correlation-id"]
                response_headers.append((b"x-correlation-id", value.encode("ascii")))
                message = {**message, "headers": response_headers}
            await send(message)

        await self.app(scope, receive, send_with_correlation)


class ResponseBoundaryMiddleware:
    def __init__(self, app: ASGIApp, max_response_bytes: int) -> None:
        self.app = app
        self.max_response_bytes = max_response_bytes

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return

        messages: list[Message] = []
        total = 0
        too_large = False

        async def capture(message: Message) -> None:
            nonlocal total, too_large
            if message["type"] == "http.response.body":
                total += len(message.get("body", b""))
                if total > self.max_response_bytes:
                    too_large = True
                    return
            if not too_large:
                messages.append(message)

        await self.app(scope, receive, capture)
        if too_large:
            correlation = _correlation_for(scope)
            body = (
                '{"code":"RESPONSE_TOO_LARGE","message":"The response is too large.","correlation_id":"'
                + correlation
                + '"}'
            ).encode("utf-8")
            await send(
                {
                    "type": "http.response.start",
                    "status": 500,
                    "headers": [(b"content-type", b"application/json"), (b"x-correlation-id", correlation.encode("ascii"))],
                }
            )
            await send({"type": "http.response.body", "body": body})
            return
        for message in messages:
            await send(message)
