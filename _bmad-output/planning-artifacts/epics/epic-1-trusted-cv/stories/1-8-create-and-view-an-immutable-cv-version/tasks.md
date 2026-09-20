# Story 1.8: Create and view an immutable CV Version — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-8-01: Freeze immutable Version fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01`, `AC-1-8-create-and-view-an-immutable-cv-version-02`, `AC-1-8-create-and-view-an-immutable-cv-version-03`, `AC-1-8-create-and-view-an-immutable-cv-version-04`, `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: 01. Add executable Version snapshot fixtures: snapshot/create/detail/list/error fixtures
  - Coordination: `E1-COORD-VERSION-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-006`
  - Outcome: 01. Add executable Version snapshot fixtures: Add executable Version snapshot fixtures.
  - Acceptance: 01. Add executable Version snapshot fixtures: complete schema/version, name, source, order and failures are frozen.
  - Verification: 01. Add executable Version snapshot fixtures: fixture syntax, completeness and cross-Epic consumer review.

- [ ] TASK-1-8-02: Deliver immutable Version backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-01`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01`, `AC-1-8-create-and-view-an-immutable-cv-version-02`, `AC-1-8-create-and-view-an-immutable-cv-version-03`, `AC-1-8-create-and-view-an-immutable-cv-version-04`, `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: 01. Implement Version aggregate and persistence: migration/model/repository/immutability constraints | 02. Implement transactional snapshot application service: source read/ownership/versionability, transaction and snapshot mapper | 03. Expose Version create/detail/list APIs: Form Request, controllers/resources/routes/pagination/errors | 04. Verify Version backend contract and immutability: unit/feature/MySQL suite
  - Coordination: `E1-COORD-VERSION-001`, `E1-COORD-PROFILE-001`
  - Blocked by: approved E1-COORD-PROFILE-001 Profile schema checkpoint from Story 1.3
  - Outcome: 01. Implement Version aggregate and persistence: Implement Version aggregate and persistence. | 02. Implement transactional snapshot application service: Implement transactional snapshot application service. | 03. Expose Version create/detail/list APIs: Expose Version create/detail/list APIs. | 04. Verify Version backend contract and immutability: Verify Version backend contract and immutability.
  - Acceptance: 01. Implement Version aggregate and persistence: stored ULID snapshot and source identity cannot be updated. | 02. Implement transactional snapshot application service: each create captures one consistent complete source or no Version. | 03. Expose Version create/detail/list APIs: owned contract fixtures pass with deterministic ordering and non-disclosure. | 04. Verify Version backend contract and immutability: complete snapshot, concurrency, ownership, ordering and immutability evidence passes.
  - Verification: 01. Implement Version aggregate and persistence: unit and disposable-MySQL migration/constraint tests. | 02. Implement transactional snapshot application service: unit/MySQL concurrency, rollback and mutation-regression tests. | 03. Expose Version create/detail/list APIs: Laravel feature/contract/MySQL tests. | 04. Verify Version backend contract and immutability: focused PHPUnit suites against disposable MySQL.

- [ ] TASK-1-8-03: Deliver and verify Version user journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-8-01`
  - Covers: `AC-1-8-create-and-view-an-immutable-cv-version-01`, `AC-1-8-create-and-view-an-immutable-cv-version-02`, `AC-1-8-create-and-view-an-immutable-cv-version-03`, `AC-1-8-create-and-view-an-immutable-cv-version-04`, `AC-1-8-create-and-view-an-immutable-cv-version-05`
  - Scope: 01. Implement Version frontend adapter and state: feature API/schema/query/mutation/error/cache mapping | 02. Build accessible Version create/list/detail UI: pages/components/routes and stale/error/empty states | 03. Verify Version journey end to end: browser create/reload/list/Profile-edit/reopen/foreign-access path
  - Coordination: `E1-COORD-VERSION-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Implement Version frontend adapter and state: Implement Version frontend adapter and state. | 02. Build accessible Version create/list/detail UI: Build accessible Version create/list/detail UI. | 03. Verify Version journey end to end: Verify Version journey end to end.
  - Acceptance: 01. Implement Version frontend adapter and state: approved fixtures map to stable create/list/detail states without live-Profile substitution. | 02. Build accessible Version create/list/detail UI: keyboard-usable UI clearly distinguishes immutable Version from mutable Profile. | 03. Verify Version journey end to end: critical path proves snapshot remains unchanged after Profile edits. | Integrated journey acceptance closes only after `TASK-1-8-02` is done with backend evidence.
  - Verification: 01. Implement Version frontend adapter and state: type-check and adapter/state tests. | 02. Build accessible Version create/list/detail UI: component tests and manual accessibility review. | 03. Verify Version journey end to end: approved Playwright command on disposable MySQL data. | Run the cross-layer journey check after `TASK-1-8-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-8-01` depends on `none`.
- `TASK-1-8-02` depends on `TASK-1-8-01`.
- `TASK-1-8-03` depends on `TASK-1-8-01`; its frontend or evidence work can proceed alongside `TASK-1-8-02`, and integrated acceptance closes after `TASK-1-8-02` is done.


## Coordination and verification gate
Consume the frozen Profile schema through `E1-COORD-PROFILE-001`, reserve all snapshot boundaries through `E1-COORD-VERSION-001`, and use `E1-COORD-TEST-001` for browser tooling. Task group 03 gates all five ACs.
