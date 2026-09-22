# Story 2.4: Generate a Match Report — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-2-4-01: Freeze Match Report generation fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-4-generate-a-match-report-01`, `AC-2-4-generate-a-match-report-02`, `AC-2-4-generate-a-match-report-03`, `AC-2-4-generate-a-match-report-04`, `AC-2-4-generate-a-match-report-05`, `AC-2-4-generate-a-match-report-06`
  - Scope: 01. Freeze matching, evidence, and Match Report fixtures: source/request/result/error fixtures, scoring/evidence corpus, stable contract
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-PREREQ-VERSION-001`; `E2-DEC-001`; `E2-DEC-005`; `E2-DEC-006`; `E2-DEC-007`; `E2-DEC-009`; approved E2-COORD-ANALYSIS-001
  - Outcome: 01. Freeze matching, evidence, and Match Report fixtures: Freeze one executable deterministic Match Report contract and quality corpus.
  - Acceptance: 01. Freeze matching, evidence, and Match Report fixtures: fixtures cover every AC, unsupported claims, rounding/order, mixed ownership, and source conflicts.
  - Verification: 01. Freeze matching, evidence, and Match Report fixtures: schema/corpus validation and product/architecture approval evidence.

- [ ] TASK-2-4-02: Deliver deterministic Match Report generation backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-01`
  - Covers: `AC-2-4-generate-a-match-report-01`, `AC-2-4-generate-a-match-report-02`, `AC-2-4-generate-a-match-report-03`, `AC-2-4-generate-a-match-report-04`, `AC-2-4-generate-a-match-report-05`, `AC-2-4-generate-a-match-report-06`
  - Scope: 01. Implement deterministic matcher and evidence resolver: local normalization/aliases, evidence classification, scoring, recommendations, rule version | 02. Implement Match Report aggregate, source transaction, and persistence: source resolver/policies, application service, migration/model/repository, transaction/dedupe | 03. Expose Match Report creation API: create request/resource/controller/route/policies/errors/rate limit | 04. Verify deterministic Match backend and quality corpus: matcher/corpus/application/feature/policy/PostgreSQL repeatability suite
  - Coordination: `E2-COORD-MATCH-001`, `E1-COORD-VERSION-001`, `E2-COORD-ANALYSIS-001`, `E2-COORD-TEST-001`; one backend integration owner sequences matcher, consistent source transaction/persistence, API, then backend and quality verification
  - Blocked by: `E2-DEC-005`; `E2-DEC-009`; `DISCOVERY-E2-001`
  - Outcome: 01. Implement deterministic matcher and evidence resolver: Produce one validated deterministic report DTO from immutable CV/Analysis inputs. | 02. Implement Match Report aggregate, source transaction, and persistence: Persist complete immutable reports pinned to one authorized consistent source set. | 03. Expose Match Report creation API: Serve the approved immutable report creation operation through `/api/v1`. | 04. Verify deterministic Match backend and quality corpus: Prove truthfulness, source pinning, quality threshold, repeatability, isolation, and atomic failure.
  - Acceptance: 01. Implement deterministic matcher and evidence resolver: all approved fixtures are repeatable and no unsupported claim is emitted. | 02. Implement Match Report aggregate, source transaction, and persistence: missing/stale/deleted/foreign/failing sources create no report and history is never overwritten. | 03. Expose Match Report creation API: all source, success, conflict, denial, repeat, and failure fixtures match exactly. | 04. Verify deterministic Match backend and quality corpus: approved corpus and counterexamples pass across repeated clean runs and source races.
  - Verification: 01. Implement deterministic matcher and evidence resolver: PHPUnit quality corpus, boundary/property, repeat-process, and rule-version tests. | 02. Implement Match Report aggregate, source transaction, and persistence: application/PostgreSQL constraint, source-race, rollback, dedupe, and two-User tests. | 03. Expose Match Report creation API: Laravel feature/contract tests with current/historical/deleted/mixed-owner cases. | 04. Verify deterministic Match backend and quality corpus: approved PHPUnit/PostgreSQL quality commands with corpus and rule-version evidence.

- [ ] TASK-2-4-03: Deliver and verify Match Report generation journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-4-01`
  - Covers: `AC-2-4-generate-a-match-report-01`, `AC-2-4-generate-a-match-report-02`, `AC-2-4-generate-a-match-report-03`, `AC-2-4-generate-a-match-report-04`, `AC-2-4-generate-a-match-report-05`, `AC-2-4-generate-a-match-report-06`
  - Scope: 01. Implement Match Report creation adapter and comparison state: report/source schema, create adapter, mutation/error/reconciliation state | 02. Build accessible Match Report source-selection and generation UI: owned source selectors, current-analysis state, compare action, pending/conflict/success/failure UX | 03. Verify Match Report generation journey end to end: browser source selection, Analyze-first, success/repeat, stale/deleted/foreign/failure paths
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-006`; `E2-DEC-008`; `E2-DEC-009`
  - Outcome: 01. Implement Match Report creation adapter and comparison state: Consume exact fixtures without browser-side matching or live-source substitution. | 02. Build accessible Match Report source-selection and generation UI: Let Users select valid sources and understand why generation is blocked or succeeds. | 03. Verify Match Report generation journey end to end: Verify report generation across browser, API, deterministic engine, and database.
  - Acceptance: 01. Implement Match Report creation adapter and comparison state: Analyze-first, conflict, denial, ambiguous success, and complete result states map deterministically. | 02. Build accessible Match Report source-selection and generation UI: keyboard flow prevents invalid requests and retains explicit source identity through navigation. | 03. Verify Match Report generation journey end to end: scenarios pass independently with exact stored IDs/result and disposable two-User data. | Integrated journey acceptance closes only after `TASK-2-4-02` is done with backend evidence.
  - Verification: 01. Implement Match Report creation adapter and comparison state: type-check and adapter/query/mutation tests. | 02. Build accessible Match Report source-selection and generation UI: component tests and manual keyboard/focus/state review. | 03. Verify Match Report generation journey end to end: approved Playwright command with fixture/rule versions and database-reset evidence. | Run the cross-layer journey check after `TASK-2-4-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-2-4-01` depends on `none`.
- `TASK-2-4-02` depends on `TASK-2-4-01`; within this backend task, matcher -> source transaction/persistence -> API -> backend and quality verification.
- `TASK-2-4-03` depends on `TASK-2-4-01`; its frontend or evidence work can proceed alongside `TASK-2-4-02`, and integrated acceptance closes after `TASK-2-4-02` is done.


Story 2.4 owns the stored Match Report semantics consumed by Story 2.5.

## Coordination gate
Task group 03 requires all six ACs, the approved quality gate, and immutable consumer checkpoints for CV Version, Analysis, and Match Report.
