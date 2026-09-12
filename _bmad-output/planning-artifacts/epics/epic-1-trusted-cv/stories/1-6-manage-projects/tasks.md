# Story 1.6: Manage projects — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

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
