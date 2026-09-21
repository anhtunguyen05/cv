---
sprint_id: sprint-10-job-description-lifecycle
title: Job Description Lifecycle
status: draft
start: null
end: null
facilitator: unassigned
goal: Stories 2.1–2.2 have complete canonical AC-to-task/verification coverage for owned Job Descriptions, immutable revisions, and logical deletion.
refinement_stories:
  - 2-1-save-a-job-description
  - 2-2-manage-saved-job-descriptions
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 10: Job Description Lifecycle

## Outcome and done signal

Stories 2.1–2.2 have complete canonical AC-to-task/verification coverage for owned Job Descriptions, immutable revisions, and logical deletion.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **2-1-save-a-job-description — Save a Job Description:** `E2-PREREQ-AUTH-001`, `E2-DEC-001` through `E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`, and `E2-COORD-JD-001`.
- **2-2-manage-saved-job-descriptions — Manage saved Job Descriptions:** approved `E2-COORD-JD-001` checkpoint from Story 2.1, `E2-DEC-001` through `E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`.

In Story 2.2, task group 03 remains blocked until Sprint 11 supplies the
approved `E2-COORD-MATCH-001` report-view checkpoint from Story 2.5.

## Dependencies and sequence

Depends on Sprint 06's approved auth consumer checkpoint
(`E2-PREREQ-AUTH-001`). Story 2.2 also depends on Story 2.1's approved
`E2-COORD-JD-001` Job Description/revision checkpoint.

Analysis begins in Sprint 11 after the Job Description source/revision contract has been refined.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
