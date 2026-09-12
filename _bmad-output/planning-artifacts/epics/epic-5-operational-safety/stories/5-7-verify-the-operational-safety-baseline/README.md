---
story_key: 5-7-verify-the-operational-safety-baseline
title: Verify the operational safety baseline
type: verification
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
  - 5-3-retain-and-delete-user-data-safely
  - 5-4-monitor-operational-health
  - 5-5-validate-deterministic-matching-quality
  - 5-6-decide-whether-multi-agent-orchestration-is-justified
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-7-verify-the-operational-safety-baseline
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.7: Verify the operational safety baseline

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** completion evidence from committed Stories 5.1–5.6,
`E5-DEC-001` through `E5-DEC-009`, `E5-COORD-BASELINE-001`, and
`E5-COORD-TEST-001`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Readiness/verdict/gaps | Evidence manifest schema | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Run the approved cross-capability safety suite, validate fresh immutable evidence,
publish a scoped human verdict with unresolved production decisions, and include
the reviewed orchestration ADR without hiding failures or gaps.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-7-verify-the-operational-safety-baseline-01:** Given hardening Stories
  are complete, when operational verification runs, provider failure, redaction,
  retention, deletion, telemetry and deterministic validation checks pass,
  unresolved production decisions are listed, and the multi-agent ADR is available.

## Readiness coverage

Covers committed-scope prerequisite check, manifest schema, exact environment/
versions/commands/artifacts, evidence freshness/access/integrity, cross-control
scenarios, known gaps/waivers/expiry, pass/pass-with-gaps/fail, human approval,
release/kill criteria, remediation ownership, rerun/supersession, and privacy.
It references rather than duplicates Story-owned verification.
