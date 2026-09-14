---
story_key: 5-6-decide-whether-multi-agent-orchestration-is-justified
title: Decide whether multi-agent orchestration is justified
type: architecture-decision
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-3-generate-a-patch-proposal
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-6-decide-whether-multi-agent-orchestration-is-justified
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.6: Decide whether multi-agent orchestration is justified

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** `E5-PREREQ-AI-001`, `E5-DEC-007`, `E5-DEC-008`,
`E5-COORD-ADR-001`, and `E5-COORD-TEST-001`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Measurement/alternatives/safety | Evidence/ADR schema | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Measure the production-representative single orchestrator, decide adopt or defer
multi-agent decomposition through a reviewed ADR, and preserve all existing
tool, validation, ownership and human-approval boundaries.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-6-decide-whether-multi-agent-orchestration-is-justified-01:** Given the
  single orchestrator is measured, the architecture review records the observed
  limitation or deferral, and any proposed agents preserve existing safety boundaries.
- **AC-5-6-decide-whether-multi-agent-orchestration-is-justified-02:** Given no
  measured limitation justifies multiple agents, review adds no component and
  records a future review trigger.

## Readiness coverage

Covers workload corpus, baseline versions/config/environment, quality/latency/
cost/reliability/operability metrics, limitation threshold, alternative designs,
security/data/tool/write/human-control impact, failure modes, migration/rollback,
ADR owner/date/trigger, independent review, and proof that defer changes no code.
