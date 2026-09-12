---
story_key: 1-4-manage-cv-summary-and-skills
title: Manage CV summary and skills
type: feature
created: 2026-09-10
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-4-manage-cv-summary-and-skills
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.4: Manage CV summary and skills

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

**Problem:** A User cannot represent their core qualifications as structured
summary and categorized skills on the mutable Profile.

**Approach:** Extend the shared Profile contract/editor with validated summary
and skill-category fields while preserving existing immutable Versions.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-BR-013`, `E1-BR-014`,
  `E1-CONTRACT-PROFILE-001`, `E1-CONTRACT-ERROR-001`,
  `E1-STATE-PROFILE-001`, `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-4-manage-cv-summary-and-skills-01:** Given I own a CV Profile, when I
  save a valid summary and categorized skills, then structured values persist
  and reload, and optional empty categories do not prevent saving.
- **AC-1-4-manage-cv-summary-and-skills-02:** Given an empty, over-limit, or
  invalid-type skill entry, when I save, then it receives a field error and is
  not silently normalized into an unsupported value.
- **AC-1-4-manage-cv-summary-and-skills-03:** Given I update summary or skills,
  when I save, then the mutable Profile changes and every saved Version remains
  unchanged.

## Readiness coverage

Save/reload, optional empty, invalid, stale, auth and Version-regression paths
are covered through shared Profile contracts/rules. Backend aggregate update,
authorization/transaction, validation limits, accessible editor states,
frontend/API mapping and unit/feature/MySQL/component/E2E evidence are planned.

## Code Map

- Shared Profile domain/persistence/API resource and Form Request boundaries.
- `apps/web/src/features/cv-profiles/` summary/skills schema, adapter and editor.
- Backend, component and browser test locations.

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.4.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
