# Story 2.2: Manage saved Job Descriptions — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks and acceptance

- [ ] TASK-2-2-01: Freeze update/delete/list/history contract fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: revision/update/delete/list/deleted-source fixtures and stable contract
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`
  - Blocked by: approved `E2-COORD-JD-001` initial-revision checkpoint from Story 2.1; `E2-DEC-001`, `E2-DEC-003`, `E2-DEC-008`
  - Outcome: Freeze one cross-layer revision and deletion contract.
  - Acceptance: fixtures define stale/no-op/retry/delete/history behavior without changing Story 2.1 identity.
  - Verification: fixture syntax and cross-Story source-field review.
- [ ] TASK-2-2-02: Implement immutable revision update service
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-01`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01`, `AC-2-2-manage-saved-job-descriptions-02`
  - Scope: update use case, validation, concurrency, transaction, current pointer
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `none`
  - Outcome: Create exactly one immutable new current revision for an accepted update.
  - Acceptance: prior revisions/derived records remain unchanged and stale/retry paths create no accidental revision.
  - Verification: PHPUnit/MySQL no-op, concurrent update, rollback, and source-regression tests.
- [ ] TASK-2-2-03: Implement logical deletion and historical-source resolver
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-01`
  - Covers: `AC-2-2-manage-saved-job-descriptions-03`, `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: delete transition, active query exclusion, new-work guards, pinned source resolution
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Hide deleted JDs from new work while preserving report reproducibility.
  - Acceptance: unrelated resources remain unchanged and only the owner can resolve pinned history.
  - Verification: policy/application/MySQL delete race, active-list, and historical report-source tests.
- [ ] TASK-2-2-04: Expose Job Description management APIs
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-02`, `TASK-2-2-03`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: list/update/delete requests/resources/controllers/routes/error mapping
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `none`
  - Outcome: Serve approved management contracts with protected ownership.
  - Acceptance: list, update, delete, stale, repeated, foreign, and deleted outcomes match fixtures.
  - Verification: Laravel feature/contract tests with two Users and malformed transport.
- [ ] TASK-2-2-05: Implement revision-aware frontend management state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-01`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: list/detail/update/delete adapters, cache keys/invalidation, stale/deleted mapping
  - Coordination: `E2-COORD-JD-001`
  - Blocked by: `none`
  - Outcome: Track logical and current revision state without reusing stale Analysis.
  - Acceptance: every fixture maps to deterministic active, stale, deleted, or historical UI state.
  - Verification: type-check and adapter/query/mutation tests.
- [ ] TASK-2-2-06: Build accessible Job Description list/edit/delete experience
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-05`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: active list, edit form, delete confirmation, stale recovery, deleted source banner
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-MATCH-001`
  - Blocked by: `E2-DEC-008`
  - Outcome: Let owners manage current JDs while understanding preserved history.
  - Acceptance: keyboard flow preserves unsaved input and disables invalid actions after deletion.
  - Verification: component tests and manual keyboard/focus/state review.
- [ ] TASK-2-2-07: Verify revision, deletion, and history backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-04`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: domain/feature/policy/migration/MySQL history regression suite
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Prove immutable history, state guards, isolation, and race behavior.
  - Acceptance: old report inputs remain identical after update/delete and all losing races are atomic.
  - Verification: approved PHPUnit/MySQL commands and pinned fixture versions.
- [ ] TASK-2-2-08: Verify Job Description management journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-2-06`, `TASK-2-2-07`
  - Covers: `AC-2-2-manage-saved-job-descriptions-01` through `AC-2-2-manage-saved-job-descriptions-04`
  - Scope: browser list/update/reload/stale/delete/report-history scenarios
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`; approved `E2-COORD-MATCH-001` report-view checkpoint from Story 2.5
  - Outcome: Verify current management and preserved historical report behavior end to end.
  - Acceptance: independent scenarios pass with keyboard behavior and disposable two-User data.
  - Verification: approved Playwright command with fixture and database-reset evidence.

## Dependency and concurrency map

```text
01 -> {02,03,05}; {02,03} -> 04 -> 07; 05 -> 06; {06,07} -> 08
```

Tasks 02, 03, and 05 may proceed in parallel after fixtures when reserved files
do not overlap. The Story owner integrates shared aggregate/API/cache changes.

## Coordination gate

Story acceptance requires tasks 07–08 and proof that revision/update/delete do
not alter the pinned source state consumed by Analysis and Match Reports.
