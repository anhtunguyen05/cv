from app.schemas.health import build_health_response


def health_payload(service_name: str) -> dict:
    return build_health_response(service_name)

