# Epic 5 UX and Validation

## E5-VAL-001 — Audit operator experience

Provide bounded filter/pagination, safe metadata, clear operational-versus-
product-state labels, redaction version, empty/loading/error/access-denied states,
timezone clarity, accessible tables/details, and no raw-content search/export.

## E5-VAL-002 — Failure and job states

Users see actionable retryable/terminal/cancelled/in-progress outcomes without
provider internals or false success. Repeated actions are guarded; refresh and
lost response reconcile exact operation/job state. Operators see correlation,
category, attempts, runbook, and bounded diagnostics.

## E5-VAL-003 — Retention and deletion

Explain eligible scope, consequences, retained/anonymized exceptions, approval/
confirmation, progress, partial/retryable/terminal outcome, support path, and
non-sensitive evidence. Destructive controls require meaningful accessible
confirmation and cannot rely on color alone.

## E5-VAL-004 — Monitoring

Dashboards label metric version, environment, units, aggregation, data delay,
missing/no-data versus zero, SLO window, alert state, owner, and runbook. Avoid
unbounded tables, User identifiers, or misleading averages without distributions.

## E5-VAL-005 — Quality and architecture evidence

Evaluation/ADR views or reports identify exact versions, sample scope, metrics,
thresholds, counter-metrics, failures, limitations, decision, owner, and next
review trigger. A pass never implies unmeasured production safety.

## E5-VAL-006 — Baseline verdict

Final evidence distinguishes pass, pass-with-gaps, and fail; lists unresolved
production decisions and expiring waivers; links each claim to an immutable safe
artifact; and remains accessible to approved reviewers.
