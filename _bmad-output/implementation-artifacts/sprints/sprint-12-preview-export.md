---
sprint_id: sprint-12-preview-export
title: Preview and Export
status: draft
start: null
end: null
facilitator: unassigned
goal: Stories 3.1–3.3 have complete canonical AC-to-task/verification coverage for Template selection, saved Version Preview, and browser print/HTML Export.
refinement_stories:
  - 3-1-select-a-template
  - 3-2-preview-a-saved-cv-version
  - 3-3-export-a-reviewed-cv
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 12: Preview and Export

## Outcome and done signal

Stories 3.1–3.3 have complete canonical AC-to-task/verification coverage for Template selection, saved Version Preview, and browser print/HTML Export.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **3-1-select-a-template — Select a Template:** `E3-DEC-001` through `E3-DEC-003`, `E3-DEC-006`, `E3-DEC-007`, `E3-COORD-TEMPLATE-001`, and `E3-COORD-TEST-001`.
- **3-2-preview-a-saved-cv-version — Preview a saved CV Version:** `E3-PREREQ-VERSION-001`, `E3-DEC-001` through `E3-DEC-004`, `E3-DEC-006`, `E3-DEC-007`, `E3-COORD-TEMPLATE-001`, `E3-COORD-RENDER-001`, and `E3-COORD-TEST-001`.
- **3-3-export-a-reviewed-cv — Export a reviewed CV:** `E3-PREREQ-VERSION-001`, `E3-DEC-003` through `E3-DEC-007`, `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, and `E3-COORD-TEST-001`.

## Dependencies and sequence

Depends on Sprint 06's approved authenticated-owner boundary and Sprint 09's
approved `E3-PREREQ-VERSION-001` reader contract. Template selection, renderer,
print, and shared verification gates are the Epic 3 checkpoints
`E3-COORD-TEMPLATE-001`, `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, and
`E3-COORD-TEST-001`.

MVP Export is browser print/HTML. Server PDF and worker work remain outside this plan absent separate approval.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
