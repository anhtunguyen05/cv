# Story 2.1: Save a Job Description — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks and acceptance

- [ ] TASK-2-1-01: Freeze executable Job Description create/read fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: stable create/read/error/revision fixtures and promoted contract
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `E2-DEC-001`, `E2-DEC-002`, `E2-DEC-003`, `E2-DEC-007`
  - Outcome: Freeze one cross-layer contract for initial JD persistence and retrieval.
  - Acceptance: fixtures cover every approved success/failure row and contain no owner-writable field.
  - Verification: validate fixture syntax and review against Global/Epic IDs.
- [ ] TASK-2-1-02: Implement Job Description aggregate and initial-revision persistence
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-01`
  - Covers: `AC-2-1-save-a-job-description-01`, `AC-2-1-save-a-job-description-02`
  - Scope: migrations, models/entities, constraints, repository, atomic initial revision
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `E2-PREREQ-AUTH-001`
  - Outcome: Persist one owned logical JD and immutable revision 1 atomically.
  - Acceptance: constraints prevent partial roots/revisions and client-owned identity.
  - Verification: PHPUnit plus disposable-MySQL migration, constraint, rollback, and reload checks.
- [ ] TASK-2-1-03: Implement owned create/read application services and policy
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-02`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: create/read use cases, canonicalization, ownership policy, dedupe/reconciliation
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `none`
  - Outcome: Enforce validation, ownership, safe retry, and exact-source retrieval.
  - Acceptance: invalid/foreign/repeated requests cannot create or disclose partial state.
  - Verification: domain/application tests including two-User, timeout, and duplicate scenarios.
- [ ] TASK-2-1-04: Expose protected Job Description create/read API
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-03`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: Form Request, API Resource, controller, routes, exceptions, rate limit
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `none`
  - Outcome: Serve the approved create/read contract through `/api/v1`.
  - Acceptance: every response matches fixtures, private content is safe, and failures create no state.
  - Verification: Laravel feature/contract tests including malformed transport and limiter expiry.
- [ ] TASK-2-1-05: Implement Job Description frontend adapter and create state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-01`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: API/schema/error adapter, mutation/query state, cache and reconciliation
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `E2-PREREQ-AUTH-001`
  - Outcome: Consume shared fixtures without duplicating domain validation.
  - Acceptance: adapter maps all approved success/error/ambiguous outcomes and never logs raw text.
  - Verification: TypeScript type-check and adapter/state tests.
- [ ] TASK-2-1-06: Build accessible Job Description create and reload experience
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-05`
  - Covers: `AC-2-1-save-a-job-description-01`, `AC-2-1-save-a-job-description-02`
  - Scope: create page/form, pending/errors, success navigation, owned detail rendering
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `E2-DEC-002`, `E2-DEC-008`
  - Outcome: Provide a keyboard-usable save/reload flow with safe raw-text rendering.
  - Acceptance: limits/errors are understandable and duplicate or lost submits do not confuse state.
  - Verification: component checks and manual keyboard/safe-render review.
- [ ] TASK-2-1-07: Verify Job Description backend contract and persistence
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-04`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: domain, Laravel feature/contract, policy, migration, MySQL integration suites
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Prove atomic revision creation, exact reload, validation, isolation, and safe logs.
  - Acceptance: all backend AC evidence passes against declared MySQL and two-User data.
  - Verification: approved focused/full PHPUnit commands with disposable MySQL result.
- [ ] TASK-2-1-08: Verify save and reload journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-06`, `TASK-2-1-07`
  - Covers: `AC-2-1-save-a-job-description-01` through `AC-2-1-save-a-job-description-03`
  - Scope: browser save/reload/validation/foreign-access/retry scenarios
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Verify the critical first Job Description journey across browser, API, and database.
  - Acceptance: scenarios pass independently with keyboard operation and isolated disposable data.
  - Verification: approved Playwright command plus captured result and fixture version.

## Dependency and concurrency map

```text
01 -> 02 -> 03 -> 04 -> 07
01 -> 05 -> 06; {06,07} -> 08
```

Backend task 02 and frontend task 05 may run in parallel after fixtures. Shared
Job Description files remain reserved by `E2-COORD-JD-001`.

## Coordination gate

Story acceptance requires tasks 07–08, evidence for all three ACs, and an
approved aggregate/revision checkpoint reusable by Stories 2.2 and 2.3.
