# CareerFit AI Service Foundation

Internal FastAPI foundation for the future CareerFit AI service. AI
capabilities, model providers, persistence, and queues are not enabled here.

## What it does

- exposes `GET /health`
- exposes `GET /api/health`
- exposes `GET /ready`
- exposes an authenticated internal probe at `GET/POST /internal/v1/_probe`
- returns only safe JSON status/error payloads

## Run

```bash
python -m app.main
# or, after installation:
careerfit-ai-service
```

The default service binds to `0.0.0.0:8001`. OpenAPI and interactive API docs
are disabled because this is an internal service foundation.

## Environment

```env
WORKER_HOST=0.0.0.0
WORKER_PORT=8001
WORKER_SERVICE_NAME=CareerFit Worker
# Required for the protected test probe; never commit a real secret.
WORKER_INTERNAL_AUTH_TOKEN=local-test-token
```

The current auth setting is a deterministic test seam only. A production mTLS
or short-lived audience-bound service identity profile must be approved before
deployment. The service rejects compressed request bodies and limits request
bodies to 64 KiB.

Manual smoke check:

```bash
curl -sS http://127.0.0.1:8001/health
curl -sS http://127.0.0.1:8001/api/health
curl -sS http://127.0.0.1:8001/ready
curl -i http://127.0.0.1:8001/internal/v1/_probe
curl -i -H 'Authorization: Bearer local-test-token' http://127.0.0.1:8001/internal/v1/_probe
```

The protected probe must reject the first request and return a small safe JSON
success payload for the second. Responses include a validated
`X-Correlation-ID`.

## Test

```bash
uv sync --extra test
uv run pytest -q
uv run python -m compileall -q app tests
```

`uv.lock` is committed so CI and local checks use the same resolved dependency
set. A plain `pip install '.[test]'` remains suitable for environments that do
not use uv, but it does not provide lockfile reproducibility.
