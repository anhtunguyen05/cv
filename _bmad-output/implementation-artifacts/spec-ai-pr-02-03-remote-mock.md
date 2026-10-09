---
title: 'AI-PR-02 and AI-PR-03 Contract and Remote Mock'
type: 'feature'
created: '2026-10-09'
status: 'done'
review_loop_iteration: 0
baseline_commit: '54b857f1a2158df56f9b82a6ea6e62a6ad90611d'
context:
  - '/home/tuna2/notthing/cv/docs/contracts/ai/patch-proposal-v1.md'
  - '/home/tuna2/notthing/cv/docs/architecture/ai-service-foundation-delivery.md'
  - '/home/tuna2/notthing/cv/docs/architecture/ai-service-implementation.md'
  - '/home/tuna2/notthing/cv/apps/api/AGENTS.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The v1 Patch Proposal contract is documented but not executable
across Laravel and the worker, and Laravel still has only the deterministic fake
provider. There is no safe end-to-end private remote-mock path that can prove
request/response binding and create one trusted pending Patch.

**Approach:** Add a shared machine-readable fixture corpus and strict contract
models/validators in Python and Laravel, then add a deterministic worker mock,
Laravel HTTP client/provider/mapper, and a database-backed logical-operation
reservation. Keep the fake provider as the default and keep Laravel as the sole
trusted-state owner.

## Boundaries & Constraints

**Always:** Use the v1 contract as the semantic source: raw lowercase 64-hex
hashes, singular `context.source_fragment`, summary-only `target_allowlist`,
closed objects, UTF-8 byte limits, exact execution/request/source binding, and
one to five positive Evidence IDs. Laravel derives `old_value`, owns
authorization, validation, idempotency, persistence, and approval. The worker
returns only an untrusted candidate and sanitized metadata. The remote mock is
explicitly selected and deterministic; the fake remains default.

**Ask First:** None for this mock-only increment. Production identity, real
provider activation, retention, telemetry, and async execution remain separate
approval gates.

**Never:** Add an LLM/model SDK, provider key or traffic, browser/internal URL
exposure, queue, public AI route, worker database access, automatic approval,
raw prompt/output persistence, or partial trusted writes on any failure.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|-----------------------------|----------------|
| valid contract | Shared valid fixture | Both runtimes accept it | No raw canary output |
| invalid contract | Extra field, bad ID/hash, byte/count violation, wrong target | Both runtimes reject it | Stable safe validation |
| remote success | Completed interview, positive Evidence, remote mode | Worker echoes bindings; Laravel creates exactly one pending Patch | Fake remains unchanged |
| binding failure | Mismatched execution/request/source tuple | No Patch or trusted mutation | Safe `PATCH_PROPOSAL_INVALID` |
| remote failure | Auth, malformed JSON, timeout, 429, 5xx, unavailable | Existing public failure mapping and attempt metadata | No body/secret/provider detail |
| duplicate operation | Same logical operation concurrently/retried | One reservation and at most one pending Patch; identical replay is idempotent | Conflict/replay, no duplicate |

</frozen-after-approval>

## Code Map

- `docs/contracts/ai/patch-proposal-v1.md` and `docs/contracts/ai/schemas/` -- canonical v1 rules and executable JSON shapes; preserve raw-hash and summary-only decisions.
- `docs/contracts/ai/fixtures/patch-proposal-v1.json` -- shared valid/invalid corpus, including synthetic injection/secret canaries.
- `apps/worker/app/schemas/patch_proposal.py` -- strict Pydantic request/response models and UTF-8 byte validation.
- `apps/worker/app/main.py`, `app/core/security.py`, and `app/core/middleware.py` -- add the authenticated JSON-only `/internal/v1/patch-proposals` boundary while preserving health/probe behavior.
- `apps/api/app/Application/Patch/PatchProposalProvider.php` and `DeterministicFakePatchProposalProvider.php` -- existing provider seam and default fake; do not regress the legacy shape.
- `apps/api/app/Application/Patch/PatchService.php:49-237,529-622` -- existing reservation, provider call, authoritative validation, idempotency, and persistence path; integrate remote request metadata without moving trust to Python.
- `apps/api/app/Application/Cv/CanonicalJson.php` and `ProfileDocument.php` -- existing canonical JSON/ULID utilities to reuse for contract hashing and ID grammar.
- `apps/api/app/Providers/AppServiceProvider.php` and `apps/api/config/` -- explicit `fake|remote` binding and fixed worker authority/token settings; fake is default.
- `apps/api/database/migrations/`, `PatchProviderAttempt.php`, and related tests -- add immutable execution/request/source bindings and unique logical-operation reservation evidence.
- `apps/api/tests/Unit/Patch/`, `apps/api/tests/Feature/Patch/`, `apps/worker/tests/test_contract.py`, and both CI workflows -- compatibility, transport, feature, concurrency, and fixture-trigger coverage.

## Tasks & Acceptance

**Execution:**

- [x] `docs/contracts/ai/fixtures/patch-proposal-v1.json` -- publish shared cases for valid boundaries, closed-object failures, binding mismatches, transport failures, and canaries -- make both runtimes consume one corpus.
- [x] `apps/worker/app/schemas/patch_proposal.py` and `apps/worker/tests/test_contract.py` -- implement strict request/response validation and fixture tests -- reject coercion, unsafe fields, and byte-limit violations.
- [x] `apps/api/app/Application/Patch/Contracts/` and `apps/api/tests/Unit/Patch/` -- implement Laravel contract DTO/validator, canonical request hash, and fixture compatibility tests -- keep schema and binding checks explicit without adding a dependency.
- [x] `apps/worker/app/main.py` and boundary modules -- add authenticated deterministic `POST /internal/v1/patch-proposals` -- echo exact bindings and return a summary replacement derived from the first positive Evidence answer.
- [x] `apps/api/app/Application/Patch/`, `config/ai.php`, and `AppServiceProvider.php` -- add fixed-authority client, remote provider, candidate mapper, metadata seam, and explicit provider switch -- normalize failures safely and retain fake default.
- [x] `apps/api/database/migrations/`, `PatchProviderAttempt.php`, and `PatchService.php` -- persist execution/request/source bindings and enforce unique logical operation -- prevent duplicate pending Patches while preserving current idempotency and retry behavior.
- [x] `apps/api/tests/Feature/Patch/` and worker tests -- prove remote success, malformed/mismatched/stale/timeout/429/transport failures, fake regression, source immutability, and replay/concurrency behavior -- verify no partial trusted state.

**Acceptance Criteria:**

- Given any valid shared fixture, when Python and Laravel validate it, then both accept it; every invalid fixture is rejected without printing canary content.
- Given a valid authenticated remote request, when the worker mock handles it, then it returns HTTP 200 with exact contract/source bindings and no `old_value` or trusted identifiers.
- Given completed Laravel Evidence and remote mode, when generation succeeds, then Laravel revalidates the candidate and creates exactly one pending Patch with non-fake provenance.
- Given malformed, mismatched, stale, unauthorized, timeout, rate-limited, or unavailable remote behavior, when generation runs, then no Patch or source mutation is created and the existing safe public error contract is preserved.
- Given fake mode or a repeated/concurrent logical operation, when generation runs, then the fake baseline remains green and at most one pending Patch exists with deterministic replay.

## Spec Change Log

## Design Notes

Request hashes use the existing sorted-key JSON encoder with the documented
`careerfit:patch-proposal-request:v1\n` prefix and `request_hash` omitted. The
remote provider maps only `candidate.proposed_text` into Laravel's existing
proposal shape; Laravel derives `old_value` from the locked CV snapshot.

## Verification

**Commands:**

- `cd apps/worker && UV_CACHE_DIR=/tmp/careerfit-uv-cache uv run --frozen pytest -q` -- expected: worker foundation and contract tests pass offline.
- `cd apps/worker && UV_CACHE_DIR=/tmp/careerfit-uv-cache uv run --frozen python -m compileall -q app tests` -- expected: zero exit.
- `cd apps/api && vendor/bin/phpunit --configuration phpunit.fast.xml tests/Unit/Patch` -- expected: contract unit tests pass without a database.
- `cd apps/api && vendor/bin/phpunit --configuration phpunit.xml tests/Unit/Patch tests/Feature/Patch` -- expected: contract, remote, fake, and lifecycle tests pass against the test database.
- `cd apps/api && vendor/bin/pint --dirty --format agent` -- expected: modified PHP files are formatted.
- `python3 -m json.tool docs/contracts/ai/fixtures/patch-proposal-v1.json >/dev/null` -- expected: fixture corpus parses.
- `git diff --check` -- expected: no whitespace errors.

**Manual checks:** Start worker with documented token, call the private mock
route with a valid fixture, verify correlation/auth behavior, and confirm the
browser-facing API never exposes the worker URL or service token.

## Suggested Review Order

**Generation orchestration**

- Reserve one logical operation, bind source state, and preserve fake compatibility.
  [`PatchService.php:64`](../../apps/api/app/Application/Patch/PatchService.php#L64)

- Persist immutable execution and source bindings for attempts and replay.
  [`2026_10_09_000019_create_patch_generation_reservations_table.php:13`](../../apps/api/database/migrations/2026_10_09_000019_create_patch_generation_reservations_table.php#L13)

**Contract and trust boundary**

- Enforce closed shapes, byte limits, canonical hashes, and response binding.
  [`PatchProposalContractValidator.php:22`](../../apps/api/app/Application/Patch/Contracts/PatchProposalContractValidator.php#L22)

- Send only the authenticated, bounded private request and map safe failures.
  [`AiServiceClient.php:22`](../../apps/api/app/Application/Patch/AiServiceClient.php#L22)

- Derive the trusted legacy proposal only after remote candidate validation.
  [`PatchCandidateMapper.php:13`](../../apps/api/app/Application/Patch/PatchCandidateMapper.php#L13)

**Worker mock boundary**

- Return a deterministic summary candidate while echoing exact request bindings.
  [`patch_proposals.py:38`](../../apps/worker/app/api/patch_proposals.py#L38)

- Reject coercion, unknown fields, unsafe text, and invalid UTF-8 byte sizes.
  [`patch_proposal.py:21`](../../apps/worker/app/schemas/patch_proposal.py#L21)

**Verification and compatibility**

- Exercise remote success, failures, stale source, replay, and egress minimization.
  [`RemotePatchProposalTest.php:27`](../../apps/api/tests/Feature/Patch/RemotePatchProposalTest.php#L27)

- Exercise shared fixtures, endpoint candidate selection, authentication, and safe errors.
  [`test_contract.py:27`](../../apps/worker/tests/test_contract.py#L27)
