---
story_key: 3-2-preview-a-saved-cv-version
title: Preview a saved CV Version
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 1-8-create-and-view-an-immutable-cv-version
  - 3-1-select-a-template
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 3-2-preview-a-saved-cv-version
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/README.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/decisions.md
---

# Story 3.2: Preview a saved CV Version

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E3-PREREQ-VERSION-001`, `E3-DEC-001` through
`E3-DEC-004`, `E3-DEC-006`, `E3-DEC-007`, `E3-COORD-TEMPLATE-001`,
`E3-COORD-RENDER-001`, and `E3-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, snapshot/domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Preview source/projection request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, test layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User needs to assess a CV's presentation without allowing later
Profile edits, empty sections, or unsafe content to change the saved source.

**Approach:** Resolve one owned immutable CV Version and one exact active
Template version into a deterministic, safe, accessible Preview projection.

</frozen-after-approval>

## References

- Global: `AD-3`, `AD-4`, `AD-8`, `AD-10`, `AD-13` through `AD-19`,
  `SEC-STD-001` through `SEC-STD-007`, `DATA-STD-001` through `DATA-STD-008`,
  `A11Y-STD-001` through `A11Y-STD-003`, and `TEST-STD-001` through
  `TEST-STD-007`.
- Epic: `E3-BR-001` through `E3-BR-013`, `E3-BR-018` through
  `E3-BR-020`, `E3-CONTRACT-PREVIEW-001`, `E3-CONTRACT-ERROR-001`,
  `E3-DATA-002`, `E3-SEC-001` through `E3-SEC-005`,
  `E3-COORD-RENDER-001`, and `E3-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-3-2-preview-a-saved-cv-version-01:** Given I own a saved CV Version and
  selected an active Template, when I open Preview, then the system renders that
  Version with the Template, shows all supported non-empty sections, and lets
  optional empty sections remain harmless.
- **AC-3-2-preview-a-saved-cv-version-02:** Given the Profile has unsaved changes
  after the Version was created, when I open Preview, then only the saved Version
  snapshot is rendered and no unsaved draft value appears.
- **AC-3-2-preview-a-saved-cv-version-03:** Given CV content contains markup-like
  characters, when Preview renders, then content is text or safely formatted
  data and does not execute as markup or script.
- **AC-3-2-preview-a-saved-cv-version-04:** Given I use keyboard or assistive
  technology, when I review Preview, then headings, sections, and interactive
  controls have meaningful accessible structure.

## Readiness coverage

The package covers exact source identity, all supported sections, empty/long and
unsafe inputs, source isolation, Template compatibility, deterministic mapping,
route/reload behavior, accessibility, visual acceptance, FE/API integration,
ownership, failures, and browser verification.

## Code map

- `apps/api/app/`, `routes/api.php` — owned CV Version projection boundary.
- `apps/web/src/` — Preview route/state, shared renderer, sections, and errors.
- Shared renderer/fixture paths are reserved through `E3-COORD-RENDER-001` and
  `E3-COORD-TEST-001` before implementation.
