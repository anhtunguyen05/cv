from dataclasses import dataclass
from os import getenv


@dataclass(frozen=True)
class Settings:
    host: str = getenv("WORKER_HOST", "0.0.0.0")
    port: int = int(getenv("WORKER_PORT", "8001"))
    service_name: str = getenv("WORKER_SERVICE_NAME", "CareerFit Worker")


settings = Settings()

