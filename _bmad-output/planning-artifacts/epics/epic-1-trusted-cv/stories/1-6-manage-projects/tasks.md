# Story 1.6: Manage projects — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-6-01: Freeze project fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-6-manage-projects-01`, `AC-1-6-manage-projects-02`, `AC-1-6-manage-projects-03`
  - Scope: 01. Extend Profile fixtures with project schema: project fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-005`
  - Outcome: 01. Extend Profile fixtures with project schema: Extend Profile fixtures with project schema.
  - Acceptance: 01. Extend Profile fixtures with project schema: fields, nested shapes, IDs, paths and limits are frozen.
  - Verification: 01. Extend Profile fixtures with project schema: schema/AC review.

- [ ] TASK-1-6-02: Deliver project persistence contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-01`
  - Covers: `AC-1-6-manage-projects-01`, `AC-1-6-manage-projects-02`, `AC-1-6-manage-projects-03`
  - Scope: 01. Implement project domain and persistence behavior: Profile aggregate project rules/repository mapping | 02. Expose project update contract: request/resource/use case/errors | 03. Verify project backend behavior: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved E1-COORD-PROFILE-001 Profile schema persistence checkpoint from Story 1.3; E1-COORD-VERSION-001 persistence fixture checkpoint
  - Outcome: 01. Implement project domain and persistence behavior: Implement project domain and persistence behavior. | 02. Expose project update contract: Expose project update contract. | 03. Verify project backend behavior: Verify project backend behavior.
  - Acceptance: 01. Implement project domain and persistence behavior: add/edit/remove is atomic with stable project IDs. | 02. Expose project update contract: nested errors and targeted mutations match fixtures. | 03. Verify project backend behavior: nested validation, ownership, atomicity and immutability pass.
  - Verification: 01. Implement project domain and persistence behavior: unit and MySQL persistence/Version-regression tests. | 02. Expose project update contract: Laravel feature/contract tests. | 03. Verify project backend behavior: focused PHPUnit suites.

- [ ] TASK-1-6-03: Deliver and verify project editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-6-01`
  - Covers: `AC-1-6-manage-projects-01`, `AC-1-6-manage-projects-02`, `AC-1-6-manage-projects-03`
  - Scope: 01. Build project repeated editor: frontend schema/API/components/state | 02. Verify projects end to end: browser add/edit/remove/reload path
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Build project repeated editor: Build project repeated editor. | 02. Verify projects end to end: Verify projects end to end.
  - Acceptance: 01. Build project repeated editor: accessible project rows preserve valid nested/sibling data and errors. | 02. Verify projects end to end: critical and failure paths pass on disposable data. | Integrated journey acceptance closes only after `TASK-1-6-02` is done with backend evidence.
  - Verification: 01. Build project repeated editor: type-check and Vitest after enablement. | 02. Verify projects end to end: approved Playwright command. | Run the cross-layer journey check after `TASK-1-6-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-6-01` depends on `none`.
- `TASK-1-6-02` depends on `TASK-1-6-01`.
- `TASK-1-6-03` depends on `TASK-1-6-01`; its frontend or evidence work can proceed alongside `TASK-1-6-02`, and integrated acceptance closes after `TASK-1-6-02` is done.


## Coordination and verification gate
Reserve shared Profile files through `E1-COORD-PROFILE-001`; task group 03 waits for the `E1-COORD-VERSION-001` persistence fixture checkpoint, uses `E1-COORD-TEST-001`, and gates all three ACs.
