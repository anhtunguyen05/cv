---
story_key: 1-8-create-and-view-an-immutable-cv-version
title: Create and view an immutable CV Version
type: feature
created: 2026-09-10
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-8-create-and-view-an-immutable-cv-version
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/data-and-lifecycle.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.8: Create and view an immutable CV Version

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`; this story package does not carry a second lifecycle status.

**Planning blockers:** `E1-DEC-003`, `E1-DEC-004`, `E1-DEC-006`
through `E1-DEC-008`, `E1-COORD-PROFILE-001`, and
`E1-COORD-VERSION-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, boundaries, domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Story-owned request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination gates |
| [Verification](verification.md) | AC traceability, verification layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Mutable Profile edits cannot serve as a stable, traceable source
for later comparison and Export.

**Approach:** Create a named, complete, versioned snapshot from one owned valid
Profile and provide owned detail/list views that never reconstruct from live
Profile state.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-4`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `REL-STD-001` through `REL-STD-005`, `A11Y-STD-001`
  through `A11Y-STD-003`, and `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-017`, `E1-CONTRACT-PROFILE-001`,
  `E1-CONTRACT-VERSION-001`, `E1-CONTRACT-ERROR-001`,
  `E1-STATE-VERSION-001`, `E1-DATA-002` through `E1-DATA-009`,
  `E1-COORD-PROFILE-001`, `E1-COORD-VERSION-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-8-create-and-view-an-immutable-cv-version-01:** Given I own a valid
  Profile, when I create a Version with a valid name, then a complete structured
  snapshot with stable ID/timestamp is stored and reloadable.
- **AC-1-8-create-and-view-an-immutable-cv-version-02:** Given I edit the source
  Profile after Version creation, when I view the Version, then its original
  snapshot remains while the Profile contains only new draft values.
- **AC-1-8-create-and-view-an-immutable-cv-version-03:** Given I own multiple
  Versions, when listed, then only my Versions are returned newest first and
  each identifies its name and source Profile.
- **AC-1-8-create-and-view-an-immutable-cv-version-04:** Given an empty or
  over-limit Version name, when I create, then a field error is returned and no
  invalid Version exists.
- **AC-1-8-create-and-view-an-immutable-cv-version-05:** Given another User
  attempts to read/create through my Profile ID, when requested, then access is
  denied without disclosing my Profile or Versions.

## Readiness coverage

Create/detail/list, invalid name, foreign access, stale/concurrent source,
reload and Profile-edit regression are explicit. Snapshot/pagination/error
contracts, transaction/immutability/ownership, validation, accessible
create/list/detail states, FE/API mapping, caching and all verification layers
are planned; exact snapshot/name/concurrency decisions remain open.

## Code Map

- `apps/api/app/`, `database/migrations/` — Version aggregate/model/repository,
  snapshot service, policy and transaction.
- Presentation HTTP boundary and `routes/api.php` — create/detail/list contract.
- `apps/web/src/features/cv-versions/`, `pages/`, router — API/query/mutation/UI.
- API/MySQL/component/Playwright test locations — verification.

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.8.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
