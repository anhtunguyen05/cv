---
sprint_id: sprint-15-operational-safety
title: Operational Safety
status: review
start: 2026-10-08
end: null
facilitator: user-directed-codex
goal: Deliver the bounded disposable Epic 5 operational-safety MVP with explicit pass-with-gaps evidence and no production side effects.
refinement_stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
  - 5-3-retain-and-delete-user-data-safely
  - 5-4-monitor-operational-health
  - 5-5-validate-deterministic-matching-quality
  - 5-6-decide-whether-multi-agent-orchestration-is-justified
  - 5-7-verify-the-operational-safety-baseline
committed_stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
  - 5-3-retain-and-delete-user-data-safely
  - 5-4-monitor-operational-health
  - 5-5-validate-deterministic-matching-quality
  - 5-6-decide-whether-multi-agent-orchestration-is-justified
  - 5-7-verify-the-operational-safety-baseline
capacity_assumptions:
  - One primary implementation owner with three independent review/implementation lanes; no production deployment or external coordination is assumed.
constraints:
  - Local/CI disposable fixtures, deterministic fake providers, and no production credentials or destructive execution.
  - `pass_with_gaps` is the only accepted MVP readiness verdict while production topology, operator surfaces, vendor telemetry, external processors, and async workers remain disabled.
  - Full production rollout requires a new baseline run after the deferred gaps are explicitly approved.
---

# Sprint 15: Operational Safety

## Outcome and done signal

All seven Epic 5 Stories have a bounded implementation slice, focused tests,
and an evidence reference. The disposable MVP is reviewable and fail-closed;
production-only capabilities remain explicit gaps rather than being silently
claimed as complete.

The measurable done signal is a green API suite, complete safety fixture
validation, a pass-with-gaps baseline manifest, and explicit deferred-work
entries for production-only follow-up. Story lifecycle remains in
`sprint-status.yaml`.

## Capacity and dates

The implementation ran on 2026-10-08. No production deployment date is forecast.

## Refinement execution plan

This charter has three implementation lanes under the approved disposable boundary.

1. **MVP quality lane:** run the pinned deterministic matching validator and
   preserve its read-only corpus boundary.
2. **Operational control lane:** ship sanitized audit storage/query, bounded
   synchronous provider recovery, retention dry-run checkpoints, and metric
   event validation; no production side effects.
3. **Architecture and readiness lane:** approve the explicit orchestration
   defer plan and assemble the pass-with-gaps baseline manifest.

The implementation owner records the self-directed decision rationale in the
Epic decision register. Any production rollout still requires independent
owner/approver evidence and a superseding baseline artifact.

## Story-specific boundaries and order

- **5-1 Audit:** disposable append-only storage/query is committed; operator HTTP/RBAC/export remains disabled.
- **5-2 Provider and async failures:** synchronous classifier/retry/audit integration is committed; async queue/worker lifecycle remains deferred.
- **5-3 Retention/deletion:** count-only dry-run policy/checkpoints are committed; persistence, scheduler, external/backup deletion, and production authorization remain deferred.
- **5-4 Monitoring:** pure metric schema/privacy boundary is committed; vendor sink, dashboards, alerts, and operator views remain deferred.
- **5-5 Deterministic matching quality:** pinned read-only quality gate is committed under E5-DEC-006.
- **5-6 Orchestration ADR:** explicit single-orchestrator defer decision is committed; no agent runtime is added.
- **5-7 Safety baseline:** pass-with-gaps manifest validates all disposable evidence and records the production-only waiver.

## Dependencies and sequence

Depends on Sprint 11 matching contracts for Story 5.5 and Sprints 13–14 for the Epic 4 provider/Patch contracts consumed by Stories 5.1 and 5.6. Refine the Epic 4 contracts before Epic 5 controls; production provider activation is deferred until required Epic 5 controls are complete.

After decision gates, Stories 5.1, 5.3, 5.5, and the measurement plan for 5.6
may progress in parallel. Story 5.2 consumes the approved provider/audit
boundary; its async branch remains conditional on a named consumer. Story 5.4
consumes audit and failure taxonomies, and Story 5.7 comes last, referencing
Story-owned evidence rather than recreating it.

For the post-MVP control lane, freeze audit/redaction before its consumers
(5.1 -> 5.2 and 5.4), freeze failure/job taxonomy before telemetry consumes it
(5.2 -> 5.4), and freeze the retention inventory/policy before any destructive
workflow (5.3). The orchestration ADR (5.6) is independent of these control
implementations but consumes the same approved safe environment. Story 5.7
comes last and references Story-owned evidence rather than recreating it.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
