# Story 1.8: Create and view an immutable CV Version — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

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
