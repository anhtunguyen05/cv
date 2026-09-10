---
story_key: 1-6-manage-projects
title: Manage projects
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-6-manage-projects
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.6: Manage projects

**Readiness:** Draft; blocked by `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot record practical project Evidence separately from a
flat skills list.

**Approach:** Add validated project entries with stable identity and approved
role, technology, and bullet structures to the mutable Profile.

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-BR-013`, `E1-BR-014`,
  `E1-CONTRACT-PROFILE-001`, `E1-STATE-PROFILE-001`, `E1-DATA-002` through
  `E1-DATA-007`, `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Give projects stable server ULIDs; keep technology and bullet data
independently retrievable; validate nested structure/limits; enforce Profile
ownership; preserve siblings and Versions.

**Ask First:** Freeze project fields, role/URL/date semantics, technology and
bullet representation, ordering, limits, duplicates and save/removal behavior.

**Never:** Invent Evidence, collapse projects into skill strings, accept nested
IDs from another Profile, persist partial malformed projects, or mutate Versions.

## Story-specific rules and edge cases

- **PROJECT-BR-001:** A project requires an approved name and its technology
  and bullet collections retain their distinct meanings.
- **PROJECT-BR-002:** Editing/removing one stable project ID preserves every
  unrelated valid project and section.

| Scenario | Expected behavior |
| --- | --- |
| Valid project | Stable ID; role/technology/bullets reload independently |
| Invalid nested field/limit | Project-level field errors; no partial project |
| Edit/remove | Only targeted current Profile entry changes |
| Foreign/stale project ID | Non-disclosing/conflict outcome; no mutation |
| Existing Version | Original projects remain unchanged |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-6-manage-projects-01:** Given I own a CV Profile, when I add a valid
  project with name and structured details, then it has a stable ID, technology
  and bullets reload independently, and it belongs only to my Profile.
- **AC-1-6-manage-projects-02:** Given invalid fields, unsupported nested data,
  or over-limit bullets, when I save, then the project is rejected and no
  partial malformed project is persisted.
- **AC-1-6-manage-projects-03:** Given I edit/remove a project, when I save,
  then the current Profile changes and previously saved Versions do not.

## Readiness coverage

Add/edit/remove, nested invalid, foreign/stale, reload and Version-regression
behavior are explicit. Shared Profile contract/transaction/ownership,
validation, accessible repeated UI, FE/API mapping and all test layers are
referenced; project schema and limits remain human-gated.

## Code Map

- Shared Profile aggregate/persistence/API/policy boundaries.
- `apps/web/src/features/cv-profiles/` project schema, API and repeated editor.
- Backend, component and browser verification locations.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-6-01: Extend Profile fixtures with project schema
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: project fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with project schema.
  - Acceptance: fields, nested shapes, IDs, paths and limits are frozen.
  - Verification: schema/AC review.
- [ ] TASK-1-6-02: Implement project domain and persistence behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-01`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: Profile aggregate project rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement project domain and persistence behavior.
  - Acceptance: add/edit/remove is atomic with stable project IDs.
  - Verification: unit and MySQL persistence/Version-regression tests.
- [ ] TASK-1-6-03: Expose project update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-02`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: request/resource/use case/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose project update contract.
  - Acceptance: nested errors and targeted mutations match fixtures.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-6-04: Build project repeated editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-01`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: frontend schema/API/components/state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build project repeated editor.
  - Acceptance: accessible project rows preserve valid nested/sibling data and errors.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-6-05: Verify project backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-03`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify project backend behavior.
  - Acceptance: nested validation, ownership, atomicity and immutability pass.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-6-06: Verify projects end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-04`, `TASK-1-6-05`
  - Covers: `AC-1-6-manage-projects-01` through `AC-1-6-manage-projects-03`
  - Scope: browser add/edit/remove/reload path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify projects end to end.
  - Acceptance: critical and failure paths pass on disposable data.
  - Verification: approved Playwright command.

## Dependency and concurrency map

```text
01 -> {02,04}; 02 -> 03 -> 05; {04,05} -> 06
```

## Coordination and verification gate

Reserve shared Profile files through `E1-COORD-PROFILE-001`; task 05 waits for
the `E1-COORD-VERSION-001` persistence fixture checkpoint; task 06 uses
`E1-COORD-TEST-001` and gates all three ACs.

## Spec Change Log

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.6.
