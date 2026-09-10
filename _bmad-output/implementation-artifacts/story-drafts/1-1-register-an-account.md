---
story_key: 1-1-register-an-account
title: Register an account
type: feature
created: 2026-09-08
status: draft
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

**Readiness:** Draft. `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007` through
`E1-DEC-009` block publication. No implementation is authorized.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A prospective User cannot create the private account required to
own and manage CV data.

**Approach:** Provide one accessible first-party registration flow that creates
exactly one User, establishes the approved Sanctum session, and returns only
the shared Public User representation.

## References

- Global: `AD-2`, `AD-13` through `AD-19`, `HTTP-CONTRACT-001` through
  `HTTP-CONTRACT-006`, `SEC-STD-001` through `SEC-STD-006`, `VAL-STD-001`
  through `VAL-STD-005`, `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-005`, `E1-BR-016`, `E1-BR-017`,
  `E1-CONTRACT-USER-001`, `E1-CONTRACT-AUTH-001`, `E1-CONTRACT-ERROR-001`,
  `E1-STATE-AUTH-001`, `E1-SEC-001` through `E1-SEC-008`,
  `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Canonicalize and validate approved fields; enforce email uniqueness
at application and database boundaries; hash through Laravel; create the User
and session under the approved failure model; prevent duplicate submission;
never return/log credentials.

**Ask First:** Resolve the five blocking Epic decisions and record approval
evidence before implementation.

**Never:** Add recovery, social login, roles, MFA, Profile creation, or a second
auth mechanism; expose an existing User; store browser bearer credentials.

## Story-specific rules and edge cases

- **REG-BR-001:** Registration accepts only `name`, `email`, `password`, and an
  approved confirmation field; a User/role/verification flag is not writable.
- **REG-BR-002:** Concurrent requests for one canonical email create at most one
  User and expose the same safe losing outcome as a normal duplicate.
- **REG-BR-003:** If persistence commits but the response is lost, the client
  reconciles current-account state before presenting a retry that could create
  or confuse identities.

| Scenario | Expected behavior |
| --- | --- |
| Valid guest submission | One User, regenerated session, `data.user`, no credential fields |
| Duplicate or concurrent duplicate | No second User; generic approved email error |
| Missing/invalid/oversized/malformed input | No User/session mutation; field or global safe error |
| Authenticated caller | No new User and no identity replacement |
| CSRF/session/server failure | Approved recoverable or terminal state; no retry loop or partial success |

</frozen-after-approval>

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

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-1-01: Add executable auth/User contract fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: stable auth contract docs/fixtures
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, `E1-DEC-009`
  - Outcome: Add executable auth/User contract fixtures.
  - Acceptance: one fixture set covers every approved auth matrix row.
  - Verification: validate fixture syntax and review against Global/Epic IDs.
- [ ] TASK-1-1-02: Configure Laravel Sanctum SPA authentication
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-05`
  - Scope: dependency, auth/session/CORS/CSRF config
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-001`
  - Outcome: Configure Laravel Sanctum SPA authentication.
  - Acceptance: approved origins and session transitions work without browser tokens.
  - Verification: focused feature tests for bootstrap, regeneration, expiry and redaction.
- [ ] TASK-1-1-03: Implement registration identity and persistence rules
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-04`
  - Scope: registration application/domain service, User migration/model/repository
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-002`
  - Outcome: Implement registration identity and persistence rules.
  - Acceptance: atomic unique User creation with approved normalization and hashing.
  - Verification: PHPUnit plus MySQL duplicate/concurrency/rollback checks.
- [ ] TASK-1-1-04: Expose register and current-account API operations
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-02`, `TASK-1-1-03`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: Form Requests, controllers, resources, routes, error mapping
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `none`
  - Outcome: Expose register and current-account API operations.
  - Acceptance: all approved responses match fixtures and exclude credentials.
  - Verification: Laravel feature/contract tests including malformed and authenticated-caller paths.
- [ ] TASK-1-1-05: Enforce registration abuse and privacy controls
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-04`
  - Covers: `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`
  - Scope: limiter, trusted proxy/store, duplicate privacy, safe logs
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-002`
  - Outcome: Enforce registration abuse and privacy controls.
  - Acceptance: approved keys/outcomes/windows and safe `429` behavior apply consistently.
  - Verification: feature tests for limits, expiry, spoofing, duplicate timing/content and redaction.
- [ ] TASK-1-1-06: Implement frontend auth transport and registration adapter
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: shared API client and auth API/schema/error mapping
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-001`, `E1-DEC-007`
  - Outcome: Implement frontend auth transport and registration adapter.
  - Acceptance: browser sends only approved fields/credentials and maps every fixture.
  - Verification: type-check and adapter/transport tests.
- [ ] TASK-1-1-07: Build registration state and accessible page
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-06`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: auth mutation/state, form, route and error presentation
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `E1-DEC-002`, `E1-DEC-007`
  - Outcome: Build registration state and accessible page.
  - Acceptance: keyboard-usable flow handles pending, validation, throttle, lost success and navigation safely.
  - Verification: manual keyboard review until component tooling is available.
- [ ] TASK-1-1-08: Verify registration backend contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-05`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: API unit/feature/MySQL integration suites
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: `none`
  - Outcome: Verify registration backend contract.
  - Acceptance: full backend behavior, transaction, concurrency and sensitive-data checks pass.
  - Verification: focused and complete PHPUnit suites against declared databases.
- [ ] TASK-1-1-09: Verify registration components
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-07`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: registration component/state tests
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify registration components.
  - Acceptance: known-bad UI/error/retry/accessibility states are pinned by tests.
  - Verification: approved Vitest command plus type-check/lint/build.
- [ ] TASK-1-1-10: Verify registration end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-08`, `TASK-1-1-09`
  - Covers: `AC-1-1-register-an-account-01` through `AC-1-1-register-an-account-05`
  - Scope: registration/current-account browser scenarios
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify registration end to end.
  - Acceptance: happy path and meaningful failures pass without test-order dependence.
  - Verification: approved Playwright command against disposable MySQL data.

## Dependency and concurrency map

```text
01 -> {02,03,06}; {02,03} -> 04 -> 05; 06 -> 07
{03,04,05} -> 08; 07 -> 09; {08,09} -> 10
```

Backend tasks 02/03 and frontend task 06 may run in parallel after task 01.
Shared auth and test files remain reserved by the Epic coordination records.

## Coordination and verification gate

Use `E1-COORD-AUTH-001` for every shared auth/User file and
`E1-COORD-TEST-001` for reusable test tooling. Story acceptance requires tasks
08–10 and evidence for every canonical AC; local contract variants are rejected.

## Spec Change Log

- 2026-09-08: Initial single-story draft created.
- 2026-09-10: Review iteration 1 moved shared rules/contracts/decisions to the
  Epic 1 package, aligned with new Global standards, restored sequential task
  IDs, and kept only registration-specific behavior and tasks here.
