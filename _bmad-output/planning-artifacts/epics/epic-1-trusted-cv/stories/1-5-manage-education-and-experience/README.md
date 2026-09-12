---
story_key: 1-5-manage-education-and-experience
title: Manage education and experience
type: feature
created: 2026-09-10
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-5-manage-education-and-experience
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.5: Manage education and experience

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`; this story package does not carry a second lifecycle status.

**Planning blockers:** `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, boundaries, domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Story-owned request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination gates |
| [Verification](verification.md) | AC traceability, verification layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot maintain education and work history as identifiable
structured entries for later comparison.

**Approach:** Add approved education/experience item schemas and atomic
add/edit/remove behavior to the owned mutable Profile.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-BR-013`, `E1-BR-014`,
  `E1-CONTRACT-PROFILE-001`, `E1-STATE-PROFILE-001`, `E1-DATA-002` through
  `E1-DATA-007`, `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-5-manage-education-and-experience-01:** Given I own a CV Profile, when
  I add valid education/experience entries, then each is stored in its section
  with a stable item ID and can be edited or removed.
- **AC-1-5-manage-education-and-experience-02:** Given an entry missing required
  fields or having invalid types, when I save, then it is rejected and no
  malformed entry is persisted.
- **AC-1-5-manage-education-and-experience-03:** Given I remove an entry, when I
  save, then it leaves the current Profile and existing Versions remain unchanged.

## Readiness coverage

Add/edit/remove, malformed, stale/foreign, reload and Version-regression paths
are explicit. Shared Profile contract, aggregate transaction, ownership,
nested validation, accessible repeated editor, mapping and all test layers are
referenced; field/date semantics remain human-gated.

## Code Map

- Shared Profile aggregate/persistence/API and policy boundaries.
- `apps/web/src/features/cv-profiles/` history schema, API and repeated editor.
- Backend, component and browser verification locations.

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.5.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
