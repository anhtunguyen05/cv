from http import HTTPStatus
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
import json

from app.api.health import health_payload
from app.core.config import settings


class WorkerHandler(BaseHTTPRequestHandler):
    def do_GET(self) -> None:  # noqa: N802
        if self.path not in {"/health", "/api/health"}:
            self._send_json(HTTPStatus.NOT_FOUND, {"status": "error", "message": "Not found"})
            return

        self._send_json(HTTPStatus.OK, health_payload(settings.service_name))

    def log_message(self, format: str, *args) -> None:  # noqa: A003
        return

    def _send_json(self, status: HTTPStatus, payload: dict) -> None:
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)


def main() -> None:
    server = ThreadingHTTPServer((settings.host, settings.port), WorkerHandler)
    print(f"{settings.service_name} ready (host={settings.host}, port={settings.port})")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()


if __name__ == "__main__":
    main()
