from functools import lru_cache

from pydantic import SecretStr, field_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_prefix="WORKER_",
        env_file=".env",
        extra="ignore",
    )

    host: str = "0.0.0.0"
    port: int = 8001
    service_name: str = "CareerFit Worker"
    internal_auth_token: SecretStr | None = None
    max_request_bytes: int = 64 * 1024
    max_response_bytes: int = 16 * 1024
    # Test-only process-local mock outcome. Production cannot select outcomes
    # from an HTTP request and leaves this unset.
    mock_outcome: str | None = None
    patch_cache_ttl_seconds: int = 300

    @field_validator("port")
    @classmethod
    def validate_port(cls, value: int) -> int:
        if not 1 <= value <= 65535:
            raise ValueError("port must be between 1 and 65535")
        return value

    @field_validator("internal_auth_token")
    @classmethod
    def validate_auth_token(cls, value: SecretStr | None) -> SecretStr | None:
        if value is not None and not value.get_secret_value():
            raise ValueError("internal_auth_token cannot be empty")
        return value

    @field_validator("max_request_bytes", "max_response_bytes")
    @classmethod
    def validate_positive_limit(cls, value: int) -> int:
        if value <= 0:
            raise ValueError("size limits must be positive")
        return value

    @field_validator("max_request_bytes")
    @classmethod
    def validate_request_limit(cls, value: int) -> int:
        if value > 64 * 1024:
            raise ValueError("max_request_bytes cannot exceed 64 KiB")
        return value

    @field_validator("max_response_bytes")
    @classmethod
    def validate_response_limit(cls, value: int) -> int:
        if value > 16 * 1024:
            raise ValueError("max_response_bytes cannot exceed 16 KiB")
        return value

    @field_validator("patch_cache_ttl_seconds")
    @classmethod
    def validate_patch_cache_ttl(cls, value: int) -> int:
        if not 1 <= value <= 3600:
            raise ValueError("patch_cache_ttl_seconds must be between 1 and 3600 seconds")
        return value


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


settings = get_settings()
