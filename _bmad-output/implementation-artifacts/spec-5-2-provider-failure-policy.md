---
title: '5.2 Freeze the bounded provider failure policy'
type: 'feature'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-5-context.md'
  - '{project-root}/_bmad-output/implementation-artifacts/spec-5-1-audit-redaction-boundary.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/requirements.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/contract.md'
  - '{project-root}/apps/api/AGENTS.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Provider failure behavior is currently distributed across Patch
service branches, which makes retryability, malformed output, cancellation, and
trusted-result rules difficult to verify consistently.

**Approach:** Add a pure provisional failure classifier that maps a small
server-owned outcome-code allowlist and validation flag to a truthful status,
stable category, safe action, and trusted-result flag. It does not freeze
retry budgets/backoff or wire a provider, queue, job table, telemetry sink, or
API surface.

## Boundaries & Constraints

**Always:** Fail closed for unknown codes and unvalidated success;
malformed/ungrounded/validation outcomes are terminal; timeout/rate/transport
categories are retryable candidates only; terminal/cancelled decisions never
authorize a trusted result; output contains only stable categories/actions and
no provider details or user content.

**Ask First:** E5-PREREQ-AI-001, E5-DEC-002, E5-DEC-003, E5-DEC-005, and
E5-DEC-008 remain required before integrating this classifier with PatchService,
audit/metrics, a real provider, or any async operation. Retry budgets,
backoff/jitter, queue leases, cancel-winner rules, and job persistence remain
human-owned decisions.

**Never:** Do not modify PatchService behavior, PatchProviderAttempt schema,
provider bindings, queue/worker infrastructure, migrations, API responses,
frontend states, telemetry, audit persistence, or async lifecycle code.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|---|---|---|---|
| Valid success | `success`, validated result | `succeeded`, trusted result allowed, no retry | N/A |
| Retryable timeout/rate/transport | Allowlisted failure signal | `retryable_failed`, retry candidate, no trusted result | Caller-owned budget remains separate |
| Malformed result | `PATCH_PROPOSAL_INVALID` or validation failure | Terminal malformed/validation state, no trusted result | Fail closed |
| Unvalidated success | Success signal, validation false | Terminal malformed state, no trusted result | `UNVALIDATED_RESULT` |
| Cancelled/unknown | Cancel signal or unknown signal | Cancelled or rejected safe decision; never success | Stable diagnostic, no input echo |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application/Patch/PatchService.php:134-164,677-688` -- current provider deadline, exception mapping, and attempt status updates; this slice extracts no integration and leaves those branches unchanged.
- `apps/api/app/Application/Patch/PatchProposalProvider.php:7-11` -- unconstrained provider DTO boundary; the policy must consume only a server-owned outcome code and validated flag.
- `apps/api/app/Models/PatchProviderAttempt.php:20-32` -- existing attempt fields and lifecycle names; schema changes are explicitly out of scope.
- `apps/api/app/Application/OperationalSafety/SanitizedAuditEventBuilder.php:22-56` -- prior pure operational-safety convention and candidate category vocabulary; no dependency on that builder is required.
- `apps/api/tests/Feature/OperationalSafety/` -- existing pure safety test placement and PHPUnit conventions.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/app/Application/OperationalSafety/ProviderFailureClassifier.php` -- implement the exact server-owned outcome-code map, retryable/terminal/cancelled classification, validation guard, and safe decision result without framework or persistence dependencies.
- [x] `apps/api/tests/Feature/OperationalSafety/ProviderFailureClassifierTest.php` -- cover every matrix row, all retryable categories, malformed/unvalidated output, cancellation, unknown input, stable bounded output, and determinism.

**Acceptance Criteria:**
- Given an allowlisted timeout, rate-limit, or transport outcome, when evaluated, then the result is a retry candidate with no trusted result and no caller-owned budget decision.
- Given malformed, ungrounded, validation-failed, or unvalidated success output, when evaluated, then the result is terminal and cannot authorize a Patch or other trusted state.
- Given an unknown outcome code, when evaluated, then the classifier returns a stable rejection without echoing input and performs no I/O.
- Given identical inputs, when evaluated twice, then the decision is identical and contains only the documented status, category, action, retry-candidate, and trusted-result fields.

## Design Notes

The classifier is intentionally a dormant foundation. It does not decide how
many retries occur or how workers lease/cancel work; E5-DEC-003 may later add
those rules without making malformed output retryable.

## Verification

**Commands:**
- `cd apps/api && php artisan test --compact tests/Feature/OperationalSafety/ProviderFailureClassifierTest.php` -- expected: all focused cases pass.
- `cd apps/api && vendor/bin/pint --dirty --format agent` -- expected: no PHP style violations.
- `cd apps/api && php -l app/Application/OperationalSafety/ProviderFailureClassifier.php` -- expected: no syntax errors.
- `git diff --check` -- expected: no whitespace errors.

## Suggested Review Order

**Bounded classification policy**

- Start with the pure allowlist, fail-closed branches, and stable decision shape.
  [`ProviderFailureClassifier.php:44`](../../apps/api/app/Application/OperationalSafety/ProviderFailureClassifier.php#L44)

- Verify the explicit policy version and retry/trusted-result flags remain side-effect free.
  [`ProviderFailureClassifier.php:121`](../../apps/api/app/Application/OperationalSafety/ProviderFailureClassifier.php#L121)

**Verification and deferred integration**

- Check matrix coverage, validation independence, cancellation, unknown-input rejection, and determinism.
  [`ProviderFailureClassifierTest.php:12`](../../apps/api/tests/Feature/OperationalSafety/ProviderFailureClassifierTest.php#L12)

- Confirm the dormant integration boundary and required human gates are recorded separately.
  [`deferred-work.md:32`](deferred-work.md#L32)
