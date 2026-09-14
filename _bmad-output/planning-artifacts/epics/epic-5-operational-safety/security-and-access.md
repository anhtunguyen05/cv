# Epic 5 Security and Access

## E5-SEC-001 — Operator identity and authorization

- Define operator identity, authentication strength, role/permission matrix,
  environment scope, time-bound access, separation of duties, and revocation.
- Every audit query, deletion approval/run, policy change, alert action, evidence
  access, and operational export is server-authorized and itself audited.
- Operator UI/API must not be assumed available until this decision is approved.

## E5-SEC-002 — Redaction and data minimization

- Classify every source field before instrumentation; allowlist metadata instead
  of denylist-only redaction.
- Test nested strings, structured errors, URLs, headers, stack traces, exception
  context, provider payloads, resource labels, and correlation against secrets,
  identifiers, and raw career content.
- Redaction failure must not fall back to unsafe logging.

## E5-SEC-003 — Destructive safeguards

- Retention/deletion requires exact environment, scoped IDs/policy version,
  dry-run counts/sample-free summaries, approval, concurrency guard, transaction/
  checkpoint, idempotency, rate limit, recovery/backup policy, and blast-radius limits.
- Never run against production-like or shared data from a test/reset command.
- Cross-User isolation and referential consistency are verified before and after.

## E5-SEC-004 — External systems

- Provider/observability/storage subprocessors require approved region, purpose,
  fields, retention, deletion propagation, access, encryption, incident, and exit policy.
- Credentials use secret management, rotation, least privilege, and redaction;
  they never enter fixtures, dashboards, alerts, evidence, or browser bundles.

## E5-SEC-005 — Abuse, integrity, and evidence

- Bound operator queries/exports, telemetry cardinality, job retries, provider
  cost, deletion batch size, evaluation workload, and evidence artifact size.
- Evidence is tamper-evident or version-controlled as approved; failures,
  waivers, and unresolved decisions cannot be silently omitted.
- Multi-agent alternatives preserve the same ownership, tool allowlist,
  validation, human approval, secrets, audit, and kill-switch boundaries.
