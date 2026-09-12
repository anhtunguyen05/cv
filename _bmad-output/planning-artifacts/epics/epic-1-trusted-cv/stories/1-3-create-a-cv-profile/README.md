---
story_key: 1-3-create-a-cv-profile
title: Create a CV Profile
type: feature
created: 2026-09-10
story_owner: unassigned
depends_on_stories:
  - 1-2-sign-in-and-sign-out
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-3-create-a-cv-profile
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.3: Create a CV Profile

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

**Problem:** An authenticated User has no owned structured source for their CV.

**Approach:** Create one valid mutable CV Profile aggregate with title and
approved personal-information fields, then retrieve the same owned resource.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `HTTP-CONTRACT-001` through
  `HTTP-CONTRACT-006`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-CONTRACT-PROFILE-001`,
  `E1-CONTRACT-ERROR-001`, `E1-STATE-PROFILE-001`, `E1-DATA-002` through
  `E1-DATA-007`, `E1-SEC-004` through `E1-SEC-008`,
  `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-3-create-a-cv-profile-01:** Given I am authenticated, when I create a
  CV Profile with a valid title and personal information, then it is created
  for me, reloadable, and belongs only to me.
- **AC-1-3-create-a-cv-profile-02:** Given an empty or over-limit title, when I
  create the Profile, then the request has a field error and no partial Profile.
- **AC-1-3-create-a-cv-profile-03:** Given invalid personal-information types or
  values, when I create the Profile, then invalid fields are rejected and valid
  unrelated data is not persisted as a partial record.
- **AC-1-3-create-a-cv-profile-04:** Given another User accesses the Profile by
  ID, when requested, then the Profile is not disclosed and remains unchanged.
- **AC-1-3-create-a-cv-profile-05:** Given invalid/incomplete form data, when
  validation fails, then relevant controls expose keyboard-usable errors and
  valid entered values remain.

## Readiness coverage

Create/reload, invalid, cross-user, duplicate and expiry behavior are covered.
Shared Profile/error contracts, aggregate transaction, ownership, layered
validation, accessible form states, API mapping, and all test layers are
referenced; exact schema and concurrency remain open decisions.

## Code Map

- `apps/api/app/`, `database/migrations/` — Profile aggregate, repository,
  policy, transaction and ULID persistence.
- `apps/api/routes/api.php`, Presentation HTTP boundary — create/detail contract.
- `apps/web/src/features/cv-profiles/`, `pages/`, `app/router/` — API/schema/form.
- API/web/E2E test locations — planned verification.

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.3.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
