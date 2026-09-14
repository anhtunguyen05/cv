# Story 5.2 — Requirements

Shared [rules](../../business-rules.md), [contracts](../../contracts.md),
[data/lifecycle](../../data-and-lifecycle.md), and [test strategy](../../test-strategy.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Classify safe retryable/terminal failures; bound timeout/retry/cost;
use idempotency/lease/precondition; persist terminal truth; reconcile late/lost/
duplicate results; emit sanitized audit/metrics; expose actionable safe states.

**Never:** Retry malformed terminal data blindly; mark timeout/worker receipt as
success; commit partial/unvalidated product state; expose provider internals;
build a generic queue without a named operation; let stale worker overwrite terminal state.

| Scenario | Expected behavior |
| --- | --- |
| Timeout/rate/transport | Approved bounded retry or terminal actionable state |
| Malformed/ungrounded output | Terminal validation failure and no trusted result |
| Worker crash/lease expiry/duplicate/late result | One valid terminal outcome under lease/idempotency rules |
| Cancel races with completion | Frozen winner rule; no false success/partial write |
| Status read after refresh/lost response | Exact current state and allowed action |
| Audit/telemetry dependency fails | Product state remains safe under approved fail policy |

</frozen-after-approval>

Backend owns classifier, retry policy, conditional job state machine, result
transaction and status API. Frontend owns typed User states/retry/cancel. Ops
owns audit, metrics, alert/runbook. Verification requires fault injection and
real MySQL for state races.
