# Story 2.3: Analyze a Job Description — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks and acceptance

- [ ] TASK-2-3-01: Freeze Analysis schema, rule version, and fixture corpus
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: stable Analysis contract, vocabulary, deterministic examples, failures
  - Coordination: `E2-COORD-ANALYSIS-001`, `E2-COORD-TEST-001`
  - Blocked by: approved `E2-COORD-JD-001` immutable-revision checkpoint from Story 2.1; `E2-DEC-001`, `E2-DEC-004`, `E2-DEC-007`, `E2-DEC-009`
  - Outcome: Freeze executable input/output/error fixtures for all supported signals.
  - Acceptance: corpus includes absent/unknown, alias, order, duplicate, malformed, and failure cases.
  - Verification: fixture/schema validation and product/architecture approval evidence.
- [ ] TASK-2-3-02: Implement deterministic Job Description parser and normalizer
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-01`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-03`
  - Scope: local parser, vocabulary/aliases, normalization, ordering, result DTO validator
  - Coordination: `E2-COORD-ANALYSIS-001`
  - Blocked by: `none`
  - Outcome: Produce one validated deterministic Analysis DTO from raw revision text.
  - Acceptance: every corpus case is repeatable and missing signals remain absent/unknown.
  - Verification: PHPUnit corpus, property/boundary, repeat-process, and no-provider tests.
- [ ] TASK-2-3-03: Implement Analysis application service and persistence
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-02`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: current-source policy, deterministic key, repository, transaction, retry classification
  - Coordination: `E2-COORD-JD-001`, `E2-COORD-ANALYSIS-001`
  - Blocked by: `none`
  - Outcome: Persist only complete successful Analysis pinned to the exact source/rule.
  - Acceptance: duplicates, source races, failure, and retry cannot create competing or partial success.
  - Verification: application/MySQL concurrency, uniqueness, rollback, and failure tests.
- [ ] TASK-2-3-04: Expose Analyze and Analysis-read API operations
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-03`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: request/resource/controller/routes/policy/error/limit mapping
  - Coordination: `E2-COORD-ANALYSIS-001`
  - Blocked by: `none`
  - Outcome: Serve the approved deterministic Analysis contract through `/api/v1`.
  - Acceptance: every fixture maps to exact status/code/schema and raw text remains private.
  - Verification: Laravel feature/contract tests for current, repeat, stale, deleted, foreign, and failure paths.
- [ ] TASK-2-3-05: Implement frontend Analysis adapter and revision-aware state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-01`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: Analysis schema/error adapter, query/mutation keys, retry/stale state
  - Coordination: `E2-COORD-ANALYSIS-001`
  - Blocked by: `none`
  - Outcome: Consume exact Analysis fixtures without client extraction or stale reuse.
  - Acceptance: complete, absent/unknown, invalid-payload, retry, and revision-change states map deterministically.
  - Verification: type-check and adapter/query/mutation tests.
- [ ] TASK-2-3-06: Build accessible Job Description Analysis review UI
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-05`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: Analyze action, raw/derived sections, signal groups, versions, stale/retry/failure states
  - Coordination: `E2-COORD-ANALYSIS-001`
  - Blocked by: `E2-DEC-004`
  - Outcome: Let Users understand extracted versus original content and missing signals.
  - Acceptance: keyboard and assistive reading order expose all groups, source identity, and safe retry.
  - Verification: component tests and manual keyboard/screen-reader-oriented review.
- [ ] TASK-2-3-07: Verify deterministic Analysis backend and corpus
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-04`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: domain/corpus/feature/policy/MySQL repeatability and failure suite
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Prove extraction truthfulness, repeatability, pinning, isolation, and atomic failure.
  - Acceptance: approved corpus passes repeatedly with exact rule/schema/source evidence.
  - Verification: approved PHPUnit/MySQL commands across repeated clean processes.
- [ ] TASK-2-3-08: Verify Analyze journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-06`, `TASK-2-3-07`
  - Covers: `AC-2-3-analyze-a-job-description-01` through `AC-2-3-analyze-a-job-description-04`
  - Scope: browser analyze/reload/missing/retry/revision-change scenarios
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Verify deterministic Analysis across browser, API, and stored result.
  - Acceptance: critical paths pass independently and never display incomplete or stale success.
  - Verification: approved Playwright command with corpus/rule version and disposable data evidence.

## Dependency and concurrency map

```text
01 -> {02,05}; 02 -> 03 -> 04 -> 07; 05 -> 06; {06,07} -> 08
```

Parser task 02 and frontend adapter task 05 may progress in parallel after
fixtures. Story 2.3 owns the Analysis boundary for Stories 2.4 and 2.5.

## Coordination gate

Story acceptance requires tasks 07–08, all four ACs, approved deterministic
fixtures, and a reusable immutable Analysis checkpoint.
