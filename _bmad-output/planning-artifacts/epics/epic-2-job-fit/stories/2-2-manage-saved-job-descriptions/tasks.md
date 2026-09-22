# Story 2.2: Manage saved Job Descriptions — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-2-2-01: Freeze Job Description management fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01`, `AC-2-2-manage-saved-job-descriptions-02`, `AC-2-2-manage-saved-job-descriptions-03`, `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: 01. Freeze update/delete/list/history contract fixtures: revision/update/delete/list/deleted-source fixtures and stable contract
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`
  - Blocked by: `E2-DEC-001`; `E2-DEC-003`; `E2-DEC-008`; approved E2-COORD-JD-001 initial-revision checkpoint from Story 2.1
  - Outcome: 01. Freeze update/delete/list/history contract fixtures: Freeze one cross-layer revision and deletion contract.
  - Acceptance: 01. Freeze update/delete/list/history contract fixtures: fixtures define stale/no-op/retry/delete/history behavior without changing Story 2.1 identity.
  - Verification: 01. Freeze update/delete/list/history contract fixtures: fixture syntax and cross-Story source-field review.

- [ ] TASK-2-2-02: Deliver revision-safe Job Description management backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-01`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01`, `AC-2-2-manage-saved-job-descriptions-02`, `AC-2-2-manage-saved-job-descriptions-03`, `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: 01. Implement immutable revision update service: update use case, validation, concurrency, transaction, current pointer | 02. Implement logical deletion and historical-source resolver: delete transition, active query exclusion, new-work guards, pinned source resolution | 03. Expose Job Description management APIs: list/update/delete requests/resources/controllers/routes/error mapping | 04. Verify revision, deletion, and history backend behavior: domain/feature/policy/migration/PostgreSQL history regression suite
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: 01. Implement immutable revision update service: Create exactly one immutable new current revision for an accepted update. | 02. Implement logical deletion and historical-source resolver: Hide deleted JDs from new work while preserving report reproducibility. | 03. Expose Job Description management APIs: Serve approved management contracts with protected ownership. | 04. Verify revision, deletion, and history backend behavior: Prove immutable history, state guards, isolation, and race behavior.
  - Acceptance: 01. Implement immutable revision update service: prior revisions/derived records remain unchanged and stale/retry paths create no accidental revision. | 02. Implement logical deletion and historical-source resolver: unrelated resources remain unchanged and only the owner can resolve pinned history. | 03. Expose Job Description management APIs: list, update, delete, stale, repeated, foreign, and deleted outcomes match fixtures. | 04. Verify revision, deletion, and history backend behavior: old report inputs remain identical after update/delete and all losing races are atomic.
  - Verification: 01. Implement immutable revision update service: PHPUnit/PostgreSQL no-op, concurrent update, rollback, and source-regression tests. | 02. Implement logical deletion and historical-source resolver: policy/application/PostgreSQL delete race, active-list, and historical report-source tests. | 03. Expose Job Description management APIs: Laravel feature/contract tests with two Users and malformed transport. | 04. Verify revision, deletion, and history backend behavior: approved PHPUnit/PostgreSQL commands and pinned fixture versions.

- [ ] TASK-2-2-03: Deliver and verify Job Description management journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-01`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01`, `AC-2-2-manage-saved-job-descriptions-02`, `AC-2-2-manage-saved-job-descriptions-03`, `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: 01. Implement revision-aware frontend management state: list/detail/update/delete adapters, cache keys/invalidation, stale/deleted mapping | 02. Build accessible Job Description list/edit/delete experience: active list, edit form, delete confirmation, stale recovery, deleted source banner | 03. Verify Job Description management journey end to end: browser list/update/reload/stale/delete/report-history scenarios
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-008`; `E2-DEC-009`; approved E2-COORD-MATCH-001 report-view checkpoint from Story 2.5
  - Outcome: 01. Implement revision-aware frontend management state: Track logical and current revision state without reusing stale Analysis. | 02. Build accessible Job Description list/edit/delete experience: Let owners manage current JDs while understanding preserved history. | 03. Verify Job Description management journey end to end: Verify current management and preserved historical report behavior end to end.
  - Acceptance: 01. Implement revision-aware frontend management state: every fixture maps to deterministic active, stale, deleted, or historical UI state. | 02. Build accessible Job Description list/edit/delete experience: keyboard flow preserves unsaved input and disables invalid actions after deletion. | 03. Verify Job Description management journey end to end: independent scenarios pass with keyboard behavior and disposable two-User data. | Integrated journey acceptance closes only after `TASK-2-2-02` is done with backend evidence.
  - Verification: 01. Implement revision-aware frontend management state: type-check and adapter/query/mutation tests. | 02. Build accessible Job Description list/edit/delete experience: component tests and manual keyboard/focus/state review. | 03. Verify Job Description management journey end to end: approved Playwright command with fixture and database-reset evidence. | Run the cross-layer journey check after `TASK-2-2-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-2-2-01` depends on `none`.
- `TASK-2-2-02` depends on `TASK-2-2-01`.
- `TASK-2-2-03` depends on `TASK-2-2-01`; its frontend or evidence work can proceed alongside `TASK-2-2-02`, and integrated acceptance closes after `TASK-2-2-02` is done.


Shared Job Description files remain reserved through `E2-COORD-JD-001`.

## Coordination gate
Task group 03 requires proof that revision/update/delete do not alter the pinned source state consumed by Analysis and Match Reports.
