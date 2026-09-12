---
story_key: 1-2-sign-in-and-sign-out
title: Sign in and sign out
type: feature
created: 2026-09-10
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

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`; this story package does not carry a second lifecycle status.

**Planning blockers:** `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`
through `E1-DEC-009`, and the approved `E1-COORD-AUTH-001` checkpoint.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, boundaries, domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Story-owned request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination gates |
| [Verification](verification.md) | AC traceability, verification layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A returning User cannot establish, inspect, or end the trusted
session needed to access private CV data.

**Approach:** Reuse the Epic auth/User contract for accessible sign-in,
current-account hydration, explicit sign-out, and safe expiry recovery.

</frozen-after-approval>

## References

- Global: `AD-13`, `AD-14`, `AD-17` through `AD-19`,
  `HTTP-CONTRACT-001` through `HTTP-CONTRACT-006`, `SEC-STD-001` through
  `SEC-STD-006`, `A11Y-STD-001` through `A11Y-STD-003`.
- Epic: `E1-BR-001` through `E1-BR-005`, `E1-BR-017`,
  `E1-CONTRACT-USER-001`, `E1-CONTRACT-AUTH-001`,
  `E1-CONTRACT-ERROR-001`, `E1-STATE-AUTH-001`, `E1-SEC-001` through
  `E1-SEC-008`, `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`.

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

## Spec Change Log

- 2026-09-10: Initial Epic-wide story analysis created from canonical Story 1.2.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
