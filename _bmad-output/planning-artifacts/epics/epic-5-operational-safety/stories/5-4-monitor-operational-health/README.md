---
story_key: 5-4-monitor-operational-health
title: Monitor operational health
type: hardening
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-4-monitor-operational-health
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.4: Monitor operational health

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** `E5-DEC-001`, `E5-DEC-002`, `E5-DEC-003`,
`E5-DEC-005`, `E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`,
`E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`, `DISCOVERY-E5-001`,
and `DISCOVERY-E5-002`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Metrics/alerts/runbooks | Metric/SLO/alert schema | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Capture content-free bounded metrics for implemented matching, provider, Patch,
Export and system operations, and give operators actionable SLO/alert/runbook
evidence without inspecting User content.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-4-monitor-operational-health-01:** Given a supported operation completes
  or fails, record defined latency/status/failure metrics without raw User CV or
  Job Description content.

## Readiness coverage

Covers operation inventory, metric taxonomy/types/units/versions, bounded labels,
cardinality/sampling, no-data, SLO/windows, alert ownership/severity/dedupe/recovery,
dashboards, runbooks/drills, RBAC, retention/vendor, canary privacy, dependency
outage, accessibility, and evidence. Unimplemented operations emit nothing.
