# Epic 5 Business Rules

## Audit and trusted-state separation

- **E5-BR-001:** Operational audit/telemetry is non-authoritative metadata and
  cannot modify or substitute CV Version, JD, Analysis, Match Report, Evidence,
  Patch, Export, or job result state.
- **E5-BR-002:** AI/provider audit records contain exact approved operation/tool,
  provider/model, contract/prompt version, sanitized actor/resource references,
  status, duration, failure category, correlation, and timestamps.
- **E5-BR-003:** Credentials and prohibited raw User/provider content never enter
  ordinary audit, logs, metrics, traces, dashboards, alerts, or error responses.
- **E5-BR-004:** Operator access is server-authorized, least-privilege, audited,
  non-disclosing, and distinct from normal authenticated User access.

## Failure and asynchronous work

- **E5-BR-005:** Provider errors map to stable retryable/terminal categories;
  retries are bounded, jittered as approved, idempotent, observable, and never
  turn malformed output into trusted state.
- **E5-BR-006:** Every introduced async operation has a versioned job/result
  contract and terminal state; lease/timeout/cancel/retry/late-result semantics
  prevent false success and duplicate trusted writes.
- **E5-BR-007:** No generic queue/job implementation is required until a named
  async product operation and owner are approved.

## Retention and deletion

- **E5-BR-008:** Every data class has an approved owner, authority/legal basis,
  retention clock/start, action, dependency order, exception/hold, and evidence.
- **E5-BR-009:** Deletion/anonymization is scoped from server-derived User/data
  identity, dry-runnable, idempotent, resumable, and isolated from other Users.
- **E5-BR-010:** Trusted records retained by policy remain internally consistent;
  dependent non-authoritative data is deleted/anonymized in approved order.
- **E5-BR-011:** Deletion audit proves request/scope/outcome without retaining the
  sensitive content or reversible identity the deletion intended to remove.

## Telemetry and quality

- **E5-BR-012:** Metrics use allowlisted bounded-cardinality labels and exclude
  raw content, emails, resource IDs, prompts, stack traces, and secrets.
- **E5-BR-013:** SLOs/alerts define window, threshold, severity, owner, runbook,
  dedupe/silence and recovery; dashboards alone are not incident readiness.
- **E5-BR-014:** Deterministic matching evaluation pins fixture corpus, source
  snapshot, expected result, engine/rule/schema version, metric version, and tool version.
- **E5-BR-015:** Evaluation never changes User data or stored Match Reports; a
  regression fails evidence/CI under approved thresholds and requires explicit review.

## Architecture and verification

- **E5-BR-016:** Multi-agent adoption requires a reproducible measured limitation
  of the single orchestrator, alternative analysis, preserved safety boundaries,
  rollback, owner, and approved ADR.
- **E5-BR-017:** Without sufficient evidence the ADR explicitly defers; no agent,
  router, tool permission, or alternate write path is added.
- **E5-BR-018:** Operational baseline evidence records exact environment,
  versions, commands/runs, timestamps, results, artifact references, owners, and
  unresolved decisions without secrets or raw User content.
- **E5-BR-019:** Multiple tasks may be `doing` with one owner and declared scope;
  shared audit/job/retention/telemetry/evaluation/config files require coordination.
- **E5-BR-020:** No production-destructive operation, provider traffic, alert,
  retention schedule, or architecture expansion is authorized by planning artifacts alone.
