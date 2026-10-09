# AI Service Foundation Delivery Plan

**Status:** proposed, documentation-only plan for AI-PR-01 through AI-PR-03.
**Scope boundary:** no application/runtime/dependency change is made by this
document. The existing deterministic Patch provider remains the active baseline.

## Outcome and sequence

The first demonstrable milestone is deliberately narrow:

```text
Laravel PatchService
  -> RemotePatchProposalProvider
  -> internal FastAPI service returning a deterministic mock candidate
  -> Laravel validation
  -> one pending Patch
```

It proves a private service boundary; it does not prove LLM quality or authorize
real provider traffic. Implement increments in order and keep each independently
reviewable.

| Increment | Goal | Explicit exclusions |
| --- | --- | --- |
| AI-PR-01 | Turn `apps/worker` into a safe, testable internal-service foundation. | LLM SDKs, LangChain, LangGraph, provider key, database access, Patch creation. |
| AI-PR-02 | Freeze the framework-neutral v1 patch-candidate contract and fixtures. | Browser API changes, remote provider activation, schema generation assumptions. |
| AI-PR-03 | Add a Laravel remote adapter and worker mock while retaining the fake provider. | Real LLM traffic, queue/async lifecycle, UI redesign, Patch approval changes. |

## Preconditions and coordination

Before an increment is marked `doing`, name one owner and record branch/worktree
and exact scope in the owning Story task artifact. Reserve the shared boundaries
`PatchProposalProvider`, `PatchService`, `PatchProviderAttempt`, Laravel config,
worker bootstrap, contract fixtures, and CI. Do not silently advance
`sprint-status.yaml` from this plan.

AI-PR-03 consumes the Story 4.3 fake-provider baseline. It must preserve:

- one bounded, evidence-grounded pending Patch or no Patch;
- source CV Version/Match Report/Evidence immutability;
- Laravel-owned authorization, idempotency, source freshness, and approval;
- sanitized attempts/audits with no raw content/secrets; and
- current API behavior until a separately approved public-contract change.

## AI-PR-01 — Worker foundation

### Implementation scope

1. Replace `ThreadingHTTPServer` routing with a FastAPI application and Uvicorn
   process while preserving response compatibility for `GET /health` and
   `GET /api/health`.
2. Add `GET /ready`; it reports readiness for this service's configured
   dependencies only. It must not claim a real provider is available before a
   provider capability exists.
3. Split app construction, routers, settings, service authentication,
   exception rendering, correlation middleware, and safe telemetry into focused
   modules under `apps/worker/app/`.
4. Use typed, environment-backed settings. Missing/invalid security settings
   fail closed for internal routes; health remains safe and content-free.
5. Define an internal-service identity abstraction. Production selection between
   mTLS and a signed, short-lived audience-bound token is an explicit delivery
   decision recorded before deployment; use a deterministic test double, not a
   permanent static token. The production profile fixes issuer/audience, key or
   certificate rotation, clock skew, revocation, and replay-store failure behavior.
6. Set a 64 KiB raw request limit and 16 KiB response limit. Accept only
   `Content-Encoding: identity`; reject compressed/chunked bodies exceeding the
   raw-byte cap before JSON parsing.
7. Add a worker Dockerfile and local run instructions only after the exact
   dependency/runtime command is selected. It must expose no public product API.

### Acceptance criteria

- Existing health paths return their documented successful response.
- `/ready` returns a safe readiness response and never leaks settings/secrets.
- Unknown paths and invalid method/body/content type receive safe JSON errors.
- A protected internal placeholder route rejects absent or invalid test identity;
  the selected production identity profile supplies expiry/audience/revocation/
  replay tests before deployment.
- Every request has a validated/generated correlation ID propagated to safe
  response/error metadata; it is never user-controlled log content.
- Tests run without LLM/provider credentials or network access.
- Existing `apps/worker` health tests are migrated or preserved with equivalent
  behavior; worker CI covers the new test runner/lint/type checks selected.

### Verification

```bash
cd apps/worker
<selected Python environment command> -m pytest
<selected Python environment command> -m compileall -q app tests
```

The exact environment/bootstrap command is selected with the dependency lock in
AI-PR-01; do not add unpinned packages or make CI download implicit latest
versions. A manual smoke test must cover the two health paths, readiness,
rejection by a protected internal route (401/403), a validation error, and
correlation ID propagation.

## AI-PR-02 — Contract and fixtures

### Deliverables

- Adopt [Patch Proposal Contract v1](../contracts/ai/patch-proposal-v1.md) as
  the single semantic source.
- Add versioned JSON Schema files and valid/invalid JSON fixtures beneath
  `docs/contracts/ai/`.
- Add schema compatibility tests in Laravel and Python; Pydantic and Laravel
  DTOs/Form Requests are implementations of the contract, not competing sources.
- Publish a contract-change policy: additive changes remain v1 only when
  existing consumers can safely handle added fields; any required-field, semantic, or
  authorization change creates a new major version.

### Acceptance criteria

- Both runtimes accept every valid fixture and reject every invalid fixture.
- Closed-object checks reject unknown top-level and nested fields.
- Limits cover IDs/hashes, strings, Evidence count, target list, metadata, and
  full body bytes.
- A response can be accepted only when version, execution ID, request hash, and
  source tuple match the Laravel reservation exactly.
- Fixture corpus includes injection and content/secret canaries without storing
  real user data.

### Verification

Run contract tests in both app environments plus a diff test that confirms the
published JSON schemas and fixtures are consumed by both sides. Test reports
must not print raw fixture content when a canary case fails.

## AI-PR-03 — Remote mock adapter

### Laravel scope

1. Add `AiServiceClient` for private HTTP transport, timeout, service identity,
   correlation propagation, and safe error normalization.
2. Add `RemotePatchProposalProvider` implementing the existing
   `PatchProposalProvider` interface.
3. Add `PatchCandidateMapper`; it derives `old_value`, collection data, and
   target ownership from Laravel's locked snapshot, then feeds the existing
   `PatchService` validation path.
4. Bind the fake or remote provider by explicit configuration. The default and
   tests remain fake unless a test explicitly selects the remote mock.
5. Update provenance/attempt metadata so remote-mock outcomes cannot be labeled
   `deterministic_fake`.
6. Restrict `AiServiceClient` to an approved fixed service authority: no
   browser-derived URL component, redirects disabled, explicit proxy policy,
   bounded connect/read/write timeouts, and TLS/mTLS validation when required.

### Worker scope

1. Add only `POST /internal/v1/patch-proposals` from contract v1.
2. Authenticate, validate, correlate, and return a deterministic mock candidate
   with matching request/source binding and safe metadata.
3. Provide deterministic injected outcomes only through process-local test
   configuration or test doubles unavailable in production. Laravel transport
   fakes own malformed-wire cases; production requests cannot select outcomes.
4. Do not add a model SDK, provider API key, persistence client, job queue, or
   public endpoint.

### Acceptance criteria

- Fake mode keeps all existing Story 4.3 behavior and tests green.
- Remote-mock mode yields one validated pending Patch only for a valid fixture.
- Laravel rejects a malformed, mismatched, stale, unsupported, or ungrounded
  candidate without a Patch or partial trusted state.
- Timeout/unavailability/rate limit use the existing public failure behavior,
  preserve idempotency, and store safe attempt/audit metadata only.
- Duplicate/concurrent requests cannot create two pending Patches. PostgreSQL
  tests prove the reservation, uniqueness, rollback, and replay behavior.
- The browser never knows the AI service URL or service identity and never calls
  an internal route.

### Verification

Run the narrowest Laravel unit, feature, and PostgreSQL suites covering Patch provider,
mapper, service, and approval; worker unit/contract tests; then the existing
browser Evidence-to-Patch flow against disposable data. Separate proof of
SQLite, PostgreSQL, and browser checks in handoff evidence.

## Non-negotiable stop conditions

Stop and seek a new decision rather than extending an increment when work would:

- send real CV/JD/Evidence to an external provider;
- add a queue, `ai_executions`, or a new browser endpoint;
- choose or persist raw prompt/output retention;
- change Patch approval, source Version semantics, deterministic matching, or
  the public product API; or
- require production deployment, secrets, public routing, or external telemetry.

Those are later real-provider, async, or operational decisions defined in the
AI blueprint and Epic 5, not incidental implementation work.
