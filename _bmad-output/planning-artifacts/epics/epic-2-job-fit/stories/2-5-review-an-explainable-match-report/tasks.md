# Story 2.5: Review an explainable Match Report — Tasks

Return to the [Story overview](README.md). Story lifecycle comes from
`sprint-status.yaml`; task lifecycle is maintained only here.

## Tasks and acceptance

- [ ] TASK-2-5-01: Freeze Match Report review and accessibility fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-04`
  - Scope: list/detail/source/group/evidence/recommendation/deleted/a11y fixtures
  - Coordination: `E2-COORD-MATCH-001`, `E2-COORD-TEST-001`
  - Blocked by: approved `E2-COORD-MATCH-001` immutable-report checkpoint from Story 2.4; `E2-DEC-001`, `E2-DEC-005`, `E2-DEC-006`, `E2-DEC-008`, `E2-DEC-009`
  - Outcome: Freeze one stored report projection and accessible presentation contract.
  - Acceptance: fixtures include complete, Weak Evidence, missing, empty, deleted, foreign, long, and invalid-schema cases.
  - Verification: schema/fixture validation and product/UX approval evidence.
- [ ] TASK-2-5-02: Implement owned Match Report read/list projection
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-01`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-03`
  - Scope: read/list query service, policy, API resource/controller/routes, source/deleted projection
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `E2-PREREQ-VERSION-001`; approved `E2-COORD-JD-001` logical-deletion checkpoint from Story 2.2
  - Outcome: Return exact stored report/source data without recomputation or disclosure.
  - Acceptance: current/deleted source state changes context/actions only, never classifications or score.
  - Verification: application/Laravel/MySQL contract, two-User, deleted-source, and no-recompute tests.
- [ ] TASK-2-5-03: Implement Match Report review adapter and query state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-01`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-04`
  - Scope: list/detail schema/error adapter, immutable query keys, invalid/deleted state mapping
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Map shared fixtures into stable review states without reclassification.
  - Acceptance: complete, empty, deleted, foreign, failure, and invalid-payload cases are deterministic.
  - Verification: type-check and adapter/query tests.
- [ ] TASK-2-5-04: Build accessible Match Report source and classification view
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-03`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-04`
  - Scope: score/source metadata, matched/missing/Weak Evidence groups, empty/deleted/error states
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `none`
  - Outcome: Present stored report meaning with semantic labels and reading order.
  - Acceptance: no category uses color alone and long/empty content remains usable by keyboard/assistive tech.
  - Verification: component/a11y tests and manual keyboard/reading-order review.
- [ ] TASK-2-5-05: Build evidence and recommendation section references
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-04`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-03`
  - Scope: evidence source display, related CV section labels/navigation, unsupported-claim guard
  - Coordination: `E2-COORD-MATCH-001`
  - Blocked by: `E2-DEC-006`
  - Outcome: Make available recommendations actionable without editing or inventing CV data.
  - Acceptance: each provided reference resolves safely and Weak/missing cases cannot appear fully evidenced.
  - Verification: component fixture and navigation tests with negative unsupported-claim cases.
- [ ] TASK-2-5-06: Verify report review contract and accessibility
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-02`, `TASK-2-5-05`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-04`
  - Scope: backend contract/history plus frontend component/accessibility/quality counterexamples
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-005`, `E2-DEC-009`, `DISCOVERY-E2-001`
  - Outcome: Prove stored truth, non-disclosure, classifications, empty states, labels, and reading order.
  - Acceptance: approved fixture corpus passes without browser recomputation or unsupported claims.
  - Verification: approved PHPUnit, type-check, Vitest, and accessibility review commands/results.
- [ ] TASK-2-5-07: Verify explainable Match Report journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-2-5-06`
  - Covers: `AC-2-5-review-an-explainable-match-report-01` through `AC-2-5-review-an-explainable-match-report-04`
  - Scope: browser open/reload/keyboard/Weak/missing/deleted-source/foreign scenarios
  - Coordination: `E2-COORD-TEST-001`
  - Blocked by: `E2-DEC-009`
  - Outcome: Verify the critical report-understanding journey across stored data and accessible UI.
  - Acceptance: scenarios pass independently with exact source/result IDs and disposable two-User data.
  - Verification: approved Playwright command with corpus/rule versions and database-reset evidence.

## Dependency and concurrency map

```text
01 -> {02,03}; 03 -> 04 -> 05; {02,05} -> 06 -> 07
```

Backend read projection task 02 and frontend task 03 may proceed in parallel
after the shared fixture. Story 2.5 never edits Story 2.4 matching semantics.

## Coordination gate

Story acceptance requires tasks 06–07, all four ACs, the quality counterexample
corpus, and accessible evidence that the UI preserves stored report meaning.
