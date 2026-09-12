# Epic 5 Test Strategy

## Required evidence layers

| Layer | Epic 5 responsibility |
| --- | --- |
| Unit/contract | Redaction, failure taxonomy, lifecycle, policy evaluation, metric labels, quality metrics |
| Laravel/MySQL | Operator auth, append-only audit, job/deletion transitions, idempotency, locks, rollback, isolation |
| Provider/job fake | Timeout/rate/malformed/cancel/late/lost/duplicate outcomes and no partial trusted state |
| Security/privacy | Secret/content canaries, access, log/metric/trace/dashboard leak scan, destructive safeguards |
| Retention integration | Inventory, dry-run, dependency order, partial failure/rerun, backup/external policy, two-User isolation |
| Observability | Metric/label schema, cardinality budget, no-data, SLO/alert routing/dedupe/recovery and runbook drill |
| Evaluation | Versioned match corpus, repeatability, quality/counter-metrics, regression, no mutation |
| Architecture/readiness | Reproducible workload measurement, ADR review, manifest completeness, unresolved gaps |

## E5-TEST-001 — Canary corpus

Synthetic unique canaries represent CV/JD/Evidence/prompt/output, credential,
email, token, resource ID, stack/error, Unicode, and nested structured content.
Scans assert prohibited canaries never appear in audit/log/metric/trace/dashboard/
alert/evidence exports.

## E5-TEST-002 — Failure matrix

Inject provider and job timeout, rate limit, malformed output, transport loss,
worker crash, lease expiry, duplicate delivery, cancellation, late completion,
dependency outage, audit/telemetry failure, and result-write failure. Assert
bounded retry, exact terminal state, idempotency, and no partial trusted state.

## E5-TEST-003 — Deletion matrix

Use disposable MySQL/storage/external fakes with two Users, complete dependency
graph, holds/exceptions, dry-run, approval, concurrent update/request, partial
batch failure, rerun, audit, isolation, consistency, and backup expiry evidence.

## E5-TEST-004 — Matching quality corpus

Versioned synthetic CV/JD/Analysis expected reports cover required/optional,
aliases, negation, Weak Evidence, missing/unknown, score boundaries, ties/order,
Unicode, empty, adversarial and counter-metric cases. Validator is read-only.

## E5-TEST-005 — Alert and runbook drills

For each critical signal, simulate firing, no-data, duplicate, recovery and
dependency noise; verify routing, ownership, severity, dedupe/silence, safe
payload, dashboard, mitigation, escalation, and post-incident evidence.

## E5-TEST-006 — Baseline acceptance

Run exact pinned suites in approved environments, collect safe immutable
artifacts, validate manifest completeness and freshness, list gaps/waivers, and
require designated human verdict. Planning or screenshots alone do not pass.
