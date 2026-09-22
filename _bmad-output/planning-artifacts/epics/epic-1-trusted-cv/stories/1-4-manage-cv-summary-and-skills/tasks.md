# Story 1.4: Manage CV summary and skills — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-4-01: Freeze summary and skills fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01`, `AC-1-4-manage-cv-summary-and-skills-02`, `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: 01. Extend Profile fixtures with summary and skills: shared Profile schema/fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003`; `E1-DEC-004`; `E1-DEC-005`
  - Outcome: 01. Extend Profile fixtures with summary and skills: Extend Profile fixtures with summary and skills.
  - Acceptance: 01. Extend Profile fixtures with summary and skills: fields, categories, limits, ordering and errors are frozen once.
  - Verification: 01. Extend Profile fixtures with summary and skills: fixture schema and AC review.

- [ ] TASK-1-4-02: Deliver summary and skills persistence contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01`, `AC-1-4-manage-cv-summary-and-skills-02`, `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: 01. Implement summary/skills domain and persistence update: Profile aggregate section rules/repository mapping | 02. Expose summary/skills update contract: Profile request/resource/update use case/error mapping | 03. Verify summary/skills backend behavior: unit/feature/PostgreSQL 16 suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved E1-COORD-PROFILE-001 Profile schema persistence checkpoint from Story 1.3; E1-COORD-VERSION-001 persistence fixture checkpoint
  - Outcome: 01. Implement summary/skills domain and persistence update: Implement summary/skills domain and persistence update. | 02. Expose summary/skills update contract: Expose summary/skills update contract. | 03. Verify summary/skills backend behavior: Verify summary/skills backend behavior.
  - Acceptance: 01. Implement summary/skills domain and persistence update: approved structured values update atomically with stable ownership. | 02. Expose summary/skills update contract: fixtures pass without partial writes or Version mutation. | 03. Verify summary/skills backend behavior: valid, empty, invalid, stale and Version-regression evidence passes.
  - Verification: 01. Implement summary/skills domain and persistence update: PHPUnit plus PostgreSQL 16 persistence/Version-regression tests. | 02. Expose summary/skills update contract: Laravel feature/contract tests. | 03. Verify summary/skills backend behavior: focused PHPUnit suites.

- [ ] TASK-1-4-03: Deliver and verify summary and skills editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01`, `AC-1-4-manage-cv-summary-and-skills-02`, `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: 01. Build summary/skills editor integration: feature schema/API/editor/error state | 02. Verify summary/skills end to end: browser save/reload/error/Version-regression path
  - Coordination: `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Build summary/skills editor integration: Build summary/skills editor integration. | 02. Verify summary/skills end to end: Verify summary/skills end to end.
  - Acceptance: 01. Build summary/skills editor integration: accessible add/edit/remove/save states preserve valid data and conflicts. | 02. Verify summary/skills end to end: critical and failure paths pass on disposable data. | Integrated journey acceptance closes only after `TASK-1-4-02` is done with backend evidence.
  - Verification: 01. Build summary/skills editor integration: type-check and Vitest after enablement. | 02. Verify summary/skills end to end: approved Playwright command. | Run the cross-layer journey check after `TASK-1-4-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-4-01` depends on `none`.
- `TASK-1-4-02` depends on `TASK-1-4-01`.
- `TASK-1-4-03` depends on `TASK-1-4-01`; its frontend or evidence work can proceed alongside `TASK-1-4-02`, and integrated acceptance closes after `TASK-1-4-02` is done.


## Coordination and verification gate
Reserve shared Profile files through `E1-COORD-PROFILE-001`; task group 03 waits for the `E1-COORD-VERSION-001` persistence fixture checkpoint, uses `E1-COORD-TEST-001`, and gates all three ACs.
