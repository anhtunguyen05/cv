# Story 1.7: Manage supplementary CV sections — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-7-01: Extend Profile fixtures with supplementary schemas
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: certificate/language/activity fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with supplementary schemas.
  - Acceptance: fields, IDs, optionality, paths, limits and errors are frozen.
  - Verification: schema/AC review.
- [ ] TASK-1-7-02: Implement supplementary domain and persistence behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: Profile aggregate section rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement supplementary domain and persistence behavior.
  - Acceptance: optional add/edit/remove is atomic with stable IDs.
  - Verification: unit and MySQL persistence/Version-regression tests.
- [ ] TASK-1-7-03: Expose supplementary update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-02`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: request/resource/use case/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose supplementary update contract.
  - Acceptance: optional/invalid/removal behavior matches fixtures without data loss.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-7-04: Build supplementary repeated editors
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: frontend schemas/API/components/state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build supplementary repeated editors.
  - Acceptance: accessible optional editors preserve valid entries and errors.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-7-05: Verify supplementary backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-03`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify supplementary backend behavior.
  - Acceptance: optionality, data preservation, ownership and immutability pass.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-7-06: Verify supplementary sections end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-04`, `TASK-1-7-05`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: browser populated/invalid/all-empty/reload path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify supplementary sections end to end.
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
