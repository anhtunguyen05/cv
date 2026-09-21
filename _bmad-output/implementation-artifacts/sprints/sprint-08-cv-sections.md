---
sprint_id: sprint-08-cv-sections
title: CV Sections
status: draft
start: null
end: null
facilitator: unassigned
goal: Stories 1.4–1.7 have complete canonical AC-to-task/verification coverage across the supported structured CV sections.
refinement_stories:
  - 1-4-manage-cv-summary-and-skills
  - 1-5-manage-education-and-experience
  - 1-6-manage-projects
  - 1-7-manage-supplementary-cv-sections
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 08: CV Sections

## Outcome and done signal

Stories 1.4–1.7 have complete canonical AC-to-task/verification coverage across the supported structured CV sections.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **1-4-manage-cv-summary-and-skills — Manage CV summary and skills:** `E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.
- **1-5-manage-education-and-experience — Manage education and experience:** `E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.
- **1-6-manage-projects — Manage projects:** `E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.
- **1-7-manage-supplementary-cv-sections — Manage supplementary CV sections:** `E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

## Dependencies and sequence

Depends on Sprint 07 Profile Core and its approved `E1-COORD-PROFILE-001`
schema/persistence checkpoint. For each Story, task group 03 remains blocked
until Sprint 09 supplies the `E1-COORD-VERSION-001` persistence fixture
checkpoint; shared verification also requires `E1-COORD-TEST-001`.

Refine the four section stories against one shared Profile schema and write/concurrency contract. Keep section scopes independently assignable after the shared checkpoint.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
