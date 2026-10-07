# Epic 1 Decisions and Coordination

Recommendations made during review are proposals; this register records the
approved decisions that govern dependent stories. Before a dependent story is
approved as ready for development, every applicable decision needs one owner,
an approved resolution, and evidence in the form `approver, YYYY-MM-DD`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E1-DEC-001 | `approved` | `Product owner (user-delegated)` | Sanctum origin/session topology | Same-origin production; Vite proxy or explicit credentialed dev origin; freeze CORS, stateful domains, cookie attributes, CSRF bootstrap/recovery, regeneration and invalidation | Browser uses same-origin `/api/v1`; Vite proxies `/api/v1` and `/sanctum` to Laravel in development; production serves web and API from one origin. Sanctum stateful domains include the local browser hosts; session cookie is host-only, `Path=/`, `HttpOnly`, `SameSite=Lax`, `Secure` only under HTTPS. Bootstrap uses `/sanctum/csrf-cookie`; register/login regenerate the session; logout invalidates the session and regenerates the CSRF token. Database sessions remain the MVP default; rate-limit/cache storage may use Redis. | User-delegated decision, 2026-09-22 | 1.1, 1.2, all protected stories |
| E1-DEC-002 | `approved` | `Product owner (user-delegated)` | Account identity policy | Freeze public User ID serialization, name/email normalization, password policy, email verification deferral, duplicate privacy acceptance, register/login limiters, and authenticated registration behavior | Existing bigint User IDs serialize as decimal strings. Name is Unicode-trimmed, preserves internal whitespace, and is limited to 120 characters. Email is trimmed and lowercased for the canonical lookup/storage key. Registration requires name, email, password, and password_confirmation; password is 12–72 characters with no forced composition rule. Email verification is deferred. Duplicate email uses `422 VALIDATION_FAILED` with a generic email field error and no User data. Registration is guest-only; an authenticated caller receives `409 AUTHENTICATED_REGISTRATION_FORBIDDEN` without mutation. Registration limits use Redis with separate hashed IP and canonical-email keys: 5 attempts/10 minutes per IP and 3 attempts/10 minutes per email; rejected and successful attempts count, and `Retry-After` is returned. | User-delegated decision, 2026-09-22 | 1.1, 1.2 |
| E1-DEC-003 | `approved` | `Product owner (user-delegated)` | CV Profile public schema | Freeze every personal/section field, required/optional state, Profile count/title uniqueness policy, nested field path, list/detail/write endpoints, response shape, and relational/JSON boundary | Adopt [`profile-v1`](../../../../docs/contracts/cv/profile-v1.md): up to 10 owner-scoped Profiles; a title and `personal_information.full_name` are required on create; sections are structured, optional as documented, and nested items have server ULIDs. The resource uses `data`, ULID strings, UTC timestamps, and `revision`; its complete HTTP contract is the stable source. Persist ownership, title, revision, schema version, and timestamps as explicit columns; persist the validated structured document as JSONB. | User-delegated decision, 2026-10-06 | 1.3–1.8 |
| E1-DEC-004 | `approved` | `Product owner (user-delegated)` | CV field semantics and limits | Freeze Unicode/trim policy, character and byte limits, URL/date semantics, collection sizes, ordering, duplicate-item rules, and safe rendering constraints | Adopt the canonicalization, field limits, fixed language/employment vocabularies, date and URL rules, collection caps, and duplicate rules in [`profile-v1`](../../../../docs/contracts/cv/profile-v1.md). Inputs use Unicode NFC, outer-whitespace trimming, and CRLF-to-LF normalization for multiline text; no unsupported coercion occurs. | User-delegated decision, 2026-10-06 | 1.3–1.8 |
| E1-DEC-005 | `approved` | `Product owner (user-delegated)` | Profile write and concurrency model | Prefer aggregate-safe section writes with an explicit `updated_at` or revision precondition; freeze omission/removal semantics, stale conflict, autosave/manual save, and ambiguous-success reconciliation | Adopt section-scoped replacement writes with required `If-Match: "<revision>"`; a successful write atomically replaces only its named section and increments `revision`. An omitted field inside a section has the contract-specific meaning only; deletion of a repeatable item occurs by submitting the replacement section without that item's server ID. Stale writes return `409 PROFILE_UPDATE_CONFLICT`; the client preserves edits, fetches the latest Profile, and asks the User to reconcile—there is no automatic retry. Existing Profile writes are manual-save only. | User-delegated decision, 2026-10-06 | 1.3–1.7 |
| E1-DEC-006 | `approved` | `Product owner (user-delegated)` | CV Version snapshot contract | Freeze Version name policy, snapshot schema/version and backward-compatible reader policy, source fields, transaction boundary, ordering/pagination, whether duplicate names are allowed, and the rule that old snapshots are never rewritten during schema evolution | Adopt [`version-v1`](../../../../docs/contracts/cv/version-v1.md): a Version has a 1–120 character name (duplicate names allowed), `source_profile_id`, `source_profile_revision`, and one complete `snapshot` document under immutable schema version `1.0`. Creation locks the owned Profile, verifies `If-Match`, and inserts one Version or none. Lists are owner-scoped and ordered `created_at DESC, id DESC`, page-number paginated. Readers support schema `1.0`; later readers are additive and historic snapshots are never rewritten. | User-delegated decision, 2026-10-06 | 1.8 and downstream Epics 2–4 |
| E1-DEC-007 | `approved` | `Product owner (user-delegated)` | Web navigation and protected-state behavior | Freeze first authenticated route, guest/auth redirects, safe return destination, expiry UX, cache clearing, unsaved-change handling, and retry classifications | Successful registration navigates to `/dashboard`. Protected routes redirect guests to `/login?return_to=<internal-path>`; external return URLs are rejected. A `401`/`419` clears protected Vue Query data and auth state, then redirects to login while retaining only the safe internal return path. A lost registration response first reconciles with `GET /api/v1/auth/me`; `200` is treated as success, `401` permits one explicit retry, and no automatic retry or duplicate submission occurs. Registration form input is preserved except credentials, which are cleared after submission failure. | User-delegated decision, 2026-09-22 | all web tasks |
| E1-DEC-008 | `approved` | `Product owner (user-delegated)` | Frontend verification enablement | Assign a separate shared enablement item for Vitest/Playwright, disposable database orchestration, commands, CI ownership, and contract fixtures | Adopt the `E1-COORD-TEST-001` delivery contract below: one named disposable Compose project, isolated PostgreSQL 16 volume/database, Vite-to-API proxy configuration, contract fixtures, and four CI commands. The harness is a shared prerequisite and must land before dependent browser evidence is accepted. | User-delegated decision, 2026-10-06 | component/E2E tasks |
| E1-DEC-009 | `approved` | `Product owner (user-delegated)` | Account endpoint and failure contract | Freeze auth routes, request fields, success statuses, error codes/messages/details, logout idempotency, current-account cache headers, malformed transport behavior, and ambiguous-success reconciliation | Registration is `POST /api/v1/auth/register` with only `name`, `email`, `password`, and `password_confirmation`; success is `201` with `{data:{user}}`. Current account is `GET /api/v1/auth/me`; success is `200` with the same `{data:{user}}`, `Cache-Control: private, no-store`; unauthenticated is `401 UNAUTHENTICATED`. All `/api/v1` failures use the common JSON envelope: malformed body `400 INVALID_REQUEST_BODY`, validation/duplicate `422 VALIDATION_FAILED` with field-keyed `details`, expired CSRF/session `419 SESSION_EXPIRED`, throttled `429 THROTTLED` plus `Retry-After`, authenticated registration `409 AUTHENTICATED_REGISTRATION_FORBIDDEN`, unexpected failure `500 INTERNAL_ERROR`. Public User contains only approved id/name/email; no password/hash/token/cookie/CSRF/security metadata. No bearer token or browser credential storage is allowed. | User-delegated decision, 2026-09-22 | 1.1, 1.2 |

## Cross-story coordination records

### E1-COORD-AUTH-001 — User, session, and account contract

- Stories: `1-1-register-an-account`, `1-2-sign-in-and-sign-out` and every
  later protected story.
- Decision owner: `Product owner (user-delegated)` for the contract; `Codex`
  is the proposed integration owner for the planning/fixture slice.
- Resolution: The shared registration/current-account contract is approved by
  `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009`. The stable
  contract document will be `docs/contracts/auth/registration.md`; the shared
  synthetic executable corpus will be
  `docs/contracts/auth/fixtures/registration-v1.json`. JSON is used so PHP and
  TypeScript can consume the same fixture without a generator or runtime
  dependency. Fixture rows use stable IDs, reference AC/Global/Epic/Decision
  IDs, and contain request, precondition, response, error, headers, client
  action, and redaction assertions. The fixture corpus is read-only for
  `TASK-1-1-02` and `TASK-1-1-03`; those tasks cannot redefine the contract.
- Reserved boundary: the paths above, auth middleware/configuration, shared web
  API client, session state, Public User fixture, and current-account endpoint.
- Sequence/merge rule: `TASK-1-1-01` creates and validates the document and
  fixture corpus first; one integration owner then lands the shared auth
  boundary. Story 1.2 consumes the same files for login/logout and may extend
  the corpus only through an explicit contract revision.

### E1-COORD-PROFILE-001 — CV Profile aggregate and editor contract

- Stories: `1-3-create-a-cv-profile` through
  `1-7-manage-supplementary-cv-sections`, plus Story 1.8 as read-only consumer.
- Decision owner: `Product owner (user-delegated)` for the contract; one
  implementation integration owner must be named before shared files change.
- Resolution: `E1-DEC-003`, `E1-DEC-004`, and `E1-DEC-005` are approved.
  [`docs/contracts/cv/profile-v1.md`](../../../../docs/contracts/cv/profile-v1.md)
  is the only stable Profile schema and HTTP source; its companion fixture
  corpus will be created by `TASK-1-3-01` and extended, never forked, by the
  section fixture tasks.
- Reserved boundary: migrations/models, aggregate service, policy, Profile API
  resource and Form Requests, frontend feature API/schema/editor state, and
  canonical contract fixtures.
- Sequence/merge rule: freeze fixtures first; assign non-overlapping section
  modules to parallel stories; one integration owner serializes changes to the
  aggregate root, shared API resource, and editor composition.

### E1-COORD-VERSION-001 — Immutable snapshot boundary

- Stories: `1-8-create-and-view-an-immutable-cv-version` and downstream Stories
  2.4, 3.1, 4.1, and 4.2.
- Decision owner: `Product owner (user-delegated)` for the contract; one
  implementation integration owner must be named before shared files change.
- Resolution: `E1-DEC-006` is approved.
  [`docs/contracts/cv/version-v1.md`](../../../../docs/contracts/cv/version-v1.md)
  is the stable immutable-snapshot source. `TASK-1-8-01` owns its executable
  fixture corpus; downstream stories consume it without reinterpretation.
- Reserved boundary: Version migration/model, snapshot service/schema,
  serialization, list ordering, and immutability fixtures.
- Sequence/merge rule: after the Profile schema contract is frozen, Story 1.8
  tasks 01–02 may establish fixtures and persistence alongside Stories 1.4–1.7.
  Their Version-regression checks wait for that checkpoint; Story 1.8 owns
  Version creation/read and downstream stories cannot reinterpret snapshots.

### E1-COORD-TEST-001 — Shared frontend and E2E tooling

- Stories: all Epic 1 stories requiring component or browser verification.
- Decision owner: `Product owner (user-delegated)` for the delivery contract;
  an implementation owner must be named before the shared harness begins.
- Resolution: `E1-DEC-008` is approved. The harness must use a dedicated
  `careerfitcv-e1-e2e` Compose project with no fixed `container_name`, a
  separately named PostgreSQL 16 volume/database, and a localhost API port
  distinct from development. Vite runs on a test port and proxies `/api` and
  `/sanctum` to that API port. The only permitted destructive reset targets the
  named test project and its named volume; it must never call
  `migrate:fresh` against the long-lived development Compose database.
  The implementation adds one repository-root entry point:
  `./scripts/e1-verify.sh {api-fast|api-pg|web|e2e|all}`. It validates or
  generates a lowercase alphanumeric `E1_RUN_ID`, uses only Compose project
  `careerfitcv-e1-e2e-$E1_RUN_ID`, and assigns free localhost API/web ports to
  `E1_API_PORT` and `E1_WEB_PORT`. For `api-pg`, `e2e`, and `all`, it starts
  `apps/api/docker-compose.e2e.yml`, waits for `GET /api/health`, applies only
  that project's migrations and fixtures, passes both port variables to PHPUnit
  or Playwright, and installs a cleanup trap that runs `down -v --remove-orphans`
  for that same project only. `api-fast` uses `phpunit.fast.xml`; `api-pg` uses
  `phpunit.e2e.xml`; `web` runs type-check, non-mutating `lint:check`, and
  Vitest; `e2e` runs Playwright journeys that create unique accounts through the
  public UI. CI publishes `apps/web/test-results` and
  `apps/web/playwright-report` on failure.
- Reserved boundary: shared Vitest/Playwright dependencies, configuration,
  reusable auth/data fixtures, CI command, and disposable database reset.
- Sequence/merge rule: the enablement item lands before dependent verification
  tasks enter `doing`; no story installs a competing harness.

## Discovered work outside Epic story scope

### DISCOVERY-E1-001 — Shared frontend verification enablement

- Status: `planned; implementation owner required`.
- Owner: `unassigned`.
- Scope: implement the approved `E1-COORD-TEST-001` harness: project-local
  disposable PostgreSQL 16 orchestration, an API test configuration that
  identifies the disposable target (`phpunit.fast.xml` and `phpunit.e2e.xml`),
  Vitest setup/fixtures, a non-mutating `lint:check`, Playwright global setup
  and journeys, Vite test proxy configuration, and CI entries.
- Reason externalized: the capability is reusable across all Epics and is not
  independently valuable registration/Profile/Version behavior.
- Blocks: every task referencing `E1-COORD-TEST-001`.
- Acceptance: one implementation owner, branch/worktree, and file boundary
  are recorded before work enters `doing`; the implementation proves that its
  reset can touch only `careerfitcv-e1-e2e` resources, then runs the four
  required command classes in a clean environment.

### DISCOVERY-E1-002 — Backend database verification target alignment

- Status: `resolved`.
- Owner: `Product owner (user-delegated)`.
- Scope: PostgreSQL 16 is the canonical Epic 1 integration and E2E datastore,
  matching `docs/standards/data.md`, `docs/standards/testing.md`, the API
  Docker Compose service, and the repository's existing development direction.
  SQLite remains permitted only for isolated fast tests when the test does not
  claim PostgreSQL constraint or integration evidence. PHPUnit configuration,
  commands, and fixtures must label the database they actually exercise.
- Evidence: User-directed database decision, 2026-09-22; repository Docker and
  environment configuration inspected; global data/testing standards aligned.
- Blocks: none after the implementation test entry points are updated to use a
  declared disposable PostgreSQL 16 database for integration evidence.
