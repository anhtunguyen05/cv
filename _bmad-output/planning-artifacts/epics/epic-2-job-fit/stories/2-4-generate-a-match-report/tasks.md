# Story 2.4: Generate a Match Report — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks and acceptance

- [ ] TASK-2-4-01: Freeze matching, evidence, and Match Report fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: source/request/result/error fixtures, scoring/evidence corpus, stable contract
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-PREREQ-VERSION-001`, approved `E2-COORD-ANALYSIS-001`, `E2-DEC-001`, `E2-DEC-005` through `E2-DEC-007`, `E2-DEC-009`
  - Outcome: Freeze one executable deterministic Match Report contract and quality corpus.
  - Acceptance: fixtures cover every AC, unsupported claims, rounding/order, mixed ownership, and source conflicts.
  - Verification: schema/corpus validation and product/architecture approval evidence.
- [ ] TASK-2-4-02: Implement deterministic matcher and evidence resolver
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-01`
  - Covers: `AC-2-4-generate-a-match-report-01`, `AC-2-4-generate-a-match-report-06`
  - Scope: local normalization/aliases, evidence classification, scoring, recommendations, rule version
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Produce one validated deterministic report DTO from immutable CV/Analysis inputs.
  - Acceptance: all approved fixtures are repeatable and no unsupported claim is emitted.
  - Verification: PHPUnit quality corpus, boundary/property, repeat-process, and rule-version tests.
- [ ] TASK-2-4-03: Implement Match Report aggregate, source transaction, and persistence
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-02`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: source resolver/policies, application service, migration/model/repository, transaction/dedupe
  - Coordination: `E1-COORD-VERSION-001`, `E2-COORD-ANALYSIS-001`, `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Persist complete immutable reports pinned to one authorized consistent source set.
  - Acceptance: missing/stale/deleted/foreign/failing sources create no report and history is never overwritten.
  - Verification: application/MySQL constraint, source-race, rollback, dedupe, and two-User tests.
- [ ] TASK-2-4-04: Expose Match Report creation API
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-03`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: create request/resource/controller/route/policies/errors/rate limit
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Serve the approved immutable report creation operation through `/api/v1`.
  - Acceptance: all source, success, conflict, denial, repeat, and failure fixtures match exactly.
  - Verification: Laravel feature/contract tests with current/historical/deleted/mixed-owner cases.
- [ ] TASK-2-4-05: Implement Match Report creation adapter and comparison state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-01`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: report/source schema, create adapter, mutation/error/reconciliation state
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Consume exact fixtures without browser-side matching or live-source substitution.
  - Acceptance: Analyze-first, conflict, denial, ambiguous success, and complete result states map deterministically.
  - Verification: type-check and adapter/query/mutation tests.
- [ ] TASK-2-4-06: Build accessible Match Report source-selection and generation UI
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-05`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-05`
  - Scope: owned source selectors, current-analysis state, compare action, pending/conflict/success/failure UX
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `E2-DEC-006`, `E2-DEC-008`
  - Outcome: Let Users select valid sources and understand why generation is blocked or succeeds.
  - Acceptance: keyboard flow prevents invalid requests and retains explicit source identity through navigation.
  - Verification: component tests and manual keyboard/focus/state review.
- [ ] TASK-2-4-07: Verify deterministic Match backend and quality corpus
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-04`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: matcher/corpus/application/feature/policy/MySQL repeatability suite
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-005`, `E2-DEC-009`, `DISCOVERY-E2-001`
  - Outcome: Prove truthfulness, source pinning, quality threshold, repeatability, isolation, and atomic failure.
  - Acceptance: approved corpus and counterexamples pass across repeated clean runs and source races.
  - Verification: approved PHPUnit/MySQL quality commands with corpus and rule-version evidence.
- [ ] TASK-2-4-08: Verify Match Report generation journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-06`, `TASK-2-4-07`
  - Covers: `AC-2-4-generate-a-match-report-01` through `AC-2-4-generate-a-match-report-06`
  - Scope: browser source selection, Analyze-first, success/repeat, stale/deleted/foreign/failure paths
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Verify report generation across browser, API, deterministic engine, and database.
  - Acceptance: scenarios pass independently with exact stored IDs/result and disposable two-User data.
  - Verification: approved Playwright command with fixture/rule versions and database-reset evidence.

## Dependency and concurrency map

```text
01 -> {02,05}; 02 -> 03 -> 04 -> 07; 05 -> 06; {06,07} -> 08
```

Matcher task 02 and frontend task 05 may proceed after the same approved
fixtures. Story 2.4 owns stored report semantics consumed by Story 2.5.

## Coordination gate

Story acceptance requires tasks 07–08, all six ACs, the approved quality gate,
and immutable consumer checkpoints for CV Version, Analysis, and Match Report.
