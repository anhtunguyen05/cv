---
sprint_id: sprint-10-job-description-lifecycle
title: Job Description Lifecycle
status: active
start: 2026-10-07
end: null
facilitator: Codex
goal: Implement and verify the Epic 2 Job Description lifecycle (Stories 2.1–2.2) on `feat/epic-2-job-fit`, preserving immutable revisions and logical deletion.
refinement_stories: []
committed_stories:
  - 2-1-save-a-job-description
  - 2-2-manage-saved-job-descriptions
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Disposable PostgreSQL 16 and the shared Playwright journey are completed
    gates; manual accessibility review remains a human checkpoint.
  - The deterministic quality baseline is approved as `DISCOVERY-E2-001` and
    is retained for Epic 5 regression validation.
---

# Sprint 10: Job Description Lifecycle

## Outcome and done signal

Stories 2.1–2.2 have complete canonical AC-to-task/verification coverage for owned Job Descriptions, immutable revisions, and logical deletion.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. Automated PostgreSQL/browser gates have passed; the sprint remains active for review and manual accessibility sign-off.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **2-1-save-a-job-description — Save a Job Description:** `E2-PREREQ-AUTH-001`, `E2-DEC-001` through `E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`, and `E2-COORD-JD-001`.
- **2-2-manage-saved-job-descriptions — Manage saved Job Descriptions:** approved `E2-COORD-JD-001` checkpoint from Story 2.1, `E2-DEC-001` through `E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`.

In Story 2.2, task group 03 consumes the report-view checkpoint now present in
the Epic 2 implementation; automated shared PostgreSQL/browser gates are
complete.

## Dependencies and sequence

Depends on Sprint 06's approved auth consumer checkpoint
(`E2-PREREQ-AUTH-001`). Story 2.2 also depends on Story 2.1's approved
`E2-COORD-JD-001` Job Description/revision checkpoint.

Analysis begins in Sprint 11 after the Job Description source/revision contract has been refined.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
