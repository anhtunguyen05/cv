---
title: 'TASK-1-2-02 Secure Session Lifecycle Backend'
type: 'feature'
created: '2026-09-23'
status: 'done'
baseline_commit: '246eecc7742341337bdd0e93bdc844465ca31565'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/README.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/tasks.md'
  - '{project-root}/docs/contracts/auth/registration.md'
  - '{project-root}/docs/contracts/auth/fixtures/registration-v1.json'
  - '{project-root}/docs/contracts/common/http.md'
  - '{project-root}/docs/standards/security.md'
  - '{project-root}/docs/standards/validation.md'
  - '{project-root}/docs/standards/testing.md'
  - '{project-root}/apps/api/AGENTS.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** The API has registration and current-account behavior but no
implemented sign-in or sign-out lifecycle matching the approved
`auth-lifecycle-v2` contract. Protected access therefore lacks a verified
credential transition, invalidation path, and abuse/privacy boundary.

**Approach:** Add thin Laravel HTTP adapters for login and logout, reuse the
existing User provider, Public User resource, CSRF/session middleware, JSON
exception mapping, and registration limiter conventions, then prove the full
backend lifecycle against the shared fixture corpus and PostgreSQL 16.

## Boundaries & Constraints

**Always:** Use Sanctum stateful HttpOnly cookie sessions and the `web` guard;
accept only login `email/password` and an empty logout body; normalize email by
trim/lowercase; regenerate the session after successful login; invalidate the
session and regenerate its CSRF token on logout; return only Public User; use
generic `401 INVALID_CREDENTIALS`; use hashed trusted-IP and canonical-email
limiters at 5/10 minutes and 3/10 minutes; return `429 THROTTLED` with
`Retry-After`; preserve User-owned data; reject bearer authentication; render
all `/api/v1` failures through the common JSON envelope.

**Ask First:** Stop if implementation requires changing the approved fixture,
adding a migration/dependency, changing registration semantics, or choosing a
different logout/limiter policy.

**Never:** Add bearer tokens, remember-me, password recovery, MFA, social login,
frontend changes, client credential storage, a second User projection, or
silently broaden this task into Story 1.2 Task 03.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|---|---|---|---|
| Valid login | Guest, valid CSRF, existing email/password | `200 data.user`, regenerated authenticated session | Public projection only |
| Invalid login | Unknown email or wrong password | Identical `401 INVALID_CREDENTIALS` response | No account disclosure |
| Login validation | Missing/invalid fields or malformed JSON | `422 VALIDATION_FAILED` or `400 INVALID_REQUEST_BODY` | No session mutation |
| Login throttle | IP/email threshold exceeded | `429 THROTTLED` and `Retry-After` | No automatic retry |
| Authenticated current account | Valid cookie session | `200 data.user`, private/no-store | No credential fields |
| Logout | Authenticated or guest, valid CSRF | `204`, invalidated session, unchanged User data | Repeated logout remains `204` |
| Invalid CSRF/bearer | State-changing request has bad CSRF or bearer token | `419 SESSION_EXPIRED` or `401 UNAUTHENTICATED` | No User/session data mutation |

</frozen-after-approval>

## Code Map

- `apps/api/routes/api.php` -- existing auth route group; add login/logout with the approved middleware order and leave `/me` ownership intact.
- `apps/api/app/Presentation/Http/Controllers/Auth/RegisterController.php` -- sibling controller showing thin Auth/session/public-resource response style.
- `apps/api/app/Presentation/Http/Controllers/Auth/CurrentAccountController.php` -- reuse Public User and private/no-store response conventions.
- `apps/api/app/Presentation/Http/Requests/RegisterRequest.php` -- sibling normalization and unknown-field validation pattern for the new LoginRequest.
- `apps/api/app/Presentation/Http/Middleware/{RequireCsrfToken,RejectBearerToken,RejectMalformedJson}.php` -- existing state-changing request and auth-boundary middleware; reuse, do not replace.
- `apps/api/app/Providers/AppServiceProvider.php` -- existing hashed registration limiter definitions; add symmetric login limiters here.
- `apps/api/app/Models/User.php` and `apps/api/config/auth.php` -- existing Eloquent web provider and hashed password cast; no model or migration change is expected.
- `apps/api/bootstrap/app.php` -- existing `/api/v1` JSON mappings for authentication, validation, CSRF, throttle, and unexpected failures.
- `apps/api/docker-compose.yml` and `apps/api/phpunit.postgres.xml` -- API test runtime and read-only mount/configuration for the repository-level canonical fixture.
- `apps/api/tests/Feature/Auth/RegistrationTest.php` -- test setup, CSRF/session headers, limiter clearing, public projection, and PostgreSQL-compatible feature-test patterns.
- `docs/contracts/auth/fixtures/registration-v1.json` -- shared `auth-lifecycle-v2` expectations; backend tests must consume relevant rows rather than redefine status/error semantics.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/app/Presentation/Http/Requests/LoginRequest.php` -- add canonical email/password validation and reject unsupported fields -- prevent contract drift before authentication.
- [x] `apps/api/app/Presentation/Http/Controllers/Auth/LoginController.php` -- authenticate through the web guard, regenerate the session, return Public User/private no-store, and map invalid credentials generically -- implement the approved login transition.
- [x] `apps/api/app/Presentation/Http/Controllers/Auth/LogoutController.php` and `apps/api/routes/api.php` -- expose CSRF-protected login/logout routes with bearer/malformed/throttle middleware and idempotent logout -- implement the approved HTTP boundary.
- [x] `apps/api/app/Providers/AppServiceProvider.php` -- add hashed `login-ip` and `login-email` limiters with the approved windows -- enforce abuse controls consistently with registration.
- [x] `apps/api/tests/Feature/Auth/AuthLifecycleTest.php` -- load the shared fixture corpus and cover login, generic failure, validation, throttle, current account, logout, CSRF, bearer rejection, regeneration/invalidation, redaction, and data preservation -- provide backend evidence.
- [x] `apps/api/docker-compose.yml` and `apps/api/phpunit.postgres.xml` -- expose the repository docs fixture read-only to the API test container -- let backend verification consume the canonical corpus without copying it.

**Acceptance Criteria:**
- Given valid fixture credentials, when login is posted with a valid browser session, then the API returns `200 data.user`, regenerates the session, and authenticates subsequent `/me` access.
- Given unknown email and wrong password, when login is posted, then both responses match the fixture's identical `401 INVALID_CREDENTIALS` public shape and no session is authenticated.
- Given malformed/unsupported login input or exceeded IP/email limits, when the request is posted, then the API returns the fixture's JSON error/status without authentication or credential disclosure.
- Given an authenticated or already-guest browser session with valid CSRF, when logout is posted, then the API returns `204`, invalidates access, regenerates CSRF state, and leaves User-owned data unchanged.
- Given invalid CSRF or bearer input, when a state-changing auth request is posted, then the API returns `419 SESSION_EXPIRED` or `401 UNAUTHENTICATED` and performs no protected mutation.
- Given the focused backend suite, when it runs against PostgreSQL 16 with Redis-backed rate limiting, then all applicable lifecycle fixture assertions pass.

## Design Notes

Use `Auth::guard('web')` explicitly so the session boundary is unambiguous.
Login should not return a token or expose whether the lookup found a User.
Logout should not require `auth:sanctum`, because the approved idempotent guest
case must still return `204` after the CSRF boundary is accepted. The test
fixture loader may use concrete synthetic credentials while expected status,
error, projection, and lifecycle assertions come from the JSON corpus.

## Verification

**Commands:**
- `docker compose exec -T app php vendor/bin/phpunit -c phpunit.postgres.xml tests/Feature/Auth/AuthLifecycleTest.php` -- expected: focused auth lifecycle tests pass against PostgreSQL 16 and Redis.
- `docker compose exec -T app php artisan route:list --path=api/v1/auth` -- expected: register, login, me, and logout are present under the approved paths.
- `docker compose exec -T app php artisan config:clear` -- expected: configuration loads with database sessions, Sanctum stateful domains, and Redis cache.
- `git diff --check` -- expected: no whitespace errors.

**Manual checks:**
- Confirm no password, hash, cookie, CSRF value, bearer token, or User-owned data enters response/log assertions.
- Confirm the shared fixture is read by the feature test and no local envelope or status variant is introduced.
- Confirm Story 1.2 Task 03 remains unchanged and frontend integration remains a downstream boundary.

## Verification Results

- Focused auth lifecycle: `OK (8 tests, 59 assertions)` against PostgreSQL 16 and Redis.
- Full API suite: `OK (23 tests, 120 assertions)` against PostgreSQL 16 and Redis.
- Auth routes: `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`, and `POST /api/v1/auth/register` present.
- Laravel config cache clear completed successfully.
- `git diff --check` passed; the only output was Git's existing CRLF normalization warning for an untouched nginx file.

## Suggested Review Order

1. `apps/api/routes/api.php` and `apps/api/app/Providers/AppServiceProvider.php` -- verify the stateful middleware boundary and independent IP/email limiter policy.
2. `apps/api/app/Presentation/Http/Requests/LoginRequest.php`, `LoginController.php`, and `LogoutController.php` -- verify normalization, generic failures, session rotation, CSRF rotation, empty-body handling, and public response redaction.
3. `apps/api/tests/Feature/Auth/AuthLifecycleTest.php` -- verify fixture-backed lifecycle, email/IP throttling, CSRF/bearer rejection, and data-preservation evidence.
4. `apps/api/docker-compose.yml` and `apps/api/phpunit.postgres.xml` -- verify the canonical fixture is mounted read-only rather than copied.
