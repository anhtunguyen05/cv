---
story_key: 3-1-select-a-template
title: Select a Template
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 1-2-sign-in-and-sign-out
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 3-1-select-a-template
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/README.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-3-preview-export/decisions.md
---

# Story 3.1: Select a Template

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E3-DEC-001` through `E3-DEC-003`, `E3-DEC-006`,
`E3-DEC-007`, `E3-COORD-TEMPLATE-001`, and `E3-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, catalog rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Catalog/selection request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, test layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot identify which presentation Templates are currently
available and compatible before opening a CV Preview.

**Approach:** Expose a safe, versioned active Template catalog and let the User
select one exact entry without creating a Preview or Export as a side effect.

</frozen-after-approval>

## References

- Global: `AD-2` through `AD-4`, `AD-10`, `AD-13` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-001` through `SEC-STD-007`,
  and `A11Y-STD-001` through `TEST-STD-007`.
- Epic: `E3-BR-001` through `E3-BR-008`, `E3-BR-018` through
  `E3-BR-020`, `E3-CONTRACT-TEMPLATE-001`, `E3-CONTRACT-ERROR-001`,
  `E3-DATA-001`, `E3-COORD-TEMPLATE-001`, and `E3-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-3-1-select-a-template-01:** Given I am authenticated, when I request
  available Templates, then the system returns only active Templates available
  to me and gives enough information to select one.
- **AC-3-1-select-a-template-02:** Given a Template is inactive or unavailable,
  when I attempt to select it, then the system rejects the selection and creates
  no Preview or Export from it.

## Readiness coverage

The package covers catalog behavior, stable identity/version, availability and
compatibility, stale selection, safe metadata, authorization, complete chooser
states, FE/API mapping, accessibility, fixtures, and browser verification.
Exact API, Template publication, selection persistence, and test matrix values
remain in the Epic decision register.

## Code map

- `apps/api/app/`, `routes/api.php` — Template catalog/policy/API boundaries.
- `apps/web/src/` — catalog adapter, selection state, and accessible chooser.
- Shared contract/fixture locations are assigned by `E3-COORD-TEMPLATE-001`
  before implementation.
