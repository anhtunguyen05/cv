---
title: 'Epic 1 Trusted CV implementation'
type: 'feature'
created: '2026-10-06'
status: 'done'
baseline_commit: 'd51ab90'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-1-context.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md'
  - '{project-root}/docs/contracts/auth/registration.md'
  - '{project-root}/docs/contracts/cv/profile-v1.md'
  - '{project-root}/docs/contracts/cv/version-v1.md'
  - '{project-root}/docs/sprint-workflow.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** Epic 1 has approved contracts and story packages, but the current
repository still contains a scaffold CV editor, incomplete shared verification,
and no verified Profile/Version implementation. Auth code must be reconciled
with its approved cookie-session contract rather than trusted from stale task
evidence.

**Approach:** Complete Epic 1 on this branch in dependency order: finish the
shared disposable verification harness and auth journey, implement the mutable
Profile aggregate and all section editors, then implement immutable Version
snapshots and the cross-story acceptance suite. Preserve Laravel ownership,
PostgreSQL 16 evidence, server validation, and the approved v1 contracts.

## Boundaries & Constraints

**Always:** Keep routes under `/api/v1`, use Sanctum HttpOnly cookie sessions,
server-side ownership, ULIDs for Profile/item/Version identities, atomic
section replacement with `If-Match`, idempotency for creates, private/no-store
responses, and immutable stored Version snapshots. Use shared fixtures across
PHP, Vue, and Playwright; isolate all destructive test resets to a named
disposable Compose project. Keep credentials, tokens, CSRF values, and provider
secrets out of responses, browser state, fixtures, and ordinary logs.

**Ask First:** Stop if implementation requires changing an approved contract,
adding a runtime dependency, changing an existing applied migration, or
changing the Laravel/Sanctum origin, session, or PostgreSQL boundary.

**Never:** Add bearer-token browser authentication, persist trusted domain state
in Vue, reconstruct Versions from live Profiles, weaken ownership/non-disclosure,
use SQLite as PostgreSQL integration evidence, or claim a browser/API result
without the corresponding disposable test evidence.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Account lifecycle | Guest or authenticated cookie, valid/invalid CSRF, valid/invalid credentials | Register/login establish a session; logout/expiry clear it and protected state | Generic auth failures, `419`/`401`, bounded recovery, no credential disclosure |
| Profile aggregate | Valid create/update, malformed nested fields, duplicate title, quota/race, foreign ID | Atomic owner-scoped resource with stable IDs and revision | `422` validation, `409` stale/idempotency conflict, non-disclosing `404` |
| Version snapshot | Owned Profile, exact `If-Match`, concurrent Profile write, repeated idempotency key | One complete snapshot with source revision; reads use stored snapshot only | Stale conflict, key reuse conflict, source/ownership `404`, immutable DB rejection |
| Verification runtime | Parallel API/browser run | Unique project, ports, database, accounts, and artifacts per run | Cleanup only named project/volume; no development DB reset |

</frozen-after-approval>

## Code Map

- `apps/api/routes/api.php`, `bootstrap/app.php`, `app/Presentation/Http/{Controllers,Requests,Resources,Middleware}` -- existing API/auth boundary; preserve response and middleware conventions while adding Profile/Version routes.
- `apps/api/app/Models/User.php`, `app/Providers/AppServiceProvider.php`, `app/Shared/Infrastructure` -- existing User, limiters, and transaction seams to reuse.
- `apps/api/database/migrations/2026_09_13_000002_create_cv_profiles_table.php` and `000003_create_cv_versions_table.php` -- scaffold schema requiring forward-only alignment with Profile/Version v1.
- `apps/api/tests/Feature/Auth/*`, `phpunit*.xml`, `docker-compose.yml` -- existing feature patterns and PostgreSQL runtime; add focused suites without weakening current auth checks.
- `apps/web/src/shared/api/client.ts`, `features/auth/**`, `app/router/index.ts`, `vite.config.ts` -- cookie-session transport, auth state, guards, and local proxy to reconcile with `/api/v1`.
- `apps/web/src/features/cv/**`, `pages/cv/CvEditorPage.vue` -- scaffold CV types/API/editor; replace fixture data and timeout save with Profile-backed section state.
- `apps/web/package.json`, `vitest.config.ts`, `playwright.config.ts`, `tests/**` -- existing unit/browser entry points; add the isolated harness and critical journeys.
- `_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-{1..8}-*/` -- canonical acceptance criteria, task boundaries, and verification gates; do not duplicate lifecycle in this spec.

## Tasks & Acceptance

**Execution:**
- [x] `scripts/e1-verify.sh`, `apps/api/docker-compose.e2e.yml`, test configs, fixtures, and CI -- implement the disposable PG16/API/web/E2E harness described by `E1-COORD-TEST-001`.
- [x] `apps/web/src/features/auth/**`, `src/shared/api/client.ts`, router, proxy, and tests -- reconcile registration/login/logout/current-account, expiry, protected-state clearing, accessible errors, and bounded recovery with the shared auth contract.
- [x] `apps/api/app/{Models,Application,Presentation}`, database forward migrations, Profile fixtures, and API tests -- implement Profile v1 aggregate, validation, ownership, quota, idempotency, section writes, revision conflicts, and list/detail/create behavior.
- [x] `apps/web/src/features/cv/**`, CV pages, schemas, and component tests -- implement Profile create/edit/empty/stale/error states for every approved section without hardcoded domain data.
- [x] `apps/api/app/{Models,Application,Presentation}`, Version migration/guard, fixtures, and tests -- implement transactional immutable Version snapshots, deterministic owner-only list/detail reads, and PostgreSQL immutability evidence.
- [x] `apps/web/tests/e2e/**`, API integration suites, and Epic acceptance mapping -- prove all eight Stories, cross-user isolation, session expiry, stale writes, snapshot regression, and cleanup/artifact behavior.

**Acceptance Criteria:**
- Given a guest or returning User, when account actions run through the public UI, then cookie-session auth, CSRF, expiry, logout, redaction, and protected-route behavior match the approved auth fixtures.
- Given an authenticated User, when Profile data is created or section-replaced, then validation, ownership, revision, idempotency, quota, stable item IDs, order, and atomicity match Profile v1.
- Given a valid Profile and Version request, when creation races a Profile write or is retried, then exactly one complete source revision is stored and later Profile writes cannot alter it.
- Given malformed, foreign, stale, throttled, or failed requests, when the API responds, then the approved status/code/details/header behavior is preserved without disclosure or partial mutation.
- Given the Epic verification entry point, when API, web, and browser suites run concurrently, then each run is isolated and produces reproducible evidence without modifying development data.

## Design Notes

The implementation graph is `E1-COORD-TEST-001/auth → Profile baseline →
section modules and Version in parallel → Epic integration gate`. Profile v1 is
the sole document shape; Version uses a named projection of the locked
`ProfileDocumentV1`, not a second section model. Existing auth implementation
must be verified against the current contract before declaring Story 1.1/1.2
complete.

## Verification

**Commands:**
- `./scripts/e1-verify.sh api-fast` -- expected: focused fast API/unit suite passes.
- `./scripts/e1-verify.sh api-pg` -- expected: disposable PostgreSQL 16 feature/concurrency/constraint suite passes.
- `./scripts/e1-verify.sh web` -- expected: type-check, non-mutating lint, and Vitest pass.
- `./scripts/e1-verify.sh e2e` -- expected: Playwright critical journeys pass with unique disposable accounts.
- `git diff --check` -- expected: no whitespace errors.

## Verification Evidence

- `./scripts/e1-verify.sh api-fast` — 7 tests, 13 assertions passed.
- `./scripts/e1-verify.sh api-pg` — PostgreSQL 16 disposable run, 36 tests, 174 assertions passed.
- `./scripts/e1-verify.sh web` — type-check, lint, Vitest, and build passed.
- `./scripts/e1-verify.sh e2e` — registration journey and immutable Version preview passed (2 Playwright tests).
- `vendor/bin/pint --dirty --format agent`, PHP lint, Prettier, and `git diff --check` passed.

## Suggested Review Order

**Runtime boundary and persistence**

- Start with the forward-only schema alignment and PostgreSQL immutability guard.
  [`2026_10_06_000010_align_trusted_cv_schema.php:14`](../../apps/api/database/migrations/2026_10_06_000010_align_trusted_cv_schema.php#L14)

- Review the Profile service for canonical validation, ownership, idempotency, and revision-safe section writes.
  [`ProfileService.php:15`](../../apps/api/app/Application/Cv/ProfileService.php#L15)

- Review Version creation and stored-snapshot semantics at the transaction boundary.
  [`VersionService.php:18`](../../apps/api/app/Application/Cv/VersionService.php#L18)

**HTTP and session security**

- Confirm cookie-session, explicit CSRF, and JSON exception behavior at the application boundary.
  [`bootstrap/app.php:19`](../../apps/api/bootstrap/app.php#L19)

- Trace public auth and protected CV routes, including ownership middleware.
  [`api.php:17`](../../apps/api/routes/api.php#L17)

**User-facing CV flow**

- Follow the editor’s server-backed section state, conflict reconciliation, and Version creation controls.
  [`CvEditorPage.vue:53`](../../apps/web/src/pages/cv/CvEditorPage.vue#L53)

- Verify immutable Version loading uses correctly unwrapped query state before rendering the snapshot.
  [`CvVersionPage.vue:12`](../../apps/web/src/pages/cv/CvVersionPage.vue#L12)

- Inspect the reusable preview projection for structured sections and safe external links.
  [`CvPreviewDocument.vue:4`](../../apps/web/src/features/cv/components/CvPreviewDocument.vue#L4)

**Verification and acceptance**

- Review disposable Compose isolation, health gates, migrations, and cleanup behavior.
  [`e1-verify.sh:34`](../../scripts/e1-verify.sh#L34)

- Read the end-to-end acceptance journey covering registration, Profile sections, and immutable Version display.
  [`trusted-cv.spec.ts:3`](../../apps/web/tests/e2e/trusted-cv.spec.ts#L3)

</spec>
