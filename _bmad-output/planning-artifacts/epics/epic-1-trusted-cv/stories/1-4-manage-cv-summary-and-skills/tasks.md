# Story 1.4: Manage CV summary and skills — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-4-01: Extend Profile fixtures with summary and skills
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: shared Profile schema/fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with summary and skills.
  - Acceptance: fields, categories, limits, ordering and errors are frozen once.
  - Verification: fixture schema and AC review.
- [ ] TASK-1-4-02: Implement summary/skills domain and persistence update
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: Profile aggregate section rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement summary/skills domain and persistence update.
  - Acceptance: approved structured values update atomically with stable ownership.
  - Verification: PHPUnit plus MySQL persistence/Version-regression tests.
- [ ] TASK-1-4-03: Expose summary/skills update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-02`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: Profile request/resource/update use case/error mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose summary/skills update contract.
  - Acceptance: fixtures pass without partial writes or Version mutation.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-4-04: Build summary/skills editor integration
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: feature schema/API/editor/error state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build summary/skills editor integration.
  - Acceptance: accessible add/edit/remove/save states preserve valid data and conflicts.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-4-05: Verify summary/skills backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-03`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify summary/skills backend behavior.
  - Acceptance: valid, empty, invalid, stale and Version-regression evidence passes.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-4-06: Verify summary/skills end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-04`, `TASK-1-4-05`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: browser save/reload/error/Version-regression path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify summary/skills end to end.
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
