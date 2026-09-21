---
sprint_id: sprint-07-profile-core
title: Profile Core
status: draft
start: null
end: null
facilitator: unassigned
goal: Story 1.3 has complete canonical AC-to-task/verification coverage for creating and retrieving the owned Profile, including schema and concurrency decisions.
refinement_stories:
  - 1-3-create-a-cv-profile
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 07: Profile Core

## Outcome and done signal

Story 1.3 has complete canonical AC-to-task/verification coverage for creating and retrieving the owned Profile, including schema and concurrency decisions.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **1-3-create-a-cv-profile — Create a CV Profile:** `E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

## Dependencies and sequence

Depends on Sprint 06 account access for the authenticated User boundary; the
consumer gate is the approved `E1-COORD-AUTH-001` checkpoint.

The Profile schema and ownership checkpoint established here are prerequisites for the CV section slices.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
