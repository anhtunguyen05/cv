---
epic_key: epic-5
title: Operate CareerFitCV Safely at Scale
created: 2026-09-12
source: _bmad-output/planning-artifacts/epics.md
stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
  - 5-3-retain-and-delete-user-data-safely
  - 5-4-monitor-operational-health
  - 5-5-validate-deterministic-matching-quality
  - 5-6-decide-whether-multi-agent-orchestration-is-justified
  - 5-7-verify-the-operational-safety-baseline
---

# Epic 5: Operate CareerFitCV Safely at Scale

## Purpose

Epic 5 establishes evidence-backed operational controls as provider and async
capabilities are introduced: sanitized traceability, honest failure states,
retention/deletion, privacy-safe telemetry, deterministic quality validation,
architecture restraint, and a reproducible safety baseline.

This package plans the work only. It does not authorize production deletion,
operator access, provider traffic, infrastructure, monitoring, or multi-agent
components.

## Scope

- Append-only sanitized AI/provider audit metadata distinct from product truth.
- Shared provider and optional async job failure taxonomy/lifecycle.
- Approved retention/deletion data-class and dependency policy with dry-run,
  isolation, idempotency, audit, and failure recovery.
- Bounded-cardinality content-free metrics, SLOs, alerts, dashboards, and runbooks.
- Versioned deterministic matching evaluation without mutating reports/User data.
- Measured single-orchestrator assessment and explicit ADR to adopt or defer.
- Cross-capability operational verification and unresolved-decision inventory.

## Out of scope

- Assuming a production operator role/RBAC, provider, queue, scheduler, storage,
  observability vendor, or legal retention period before approval.
- Raw CV/JD/Evidence/prompt/output in ordinary logs, metrics, or dashboards.
- Destructive production commands without scoped dry-run, approval, backup/
  recovery, isolation, and audit controls.
- Adding multi-agent components without measured limitation and preserved safety boundaries.

## Source requirements

- `NFR-1`: privacy, isolation, redaction, retention, and deletion control.
- `NFR-2`: source/version/audit traceability without alternative trusted state.
- `NFR-3`: clear accessible User/operator states and actionable runbooks.
- `NFR-4`: bounded retry, terminal outcomes, atomicity, recovery, monitoring.
- `NFR-5`: deterministic/versioned evaluation and regression detection.

## Global and cross-Epic references

- Architecture: `AD-2`, `AD-4` through `AD-8`, `AD-13` through `AD-19`.
- Standards: `API-STD-001` through `API-STD-007`, `SEC-STD-001` through
  `SEC-STD-007`, `DATA-STD-001` through `DATA-STD-008`, `REL-STD-001`
  through `REL-STD-005`, and `TEST-STD-001` through `TEST-STD-007`.
- Epic 2: deterministic Match Report and `DISCOVERY-E2-001` quality baseline.
- Epic 4: provider/Patch contracts and `E4-PREREQ-OPS-001`.

## Epic package

| Artifact | Owns |
| --- | --- |
| [Business rules](business-rules.md) | Audit, failure, retention, telemetry, quality, ADR, and baseline invariants |
| [Contracts](contracts.md) | Operational event, job, deletion, metric, evaluation, and ADR schemas |
| [Data and lifecycle](data-and-lifecycle.md) | Data classes, state machines, retention/deletion dependencies, immutability |
| [Security and access](security-and-access.md) | Operator authorization, redaction, secrets, isolation, destructive safeguards |
| [UX and validation](ux-and-validation.md) | User/operator states, dashboards, runbooks, deletion, accessibility |
| [Test strategy](test-strategy.md) | Failure injection, privacy, deletion, metrics, evaluation, readiness evidence |
| [Decisions](decisions.md) | Human-owned decisions and coordination records blocking readiness |

## Story packages

| Story | Package |
| --- | --- |
| 5.1 Audit AI tool calls safely | [Open](stories/5-1-audit-ai-tool-calls-safely/README.md) |
| 5.2 Handle provider and background failures | [Open](stories/5-2-handle-provider-and-background-failures/README.md) |
| 5.3 Retain and delete User data safely | [Open](stories/5-3-retain-and-delete-user-data-safely/README.md) |
| 5.4 Monitor operational health | [Open](stories/5-4-monitor-operational-health/README.md) |
| 5.5 Validate deterministic matching quality | [Open](stories/5-5-validate-deterministic-matching-quality/README.md) |
| 5.6 Decide whether multi-agent orchestration is justified | [Open](stories/5-6-decide-whether-multi-agent-orchestration-is-justified/README.md) |
| 5.7 Verify the operational safety baseline | [Open](stories/5-7-verify-the-operational-safety-baseline/README.md) |

## Dependency map

```text
5.1 Audit ----┐
5.2 Failures -┼-> 5.4 Monitor ----┐
5.3 Retention --------------------┼-> 5.7 Safety baseline
5.5 Match quality ---------------┤
5.6 Orchestration ADR -----------┘
```

Stories 5.1, 5.3, 5.5, and the measurement plan for 5.6 may progress in
parallel after their source and policy gates. Story 5.7 consumes evidence; it
does not duplicate individual Story verification.

## Epic Definition of Ready

- Every applicable decision has an owner, resolution, and dated evidence.
- Data inventory/classification, operator identity/access, environments, audit/
  metric taxonomy, job lifecycle, retention/deletion, matching threshold,
  orchestrator measurement, and runbooks are approved.
- Every AC maps to atomic tasks/evidence and each shared boundary has one owner.

## Epic Definition of Done

- All committed Epic 5 Stories are `done`; Story 5.5 MVP status is reported
  independently of post-MVP Stories.
- Operational metadata contains no prohibited content or unbounded labels.
- Failures never produce false success or partial trusted state.
- Retention/deletion is scoped, isolated, idempotent, auditable, and recoverable
  under approved policy.
- Matching evaluation, ADR, alert/runbook, security, MySQL, and baseline checks
  pass with immutable evidence and explicit unresolved production decisions.
