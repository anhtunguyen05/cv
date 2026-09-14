# Epic 5 Shared Contracts

All values are proposals until decisions are approved and stable operational
contracts/runbooks are published.

## E5-CONTRACT-AUDIT-001 — Sanitized operational event

Fields: ULID/event time, actor type and approved non-content reference, operation/
tool, provider/model, contract/prompt/tool-schema versions, resource types and
opaque approved references, correlation/attempt, outcome/status, duration bucket,
failure category, retry count, token/cost bucket if approved, and redaction version.
No raw CV/JD/Evidence/prompt/output, credentials, cookie/token, email, or stack
trace is accepted.

## E5-CONTRACT-JOB-001 — Optional async lifecycle

```text
queued -> running -> succeeded | retryable_failed -> queued | terminal_failed | cancelled
```

A job pins owner, operation, source/result contract versions, idempotency key,
attempt/lease/deadline, progress category, terminal reason, and result reference.
Late/stale workers cannot overwrite terminal state. This contract is dormant
until a named async operation is approved.

## E5-CONTRACT-RETENTION-001 — Policy and execution

Each policy row identifies data class, system/store, authority/owner, subject
scope, retention trigger/period, action (`retain|delete|anonymize|aggregate`),
dependency order, hold/exception, dry-run counts, idempotency/recovery, audit
summary, and verification query. A deletion request returns a non-sensitive
request/status summary and per-class terminal/retryable outcome.

## E5-CONTRACT-METRIC-001 — Telemetry taxonomy

Metric definitions freeze name/type/unit, event source, status/failure taxonomy,
bounded labels, forbidden fields, sampling, aggregation/window, SLO/threshold,
alert severity/owner/runbook, and version. Operations cover matching, provider,
Patch, Export, dependencies, and system health only when implemented.

## E5-CONTRACT-EVALUATION-001 — Matching quality result

The validator consumes a versioned synthetic fixture corpus and exact matching
engine/rule/schema/metric/tool versions. It emits repeatability, classification/
score metrics, threshold/counter-metric results, fixture-level safe diagnostics,
overall pass/fail, and immutable artifact reference. It never accepts a User
Match Report as writable input.

## E5-CONTRACT-ADR-001 — Orchestration decision evidence

Record workload/scenario corpus, single-orchestrator versions/config, measured
quality/latency/cost/reliability/operability results, observed limitation,
alternatives, added boundaries/risks, safety invariants, migration/rollback,
owner/date/review trigger, and decision `adopt|defer`. `adopt` does not itself
authorize implementation.

## E5-CONTRACT-BASELINE-001 — Readiness evidence manifest

One immutable manifest references exact commits/config/contract versions,
environment, fixture versions, test/evaluation/security/deletion dry-run/alert
drill/ADR artifacts, timestamps/owners/results, known gaps, unresolved decisions,
waivers/expiry, and overall scoped verdict. It contains no sensitive payload.
