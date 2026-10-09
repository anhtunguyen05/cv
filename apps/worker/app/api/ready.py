from app.core.config import Settings
from app.schemas.ready import build_readiness_response


def readiness_payload(settings: Settings) -> dict[str, str]:
    return build_readiness_response(settings.service_name)
