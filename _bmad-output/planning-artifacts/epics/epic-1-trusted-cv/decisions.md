# Epic 1 Decisions and Coordination

Recommendations are review proposals, not approved requirements. Before a
dependent story is approved as ready for development, every applicable decision needs one owner, an
approved resolution, and evidence in the form `approver, YYYY-MM-DD`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E1-DEC-001 | `approved` | `Product owner (user-delegated)` | Sanctum origin/session topology | Same-origin production; Vite proxy or explicit credentialed dev origin; freeze CORS, stateful domains, cookie attributes, CSRF bootstrap/recovery, regeneration and invalidation | Browser uses same-origin `/api/v1`; Vite proxies `/api/v1` and `/sanctum` to Laravel in development; production serves web and API from one origin. Sanctum stateful domains include the local browser hosts; session cookie is host-only, `Path=/`, `HttpOnly`, `SameSite=Lax`, `Secure` only under HTTPS. Bootstrap uses `/sanctum/csrf-cookie`; register/login regenerate the session; logout invalidates the session and regenerates the CSRF token. Database sessions remain the MVP default; rate-limit/cache storage may use Redis. | User-delegated decision, 2026-09-22 | 1.1, 1.2, all protected stories |
| E1-DEC-002 | `approved` | `Product owner (user-delegated)` | Account identity policy | Freeze public User ID serialization, name/email normalization, password policy, email verification deferral, duplicate privacy acceptance, register/login limiters, and authenticated registration behavior | Existing bigint User IDs serialize as decimal strings. Name is Unicode-trimmed, preserves internal whitespace, and is limited to 120 characters. Email is trimmed and lowercased for the canonical lookup/storage key. Registration requires name, email, password, and password_confirmation; password is 12–72 characters with no forced composition rule. Email verification is deferred. Duplicate email uses `422 VALIDATION_FAILED` with a generic email field error and no User data. Registration is guest-only; an authenticated caller receives `409 AUTHENTICATED_REGISTRATION_FORBIDDEN` without mutation. Registration limits use Redis with separate hashed IP and canonical-email keys: 5 attempts/10 minutes per IP and 3 attempts/10 minutes per email; rejected and successful attempts count, and `Retry-After` is returned. | User-delegated decision, 2026-09-22 | 1.1, 1.2 |
| E1-DEC-003 | `open` | `unassigned` | CV Profile public schema | Freeze every personal/section field, required/optional state, Profile count/title uniqueness policy, nested field path, list/detail/write endpoints, response shape, and relational/JSON boundary | `pending` | `pending` | 1.3–1.8 |
| E1-DEC-004 | `open` | `unassigned` | CV field semantics and limits | Freeze Unicode/trim policy, character and byte limits, URL/date semantics, collection sizes, ordering, duplicate-item rules, and safe rendering constraints | `pending` | `pending` | 1.3–1.8 |
| E1-DEC-005 | `open` | `unassigned` | Profile write and concurrency model | Prefer aggregate-safe section writes with an explicit `updated_at` or revision precondition; freeze omission/removal semantics, stale conflict, autosave/manual save, and ambiguous-success reconciliation | `pending` | `pending` | 1.3–1.7 |
| E1-DEC-006 | `open` | `unassigned` | CV Version snapshot contract | Freeze Version name policy, snapshot schema/version and backward-compatible reader policy, source fields, transaction boundary, ordering/pagination, whether duplicate names are allowed, and the rule that old snapshots are never rewritten during schema evolution | `pending` | `pending` | 1.8 and downstream Epics 2–4 |
| E1-DEC-007 | `approved` | `Product owner (user-delegated)` | Web navigation and protected-state behavior | Freeze first authenticated route, guest/auth redirects, safe return destination, expiry UX, cache clearing, unsaved-change handling, and retry classifications | Successful registration navigates to `/dashboard`. Protected routes redirect guests to `/login?return_to=<internal-path>`; external return URLs are rejected. A `401`/`419` clears protected Vue Query data and auth state, then redirects to login while retaining only the safe internal return path. A lost registration response first reconciles with `GET /api/v1/auth/me`; `200` is treated as success, `401` permits one explicit retry, and no automatic retry or duplicate submission occurs. Registration form input is preserved except credentials, which are cleared after submission failure. | User-delegated decision, 2026-09-22 | all web tasks |
| E1-DEC-008 | `open` | `unassigned` | Frontend verification enablement | Assign a separate shared enablement item for Vitest/Playwright, disposable database orchestration, commands, CI ownership, and contract fixtures | `pending` | `pending` | component/E2E tasks |
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
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-003, E1-DEC-004, E1-DEC-005`.
- Reserved boundary: migrations/models, aggregate service, policy, Profile API
  resource and Form Requests, frontend feature API/schema/editor state, and
  canonical contract fixtures.
- Sequence/merge rule: freeze fixtures first; assign non-overlapping section
  modules to parallel stories; one integration owner serializes changes to the
  aggregate root, shared API resource, and editor composition.

### E1-COORD-VERSION-001 — Immutable snapshot boundary

- Stories: `1-8-create-and-view-an-immutable-cv-version` and downstream Stories
  2.4, 3.1, 4.1, and 4.2.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-006`.
- Reserved boundary: Version migration/model, snapshot service/schema,
  serialization, list ordering, and immutability fixtures.
- Sequence/merge rule: after the Profile schema contract is frozen, Story 1.8
  tasks 01–02 may establish fixtures and persistence alongside Stories 1.4–1.7.
  Their Version-regression checks wait for that checkpoint; Story 1.8 owns
  Version creation/read and downstream stories cannot reinterpret snapshots.

### E1-COORD-TEST-001 — Shared frontend and E2E tooling

- Stories: all Epic 1 stories requiring component or browser verification.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-008`.
- Reserved boundary: shared Vitest/Playwright dependencies, configuration,
  reusable auth/data fixtures, CI command, and disposable database reset.
- Sequence/merge rule: the enablement item lands before dependent verification
  tasks enter `doing`; no story installs a competing harness.

## Discovered work outside Epic story scope

### DISCOVERY-E1-001 — Shared frontend verification enablement

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: add and configure the project-level Vitest/Playwright harness,
  reusable fixtures, disposable PostgreSQL 16 orchestration, commands, and CI entry.
- Reason externalized: the capability is reusable across all Epics and is not
  independently valuable registration/Profile/Version behavior.
- Blocks: every task referencing `E1-COORD-TEST-001`.
- Acceptance: the owning planning item defines one implementation owner,
  branch/worktree, file boundary, safe database reset contract, and commands
  before any dependent task enters `doing`.

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
