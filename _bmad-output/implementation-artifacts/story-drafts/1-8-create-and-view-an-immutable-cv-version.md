---
story_key: 1-8-create-and-view-an-immutable-cv-version
title: Create and view an immutable CV Version
type: feature
created: 2026-09-10
status: draft
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

**Readiness:** Draft; blocked by `E1-DEC-003`, `E1-DEC-004`, `E1-DEC-006`
through `E1-DEC-008`, `E1-COORD-PROFILE-001`, and
`E1-COORD-VERSION-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Mutable Profile edits cannot serve as a stable, traceable source
for later comparison and Export.

**Approach:** Create a named, complete, versioned snapshot from one owned valid
Profile and provide owned detail/list views that never reconstruct from live
Profile state.

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

## Boundaries & Constraints

**Always:** Check source ownership and versionability inside the transaction;
capture the complete approved Profile schema with a schema version; use a new
ULID and UTC timestamp; order lists deterministically; read stored snapshots.

**Ask First:** Freeze name/duplicate policy, snapshot content/versioning,
transaction and stale-source behavior, pagination/sort, cache and first Version
route through the Epic decisions.

**Never:** Snapshot unsaved browser state, update/delete a Version, read live
Profile fields to render history, disclose foreign resources, or add downstream
matching/Preview/Export behavior.

## Story-specific rules and edge cases

- **VERSION-BR-001:** Snapshot creation is all-or-nothing and contains every
  supported section, including approved empty optional sections.
- **VERSION-BR-002:** Version detail is derived only from immutable Version
  state; the source Profile is a reference, not a live content dependency.
- **VERSION-BR-003:** A Version list is owner-scoped, newest first, with a
  stable tie-breaker and approved pagination metadata.

| Scenario | Expected behavior |
| --- | --- |
| Valid owned Profile/name | Complete immutable snapshot created and reloadable |
| Later Profile edit | Existing Version content remains original |
| Multiple Versions | Only owner's Versions, deterministic reverse creation order |
| Invalid name/source state | Field/conflict error; no Version |
| Foreign Profile/Version ID | Non-disclosing not found; no snapshot/disclosure |
| Concurrent snapshot/Profile edit | Approved consistent source or safe conflict, never mixed snapshot |

</frozen-after-approval>

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

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-8-01: Add executable Version snapshot fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: snapshot/create/detail/list/error fixtures
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `E1-DEC-003`, `E1-DEC-004`, `E1-DEC-006`
  - Outcome: Add executable Version snapshot fixtures.
  - Acceptance: complete schema/version, name, source, order and failures are frozen.
  - Verification: fixture syntax, completeness and cross-Epic consumer review.
- [ ] TASK-1-8-02: Implement Version aggregate and persistence
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-01`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: migration/model/repository/immutability constraints
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema checkpoint from Story 1.3
  - Outcome: Implement Version aggregate and persistence.
  - Acceptance: stored ULID snapshot and source identity cannot be updated.
  - Verification: unit and disposable-MySQL migration/constraint tests.
- [ ] TASK-1-8-03: Implement transactional snapshot application service
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-02`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01`, `AC-1-8-create-and-view-an-immutable-cv-version-02`, `AC-1-8-create-and-view-an-immutable-cv-version-04`, `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: source read/ownership/versionability, transaction and snapshot mapper
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-VERSION-001`
  - Blocked by: `none`
  - Outcome: Implement transactional snapshot application service.
  - Acceptance: each create captures one consistent complete source or no Version.
  - Verification: unit/MySQL concurrency, rollback and mutation-regression tests.
- [ ] TASK-1-8-04: Expose Version create/detail/list APIs
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-03`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: Form Request, controllers/resources/routes/pagination/errors
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `none`
  - Outcome: Expose Version create/detail/list APIs.
  - Acceptance: owned contract fixtures pass with deterministic ordering and non-disclosure.
  - Verification: Laravel feature/contract/MySQL tests.
- [ ] TASK-1-8-05: Implement Version frontend adapter and state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-01`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: feature API/schema/query/mutation/error/cache mapping
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Implement Version frontend adapter and state.
  - Acceptance: approved fixtures map to stable create/list/detail states without live-Profile substitution.
  - Verification: type-check and adapter/state tests.
- [ ] TASK-1-8-06: Build accessible Version create/list/detail UI
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-05`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: pages/components/routes and stale/error/empty states
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build accessible Version create/list/detail UI.
  - Acceptance: keyboard-usable UI clearly distinguishes immutable Version from mutable Profile.
  - Verification: component tests and manual accessibility review.
- [ ] TASK-1-8-07: Verify Version backend contract and immutability
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-04`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `none`
  - Outcome: Verify Version backend contract and immutability.
  - Acceptance: complete snapshot, concurrency, ownership, ordering and immutability evidence passes.
  - Verification: focused PHPUnit suites against disposable MySQL.
- [ ] TASK-1-8-08: Verify Version journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-06`, `TASK-1-8-07`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01` through `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: browser create/reload/list/Profile-edit/reopen/foreign-access path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify Version journey end to end.
  - Acceptance: critical path proves snapshot remains unchanged after Profile edits.
  - Verification: approved Playwright command on disposable MySQL data.

## Dependency and concurrency map

```text
01 -> {02,05}; 02 -> 03 -> 04 -> 07; 05 -> 06; {06,07} -> 08
```

## Coordination and verification gate

Consume the frozen Profile schema through `E1-COORD-PROFILE-001`, reserve all
snapshot boundaries through `E1-COORD-VERSION-001`, and use
`E1-COORD-TEST-001` for browser tooling. Tasks 07–08 gate all five ACs.

## Spec Change Log

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.8.
