# Story 1.2: Sign in and sign out — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [x] TASK-1-2-01: Freeze authentication lifecycle fixtures
  - Status: `done`
  - Owner: `Codex`
  - Branch/worktree: `feat/task-1-2-01-auth-lifecycle-fixtures`
  - Depends on: `none`
  - Covers: `AC-1-2-sign-in-and-sign-out-01`, `AC-1-2-sign-in-and-sign-out-02`, `AC-1-2-sign-in-and-sign-out-03`, `AC-1-2-sign-in-and-sign-out-04`, `AC-1-2-sign-in-and-sign-out-05`
  - Scope: 01. Revise `docs/contracts/auth/registration.md` as the shared auth lifecycle contract without creating a second corpus. | 02. Extend `docs/contracts/auth/fixtures/registration-v1.json` with login, generic invalid-credential, sign-in throttle, logout idempotency, CSRF/session expiry, protected-state clearing, stale-response, redaction, and data-preservation rows while preserving Story 1.1 semantics. | 03. Record the approved revision metadata, limiter policy, logout policy, owner, branch, and verification evidence.
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `none`; `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009` are resolved for this fixture revision.
  - Outcome: One versioned fixture set defines registration, login, current-account, logout, expiry, and recovery classification for both backend and frontend consumers.
  - Acceptance: The revised contract and corpus preserve all Story 1.1 rows, cover all five Story 1.2 ACs, make unknown-email and wrong-password failures identical, define the approved sign-in limiter and idempotent logout behavior, and assert public User redaction plus stale protected-state clearing.
  - Verification: Node structural audit passed with `auth-lifecycle` fixture version 2, 24 unique rows, all six required operations, all five Story 1.2 AC mappings, and public User keys exactly `email,id,name`; forbidden credential fields were absent. `git diff --check` passed. Manual review confirmed existing Story 1.1 rows retain their semantics and the revised contract/fixture agree on limiter, logout idempotency, redaction, and expiry recovery.

- [ ] TASK-1-2-02: Deliver secure session lifecycle backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-01`
  - Covers: `AC-1-2-sign-in-and-sign-out-01`, `AC-1-2-sign-in-and-sign-out-02`, `AC-1-2-sign-in-and-sign-out-03`, `AC-1-2-sign-in-and-sign-out-04`, `AC-1-2-sign-in-and-sign-out-05`
  - Scope: 01. Implement login and logout application/API behavior: login/logout application services, requests/resources/routes; reuse the Story 1.1 current-account operation | 02. Enforce login privacy and abuse controls: limiter, generic failures, safe logging | 03. Verify backend auth lifecycle: API feature/integration suite
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-002`; approved E1-COORD-AUTH-001 authentication boundary checkpoint from Story 1.1
  - Outcome: 01. Implement login and logout application/API behavior: Implement login and logout application/API behavior. | 02. Enforce login privacy and abuse controls: Enforce login privacy and abuse controls. | 03. Verify backend auth lifecycle: Verify backend auth lifecycle.
  - Acceptance: 01. Implement login and logout application/API behavior: sessions regenerate/invalidate and all fixture responses match. | 02. Enforce login privacy and abuse controls: unknown/wrong credentials and limits are non-disclosing. | 03. Verify backend auth lifecycle: session, non-disclosure, expiry and data-preservation evidence passes.
  - Verification: 01. Implement login and logout application/API behavior: PHPUnit and Laravel feature tests. | 02. Enforce login privacy and abuse controls: timing/content, proxy, expiry and redaction feature tests. | 03. Verify backend auth lifecycle: complete focused PHPUnit suite against PostgreSQL 16 where persistence integration is applicable.

- [ ] TASK-1-2-03: Deliver and verify sign-in and sign-out journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-2-01`
  - Covers: `AC-1-2-sign-in-and-sign-out-01`, `AC-1-2-sign-in-and-sign-out-02`, `AC-1-2-sign-in-and-sign-out-03`, `AC-1-2-sign-in-and-sign-out-04`, `AC-1-2-sign-in-and-sign-out-05`
  - Scope: 01. Implement frontend auth hydration and expiry-safe state: auth API/query/store, cache clearing, route guard | 02. Build accessible sign-in and sign-out interactions: sign-in page/form and sign-out action | 03. Verify sign-in/out end to end: browser auth journey
  - Coordination: `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-007`; `E1-DEC-008`
  - Outcome: 01. Implement frontend auth hydration and expiry-safe state: Implement frontend auth hydration and expiry-safe state. | 02. Build accessible sign-in and sign-out interactions: Build accessible sign-in and sign-out interactions. | 03. Verify sign-in/out end to end: Verify sign-in/out end to end.
  - Acceptance: 01. Implement frontend auth hydration and expiry-safe state: current account hydrates once and stale responses cannot restore cleared data. | 02. Build accessible sign-in and sign-out interactions: keyboard-usable pending/error/success/expiry states preserve no password. | 03. Verify sign-in/out end to end: sign-in, protected access, sign-out and expiry pass without stale data. | Integrated journey acceptance closes only after `TASK-1-2-02` is done with backend evidence.
  - Verification: 01. Implement frontend auth hydration and expiry-safe state: adapter/state/guard tests and type-check. | 02. Build accessible sign-in and sign-out interactions: manual accessibility check, then Vitest after enablement. | 03. Verify sign-in/out end to end: approved Playwright command on disposable data. | Run the cross-layer journey check after `TASK-1-2-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-2-01` depends on `none`.
- `TASK-1-2-02` depends on `TASK-1-2-01`.
- `TASK-1-2-03` depends on `TASK-1-2-01`; its frontend or evidence work can proceed alongside `TASK-1-2-02`, and integrated acceptance closes after `TASK-1-2-02` is done.


## Coordination and verification gate
Reuse Story 1.1 current-account ownership under `E1-COORD-AUTH-001`; reserve shared browser tooling through `E1-COORD-TEST-001`. Task group 03 must prove all five ACs before the story can leave review.
