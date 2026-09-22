# Story 2.1: Save a Job Description — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-2-1-01: Freeze Job Description create/read fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-1-save-a-job-description-01`, `AC-2-1-save-a-job-description-02`, `AC-2-1-save-a-job-description-03`
  - Scope: 01. Freeze executable Job Description create/read fixtures: stable create/read/error/revision fixtures and promoted contract
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `E2-DEC-001`; `E2-DEC-002`; `E2-DEC-003`; `E2-DEC-007`
  - Outcome: 01. Freeze executable Job Description create/read fixtures: Freeze one cross-layer contract for initial JD persistence and retrieval.
  - Acceptance: 01. Freeze executable Job Description create/read fixtures: fixtures cover every approved success/failure row and contain no owner-writable field.
  - Verification: 01. Freeze executable Job Description create/read fixtures: validate fixture syntax and review against Global/Epic IDs.

- [ ] TASK-2-1-02: Deliver owned Job Description create/read backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-01`
  - Covers: `AC-2-1-save-a-job-description-01`, `AC-2-1-save-a-job-description-02`, `AC-2-1-save-a-job-description-03`
  - Scope: 01. Implement Job Description aggregate and initial-revision persistence: migrations, models/entities, constraints, repository, atomic initial revision | 02. Implement owned create/read application services and policy: create/read use cases, canonicalization, ownership policy, dedupe/reconciliation | 03. Expose protected Job Description create/read API: Form Request, API Resource, controller, routes, exceptions, rate limit | 04. Verify Job Description backend contract and persistence: domain, Laravel feature/contract, policy, migration, PostgreSQL integration suites
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-PREREQ-AUTH-001`; `E2-DEC-009`
  - Outcome: 01. Implement Job Description aggregate and initial-revision persistence: Persist one owned logical JD and immutable revision 1 atomically. | 02. Implement owned create/read application services and policy: Enforce validation, ownership, safe retry, and exact-source retrieval. | 03. Expose protected Job Description create/read API: Serve the approved create/read contract through `/api/v1`. | 04. Verify Job Description backend contract and persistence: Prove atomic revision creation, exact reload, validation, isolation, and safe logs.
  - Acceptance: 01. Implement Job Description aggregate and initial-revision persistence: constraints prevent partial roots/revisions and client-owned identity. | 02. Implement owned create/read application services and policy: invalid/foreign/repeated requests cannot create or disclose partial state. | 03. Expose protected Job Description create/read API: every response matches fixtures, private content is safe, and failures create no state. | 04. Verify Job Description backend contract and persistence: all backend AC evidence passes against declared PostgreSQL and two-User data.
  - Verification: 01. Implement Job Description aggregate and initial-revision persistence: PHPUnit plus disposable-PostgreSQL migration, constraint, rollback, and reload checks. | 02. Implement owned create/read application services and policy: domain/application tests including two-User, timeout, and duplicate scenarios. | 03. Expose protected Job Description create/read API: Laravel feature/contract tests including malformed transport and limiter expiry. | 04. Verify Job Description backend contract and persistence: approved focused/full PHPUnit commands with disposable PostgreSQL result.

- [ ] TASK-2-1-03: Deliver and verify save and reload journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-1-01`
  - Covers: `AC-2-1-save-a-job-description-01`, `AC-2-1-save-a-job-description-02`, `AC-2-1-save-a-job-description-03`
  - Scope: 01. Implement Job Description frontend adapter and create state: API/schema/error adapter, mutation/query state, cache and reconciliation | 02. Build accessible Job Description create and reload experience: create page/form, pending/errors, success navigation, owned detail rendering | 03. Verify save and reload journey end to end: browser save/reload/validation/foreign-access/retry scenarios
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-PREREQ-AUTH-001`; `E2-DEC-002`; `E2-DEC-008`; `E2-DEC-009`
  - Outcome: 01. Implement Job Description frontend adapter and create state: Consume shared fixtures without duplicating domain validation. | 02. Build accessible Job Description create and reload experience: Provide a keyboard-usable save/reload flow with safe raw-text rendering. | 03. Verify save and reload journey end to end: Verify the critical first Job Description journey across browser, API, and database.
  - Acceptance: 01. Implement Job Description frontend adapter and create state: adapter maps all approved success/error/ambiguous outcomes and never logs raw text. | 02. Build accessible Job Description create and reload experience: limits/errors are understandable and duplicate or lost submits do not confuse state. | 03. Verify save and reload journey end to end: scenarios pass independently with keyboard operation and isolated disposable data. | Integrated journey acceptance closes only after `TASK-2-1-02` is done with backend evidence.
  - Verification: 01. Implement Job Description frontend adapter and create state: TypeScript type-check and adapter/state tests. | 02. Build accessible Job Description create and reload experience: component checks and manual keyboard/safe-render review. | 03. Verify save and reload journey end to end: approved Playwright command plus captured result and fixture version. | Run the cross-layer journey check after `TASK-2-1-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-2-1-01` depends on `none`.
- `TASK-2-1-02` depends on `TASK-2-1-01`.
- `TASK-2-1-03` depends on `TASK-2-1-01`; its frontend or evidence work can proceed alongside `TASK-2-1-02`, and integrated acceptance closes after `TASK-2-1-02` is done.


Shared Job Description files remain reserved by `E2-COORD-JD-001`.

## Coordination gate
Task group 03 requires evidence for all three ACs and an approved aggregate/revision checkpoint reusable by Stories 2.2 and 2.3.
