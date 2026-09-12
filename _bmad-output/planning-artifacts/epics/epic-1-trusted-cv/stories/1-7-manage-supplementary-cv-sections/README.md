---
story_key: 1-7-manage-supplementary-cv-sections
title: Manage supplementary CV sections
type: feature
created: 2026-09-10
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-7-manage-supplementary-cv-sections
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.7: Manage supplementary CV sections

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

**Problem:** A User cannot represent certificates, languages, and activities
beyond core experience.

**Approach:** Add three optional, structured, independently editable section
types to the shared mutable Profile contract and editor.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-014`, `E1-CONTRACT-PROFILE-001`,
  `E1-STATE-PROFILE-001`, `E1-DATA-002` through `E1-DATA-007`,
  `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-7-manage-supplementary-cv-sections-01:** Given I own a Profile, when I
  add valid certificates, languages, or activities, then each is stored in its
  section and can be edited or removed from the mutable Profile.
- **AC-1-7-manage-supplementary-cv-sections-02:** Given one empty/invalid-type
  entry, when I save, then it receives a field error and valid existing entries
  are not silently deleted.
- **AC-1-7-manage-supplementary-cv-sections-03:** Given all supplementary
  sections are empty, when I save/view, then the Profile remains valid and the
  empty sections do not block Version creation or Preview.

## Readiness coverage

Add/edit/remove, invalid, all-empty, foreign/stale, reload and Version-regression
behavior are explicit. Shared Profile contract, atomicity, ownership,
validation, accessible repeated editors, mapping and all verification layers
are referenced; section schemas remain human-gated.

## Code Map

- Shared Profile aggregate/persistence/API/policy boundaries.
- `apps/web/src/features/cv-profiles/` supplementary schemas and editors.
- Backend, component and browser verification locations.

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.7.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
