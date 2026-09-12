---
story_key: 1-1-register-an-account
title: Register an account
type: feature
created: 2026-09-08
story_owner: unassigned
depends_on_stories: []
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-1-register-an-account
review_loop_iteration: 1
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/security-and-access.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.1: Register an account

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`; this story package does not carry a second lifecycle status.

**Planning blockers:** `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007` through
`E1-DEC-009` block readiness approval. No implementation is authorized.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, boundaries, domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Story-owned request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination gates |
| [Verification](verification.md) | AC traceability, verification layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A prospective User cannot create the private account required to
own and manage CV data.

**Approach:** Provide one accessible first-party registration flow that creates
exactly one User, establishes the approved Sanctum session, and returns only
the shared Public User representation.

</frozen-after-approval>

## References

- Global: `AD-2`, `AD-13` through `AD-19`, `HTTP-CONTRACT-001` through
  `HTTP-CONTRACT-006`, `SEC-STD-001` through `SEC-STD-006`, `VAL-STD-001`
  through `VAL-STD-005`, `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-005`, `E1-BR-016`, `E1-BR-017`,
  `E1-CONTRACT-USER-001`, `E1-CONTRACT-AUTH-001`, `E1-CONTRACT-ERROR-001`,
  `E1-STATE-AUTH-001`, `E1-SEC-001` through `E1-SEC-008`,
  `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-1-1-register-an-account-01:** Given I am not authenticated, when I submit
  a valid name, unique email, and password, then the system creates my User
  account, authenticates me as that User, and never returns my password.
- **AC-1-1-register-an-account-02:** Given the submitted email already belongs
  to a User, when I submit the form, then no account is created, I receive a
  field-level validation error, and existing User data is not disclosed.
- **AC-1-1-register-an-account-03:** Given required fields are missing or
  invalid, when I submit the form, then the request is rejected, no partial User
  is created, and errors identify every affected field.
- **AC-1-1-register-an-account-04:** Given the password does not meet the
  documented policy, when I submit, then the request is rejected and the policy
  is communicated without exposing sensitive details.
- **AC-1-1-register-an-account-05:** Given registration succeeds, when I request
  my current account, then the response identifies my User and excludes the
  password and password hash.

## Readiness coverage

Behavior covers success, duplicate, validation, authenticated caller, lost
response, expiry and terminal failure. Contract references the shared auth/User
and global error envelopes. Backend covers identity rules, persistence,
transaction/session, concurrency and current account. Security covers hashing,
CSRF, enumeration, throttling and redaction. Validation covers name, canonical
email, password and transport boundaries. Frontend covers form, pending,
recovery, success/navigation and accessible errors. Integration covers
credentials, CSRF bootstrap and cache invalidation. Verification covers unit,
feature/MySQL, component and Playwright layers. Exact policies remain blocked
by the decision register.

## Code Map

- `apps/api/composer.json`, `bootstrap/app.php`, `config/`, `routes/api.php` —
  Sanctum/session/CSRF dependency and HTTP boundary.
- `apps/api/app/` and `database/` — registration use case, User persistence,
  canonical uniqueness and resource serialization.
- `apps/web/src/shared/api/`, `features/auth/`, `pages/`, `app/router/` — browser
  transport, session state, form, errors and navigation.
- `apps/api/tests/`, `apps/web/src/**/*.spec.ts`, `apps/web/e2e/` — planned
  verification after shared tooling is approved.

## Spec Change Log

- 2026-09-08: Initial single-story analysis created.
- 2026-09-10: Review iteration 1 moved shared rules/contracts/decisions to the
  Epic 1 package, aligned with new Global standards, restored sequential task
  IDs, and kept only registration-specific behavior and tasks here.
- 2026-09-12: Moved into the Epic 1 story hierarchy and split into focused files; lifecycle remains authoritative only in `sprint-status.yaml`.
