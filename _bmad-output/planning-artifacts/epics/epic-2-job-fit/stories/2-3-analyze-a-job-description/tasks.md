# Story 2.3: Analyze a Job Description — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-2-3-01: Freeze deterministic Analysis fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-3-analyze-a-job-description-01`, `AC-2-3-analyze-a-job-description-02`, `AC-2-3-analyze-a-job-description-03`, `AC-2-3-analyze-a-job-description-04`
  - Scope: 01. Freeze Analysis schema, rule version, and fixture corpus: stable Analysis contract, vocabulary, deterministic examples, failures
  - Coordination: `E2-COORD-ANALYSIS-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-001`; `E2-DEC-004`; `E2-DEC-007`; `E2-DEC-009`; approved E2-COORD-JD-001 immutable-revision checkpoint from Story 2.1
  - Outcome: 01. Freeze Analysis schema, rule version, and fixture corpus: Freeze executable input/output/error fixtures for all supported signals.
  - Acceptance: 01. Freeze Analysis schema, rule version, and fixture corpus: corpus includes absent/unknown, alias, order, duplicate, malformed, and failure cases.
  - Verification: 01. Freeze Analysis schema, rule version, and fixture corpus: fixture/schema validation and product/architecture approval evidence.

- [ ] TASK-2-3-02: Deliver deterministic Job Description Analysis backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-01`
  - Covers: `AC-2-3-analyze-a-job-description-01`, `AC-2-3-analyze-a-job-description-02`, `AC-2-3-analyze-a-job-description-03`, `AC-2-3-analyze-a-job-description-04`
  - Scope: 01. Implement deterministic Job Description parser and normalizer: local parser, vocabulary/aliases, normalization, ordering, result DTO validator | 02. Implement Analysis application service and persistence: current-source policy, deterministic key, repository, transaction, retry classification | 03. Expose Analyze and Analysis-read API operations: request/resource/controller/routes/policy/error/limit mapping | 04. Verify deterministic Analysis backend and corpus: domain/corpus/feature/policy/MySQL repeatability and failure suite
  - Coordination: `E2-COORD-ANALYSIS-001`, `E2-COORD-JD-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: 01. Implement deterministic Job Description parser and normalizer: Produce one validated deterministic Analysis DTO from raw revision text. | 02. Implement Analysis application service and persistence: Persist only complete successful Analysis pinned to the exact source/rule. | 03. Expose Analyze and Analysis-read API operations: Serve the approved deterministic Analysis contract through `/api/v1`. | 04. Verify deterministic Analysis backend and corpus: Prove extraction truthfulness, repeatability, pinning, isolation, and atomic failure.
  - Acceptance: 01. Implement deterministic Job Description parser and normalizer: every corpus case is repeatable and missing signals remain absent/unknown. | 02. Implement Analysis application service and persistence: duplicates, source races, failure, and retry cannot create competing or partial success. | 03. Expose Analyze and Analysis-read API operations: every fixture maps to exact status/code/schema and raw text remains private. | 04. Verify deterministic Analysis backend and corpus: approved corpus passes repeatedly with exact rule/schema/source evidence.
  - Verification: 01. Implement deterministic Job Description parser and normalizer: PHPUnit corpus, property/boundary, repeat-process, and no-provider tests. | 02. Implement Analysis application service and persistence: application/MySQL concurrency, uniqueness, rollback, and failure tests. | 03. Expose Analyze and Analysis-read API operations: Laravel feature/contract tests for current, repeat, stale, deleted, foreign, and failure paths. | 04. Verify deterministic Analysis backend and corpus: approved PHPUnit/MySQL commands across repeated clean processes.

- [ ] TASK-2-3-03: Deliver and verify Analysis review journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-3-01`
  - Covers: `AC-2-3-analyze-a-job-description-01`, `AC-2-3-analyze-a-job-description-02`, `AC-2-3-analyze-a-job-description-03`, `AC-2-3-analyze-a-job-description-04`
  - Scope: 01. Implement frontend Analysis adapter and revision-aware state: Analysis schema/error adapter, query/mutation keys, retry/stale state | 02. Build accessible Job Description Analysis review UI: Analyze action, raw/derived sections, signal groups, versions, stale/retry/failure states | 03. Verify Analyze journey end to end: browser analyze/reload/missing/retry/revision-change scenarios
  - Coordination: `E2-COORD-ANALYSIS-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-004`; `E2-DEC-009`
  - Outcome: 01. Implement frontend Analysis adapter and revision-aware state: Consume exact Analysis fixtures without client extraction or stale reuse. | 02. Build accessible Job Description Analysis review UI: Let Users understand extracted versus original content and missing signals. | 03. Verify Analyze journey end to end: Verify deterministic Analysis across browser, API, and stored result.
  - Acceptance: 01. Implement frontend Analysis adapter and revision-aware state: complete, absent/unknown, invalid-payload, retry, and revision-change states map deterministically. | 02. Build accessible Job Description Analysis review UI: keyboard and assistive reading order expose all groups, source identity, and safe retry. | 03. Verify Analyze journey end to end: critical paths pass independently and never display incomplete or stale success. | Integrated journey acceptance closes only after `TASK-2-3-02` is done with backend evidence.
  - Verification: 01. Implement frontend Analysis adapter and revision-aware state: type-check and adapter/query/mutation tests. | 02. Build accessible Job Description Analysis review UI: component tests and manual keyboard/screen-reader-oriented review. | 03. Verify Analyze journey end to end: approved Playwright command with corpus/rule version and disposable data evidence. | Run the cross-layer journey check after `TASK-2-3-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-2-3-01` depends on `none`.
- `TASK-2-3-02` depends on `TASK-2-3-01`.
- `TASK-2-3-03` depends on `TASK-2-3-01`; its frontend or evidence work can proceed alongside `TASK-2-3-02`, and integrated acceptance closes after `TASK-2-3-02` is done.


Story 2.3 owns the Analysis boundary consumed by Stories 2.4 and 2.5.

## Coordination gate
Task group 03 requires all four ACs, approved deterministic fixtures, and a reusable immutable Analysis checkpoint.
