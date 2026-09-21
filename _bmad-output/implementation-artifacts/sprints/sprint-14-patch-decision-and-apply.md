---
sprint_id: sprint-14-patch-decision-and-apply
title: Patch Decision and Apply
status: draft
start: null
end: null
facilitator: unassigned
goal: Stories 4.4–4.6 have complete canonical AC-to-task/verification coverage for Patch review, human decisions, atomic apply, and regeneration lineage.
refinement_stories:
  - 4-4-review-edit-or-reject-a-patch
  - 4-5-approve-a-patch-into-a-new-cv-version
  - 4-6-regenerate-a-rejected-or-invalid-patch
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 14: Patch Decision and Apply

## Outcome and done signal

Stories 4.4–4.6 have complete canonical AC-to-task/verification coverage for Patch review, human decisions, atomic apply, and regeneration lineage.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **4-4-review-edit-or-reject-a-patch — Review, edit, or reject a Patch:** `E4-DEC-001`, `E4-DEC-004`, `E4-DEC-007` through `E4-DEC-009`, `E4-COORD-PATCH-001`, and `E4-COORD-TEST-001`.
- **4-5-approve-a-patch-into-a-new-cv-version — Approve a Patch into a new CV Version:** `E4-PREREQ-VERSION-001`, `E4-DEC-001`, `E4-DEC-004`, `E4-DEC-007` through `E4-DEC-009`, `E4-COORD-PATCH-001`, `E4-COORD-APPLY-001`, and `E4-COORD-TEST-001`.
- **4-6-regenerate-a-rejected-or-invalid-patch — Regenerate a rejected or invalid Patch:** `E4-PREREQ-VERSION-001`, `E4-PREREQ-MATCH-001`, `E4-DEC-001` through `E4-DEC-009`, `E4-COORD-INTERVIEW-001`, `E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`, `DISCOVERY-E4-001`, and `DISCOVERY-E4-002`.

## Dependencies and sequence

Depends on Sprint 13's approved provider/Patch contracts and Sprint 09's
approved `E4-PREREQ-VERSION-001` snapshot/apply contract. Story 4.6 also needs
the approved `E4-PREREQ-MATCH-001` report consumer contract.

Keep explicit User approval as the only path to a new Version. Production provider activation remains deferred until the required Epic 5 controls are complete.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
