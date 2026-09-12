# Story 1.2: Sign in and sign out — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-2-01: Extend executable auth fixtures for login/logout/expiry
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-2-sign-in-and-sign-out-01` through `AC-1-2-sign-in-and-sign-out-05`
  - Scope: stable auth contract fixtures
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, `E1-DEC-009`
  - Outcome: Extend executable auth fixtures for login/logout/expiry.
  - Acceptance: one fixture set defines every operation and recovery classification.
  - Verification: syntax and cross-story contract review.
- [ ] TASK-1-2-02: Implement login and logout application/API behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-01`
  - Covers: `AC-1-2-sign-in-and-sign-out-01` through `AC-1-2-sign-in-and-sign-out-04`
  - Scope: login/logout application services, requests/resources/routes; reuse the Story 1.1 current-account operation
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: approved `E1-COORD-AUTH-001` authentication boundary checkpoint from Story 1.1
  - Outcome: Implement login and logout application/API behavior.
  - Acceptance: sessions regenerate/invalidate and all fixture responses match.
  - Verification: PHPUnit and Laravel feature tests.
- [ ] TASK-1-2-03: Enforce login privacy and abuse controls
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-02`
  - Covers: `AC-1-2-sign-in-and-sign-out-02`, `AC-1-2-sign-in-and-sign-out-05`
  - Scope: limiter, generic failures, safe logging
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-002`
  - Outcome: Enforce login privacy and abuse controls.
  - Acceptance: unknown/wrong credentials and limits are non-disclosing.
  - Verification: timing/content, proxy, expiry and redaction feature tests.
- [ ] TASK-1-2-04: Implement frontend auth hydration and expiry-safe state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-01`
  - Covers: `AC-1-2-sign-in-and-sign-out-01`, `AC-1-2-sign-in-and-sign-out-03` through `AC-1-2-sign-in-and-sign-out-05`
  - Scope: auth API/query/store, cache clearing, route guard
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Implement frontend auth hydration and expiry-safe state.
  - Acceptance: current account hydrates once and stale responses cannot restore cleared data.
  - Verification: adapter/state/guard tests and type-check.
- [ ] TASK-1-2-05: Build accessible sign-in and sign-out interactions
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-04`
  - Covers: `AC-1-2-sign-in-and-sign-out-01`, `AC-1-2-sign-in-and-sign-out-02`, `AC-1-2-sign-in-and-sign-out-04`, `AC-1-2-sign-in-and-sign-out-05`
  - Scope: sign-in page/form and sign-out action
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build accessible sign-in and sign-out interactions.
  - Acceptance: keyboard-usable pending/error/success/expiry states preserve no password.
  - Verification: manual accessibility check, then Vitest after enablement.
- [ ] TASK-1-2-06: Verify backend auth lifecycle
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-02`, `TASK-1-2-03`
  - Covers: `AC-1-2-sign-in-and-sign-out-01` through `AC-1-2-sign-in-and-sign-out-05`
  - Scope: API feature/integration suite
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `none`
  - Outcome: Verify backend auth lifecycle.
  - Acceptance: session, non-disclosure, expiry and data-preservation evidence passes.
  - Verification: complete focused PHPUnit suite against MySQL where applicable.
- [ ] TASK-1-2-07: Verify sign-in/out end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-05`, `TASK-1-2-06`
  - Covers: `AC-1-2-sign-in-and-sign-out-01` through `AC-1-2-sign-in-and-sign-out-05`
  - Scope: browser auth journey
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify sign-in/out end to end.
  - Acceptance: sign-in, protected access, sign-out and expiry pass without stale data.
  - Verification: approved Playwright command on disposable data.

## Dependency and concurrency map

```text
01 -> {02,04}; 02 -> 03; 04 -> 05; {02,03} -> 06; {05,06} -> 07
```

## Coordination and verification gate

Reuse Story 1.1 current-account ownership under `E1-COORD-AUTH-001`; reserve
shared browser tooling through `E1-COORD-TEST-001`. Tasks 06–07 must prove all
five ACs before the story can leave review.
