# Story 1.3: Create a CV Profile — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-3-01: Freeze Profile and personal-information fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-3-create-a-cv-profile-01`, `AC-1-3-create-a-cv-profile-02`, `AC-1-3-create-a-cv-profile-03`, `AC-1-3-create-a-cv-profile-04`, `AC-1-3-create-a-cv-profile-05`
  - Scope: 01. Add executable Profile and personal-information fixtures: stable Profile contract fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-005`
  - Outcome: 01. Add executable Profile and personal-information fixtures: Add executable Profile and personal-information fixtures.
  - Acceptance: 01. Add executable Profile and personal-information fixtures: fixtures freeze fields, paths, errors, ownership and create/reload shapes.
  - Verification: 01. Add executable Profile and personal-information fixtures: schema validation and rule/AC review.

- [ ] TASK-1-3-02: Deliver protected CV Profile backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01`, `AC-1-3-create-a-cv-profile-02`, `AC-1-3-create-a-cv-profile-03`, `AC-1-3-create-a-cv-profile-04`, `AC-1-3-create-a-cv-profile-05`
  - Scope: 01. Implement Profile aggregate and persistence foundation: domain/application, migration/model/repository/transaction | 02. Implement Profile authorization policy: policy/query ownership and non-disclosure | 03. Expose Profile create and detail API: Form Request, controller/resource/routes/errors | 04. Verify Profile create backend and integration: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: Story 1.2 sign-in and sign-out behavior or approved `E1-COORD-AUTH-001` auth checkpoint before Profile authorization and authenticated API clauses
  - Outcome: 01. Implement Profile aggregate and persistence foundation: Implement Profile aggregate and persistence foundation. | 02. Implement Profile authorization policy: Implement Profile authorization policy. | 03. Expose Profile create and detail API: Expose Profile create and detail API. | 04. Verify Profile create backend and integration: Verify Profile create backend and integration.
  - Acceptance: 01. Implement Profile aggregate and persistence foundation: owned ULID Profile creates atomically with approved schema. | 02. Implement Profile authorization policy: cross-user and missing IDs are externally identical. | 03. Expose Profile create and detail API: contract fixtures pass with no partial or sensitive state. | 04. Verify Profile create backend and integration: atomicity, reload, ownership, malformed and concurrency evidence pass.
  - Verification: 01. Implement Profile aggregate and persistence foundation: unit and disposable-MySQL migration/persistence tests. | 02. Implement Profile authorization policy: policy and feature tests with two Users. | 03. Expose Profile create and detail API: Laravel feature and MySQL integration tests. | 04. Verify Profile create backend and integration: focused PHPUnit suites on declared databases.

- [ ] TASK-1-3-03: Deliver and verify CV Profile create journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01`, `AC-1-3-create-a-cv-profile-02`, `AC-1-3-create-a-cv-profile-03`, `AC-1-3-create-a-cv-profile-04`, `AC-1-3-create-a-cv-profile-05`
  - Scope: 01. Implement Profile create frontend adapter and form: feature API/schema/mutation/page/form | 02. Verify Profile create end to end: browser create/reload/error/cross-user journey
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Implement Profile create frontend adapter and form: Implement Profile create frontend adapter and form. | 02. Verify Profile create end to end: Verify Profile create end to end.
  - Acceptance: 01. Implement Profile create frontend adapter and form: accessible create/pending/error/reconcile/success states preserve valid values. | 02. Verify Profile create end to end: critical path and meaningful failures pass on disposable data. | Integrated journey acceptance closes only after `TASK-1-3-02` is done with backend evidence.
  - Verification: 01. Implement Profile create frontend adapter and form: type-check and component tests after enablement. | 02. Verify Profile create end to end: approved Playwright command. | Run the cross-layer journey check after `TASK-1-3-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-3-01` depends on `none`.
- `TASK-1-3-02` depends on `TASK-1-3-01`.
- `TASK-1-3-03` depends on `TASK-1-3-01`; its frontend or evidence work can proceed alongside `TASK-1-3-02`, and integrated acceptance closes after `TASK-1-3-02` is done.


## Coordination and verification gate
Use `E1-COORD-AUTH-001` for the session boundary, `E1-COORD-PROFILE-001` for aggregate/API/editor files, and `E1-COORD-TEST-001` for browser tooling. Task group 03 gates all five ACs.
