# Story 1.5: Manage education and experience — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-5-01: Extend Profile fixtures with history item schemas
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: education/experience fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with history item schemas.
  - Acceptance: fields, date rules, IDs, paths, limits and errors are frozen.
  - Verification: schema/AC review.
- [ ] TASK-1-5-02: Implement history item domain and persistence behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: Profile aggregate history rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement history item domain and persistence behavior.
  - Acceptance: add/edit/remove is atomic with stable server IDs.
  - Verification: unit and MySQL persistence/Version-regression tests.
- [ ] TASK-1-5-03: Expose history update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-02`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: request/resource/use case/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose history update contract.
  - Acceptance: nested errors and targeted removal match fixtures without partial writes.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-5-04: Build education/experience repeated editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: frontend schema/API/components/state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build education/experience repeated editor.
  - Acceptance: keyboard-usable stable rows preserve valid siblings and errors.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-5-05: Verify history backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-03`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify history backend behavior.
  - Acceptance: IDs, atomicity, ownership, validation/removal and immutability pass.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-5-06: Verify history end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-04`, `TASK-1-5-05`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: browser add/edit/remove/reload path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify history end to end.
  - Acceptance: critical and meaningful failure paths pass on disposable data.
  - Verification: approved Playwright command.

## Dependency and concurrency map

```text
01 -> {02,04}; 02 -> 03 -> 05; {04,05} -> 06
```

## Coordination and verification gate

Reserve shared Profile files through `E1-COORD-PROFILE-001`; task 05 waits for
the `E1-COORD-VERSION-001` persistence fixture checkpoint; task 06 uses
`E1-COORD-TEST-001` and gates all three ACs.
