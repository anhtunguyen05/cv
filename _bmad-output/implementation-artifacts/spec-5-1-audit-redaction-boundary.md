---
title: '5.1 Build the privacy-safe audit redaction boundary'
type: 'feature'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-5-context.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-1-audit-ai-tool-calls-safely/requirements.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-1-audit-ai-tool-calls-safely/contract.md'
  - '{project-root}/apps/api/AGENTS.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Story 5.1 needs a single privacy boundary before any provider or
operator integration can be considered: operational metadata must be useful for
diagnostics while never becoming a second source of CV, Evidence, Patch, or
provider content.

**Approach:** Add a pure, versioned, fail-closed builder that accepts only
server-owned operation context and a small allowlist of outcome fields. It
returns a bounded non-authoritative event or a stable safe rejection; it does
not persist, emit, query, authorize, call a provider, or mutate product state.

## Boundaries & Constraints

**Always:** Keep event and redaction versions explicit; use exact allowlists for
fields, statuses, and failure categories; bound identifiers, labels, and
duration/retry values; reject unknown fields, forbidden keys, content canaries,
malformed input, and cross-field contradictions; mark every accepted event
`non_authoritative`; return diagnostics that contain no input values.

**Ask First:** E5-PREREQ-AI-001, E5-DEC-001, E5-DEC-002, E5-DEC-004, and
E5-DEC-008 remain required before persistence, provider emission, operator
access, retention, export, or baseline closure. This slice does not resolve
those decisions.

**Never:** Do not add a database table/model, migration, queue/worker,
provider call, API route, operator UI/RBAC, telemetry vendor, retention job,
export, raw request/response/error field, or fallback that records unsafe data.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|---|---|---|---|
| Safe success | Exact server context; `status=succeeded`, `failure_category=none`, bounded duration/retries | Accepted versioned operational event with no extra fields | N/A |
| Safe failure | Exact context; allowlisted non-success status/category | Accepted event with stable category and no provider content | N/A |
| Forbidden nested payload | Unknown nested key or value containing a secret/content canary | No event is returned or persisted; only `FORBIDDEN_CONTENT` | Fail closed |
| Unknown/oversized field | Extra key, malformed ID/label, duration above bound, or retry above bound | No event; stable schema/bounds diagnostic without echoing input | Fail closed |
| Contradictory outcome | Success with non-`none` category, or timeout with another category | No event; `OUTCOME_INCONSISTENT` | Fail closed |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application/OperationalSafety/` -- existing read-only safety validators; the new builder belongs beside them and must remain framework-independent.
- `apps/api/app/Application/Cv/CanonicalJson.php:7-31` -- canonical serialization convention if deterministic event fixtures need hashing; no product persistence is required here.
- `apps/api/app/Application/Patch/PatchService.php:106-165,674-691` -- current provider-attempt metadata and failure paths; reuse only as read-only context evidence, never as an audit store substitute or integration point in this slice.
- `apps/api/app/Models/PatchProviderAttempt.php:10-49` -- existing trusted-domain attempt model; must remain untouched because Story 5.1 explicitly separates operational audit metadata from product persistence.
- `apps/api/tests/Feature/OperationalSafety/` -- existing validator test conventions; add isolated unit-style coverage for accepted and rejected event shapes without database/provider setup.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php` -- implement the provisional v1 exact allowlist, bounded scalar validation, forbidden-key/content-canary rejection, cross-field checks, and safe diagnostic result; keep the class side-effect free.
- [x] `apps/api/tests/Feature/OperationalSafety/SanitizedAuditEventBuilderTest.php` -- cover safe success/failure, unknown and nested forbidden input, malformed or oversized values, contradictory outcomes, non-authoritative labeling, and proof that no database/provider interaction is required.

**Acceptance Criteria:**
- Given exact server-owned context and a valid allowlisted outcome, when the builder runs, then it returns a versioned event containing only bounded operational metadata and `non_authoritative=true`.
- Given an unknown field, nested secret/content canary, malformed identifier, or out-of-range scalar, when the builder runs, then it returns no event and a stable diagnostic without echoing input values.
- Given an inconsistent status/category pair, when the builder runs, then it fails closed with `OUTCOME_INCONSISTENT` and never calls persistence, providers, queues, or product services.
- Given the same valid inputs twice, when the builder runs, then the safe event is identical and contains no raw CV, JD, Evidence, prompt, output, credential, cookie, email, stack trace, or unbounded label.

## Design Notes

This is a foundation slice, not Story 5.1 completion. The provisional field
taxonomy is intentionally narrow so later approved E5-DEC-002 decisions can
extend it without having already created unsafe sinks. Rejection is preferred
to best-effort redaction because the surrounding persistence/fail policy is
still human-owned.

## Verification

**Commands:**
- `cd apps/api && php artisan test --compact tests/Feature/OperationalSafety/SanitizedAuditEventBuilderTest.php` -- expected: all focused cases pass.
- `cd apps/api && vendor/bin/pint --dirty --format agent` -- expected: no PHP style violations.
- `cd apps/api && php -l app/Application/OperationalSafety/SanitizedAuditEventBuilder.php` -- expected: no syntax errors.
- `git diff --check` -- expected: no whitespace errors.

## Suggested Review Order

**Boundary contract**

- The builder defines the only accepted operational event shape and versions.
  [`SanitizedAuditEventBuilder.php:10`](../../apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php#L10)

- Exact field validation rejects unknown input before normalization or output.
  [`SanitizedAuditEventBuilder.php:66`](../../apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php#L66)

- Context and outcome checks enforce bounded values and cross-field consistency.
  [`SanitizedAuditEventBuilder.php:134`](../../apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php#L134)

- Forbidden nested payloads and credential/content canaries fail closed.
  [`SanitizedAuditEventBuilder.php:220`](../../apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php#L220)

**Verification**

- Focused tests cover accepted events, failure states, malformed input, canaries, and determinism.
  [`SanitizedAuditEventBuilderTest.php:12`](../../apps/api/tests/Feature/OperationalSafety/SanitizedAuditEventBuilderTest.php#L12)

- Deferred findings remain explicit and separate from this pure foundation slice.
  [`deferred-work.md:22`](deferred-work.md#L22)
