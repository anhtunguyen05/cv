---
title: 'API Architecture Boundary Hardening'
type: 'refactor'
created: '2026-10-09'
status: 'implemented'
review_loop_iteration: 0
baseline_commit: 'c030ffca3742745fbd75ced7034a22e9a22adb9d'
context:
  - '/home/tuna2/notthing/cv/AGENTS.md'
  - '/home/tuna2/notthing/cv/apps/api/AGENTS.md'
  - '/home/tuna2/notthing/cv/_bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md'

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The API mixes Eloquent persistence, database transactions, HTTP providers, and business rules across Application, Presentation, Models, and Infrastructure. Domain scaffolding exists but is unused, while state values are repeated as strings.

**Approach:** Refactor the full API to module-level Ports-and-Adapters. Keep public HTTP and persisted contracts unchanged; use pure Domain policies, Value Objects, and backed enums, with Eloquent and external clients isolated in Infrastructure.

## Boundaries & Constraints

**Always:** Preserve routes, JSON envelopes, status/error codes, ownership/non-disclosure, idempotency, ETags, immutable snapshots, database schema, persisted string values, triggers, and existing behavior. Domain has no Laravel imports. Application has no Models, Eloquent, DB, or HTTP-client imports. Presentation performs HTTP mapping only. Infrastructure owns Eloquent, locks, transactions, external HTTP, and DI.

**Ask First:** Stop if implementation requires a public contract change, migration/backfill, dependency addition, provider behavior change, or a new exception/status code.

**Never:** Do not introduce full aggregate/entity mapping, generic CRUD repositories, API v2, destructive data operations, or silent changes to Epic 2/4/5 story scope.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|-----------------------------|----------------|
| Existing API request | Current valid route and payload | Byte/semantic-equivalent response and persisted result | Existing status/code/envelope preserved |
| Ownership conflict | Authenticated user requests another user's resource | Existing non-disclosing not-found behavior | No cross-owner read or write |
| Concurrent mutation | Stale revision/idempotency key or lock race | Existing conflict/replay behavior | Rollback and normalized existing error |
| Invalid domain state | Unknown enum/status/transition or malformed Value Object | Rejected before persistence | Existing validation/error mapping |
| Provider failure | Timeout, unavailable, malformed, rate-limited provider result | Existing Patch outcome and retry semantics | No trusted state written on failure |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application` -- use-case façades, module contracts/DTOs, validation, and framework-independent policies.
- `apps/api/app/Infrastructure/Persistence` -- relocated Active Record models, casts, relations, repositories, read adapters, locks, and audit persistence.
- `apps/api/app/Presentation/Http` -- HTTP-only controllers, requests, resources, responses, and status mapping; Profile/Dashboard reads use Application ports.
- `apps/api/app/Infrastructure/Workflow` -- Eloquent-backed workflow adapters implementing Application module ports while preserving the existing feature contract.
- `apps/api/app/Shared/Domain` -- unused entity/event/value-object scaffold covered only by `DomainFoundationTest`.
- `apps/api/database/migrations` -- authoritative constraints, status values, unique keys, locks/triggers, and immutable persistence contracts; no migration changes allowed.
- `apps/api/tests/Feature` and `apps/api/tests/Unit` -- current API contract, lifecycle, immutability, matching, Patch, and operational-safety regression evidence.
- `scripts/e1-verify.sh` -- fast, PostgreSQL disposable, and E2E verification entrypoints.
- `_bmad-output/implementation-artifacts/sprint-status.yaml` -- lifecycle source; Epic 6 planning must remain separate from Epic 2/4/5 stories.

## Tasks & Acceptance

**Execution:**
- [x] Create Epic 6 planning package, ADR, sprint charter, boundary rules, and recursive architecture test harness.
- [x] Move Auth/CV Eloquent models and persistence behind ports; expose CV/Auth DTOs and Presentation resources.
- [x] Refactor Job Fit persistence, revision/snapshot policies, read/write ports, and deterministic matching integration.
- [x] Refactor Evidence lifecycle, immutable answers, source pinning, expiry policy, and status enums.
- [x] Refactor Patch lifecycle, `PatchTarget`, status/retry policies, reservations/attempts, and remote/fake provider adapters.
- [x] Refactor Operational Safety persistence/querying and Dashboard read model; remove unused domain scaffold after real replacements exist.

**Acceptance Criteria:**
- Given the complete API source, when boundary tests run, then prohibited imports fail and all allowed composition-root bindings pass.
- Given every existing feature contract test, when the refactor is complete, then routes, payloads, statuses, ownership, replay, snapshots, triggers, and lifecycle behavior remain unchanged.
- Given valid persisted status strings, when models hydrate and save, then backed enums round-trip without schema/data changes.
- Given provider, lock, transaction, or domain-policy failures, when the use case executes, then existing rollback/retry/error semantics are preserved.

## Design Notes

Eloquent remains the persistence representation, not the Application contract. Repositories are cohesive workflow ports, not one repository per table. Enums use existing database string values. Application errors carry stable codes/details; Presentation owns HTTP status mapping. Transactions remain one boundary around each mutation, with Infrastructure adapters owning driver-specific locks.

## Verification

**Commands:**
- `cd apps/api && vendor/bin/pint --dirty --format agent` -- expected: modified PHP is formatted.
- `./scripts/e1-verify.sh api-fast` -- expected: fast API PHPUnit and boundary tests pass.
- `./scripts/e1-verify.sh api-pg` -- expected: disposable PostgreSQL constraints, triggers, locks, and API tests pass at integration milestone/final.
- `./scripts/e1-verify.sh e2e` -- expected: existing browser/API journey remains green at final completion.
- `git diff --check` -- expected: no whitespace errors.

Current verification evidence: `vendor/bin/phpunit --configuration phpunit.xml`
passes (190 passed, 1 skipped); `./scripts/e1-verify.sh api-fast` passes; Pint
and `git diff --check` pass. PostgreSQL and E2E gates were attempted but are
blocked in this environment because Docker access is denied.
