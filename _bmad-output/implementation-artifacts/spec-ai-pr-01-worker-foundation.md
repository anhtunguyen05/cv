---
title: 'AI-PR-01 Worker Foundation'
type: 'feature'
created: '2026-10-09'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e86bfe601c739d95ee4ec751557c2022243420f7'
context:
  - '/home/tuna2/notthing/cv/docs/architecture/ai-service-foundation-delivery.md'
  - '/home/tuna2/notthing/cv/docs/architecture/ai-service-implementation.md'
  - '/home/tuna2/notthing/cv/docs/contracts/ai/patch-proposal-v1.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** `apps/worker` is a standard-library HTTP health skeleton. It cannot
provide a safe, typed, testable internal boundary for the future AI service.

**Approach:** Replace the hand-written HTTP server with FastAPI/Uvicorn while
preserving both health paths, then add a content-free readiness endpoint,
typed settings, protected internal-route foundation, safe correlation handling,
bounded JSON input, and deterministic offline tests. Do not add LLM logic.

## Boundaries & Constraints

**Always:** Preserve successful response shape for `GET /health` and
`GET /api/health`; keep health endpoints content-free; fail closed for protected
internal routes; reject unsupported methods/content types/oversized bodies with
safe JSON; never log request bodies, credentials, or secrets; keep tests offline;
retain Python `>=3.11` compatibility and the existing worker CI entry points.

**Ask First:** A production identity profile (mTLS or signed short-lived
audience-bound token) must be selected before production deployment. This PR
only defines the abstraction and deterministic test double.

**Never:** Add LangChain/LangGraph/LLM SDKs, provider keys, database access,
Laravel business writes, queues, public product routes, browser integration, or
real provider traffic.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|----------------------------|----------------|
| health | `GET /health` or `GET /api/health` | `200`, existing `{status, service, component, timestamp}` shape | No secret/config detail |
| readiness | `GET /ready` | `200` safe readiness payload when process/config is ready | Non-ready state is safe and non-sensitive |
| protected route without identity | Internal request without valid test identity | Route is not entered | `401`/`403` safe JSON, no reason disclosure |
| malformed request | Unsupported method/content type/invalid JSON | Handler rejects before capability logic | Stable safe JSON error, no rejected body echo |
| oversized/encoded request | Body over 64 KiB or unsupported encoding | Reject before JSON parsing | `413`/`415`, no decompression amplification |
| correlation | Missing or malformed `X-Correlation-ID` | Generate or replace with validated opaque ID | Return only safe correlation metadata |
| unknown path | Any unregistered route | No capability execution | `404` safe JSON |

</frozen-after-approval>

## Code Map

- `apps/worker/pyproject.toml` -- current Python package metadata; add only the
  foundation dependencies and a reproducible test command.
- `apps/worker/app/main.py` -- current `ThreadingHTTPServer` bootstrap and
  routing; replace with an app factory/Uvicorn entry point.
- `apps/worker/app/core/config.py` -- current environment defaults; replace with
  typed settings and safe readiness/security configuration.
- `apps/worker/app/api/health.py` and `apps/worker/app/schemas/health.py` --
  preserve the existing health payload contract and add readiness schema.
- `apps/worker/tests/test_health.py` -- current live-server tests; migrate to
  the selected FastAPI test seam and retain both health aliases.
- `apps/worker/README.md` -- update run/environment/test instructions for the
  new process, health aliases, readiness, and local protected-route smoke test.
- `.github/workflows/ci-worker.yml` -- preserve Python 3.11 CI; align install,
  compile, and test commands with the locked foundation dependencies.
- `docs/architecture/ai-service-foundation-delivery.md` -- frozen scope and
  acceptance criteria; no AI-PR-02/03 work in this spec.

## Tasks & Acceptance

**Execution:**

- [x] `apps/worker/pyproject.toml` -- declare pinned-compatible FastAPI,
  Uvicorn, Pydantic settings, HTTP test, and test dependencies -- make the
  foundation reproducible without LLM/provider packages.
- [x] `apps/worker/app/{main.py,api,core,schemas}` -- implement app factory,
  health/readiness routers, typed settings, safe errors, correlation middleware,
  protected internal-route abstraction, and raw request limits -- establish the
  boundary without adding capability logic.
- [x] `apps/worker/tests/` -- cover health aliases, readiness, invalid method/
  content/JSON, auth rejection, correlation, body/encoding bounds, and unknown
  paths -- prove the I/O matrix offline.
- [x] `apps/worker/README.md` and `.github/workflows/ci-worker.yml` -- document
  and execute the reproducible foundation checks -- keep local and CI behavior
  aligned.

**Acceptance Criteria:**

- Given a clean Python 3.11 environment, when CI installs the worker package and
  runs its checks, then compile and all worker tests pass without network or
  provider credentials.
- Given either existing health URL, when requested, then the response remains
  `200` with the current health payload fields and no sensitive settings.
- Given `/ready`, when foundation settings are valid, then it returns safe
  readiness; invalid security configuration cannot make an internal route open.
- Given an internal request with missing/invalid test identity, when it reaches
  a protected placeholder route, then it is rejected with safe JSON and no body
  or credential echo.
- Given unsupported content encoding, invalid JSON/content type, or a body above
  64 KiB, when sent to a protected route, then it is rejected before capability
  parsing and does not amplify/decompress input.
- Given missing/malformed correlation input, when any request is handled, then a
  validated opaque correlation ID is available to safe response/error metadata.

## Verification

**Commands:**

- `cd apps/worker && python3 -m compileall -q app tests` -- expected: zero exit.
- `cd apps/worker && python3 -m pytest -q` -- expected: all worker tests pass
  offline using the same runner as CI.

**Manual checks:** Start the service with the documented command; request both
health aliases and `/ready`; verify the protected placeholder route rejects an
unauthenticated request and responses never contain environment secrets.

## Suggested Review Order

**Application boundary**

- Start with the app factory and middleware composition that defines the private service boundary.
  [`main.py:15`](../../apps/worker/app/main.py#L15)

- Check fail-closed identity validation before request parsing reaches protected routes.
  [`security.py:16`](../../apps/worker/app/core/security.py#L16)

- Inspect raw request, content-encoding, content-type, correlation, and response-size enforcement.
  [`middleware.py:10`](../../apps/worker/app/core/middleware.py#L10)

**Configuration and schemas**

- Verify typed environment settings and hard 64 KiB/16 KiB safety caps.
  [`config.py:7`](../../apps/worker/app/core/config.py#L7)

- Confirm closed probe input and safe readiness response shapes.
  [`probe.py:1`](../../apps/worker/app/schemas/probe.py#L1)

**Verification and operations**

- Review the offline tests covering compatibility, authentication, limits, correlation, and safe failures.
  [`test_health.py:43`](../../apps/worker/tests/test_health.py#L43)

- Follow the documented smoke flow and reproducible local test command.
  [`README.md:39`](../../apps/worker/README.md#L39)

- Confirm CI installs and executes the committed dependency resolution.
  [`ci-worker.yml:37`](../../.github/workflows/ci-worker.yml#L37)

- Inspect the resolved Python dependency set used by the worker checks.
  [`uv.lock:1`](../../apps/worker/uv.lock#L1)

**AI integration context**

- Read the implementation blueprint for the Laravel-owned trust boundary and deferred provider scope.
  [`ai-service-implementation.md:16`](../../docs/architecture/ai-service-implementation.md#L16)

- Read the v1 contract alongside its schema before starting AI-PR-02 work.
  [`patch-proposal-v1.md:6`](../../docs/contracts/ai/patch-proposal-v1.md#L6)
