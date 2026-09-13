---
story_key: 2-2-manage-saved-job-descriptions
title: Manage saved Job Descriptions
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 2-1-save-a-job-description
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 2-2-manage-saved-job-descriptions
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/README.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/data-and-lifecycle.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/decisions.md
---

# Story 2.2: Manage saved Job Descriptions

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** approved `E2-COORD-JD-001` checkpoint from Story 2.1,
`E2-DEC-001` through `E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Revision, deletion, history, security, validation, UI, and edge cases |
| [Contract](contract.md) | List/update/delete and historical-source contract slice |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, test layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A saved target role becomes stale, but mutating or physically
deleting source data would make earlier Analysis and Match Reports unreliable.

**Approach:** Let the owner update by creating a new immutable current revision
or logically delete the resource while preserving pinned historical results.

</frozen-after-approval>

## References

- Global: `AD-2`, `AD-4`, `AD-11`, `AD-14` through `AD-19`, Global API,
  error, security, data, reliability, accessibility, and testing standards.
- Epic: `E2-BR-001` through `E2-BR-006`, `E2-BR-010`, `E2-BR-013`,
  `E2-BR-017`, `E2-CONTRACT-JD-001`, `E2-CONTRACT-JD-REVISION-001`,
  `E2-CONTRACT-ERROR-001`, `E2-DATA-001`, `E2-DATA-003`,
  `E2-COORD-JD-001`, `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-2-2-manage-saved-job-descriptions-01:** Given I own a saved Job
  Description, when I update its raw text or optional role information with
  valid data, then the system creates a new immutable revision, makes it
  current, reflects it on the saved Job Description, and keeps its owner.
- **AC-2-2-manage-saved-job-descriptions-02:** Given an earlier revision has an
  Analysis or Match Report, when I update the Job Description, then the earlier
  revision, Analysis, and Match Report remain unchanged and reproducible, while
  the new revision has no successful Analysis until I request it.
- **AC-2-2-manage-saved-job-descriptions-03:** Given I own a saved Job
  Description, when I delete it, then it is logically deleted, disappears from
  active lists, cannot be edited/analyzed/used for a new comparison, and
  unrelated Job Descriptions remain unchanged.
- **AC-2-2-manage-saved-job-descriptions-04:** Given I own a Match Report from a
  deleted Job Description revision, when I open it, then the report remains
  accessible with its original revision and Analysis and clearly identifies
  the deleted parent.

## Readiness coverage

The package covers immutable revision creation, stale and competing updates,
analysis invalidation for the new revision, logical deletion, active list,
historical reproducibility, mixed ownership, delete/update/analyze races,
accessible warnings, FE cache invalidation, MySQL constraints, and E2E history.
Exact concurrency, idempotency, pagination, deleted-source presentation, and
tooling values remain open decisions.

## Code map

- Laravel Job Description aggregate/repository/service/policy and migrations.
- `/api/v1` list/update/delete resources, requests, and exceptions.
- Vue Job Description query/mutation cache, edit/list/detail UI, and deleted
  historical context consumed by report views.
- PHPUnit/MySQL/Vitest/Playwright verification locations.

## Spec change log

- 2026-09-12: Initial Story package created from canonical Story 2.2.
