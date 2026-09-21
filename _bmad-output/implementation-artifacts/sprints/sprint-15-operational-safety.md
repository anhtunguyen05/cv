---
sprint_id: sprint-15-operational-safety
title: Operational Safety
status: draft
start: null
end: null
facilitator: unassigned
goal: All seven Epic 5 Stories have complete canonical AC-to-task/verification coverage, with the MVP deterministic matching baseline separable from post-MVP hardening.
refinement_stories:
  - 5-1-audit-ai-tool-calls-safely
  - 5-2-handle-provider-and-background-failures
  - 5-3-retain-and-delete-user-data-safely
  - 5-4-monitor-operational-health
  - 5-5-validate-deterministic-matching-quality
  - 5-6-decide-whether-multi-agent-orchestration-is-justified
  - 5-7-verify-the-operational-safety-baseline
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
  - Story 5.5 is MVP baseline; Stories 5.1–5.4 and 5.6–5.7 are post-MVP refinement only.
  - Complete Epic 4 provider/Patch contract refinement before Epic 5 control refinement; defer production provider activation until required Epic 5 controls are complete.
---

# Sprint 15: Operational Safety

## Outcome and done signal

All seven Epic 5 Stories have complete canonical AC-to-task/verification coverage, with the MVP deterministic matching baseline separable from post-MVP hardening.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **5-1-audit-ai-tool-calls-safely — Audit AI tool calls safely:** `E5-PREREQ-AI-001`, `E5-DEC-001`, `E5-DEC-002`, `E5-DEC-004`, `E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-TEST-001`, and `DISCOVERY-E5-001`.
- **5-2-handle-provider-and-background-failures — Handle provider and background failures:** `E5-PREREQ-AI-001`, conditional `E5-PREREQ-ASYNC-001`, `E5-DEC-002`, `E5-DEC-003`, `E5-DEC-005`, `E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`, `E5-COORD-TELEMETRY-001`, and `E5-COORD-TEST-001`.
- **5-3-retain-and-delete-user-data-safely — Retain and delete User data safely:** `E5-DEC-001`, `E5-DEC-002`, `E5-DEC-004`, `E5-DEC-008`, `E5-COORD-RETENTION-001`, `E5-COORD-TEST-001`, `DISCOVERY-E5-001`, and `DISCOVERY-E5-003` for production execution.
- **5-4-monitor-operational-health — Monitor operational health:** `E5-DEC-001`, `E5-DEC-002`, `E5-DEC-003`, `E5-DEC-005`, `E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`, `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`, `DISCOVERY-E5-001`, and `DISCOVERY-E5-002`.
- **5-5-validate-deterministic-matching-quality — Validate deterministic matching quality:** `E5-PREREQ-MATCH-001`, `E5-DEC-006`, `E5-DEC-008`, `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`, and `DISCOVERY-E2-001`.
- **5-6-decide-whether-multi-agent-orchestration-is-justified — Decide whether multi-agent orchestration is justified:** `E5-PREREQ-AI-001`, `E5-DEC-007`, `E5-DEC-008`, `E5-COORD-ADR-001`, and `E5-COORD-TEST-001`.
- **5-7-verify-the-operational-safety-baseline — Verify the operational safety baseline:** completed verification evidence from Stories 5.1–5.6, `E5-DEC-001` through `E5-DEC-009`, `E5-COORD-BASELINE-001`, and `E5-COORD-TEST-001`. It may be refined here, but it cannot become ready or committed until each prerequisite Story has implementation completion and its required verification evidence recorded.

## Dependencies and sequence

Depends on Sprint 11 matching contracts for Story 5.5 and Sprints 13–14 for the Epic 4 provider/Patch contracts consumed by Stories 5.1 and 5.6. Refine the Epic 4 contracts before Epic 5 controls; production provider activation is deferred until required Epic 5 controls are complete.

Story 5.5 is the MVP baseline and its task/evidence coverage is the measurable
MVP outcome of this charter. Stories 5.1–5.4 and 5.6–5.7 are optional post-MVP
refinement only; none can be committed to implementation by this charter. Story
5.7 remains blocked as a future readiness prerequisite until Stories 5.1–5.6
have implementation completion and their required verification evidence is
recorded. Refining 5.7 now does not commit it for MVP implementation.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
