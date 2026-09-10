---
story_key: 1-2-sign-in-and-sign-out
title: Sign in and sign out
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-1-register-an-account
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-2-sign-in-and-sign-out
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/security-and-access.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.2: Sign in and sign out

**Readiness:** Draft; blocked by `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`
through `E1-DEC-009`, and the approved `E1-COORD-AUTH-001` checkpoint.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A returning User cannot establish, inspect, or end the trusted
session needed to access private CV data.

**Approach:** Reuse the Epic auth/User contract for accessible sign-in,
current-account hydration, explicit sign-out, and safe expiry recovery.

## References

- Global: `AD-13`, `AD-14`, `AD-17` through `AD-19`,
  `HTTP-CONTRACT-001` through `HTTP-CONTRACT-006`, `SEC-STD-001` through
  `SEC-STD-006`, `A11Y-STD-001` through `A11Y-STD-003`.
- Epic: `E1-BR-001` through `E1-BR-005`, `E1-BR-017`,
  `E1-CONTRACT-USER-001`, `E1-CONTRACT-AUTH-001`,
  `E1-CONTRACT-ERROR-001`, `E1-STATE-AUTH-001`, `E1-SEC-001` through
  `E1-SEC-008`, `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Use generic invalid-credential behavior; regenerate/invalidate the
server session; clear protected client state on logout/expiry; never expose or
persist credentials; keep User-owned resources unchanged.

**Ask First:** Freeze limiter, logout idempotency, safe return route, expiry
recovery and cache-clearing behavior through the Epic decisions.

**Never:** Add remember-me, recovery, social login, MFA, bearer storage, or a
parallel current-account/User shape.

## Story-specific rules and edge cases

- **LOGIN-BR-001:** Unknown email and wrong password are externally identical.
- **LOGOUT-BR-001:** A successful logout invalidates the active application
  session and client cache without editing User data.
- **AUTH-BR-001:** Session expiry removes protected content before redirect;
  stale in-flight responses cannot repopulate it.

| Scenario | Expected behavior |
| --- | --- |
| Valid credentials | Regenerated authenticated session and shared Public User |
| Unknown email/wrong password | Same generic error and no authenticated state |
| Current account | Private/no-store shared Public User or `UNAUTHENTICATED` |
| Sign out | Session invalidated, protected cache cleared, later access rejected |
| Expiry during protected work | Data hidden, safe sign-in state, no retry loop |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-2-sign-in-and-sign-out-01:** Given I have an existing User account,
  when I submit the correct email and password, then the system authenticates
  me, protected capabilities are accessible, and no password/hash is exposed.
- **AC-1-2-sign-in-and-sign-out-02:** Given an unknown email or incorrect
  password, when I submit sign-in, then authentication fails with a generic
  error that does not reveal whether the email exists.
- **AC-1-2-sign-in-and-sign-out-03:** Given I am authenticated, when I request
  my current account, then my User is returned without password/hash fields.
- **AC-1-2-sign-in-and-sign-out-04:** Given I am authenticated, when I sign out,
  then application auth state is invalidated, later protected requests fail,
  and no User-owned data is modified or deleted.
- **AC-1-2-sign-in-and-sign-out-05:** Given expired/invalid auth state, when I
  request a protected capability, then it is rejected, the web app returns to
  sign-in state, and no protected data is displayed.

## Readiness coverage

Behavior, alternate failures, logout and expiry are explicit. Contract,
backend session transitions, security/enumeration/limits, input validation,
frontend states, CSRF/cache integration, and unit/feature/component/E2E layers
reference the shared Epic artifacts. Exact policy values remain human-gated.

## Code Map

- `apps/api/app/`, `routes/api.php`, `config/`, `bootstrap/app.php` — login,
  logout, current-account, middleware and session behavior.
- `apps/web/src/features/auth/`, `shared/api/`, `app/router/` — auth adapter,
  state, route guards, forms and expiry handling.
- `apps/api/tests/`, `apps/web/src/**/*.spec.ts`, `apps/web/e2e/` — verification.

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
  - Blocked by: Story 1.1 auth boundary
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

## Spec Change Log

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.2.
