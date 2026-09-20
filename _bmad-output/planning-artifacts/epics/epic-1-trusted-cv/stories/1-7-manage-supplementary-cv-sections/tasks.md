# Story 1.7: Manage supplementary CV sections — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-7-01: Freeze supplementary-section fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01`, `AC-1-7-manage-supplementary-cv-sections-02`, `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: 01. Extend Profile fixtures with supplementary schemas: certificate/language/activity fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-005`
  - Outcome: 01. Extend Profile fixtures with supplementary schemas: Extend Profile fixtures with supplementary schemas.
  - Acceptance: 01. Extend Profile fixtures with supplementary schemas: fields, IDs, optionality, paths, limits and errors are frozen.
  - Verification: 01. Extend Profile fixtures with supplementary schemas: schema/AC review.

- [ ] TASK-1-7-02: Deliver supplementary-section persistence contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01`, `AC-1-7-manage-supplementary-cv-sections-02`, `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: 01. Implement supplementary domain and persistence behavior: Profile aggregate section rules/repository mapping | 02. Expose supplementary update contract: request/resource/use case/errors | 03. Verify supplementary backend behavior: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved E1-COORD-PROFILE-001 Profile schema persistence checkpoint from Story 1.3; E1-COORD-VERSION-001 persistence fixture checkpoint
  - Outcome: 01. Implement supplementary domain and persistence behavior: Implement supplementary domain and persistence behavior. | 02. Expose supplementary update contract: Expose supplementary update contract. | 03. Verify supplementary backend behavior: Verify supplementary backend behavior.
  - Acceptance: 01. Implement supplementary domain and persistence behavior: optional add/edit/remove is atomic with stable IDs. | 02. Expose supplementary update contract: optional/invalid/removal behavior matches fixtures without data loss. | 03. Verify supplementary backend behavior: optionality, data preservation, ownership and immutability pass.
  - Verification: 01. Implement supplementary domain and persistence behavior: unit and MySQL persistence/Version-regression tests. | 02. Expose supplementary update contract: Laravel feature/contract tests. | 03. Verify supplementary backend behavior: focused PHPUnit suites.

- [ ] TASK-1-7-03: Deliver and verify supplementary-section editors
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01`, `AC-1-7-manage-supplementary-cv-sections-02`, `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: 01. Build supplementary repeated editors: frontend schemas/API/components/state | 02. Verify supplementary sections end to end: browser populated/invalid/all-empty/reload path
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Build supplementary repeated editors: Build supplementary repeated editors. | 02. Verify supplementary sections end to end: Verify supplementary sections end to end.
  - Acceptance: 01. Build supplementary repeated editors: accessible optional editors preserve valid entries and errors. | 02. Verify supplementary sections end to end: critical and failure paths pass on disposable data. | Integrated journey acceptance closes only after `TASK-1-7-02` is done with backend evidence.
  - Verification: 01. Build supplementary repeated editors: type-check and Vitest after enablement. | 02. Verify supplementary sections end to end: approved Playwright command. | Run the cross-layer journey check after `TASK-1-7-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-7-01` depends on `none`.
- `TASK-1-7-02` depends on `TASK-1-7-01`.
- `TASK-1-7-03` depends on `TASK-1-7-01`; its frontend or evidence work can proceed alongside `TASK-1-7-02`, and integrated acceptance closes after `TASK-1-7-02` is done.


## Coordination and verification gate
Reserve shared Profile files through `E1-COORD-PROFILE-001`; task group 03 waits for the `E1-COORD-VERSION-001` persistence fixture checkpoint, uses `E1-COORD-TEST-001`, and gates all three ACs.
