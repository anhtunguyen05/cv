# Story 2.5: Review an explainable Match Report — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-2-5-01: Freeze Match Report review fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-5-review-an-explainable-match-report-01`, `AC-2-5-review-an-explainable-match-report-02`, `AC-2-5-review-an-explainable-match-report-03`, `AC-2-5-review-an-explainable-match-report-04`
  - Scope: 01. Freeze Match Report review and accessibility fixtures: list/detail/source/group/evidence/recommendation/deleted/a11y fixtures
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-001`; `E2-DEC-005`; `E2-DEC-006`; `E2-DEC-008`; `E2-DEC-009`; approved E2-COORD-MATCH-001 immutable-report checkpoint from Story 2.4
  - Outcome: 01. Freeze Match Report review and accessibility fixtures: Freeze one stored report projection and accessible presentation contract.
  - Acceptance: 01. Freeze Match Report review and accessibility fixtures: fixtures include complete, Weak Evidence, missing, empty, deleted, foreign, long, and invalid-schema cases.
  - Verification: 01. Freeze Match Report review and accessibility fixtures: schema/fixture validation and product/UX approval evidence.

- [ ] TASK-2-5-02: Deliver owned explainable Match Report read backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-01`
  - Covers: `AC-2-5-review-an-explainable-match-report-01`, `AC-2-5-review-an-explainable-match-report-02`, `AC-2-5-review-an-explainable-match-report-03`
  - Scope: 01. Implement owned Match Report read/list projection: read/list query service, policy, API resource/controller/routes, source/deleted projection
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `E2-PREREQ-VERSION-001`; approved E2-COORD-JD-001 logical-deletion checkpoint from Story 2.2
  - Outcome: 01. Implement owned Match Report read/list projection: Return exact stored report/source data without recomputation or disclosure.
  - Acceptance: 01. Implement owned Match Report read/list projection: current/deleted source state changes context/actions only, never classifications or score.
  - Verification: 01. Implement owned Match Report read/list projection: application/Laravel/PostgreSQL contract/history, two-User, deleted-source, and no-recompute tests.

- [ ] TASK-2-5-03: Deliver and verify explainable Match Report review
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-01`
  - Covers: `AC-2-5-review-an-explainable-match-report-01`, `AC-2-5-review-an-explainable-match-report-02`, `AC-2-5-review-an-explainable-match-report-03`, `AC-2-5-review-an-explainable-match-report-04`
  - Scope: 01. Implement Match Report review adapter and query state: list/detail schema/error adapter, immutable query keys, invalid/deleted state mapping | 02. Build accessible Match Report source and classification view: score/source metadata, matched/missing/Weak Evidence groups, empty/deleted/error states | 03. Build evidence and recommendation section references: evidence source display, related CV section labels/navigation, unsupported-claim guard | 04. Verify frontend presentation, accessibility, and quality counterexamples: component behavior, reading order, labels, and unsupported-claim states | 05. Verify explainable Match Report journey end to end: browser open/reload/keyboard/Weak/missing/deleted-source/foreign scenarios
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-006`; `E2-DEC-005`; `E2-DEC-009`; `DISCOVERY-E2-001`
  - Outcome: 01. Implement Match Report review adapter and query state: Map shared fixtures into stable review states without reclassification. | 02. Build accessible Match Report source and classification view: Present stored report meaning with semantic labels and reading order. | 03. Build evidence and recommendation section references: Make available recommendations actionable without editing or inventing CV data. | 04. Verify frontend presentation, accessibility, and quality counterexamples: Prove the UI preserves stored meaning and accessible reading order. | 05. Verify explainable Match Report journey end to end: Verify the critical report-understanding journey across stored data and accessible UI.
  - Acceptance: 01. Implement Match Report review adapter and query state: complete, empty, deleted, foreign, failure, and invalid-payload cases are deterministic. | 02. Build accessible Match Report source and classification view: no category uses color alone and long/empty content remains usable by keyboard/assistive tech. | 03. Build evidence and recommendation section references: each provided reference resolves safely and Weak/missing cases cannot appear fully evidenced. | 04. Verify frontend presentation, accessibility, and quality counterexamples: shared UI fixtures pass without browser recomputation or unsupported claims. | 05. Verify explainable Match Report journey end to end: scenarios pass independently with exact source/result IDs and disposable two-User data. | Integrated journey acceptance closes only after `TASK-2-5-02` is done with backend evidence.
  - Verification: 01. Implement Match Report review adapter and query state: type-check and adapter/query tests. | 02. Build accessible Match Report source and classification view: component/a11y tests and manual keyboard/reading-order review. | 03. Build evidence and recommendation section references: component fixture and navigation tests with negative unsupported-claim cases. | 04. Verify frontend presentation, accessibility, and quality counterexamples: type-check, Vitest component suite, and accessibility review. | 05. Verify explainable Match Report journey end to end: approved Playwright command with corpus/rule versions and database-reset evidence. | Run the cross-layer journey check after `TASK-2-5-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-2-5-01` depends on `none`.
- `TASK-2-5-02` depends on `TASK-2-5-01`.
- `TASK-2-5-03` depends on `TASK-2-5-01`; its frontend or evidence work can proceed alongside `TASK-2-5-02`, and integrated acceptance closes after `TASK-2-5-02` is done.


Story 2.5 never edits Story 2.4 matching semantics.

## Coordination gate
Task group 03 requires all four ACs, the quality counterexample corpus, and accessible evidence that the UI preserves stored report meaning.
