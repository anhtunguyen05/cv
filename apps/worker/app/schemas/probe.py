from pydantic import BaseModel, ConfigDict


class ProbeRequest(BaseModel):
    model_config = ConfigDict(extra="forbid")

    probe: bool
