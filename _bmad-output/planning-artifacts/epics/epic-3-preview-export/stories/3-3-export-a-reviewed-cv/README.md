---
story_key: 3-3-export-a-reviewed-cv
title: Export a reviewed CV
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 3-2-preview-a-saved-cv-version
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 3-3-export-a-reviewed-cv
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/README.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/decisions.md
---

# Story 3.3: Export a reviewed CV

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E3-PREREQ-VERSION-001`, `E3-DEC-003` through
`E3-DEC-007`, `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, and
`E3-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, source rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Print intent, source equality, failure, retry, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, test layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User needs an application-ready copy of exactly the CV Version
and Template they reviewed, with safe recovery when browser export cannot run.

**Approach:** Prepare the successful Preview tuple for browser print/HTML,
invoke the approved browser action, and avoid unverifiable artifact claims or
server/worker dependencies.

</frozen-after-approval>

## References

- Global: `AD-4`, `AD-8`, `AD-10`, `AD-13` through `AD-19`,
  `SEC-STD-001` through `SEC-STD-007`, `REL-STD-001` through `REL-STD-005`,
  `A11Y-STD-001` through `A11Y-STD-003`, and `TEST-STD-001` through
  `TEST-STD-007`.
- Epic: `E3-BR-001` through `E3-BR-004`, `E3-BR-009` through `E3-BR-020`,
  `E3-CONTRACT-PREVIEW-001`, `E3-CONTRACT-EXPORT-001`,
  `E3-CONTRACT-ERROR-001`, `E3-DATA-002`, `E3-DATA-003`,
  `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, and `E3-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-3-3-export-a-reviewed-cv-01:** Given I own a saved CV Version and have a
  valid Preview, when I initiate Export, then browser print/HTML uses the
  selected Version and Template, excludes unsaved Profile values, and requires
  no AI provider.
- **AC-3-3-export-a-reviewed-cv-02:** Given Export cannot complete, when failure
  is returned, then I see an actionable error and can retry without duplicate
  unexplained state.
- **AC-3-3-export-a-reviewed-cv-03:** Given another User attempts to Export my
  CV Version, when requested, then access is denied and no artifact or source
  content is exposed.

## Readiness coverage

The package covers reviewed-source equality, browser capability, preparation,
print styling, cancel/unknown outcome, retry, no-provider/no-worker scope,
ownership, safe content, multi-tab/source races, accessibility, print fixtures,
and E2E evidence. Exact browser matrix and artifact acceptance remain open.

## Code map

- `apps/web/src/` — reviewed source state, print route/control, styles, and errors.
- `apps/api/app/`, `routes/api.php` — existing authorized Version/Preview source
  boundary and optional intent-only audit if approved.
- Shared print/fixture paths are reserved through `E3-COORD-PRINT-001` and
  `E3-COORD-TEST-001` before implementation.
