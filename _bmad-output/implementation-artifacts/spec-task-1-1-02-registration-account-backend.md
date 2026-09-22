---
title: 'TASK-1-1-02 Registration and Account Backend'
type: 'feature'
created: '2026-09-22'
status: 'done'
baseline_commit: '8e7bc9b796e23d2139a63716dac1e2fbd43bbc37'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/tasks.md'
  - '{project-root}/docs/contracts/auth/registration.md'
  - '{project-root}/docs/standards/testing.md'
  - '{project-root}/apps/api/AGENTS.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** The Laravel API exposes only health and has no registration, current-account, Sanctum session, CSRF, or approved JSON error boundary. The existing web bearer-token scaffold cannot satisfy the frozen registration contract.

**Approach:** Implement the backend contract for `POST /api/v1/auth/register`, `GET /api/v1/auth/me`, and Sanctum CSRF/session bootstrap using the existing User model, transaction boundary, and PostgreSQL 16 integration target. Prove behavior with the shared fixture corpus and focused Laravel tests.

## Boundaries & Constraints

**Always:** Preserve the existing bigint User ID and hashed password cast. Normalize name/email exactly as the approved contract requires; return only `id`, `name`, and `email`. Use stateful HttpOnly cookie sessions, CSRF protection, server-owned identity, private/no-store account responses, generic duplicate-email disclosure, hashed limiter keys, safe logs, and PostgreSQL 16 for persistence/constraint/concurrency evidence.

**Ask First:** Stop if the installed Laravel/Sanctum version requires a contract change, if the existing transaction or session boundary cannot support the approved behavior, or if PostgreSQL integration requires an infrastructure change outside `apps/api`.

**Never:** Add bearer-token authentication, expose credentials or internal User fields, add a generic repository without a consumer, modify frontend registration, mark TASK-1-1-03 or the Story done, or claim SQLite-only evidence as PostgreSQL integration proof.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Register | Valid guest request after CSRF bootstrap | `201` with `data.user`, regenerated session | No credential material in response/logs |
| Invalid request | Missing/unknown fields, invalid name/email/password | `422 VALIDATION_FAILED` with field details | No User/session is created |
| Duplicate | Existing canonical email, including concurrent race | Generic `422 VALIDATION_FAILED` | No email existence disclosure beyond approved shape |
| Authenticated caller | Existing authenticated session posts registration | `409 AUTHENTICATED_REGISTRATION_FORBIDDEN` | Existing identity remains unchanged |
| Current account | Valid session calls `/api/v1/auth/me` | `200`, exact Public User, `Cache-Control: no-store` | Unauthenticated/expired session returns approved `401` |
| Abuse | Trusted IP or canonical email exceeds Redis window | `429 THROTTLED` with `Retry-After` | Keys contain hashes only |

</frozen-after-approval>

## Code Map

- `apps/api/composer.json` -- Laravel dependency boundary; Sanctum is not installed yet.
- `apps/api/bootstrap/app.php` -- API routing, middleware, and JSON exception rendering currently have no auth/session configuration.
- `apps/api/routes/api.php` -- current API route surface contains only `/health`; add versioned auth routes without changing health.
- `apps/api/app/Models/User.php` -- existing Authenticatable with fillable identity fields, hidden secrets, and Laravel hashed password cast; preserve bigint identity.
- `apps/api/app/Shared/Application/Contracts/TransactionManager.php` and `apps/api/app/Shared/Infrastructure/Persistence/LaravelTransactionManager.php` -- existing transaction port and DB-backed adapter to reuse.
- `apps/api/database/migrations/0001_01_01_000000_create_users_table.php` -- existing unique email and database sessions schema; verify PostgreSQL compatibility before adding migrations.
- `apps/api/phpunit.xml` -- current SQLite in-memory fast-test defaults; add an explicit PostgreSQL integration entry point rather than silently replacing all fast tests.
- `apps/api/docker-compose.yml` and `apps/api/.env.example` -- PostgreSQL 16 and Redis local integration services.
- `docs/contracts/auth/fixtures/registration-v1.json` -- shared executable contract corpus; backend contract tests must consume it.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/composer.json`, `apps/api/config/`, `apps/api/bootstrap/app.php` -- install/configure Sanctum stateful SPA sessions, CSRF, credentialed origins, cookie attributes, and JSON framework failures -- establish the approved transport boundary.
- [x] `apps/api/app/` and `apps/api/routes/api.php` -- implement bounded registration/current-account application flow, requests, resource, controllers, routes, normalization, transaction, session regeneration, and error mapping -- deliver the approved API behavior without a generic repository layer.
- [x] `apps/api/tests/` and `apps/api/phpunit.xml` -- add focused feature tests and an explicit disposable PostgreSQL 16 integration path for constraints, session, CSRF, throttling, redaction, and ownership -- make evidence reproducible.

**Acceptance Criteria:**
- Given a CSRF-bootstrapped guest, when valid registration is posted, then one canonical User and authenticated session are created and the response is `201 data.user` with no secrets.
- Given invalid, duplicate, authenticated-caller, expired-session, malformed, or throttled input, when the corresponding API operation runs, then the status/code/details/headers match the frozen fixture and no unintended state is committed.
- Given PostgreSQL 16 integration data, when migrations and concurrent registration tests run, then unique constraints, transaction rollback, and at-most-one-user behavior are proven against PostgreSQL rather than SQLite alone.

## Design Notes

Keep the first implementation concrete and ownership-based: the User model plus a small registration application service is sufficient. Use the existing transaction port at the application boundary, Laravel Form Requests for HTTP shape, and a resource for the exact public projection. Keep Redis-specific abuse controls behind Laravel's limiter boundary so tests can assert hashed keys without coupling the domain to Redis.

## Verification

**Commands:**
- `composer validate` -- expected: valid Composer metadata after Sanctum installation.
- `php artisan route:list --path=api/v1` -- expected: register and current-account routes plus no health regression.
- `php artisan test --testsuite=Unit,Feature` -- expected: fast/unit and HTTP contract tests pass.
- approved PostgreSQL 16 integration command from the implementation -- expected: migration, constraint, rollback, concurrency, and fixture-driven backend evidence passes.
- `git diff --check` -- expected: no whitespace errors.

## Suggested Review Order

**Transport and authentication boundary**

- Stateful cookie registration, CSRF enforcement, bearer-token rejection, and throttling are composed at the route boundary.
  [`api.php:13`](../../apps/api/routes/api.php#L13)

- The registration controller owns guest protection, canonical persistence, session regeneration, duplicate privacy, and public projection.
  [`RegisterController.php:17`](../../apps/api/app/Presentation/Http/Controllers/Auth/RegisterController.php#L17)

- The explicit CSRF guard makes the browser-session requirement testable and returns the approved expiry error.
  [`RequireCsrfToken.php:11`](../../apps/api/app/Presentation/Http/Middleware/RequireCsrfToken.php#L11)

**Persistence and public contract**

- The application service reuses the existing transaction port and keeps persistence ownership concrete.
  [`RegisterUser.php:9`](../../apps/api/app/Application/Auth/RegisterUser.php#L9)

- The resource limits account responses to the approved public identity fields.
  [`PublicUserResource.php:9`](../../apps/api/app/Presentation/Http/Resources/PublicUserResource.php#L9)

**Verification and runtime configuration**

- PostgreSQL and Redis integration settings provide the reproducible backend test entry point.
  [`phpunit.postgres.xml:17`](../../apps/api/phpunit.postgres.xml#L17)

- Feature tests cover registration, duplicate privacy, sessions, CSRF, bearer rejection, malformed input, and throttling.
  [`RegistrationTest.php:13`](../../apps/api/tests/Feature/Auth/RegistrationTest.php#L13)
