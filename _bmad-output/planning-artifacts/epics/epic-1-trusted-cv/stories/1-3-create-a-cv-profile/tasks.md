# Story 1.3: Create a CV Profile — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-3-01: Add executable Profile and personal-information fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: stable Profile contract fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Add executable Profile and personal-information fixtures.
  - Acceptance: fixtures freeze fields, paths, errors, ownership and create/reload shapes.
  - Verification: schema validation and rule/AC review.
- [ ] TASK-1-3-02: Implement Profile aggregate and persistence foundation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-04`
  - Scope: domain/application, migration/model/repository/transaction
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Implement Profile aggregate and persistence foundation.
  - Acceptance: owned ULID Profile creates atomically with approved schema.
  - Verification: unit and disposable-MySQL migration/persistence tests.
- [ ] TASK-1-3-03: Implement Profile authorization policy
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-02`
  - Covers: `AC-1-3-create-a-cv-profile-01`, `AC-1-3-create-a-cv-profile-04`
  - Scope: policy/query ownership and non-disclosure
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `1-2-sign-in-and-sign-out` or approved `E1-COORD-AUTH-001` checkpoint
  - Outcome: Implement Profile authorization policy.
  - Acceptance: cross-user and missing IDs are externally identical.
  - Verification: policy and feature tests with two Users.
- [ ] TASK-1-3-04: Expose Profile create and detail API
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-02`, `TASK-1-3-03`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-04`
  - Scope: Form Request, controller/resource/routes/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose Profile create and detail API.
  - Acceptance: contract fixtures pass with no partial or sensitive state.
  - Verification: Laravel feature and MySQL integration tests.
- [ ] TASK-1-3-05: Implement Profile create frontend adapter and form
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-03`, `AC-1-3-create-a-cv-profile-05`
  - Scope: feature API/schema/mutation/page/form
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Implement Profile create frontend adapter and form.
  - Acceptance: accessible create/pending/error/reconcile/success states preserve valid values.
  - Verification: type-check and component tests after enablement.
- [ ] TASK-1-3-06: Verify Profile create backend and integration
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-04`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Verify Profile create backend and integration.
  - Acceptance: atomicity, reload, ownership, malformed and concurrency evidence pass.
  - Verification: focused PHPUnit suites on declared databases.
- [ ] TASK-1-3-07: Verify Profile create end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-05`, `TASK-1-3-06`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: browser create/reload/error/cross-user journey
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify Profile create end to end.
  - Acceptance: critical path and meaningful failures pass on disposable data.
  - Verification: approved Playwright command.

## Dependency and concurrency map

```text
01 -> {02,05}; 02 -> 03; {02,03} -> 04 -> 06; {05,06} -> 07
```

## Coordination and verification gate

Use `E1-COORD-AUTH-001` for the session boundary,
`E1-COORD-PROFILE-001` for aggregate/API/editor files, and
`E1-COORD-TEST-001` for browser tooling. Tasks 06–07 gate all five ACs.
