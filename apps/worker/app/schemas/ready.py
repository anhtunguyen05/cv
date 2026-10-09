from dataclasses import asdict, dataclass


@dataclass(frozen=True)
class ReadinessResponse:
    status: str
    service: str
    component: str


def build_readiness_response(service: str) -> dict[str, str]:
    return asdict(ReadinessResponse(status="ready", service=service, component="worker"))
