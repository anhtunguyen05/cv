from dataclasses import dataclass, asdict
from datetime import datetime, timezone


@dataclass(frozen=True)
class HealthResponse:
    status: str
    service: str
    component: str
    timestamp: str


def build_health_response(service: str) -> dict:
    payload = HealthResponse(
        status="ok",
        service=service,
        component="worker",
        timestamp=datetime.now(timezone.utc).isoformat().replace("+00:00", "Z"),
    )
    return asdict(payload)

