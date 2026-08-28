# CareerFit Worker

Lightweight worker service for CareerFit AI.

## What it does

- exposes `GET /health`
- exposes `GET /api/health`
- returns a small JSON status payload

## Run

```bash
python -m app.main
```

## Environment

```env
WORKER_HOST=0.0.0.0
WORKER_PORT=8001
WORKER_SERVICE_NAME=CareerFit Worker
```

## Test

```bash
python -m unittest discover -s tests
```

