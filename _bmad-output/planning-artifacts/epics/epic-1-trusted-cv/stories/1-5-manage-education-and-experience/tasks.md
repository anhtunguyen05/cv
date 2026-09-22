# Story 1.5: Manage education and experience — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-5-01: Freeze education and experience fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-5-manage-education-and-experience-01`, `AC-1-5-manage-education-and-experience-02`, `AC-1-5-manage-education-and-experience-03`
  - Scope: 01. Extend Profile fixtures with history item schemas: education/experience fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-005`
  - Outcome: 01. Extend Profile fixtures with history item schemas: Extend Profile fixtures with history item schemas.
  - Acceptance: 01. Extend Profile fixtures with history item schemas: fields, date rules, IDs, paths, limits and errors are frozen.
  - Verification: 01. Extend Profile fixtures with history item schemas: schema/AC review.

- [ ] TASK-1-5-02: Deliver education and experience persistence contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01`, `AC-1-5-manage-education-and-experience-02`, `AC-1-5-manage-education-and-experience-03`
  - Scope: 01. Implement history item domain and persistence behavior: Profile aggregate history rules/repository mapping | 02. Expose history update contract: request/resource/use case/errors | 03. Verify history backend behavior: unit/feature/PostgreSQL 16 suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved E1-COORD-PROFILE-001 Profile schema persistence checkpoint from Story 1.3; E1-COORD-VERSION-001 persistence fixture checkpoint
  - Outcome: 01. Implement history item domain and persistence behavior: Implement history item domain and persistence behavior. | 02. Expose history update contract: Expose history update contract. | 03. Verify history backend behavior: Verify history backend behavior.
  - Acceptance: 01. Implement history item domain and persistence behavior: add/edit/remove is atomic with stable server IDs. | 02. Expose history update contract: nested errors and targeted removal match fixtures without partial writes. | 03. Verify history backend behavior: IDs, atomicity, ownership, validation/removal and immutability pass.
  - Verification: 01. Implement history item domain and persistence behavior: unit and PostgreSQL 16 persistence/Version-regression tests. | 02. Expose history update contract: Laravel feature/contract tests. | 03. Verify history backend behavior: focused PHPUnit suites.

- [ ] TASK-1-5-03: Deliver and verify education and experience editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01`, `AC-1-5-manage-education-and-experience-02`, `AC-1-5-manage-education-and-experience-03`
  - Scope: 01. Build education/experience repeated editor: frontend schema/API/components/state | 02. Verify history end to end: browser add/edit/remove/reload path
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Build education/experience repeated editor: Build education/experience repeated editor. | 02. Verify history end to end: Verify history end to end.
  - Acceptance: 01. Build education/experience repeated editor: keyboard-usable stable rows preserve valid siblings and errors. | 02. Verify history end to end: critical and meaningful failure paths pass on disposable data. | Integrated journey acceptance closes only after `TASK-1-5-02` is done with backend evidence.
  - Verification: 01. Build education/experience repeated editor: type-check and Vitest after enablement. | 02. Verify history end to end: approved Playwright command. | Run the cross-layer journey check after `TASK-1-5-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-5-01` depends on `none`.
- `TASK-1-5-02` depends on `TASK-1-5-01`.
- `TASK-1-5-03` depends on `TASK-1-5-01`; its frontend or evidence work can proceed alongside `TASK-1-5-02`, and integrated acceptance closes after `TASK-1-5-02` is done.


## Coordination and verification gate
Reserve shared Profile files through `E1-COORD-PROFILE-001`; task group 03 waits for the `E1-COORD-VERSION-001` persistence fixture checkpoint, uses `E1-COORD-TEST-001`, and gates all three ACs.
