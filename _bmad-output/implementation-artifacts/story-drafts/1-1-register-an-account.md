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
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/prd.md
  - _bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md
  - docs/api.md
  - docs/database.md
  - docs/frontend-architecture.md
---

# Story 1.1: Register an account

**Readiness:** Draft; blocked from `ready-for-dev` by the decisions listed
below. No implementation work is authorized by this artifact.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A prospective User cannot yet create a private CareerFitCV account
or establish the authentication state required by later User-owned features.

**Approach:** Provide one accessible registration flow backed by a versioned
Laravel API contract. Validate and normalize input at the approved boundary,
create exactly one User within the application transaction boundary, hash the
password, establish the approved authentication state, and return only
non-sensitive account fields.

## Boundaries & Constraints

**Always:** Preserve the five canonical acceptance scenarios; keep Laravel as
the sole owner of authentication, validation, persistence, and trusted state;
keep product routes under `/api/v1`; enforce normalized email uniqueness at the
application and database boundaries; hash passwords through the configured
Laravel hasher; exclude password and password-hash fields from every response
and log; reject invalid input without a partial User; expose field errors in an
accessible form; and make double submission safe.

**Ask First:** Resolve DECISION-1-1-01 through DECISION-1-1-09 before approval.
Any change to the canonical Given/When/Then intent, the authentication model,
public request/response fields, privacy behavior, or verification tooling
requires human approval.

**Never:** Implement this draft; add account recovery, social login, roles,
multi-factor authentication, AI, worker behavior, or CV Profile creation; store
or return a plaintext password; trust a client-supplied User ID; reveal an
existing User record; silently weaken validation; or modify Story 1.2 behavior
without a coordination decision.

## Canonical Acceptance Criteria

- **AC-1-1-register-an-account-01:** Given I am not authenticated, when I submit
  a valid name, unique email, and password, then the system creates my User
  account, authenticates me as that User, and never returns my password.
- **AC-1-1-register-an-account-02:** Given the submitted email already belongs
  to a User, when I submit the form, then no account is created, I receive a
  field-level validation error, and existing User data is not disclosed.
- **AC-1-1-register-an-account-03:** Given required fields are missing or
  invalid, when I submit the form, then the request is rejected, no partial
  User is created, and errors identify every affected field.
- **AC-1-1-register-an-account-04:** Given the password does not meet the
  documented policy, when I submit the form, then the request is rejected and
  the policy is communicated without exposing sensitive details.
- **AC-1-1-register-an-account-05:** Given registration succeeds, when I request
  my current account, then the response identifies my User and excludes the
  password and password hash.

## Assumptions and unresolved decisions

Recommendations below are proposals for review, not approved requirements.
Before publication, every row must have an assigned owner, an approved
resolution, and approval evidence in the form `approver, YYYY-MM-DD`.

| ID | Status | Owner | Decision required | Recommended starting point | Approved resolution | Approval evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DECISION-1-1-01 | `open` | `unassigned` | Browser authentication mechanism and origin topology | First-party, HTTP-only cookie session because the repository already has database sessions and the web client sends credentials. Confirm same-origin/cross-origin topology in development and deployment, Laravel middleware/package, CSRF bootstrap/recovery, trusted origins, cookie domain/SameSite/Secure settings, CORS, session regeneration, and any Vite proxy/environment ownership. | `pending` | `pending` | API, session, CSRF, FE state, E2E |
| DECISION-1-1-02 | `open` | `unassigned` | Public endpoint and response envelope | `POST /api/v1/auth/register` returning `201` and `data.user`; `GET /api/v1/auth/me` returning `200` and the same non-sensitive User shape. Freeze validation, authentication, malformed JSON, unsupported media type, oversized request, throttle, generic server-error statuses/codes/messages, field keys, and relevant headers. | `pending` | `pending` | BE/FE contract |
| DECISION-1-1-03 | `open` | `unassigned` | Name policy | Trim surrounding whitespace; require a non-empty display name; confirm Unicode handling and the maximum character/byte length. | `pending` | `pending` | Validation and UI |
| DECISION-1-1-04 | `open` | `unassigned` | Email identity policy | For MVP, choose an explicit accepted email repertoire and one canonicalization algorithm covering Unicode/IDNA domains, Unicode normalization, local-part case, and stored representation. Apply it before validation/lookup/persistence and validate the canonical value against database character/byte limits. | `pending` | `pending` | Domain, persistence, duplicate behavior |
| DECISION-1-1-05 | `open` | `unassigned` | Password policy | Prefer a length-based policy without mandatory character classes; confirm minimum, supported maximum/byte limit, compromised-password checks, and confirmation-field behavior. | `pending` | `pending` | Validation, UI, tests |
| DECISION-1-1-06 | `open` | `unassigned` | Duplicate-email privacy behavior | Return a generic field-level error that discloses no User record details. Explicitly accept or change the unavoidable account-existence signal; if accepted, align content/timing and mitigate it through the approved rate limit. | `pending` | `pending` | Error contract and security |
| DECISION-1-1-07 | `open` | `unassigned` | Registration throttling | Freeze limiter keys, trusted-proxy handling, thresholds, windows, counted outcomes (including invalid email), backing-store/distributed semantics, `Retry-After`, and trusted test/local overrides. | `pending` | `pending` | API and E2E |
| DECISION-1-1-08 | `open` | `unassigned` | Verification and failure recovery | Confirm whether email verification is deferred; choose rollback, valid-but-unauthenticated recovery, or another explicit state when authentication setup fails; and define idempotency/reconciliation when the server commits but the browser loses the response. | `pending` | `pending` | Transaction semantics and success behavior |
| DECISION-1-1-09 | `open` | `unassigned` | Browser routing and verification tooling | Choose the first authenticated route, define UI/API behavior when an authenticated User opens registration, and approve registration-specific component/E2E commands after shared test enablement is assigned. | `pending` | `pending` | FE behavior and verification |

## Proposed endpoint contract matrix

This is a review aid, not an approved API. DECISION-1-1-01, 02, 06, 07, 08,
and 09 must replace every `pending` value before publication.

| Operation / scenario | Proposed status | Stable code | Body and headers |
| --- | --- | --- | --- |
| `POST /api/v1/auth/register` succeeds | `201` | `pending` | `data.user = { id, name, email }`; approved authentication state; no credentials |
| Registration fields fail validation | `422` | `validation_failed` | `errors` keyed only by approved form fields; stable safe message |
| Normalized email is unavailable | `422` proposed | `email_unavailable` proposed | Generic email field error; no existing User details; privacy decision pending |
| Registration request is throttled | `429` | `registration_rate_limited` proposed | Stable safe message and approved `Retry-After` behavior |
| JSON/media type/body size is invalid | `400` / `415` / `413` proposed | `invalid_request` variants pending | No framework details; no persistence/authentication mutation |
| Cookie-session CSRF/bootstrap fails | `419` or `403` pending | `authentication_state_invalid` proposed | Stable recoverable/terminal behavior; no retry loop |
| Authenticated User calls registration | `pending` | `pending` | No new User and no implicit identity replacement; redirect behavior is also pending |
| Registration/authentication setup fails | approved `5xx` | `registration_failed` proposed | Generic response without database/framework details; recovery model pending |
| `GET /api/v1/auth/me` succeeds | `200` | `pending` | Same `data.user`; `Cache-Control: private, no-store` or stricter approved header |
| Current account is unauthenticated | `401` proposed | `unauthenticated` proposed | Generic response; no cached/stale User data |

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
| --- | --- | --- | --- |
| Happy path | Unauthenticated; valid name, unique normalized email, conforming password | One User is persisted, authentication state is established, non-sensitive User data is returned, and the UI enters the approved authenticated route | No password or hash appears in body, state, logs, or browser-visible errors |
| Duplicate email | Existing canonical email, including case/whitespace variants | No User is created and no existing User data is returned | Field-level email error using the approved generic message |
| Concurrent duplicate | Two valid requests race for the same canonical email | Exactly one User is created | Losing request maps the database uniqueness conflict to the same safe duplicate response |
| Invalid request | Missing/invalid name, email, password, or confirmation | No persistence or authentication change; all affected fields are identified | Stable validation envelope; entered password is cleared and non-sensitive name/email values remain |
| Password-policy failure | Password violates the approved policy | No User is created | Field-level policy guidance without echoing the password or sensitive checks |
| Repeated UI submit | Submit is pressed repeatedly while pending | Only one registration request is in flight | Submit is disabled; retry becomes available only after a terminal response |
| Throttled request | Approved rate limit is exceeded | No User is created | `429` with stable code/message and safe retry guidance |
| Authentication-state failure | User persistence or session/token establishment cannot complete | Must follow DECISION-1-1-08 and must not report registration success | Explicit retry or recovery outcome; no silent partial state |
| Already authenticated | An authenticated User reaches or calls registration | Follow the approved redirect/API behavior without creating another User | Do not replace or merge the existing identity implicitly |
| Network/server failure | Browser receives no response or a retryable `5xx` | Form remains recoverable and does not claim success | Preserve non-sensitive fields; prevent accidental duplicate submission on retry |
| Ambiguous completion | Server commits registration but its response is lost | Reconcile through the approved idempotency/current-account flow before creating again | Do not present a duplicate-email failure as proof that registration failed |
| CSRF failure | Cookie-session request has a missing, expired, or mismatched CSRF token | No User is created; return the approved stable authentication/CSRF error | At most one approved bootstrap refresh/retry; avoid retry loops |
| Invalid transport | Malformed JSON, unsupported media type, or oversized body | Reject before validation, persistence, or authentication mutation | Stable `400`/`413`/`415` mapping or the explicitly approved equivalents; no framework details |
| Current-account caching | Authenticated current-account response passes through browser/proxy caches | Response is private and not reusable across sessions | Require and verify `Cache-Control: private, no-store` or a stricter approved policy |

</frozen-after-approval>

## Story readiness analysis

| Area | Defined in this draft | Open before approval | Decision IDs |
| --- | --- | --- | --- |
| Behavior | An unauthenticated User submits name, email, password, and confirmation. Success creates one account, establishes authentication, and resolves the same current-account representation. Validation/duplicate failures create nothing and retain only non-sensitive values. | Authenticated-caller behavior, destination, email verification, ambiguous completion, and post-persistence auth failure. | 01, 08, 09 |
| Contract | Proposed request fields are `name`, `email`, `password`, `password_confirmation`; proposed success is `201` with `data.user = { id, name, email }`; current-account uses that shape. Credentials/internal fields are absent. | Exact endpoints, envelopes, stable field/non-field codes, transport errors, auth/CSRF errors, and caching policy. | 01, 02, 06, 07, 08 |
| Backend | Presentation maps transport/errors; Application coordinates identity, transaction, persistence, and auth; Domain owns reusable invariants; Infrastructure adapts Eloquent and Laravel auth/hash. The `users.email` unique index is the final race guard; use `TransactionManager`. | Atomic boundary and recovery/reconciliation behavior. | 04, 05, 08 |
| Security | Password hashing is verified at persistence; responses/logs exclude credentials, tokens, database errors, and existing User records; current-account responses are private/no-store. | Auth mechanism, CSRF, cookies or token storage/revocation, session fixation/regeneration, origin/CORS, and rate limits. | 01, 06, 07 |
| Validation | Normalize before uniqueness; validate the canonical persisted representation; report all affected fields with stable keys; reject wrong types/encodings/sizes, malformed email, policy failures, and confirmation mismatch. | Exact name, email, and password boundaries. | 03, 04, 05 |
| Frontend | Accessible labeled form; explicit idle/editing/submitting/error/throttled/success states; one request at a time; clear passwords after failure; retain only name/email; announce and focus errors. | Navigation, authenticated-caller behavior, exact policy guidance, and test runner. | 03, 05, 09 |
| Integration | Shared `ofetch` client may send cookies only under the approved mechanism; backend fields/codes map directly to form state; registration and Story 1.2 reuse one immutable User/auth contract. | Contract freeze, auth bootstrap, ambiguous-response reconciliation, and coordination with Story 1.2. | 01, 02, 08, 09 |
| Verification readiness | Unit, feature/integration, component, and E2E coverage is assigned below, including concurrency, rollback/recovery, credential exclusion, accessibility, retry, and current account. | Exact component/E2E tools and commands. | 09 |

## Code Map

- `apps/api/app/Models/User.php` -- current Eloquent authentication model with
  hidden credential fields and hashed password cast.
- `apps/api/database/migrations/0001_01_01_000000_create_users_table.php` --
  existing User, unique email, password, token, and session persistence.
- `apps/api/app/Shared/Application/Contracts/TransactionManager.php` -- inward
  transaction contract for the registration use case.
- `apps/api/app/Shared/Infrastructure/Persistence/LaravelTransactionManager.php`
  -- Laravel transaction adapter.
- `apps/api/routes/api.php` -- current `/api` route entry point; product routes
  must retain the `/api/v1` boundary.
- `apps/api/bootstrap/app.php` and `apps/api/config/auth.php` -- middleware and
  approved authentication mechanism configuration.
- `apps/api/app/Domain/User/` -- planned framework-independent identity rules
  and repository contract; create only artifacts justified by the approved
  decisions.
- `apps/api/app/Application/User/` -- planned registration and current-account
  use cases and safe DTOs.
- `apps/api/app/Infrastructure/Persistence/Eloquent/` -- planned User repository
  adapter using the existing Eloquent model.
- `apps/api/app/Presentation/Http/` -- planned requests, controllers, resources,
  error mapping, and throttling boundary.
- `apps/web/src/shared/api/client.ts` -- current credentialed shared HTTP client.
- `apps/web/src/features/auth/` -- planned registration API, schema, state, form,
  and public feature surface.
- `apps/web/src/app/router/index.ts` -- registration and authenticated-route
  composition after DECISION-1-1-09.
- `apps/api/tests/` and planned frontend/E2E test locations -- verification
  boundaries; frontend tooling does not yet exist.

## Discovered work kept outside this story

### DISCOVERY-1-1-01: Shared frontend verification infrastructure

- Status: `requires-backlog-decision`
- Owner: `unassigned`
- Scope: Reusable Vue component-test and browser E2E dependencies,
  configuration, scripts, environment setup, and test utilities.
- Reason: The repository has no component or E2E runner. This capability will
  serve multiple stories and must not be hidden inside registration scope.
- Story relationship: TASK-1-1-11 and TASK-1-1-12 remain blocked until the
  product/sprint owner creates or assigns an approved enablement item that
  supplies the canonical commands and ownership boundary.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-1-01: Implement registration identity rules and application use case
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: `apps/api/app/Domain/User/`, `apps/api/app/Application/User/`
  - Coordination: `COORD-1-1-01`
  - Blocked by: `DECISION-1-1-03`, `DECISION-1-1-04`, `DECISION-1-1-05`, `DECISION-1-1-06`, `DECISION-1-1-08`
  - Outcome: Framework-independent registration input, non-sensitive result DTO, identity rules, and use-case transaction orchestration.
  - Acceptance: The use case produces one non-sensitive User result or one stable expected failure without importing Eloquent into Domain/Application.
  - Verification: Unit tests for normalization, policy boundaries, duplicate mapping, and transaction behavior.

- [ ] TASK-1-1-02: Implement User persistence and credential hashing adapter
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: `apps/api/app/Infrastructure/Persistence/Eloquent/`, User repository binding, existing `User` model only where required
  - Coordination: `COORD-1-1-01`
  - Blocked by: `DECISION-1-1-04`, `DECISION-1-1-05`, `DECISION-1-1-08`
  - Outcome: Race-safe normalized uniqueness, configured password hashing, non-sensitive persistence mapping, and transaction integration.
  - Acceptance: A concurrent duplicate creates exactly one User; plaintext credentials never persist; expected conflicts do not leak database details.
  - Verification: Repository integration tests with the database unique constraint and configured hasher.

- [ ] TASK-1-1-03: Expose the registration HTTP contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`, `TASK-1-1-02`, `TASK-1-1-16`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: `apps/api/routes/api.php` plus registration Request/Controller/Resource and application-error mapping
  - Coordination: `COORD-1-1-01`
  - Blocked by: `DECISION-1-1-02`, `DECISION-1-1-06`, `DECISION-1-1-08`
  - Outcome: Versioned registration endpoint that implements the approved transport and application response contract.
  - Acceptance: Every approved request/response branch is mapped without serializing credentials, database details, or internal exceptions.
  - Verification: Focused Laravel feature tests for success, validation, duplicate, malformed transport, ambiguous recovery, and log redaction.

- [ ] TASK-1-1-04: Expose the shared current-account query
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-03`, `TASK-1-1-13`
  - Covers: `AC-1-1-register-an-account-05`
  - Scope: Current-account Application query plus versioned Request/Controller/Resource/route
  - Coordination: `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01`, `DECISION-1-1-02`
  - Outcome: One reusable authenticated User representation for registration and Story 1.2.
  - Acceptance: Authenticated requests return only the approved User fields; unauthenticated requests return the approved generic error.
  - Verification: Feature tests for authenticated, unauthenticated, password/hash exclusion, and private/no-store cache headers.

- [ ] TASK-1-1-05: Complete backend unit verification
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: `apps/api/tests/Unit/` registration domain/application tests
  - Coordination: `none`
  - Blocked by: Approved validation and failure decisions
  - Outcome: Fast tests pin domain rules and application orchestration independently of HTTP and Eloquent.
  - Acceptance: Every approved boundary and expected application failure has a deterministic unit assertion.
  - Verification: Run the focused PHPUnit unit suite successfully.

- [ ] TASK-1-1-06: Complete backend feature and integration verification
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-02`, `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-13`, `TASK-1-1-15`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: `apps/api/tests/Feature/` and persistence/auth integration tests
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`
  - Blocked by: `none`
  - Outcome: Executable evidence for the full backend contract, including concurrency, rollback/recovery, credential exclusion, and log redaction.
  - Acceptance: All canonical backend scenarios pass against real framework boundaries and isolated test persistence.
  - Verification: Run the focused feature/integration suite and complete API suite; capture logs on validation, duplicate, authentication, throttle, and unexpected-exception paths and assert that credentials/tokens/database details are absent.

- [ ] TASK-1-1-07: Implement the frontend registration contract adapter
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-16`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: `apps/web/src/features/auth/` API, schema, types, error mapping, and public exports; remain compatible with the transport boundary reserved to TASK-1-1-14
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01`, `DECISION-1-1-02`, `DECISION-1-1-03`, `DECISION-1-1-04`, `DECISION-1-1-05`, `DECISION-1-1-06`, `DECISION-1-1-07`
  - Outcome: Typed registration/current-account calls and stable mapping from API failures to form states.
  - Acceptance: The adapter sends only approved fields and maps each approved response without leaking transport details into components.
  - Verification: Type-check plus adapter tests using representative success and error fixtures.

- [ ] TASK-1-1-08: Implement authenticated client state and registration mutation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-07`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-05`
  - Scope: Auth feature mutation/query and minimal Pinia session state; do not copy ordinary server query data unnecessarily
  - Coordination: `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01`, `DECISION-1-1-08`, `DECISION-1-1-09`
  - Outcome: Registration success establishes client auth state from the approved current-account representation.
  - Acceptance: Success enters the authentication state once; failure never does; credentials are not persisted in client state.
  - Verification: State/mutation tests for success, invalid response, retry, and stale request completion.

- [ ] TASK-1-1-09: Build the accessible registration page and form
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-07`, `TASK-1-1-08`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: Auth registration components/page and router composition under `apps/web/src/`
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-03`, `DECISION-1-1-05`, `DECISION-1-1-09`
  - Outcome: Keyboard-usable registration UI with pending, success, field-error, throttle, server, and network states.
  - Acceptance: The form prevents double submission, associates and announces errors, clears passwords on failure, and preserves only non-sensitive name/email values.
  - Verification: Manual keyboard/screen-reader-oriented checks until component automation is available.

- [ ] TASK-1-1-11: Automate registration component behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-09`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`
  - Scope: Registration schema, form, accessibility, and client-state component tests
  - Coordination: `COORD-1-1-01`
  - Blocked by: `DECISION-1-1-09`, `DISCOVERY-1-1-01`
  - Outcome: Automated evidence for form validation, API error presentation, duplicate-submit prevention, preservation of non-sensitive values, and password clearing.
  - Acceptance: Tests fail for each known-bad UI behavior and pass for the approved implementation.
  - Verification: Run the focused component suite, web type-check, lint, and build.

- [ ] TASK-1-1-12: Verify registration end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-05`, `TASK-1-1-06`, `TASK-1-1-11`, `TASK-1-1-13`, `TASK-1-1-14`, `TASK-1-1-15`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: Registration-specific scenarios across browser, API, auth state, and database; do not own the reusable E2E harness/configuration
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`, `COORD-1-1-03`
  - Blocked by: `DECISION-1-1-09`, `DISCOVERY-1-1-01`
  - Outcome: Repeatable evidence that a User can register and retrieve the same current account, while meaningful failure paths preserve data integrity.
  - Acceptance: Happy path, duplicate email, invalid input, password policy, throttle, and unauthenticated current-account scenarios pass without test-order dependence.
  - Verification: Run the approved E2E command against isolated application/test data.

- [ ] TASK-1-1-13: Configure the Laravel browser authentication boundary
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-03`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-05`
  - Scope: `apps/api/bootstrap/app.php`, `apps/api/config/`, auth/session/CSRF middleware and trusted-origin configuration
  - Coordination: `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01`, `DECISION-1-1-08`
  - Outcome: Registration securely establishes the approved browser authentication state and current-account requests resolve it.
  - Acceptance: CSRF bootstrap/failure, cookie/session settings, regeneration, origin policy, and authentication failure recovery match the approved contract.
  - Verification: Focused feature tests for session creation/regeneration, current account, CSRF failure/recovery, and sensitive-log exclusion.

- [ ] TASK-1-1-14: Configure the web-to-API authentication transport
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-16`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-05`
  - Scope: `apps/web/vite.config.*`, approved web environment configuration, shared API client, and auth bootstrap/recovery adapter
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01`, `DECISION-1-1-02`
  - Outcome: Browser requests reach Laravel in development and deployment using the approved origin, credential, and CSRF exchange.
  - Acceptance: No hard-coded environment-specific origin or insecure credential fallback is required; a terminal CSRF failure cannot create a retry loop.
  - Verification: Type-check and transport tests for origin resolution, credential/CSRF behavior, one approved recovery attempt, and terminal failure.

- [ ] TASK-1-1-15: Enforce registration abuse protection
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-03`
  - Covers: `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`
  - Scope: Registration limiter configuration/middleware and stable throttle response mapping
  - Coordination: `COORD-1-1-01`
  - Blocked by: `DECISION-1-1-06`, `DECISION-1-1-07`
  - Outcome: Approved limits apply consistently to valid, duplicate, and invalid attempts without trusting spoofed client IP data.
  - Acceptance: Keys, counted outcomes, windows, storage, proxy trust, `Retry-After`, and environment overrides match the approved decision.
  - Verification: Feature tests for each key/outcome, expiry, proxy handling, distributed-store behavior where applicable, stable `429`, and log redaction.

- [ ] TASK-1-1-16: Publish the approved shared auth contract fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: `docs/api.md` and one canonical registration/current-account example fixture under `docs/contracts/`
  - Coordination: `COORD-1-1-01`, `COORD-1-1-02`
  - Blocked by: `DECISION-1-1-01` through `DECISION-1-1-09`
  - Outcome: Backend, frontend, and verification tasks consume one versioned set of approved request/response/error examples.
  - Acceptance: Fixtures cover every approved endpoint-matrix row, form field key, approved non-sensitive User field, auth/CSRF header or cookie behavior, and retry classification.
  - Verification: Validate fixture syntax and review it against the approved decision table and canonical acceptance criteria.

**Acceptance Criteria:** The five canonical criteria above remain the system
acceptance contract. Task completion is necessary but does not replace them.

## Dependency and concurrency map

```text
TASK-1-1-01 -> TASK-1-1-02
TASK-1-1-16 -> { TASK-1-1-03, TASK-1-1-07, TASK-1-1-14 }
{ TASK-1-1-01, TASK-1-1-02, TASK-1-1-16 } -> TASK-1-1-03
TASK-1-1-03 -> { TASK-1-1-13, TASK-1-1-15 }
{ TASK-1-1-03, TASK-1-1-13 } -> TASK-1-1-04
TASK-1-1-01 -> TASK-1-1-05
{ TASK-1-1-02, TASK-1-1-03, TASK-1-1-04, TASK-1-1-13, TASK-1-1-15 } -> TASK-1-1-06
TASK-1-1-07 -> TASK-1-1-08 -> TASK-1-1-09 -> TASK-1-1-11
{ TASK-1-1-05, TASK-1-1-06, TASK-1-1-11, TASK-1-1-13, TASK-1-1-14, TASK-1-1-15 } -> TASK-1-1-12
```

- A dependency is satisfied only when the predecessor meets its acceptance and
  verification clauses, not when its implementation files merely exist.
- After decisions and TASK-1-1-16 fixtures are frozen, backend TASK-1-1-01 and
  frontend TASK-1-1-07/TASK-1-1-14 may proceed on reserved scopes.
- TASK-1-1-03 and TASK-1-1-07 implement in parallel against TASK-1-1-16 under
  COORD-1-1-01.
- TASK-1-1-04 and TASK-1-1-08 share authenticated User state under
  COORD-1-1-02.
- TASK-1-1-05 and TASK-1-1-06 may run in parallel once their own implementation
  dependencies are satisfied.
- TASK-1-1-12 is the integration gate and starts only after backend and frontend
  verification tasks are done.

## Coordination records

### COORD-1-1-01: Registration request, response, and error contract

- Tasks: `TASK-1-1-01`, `TASK-1-1-02`, `TASK-1-1-03`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-09`, `TASK-1-1-11`, `TASK-1-1-12`, `TASK-1-1-14`, `TASK-1-1-15`, `TASK-1-1-16`
- Status: `open`
- Decision owner: `unassigned`
- Resolution: `pending`
- Pending action: Freeze normalized fields, status codes, non-sensitive User shape, stable error codes, duplicate/throttle semantics, and the canonical TASK-1-1-16 fixture after DECISION-1-1-02 through DECISION-1-1-07 are approved.
- Reserved artifact: `docs/contracts/` fixture path remains reserved to TASK-1-1-16; exact filename is frozen at approval.
- Sequence/merge rule: Contract approval and TASK-1-1-16 precede dependent `doing`; backend and frontend consume the same fixtures; contract changes require coordination before merge.

### COORD-1-1-02: Authenticated state and current-account ownership

- Tasks: `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-12`, `TASK-1-1-13`, `TASK-1-1-14`, `TASK-1-1-16`; future Story 1.2 tasks
- Status: `open`
- Decision owner: `unassigned`
- Resolution: `pending`
- Pending action: Confirm that Story 1.1 owns the minimal registration-to-current-account contract required by AC-05 and Story 1.2 reuses it while adding sign-in/sign-out behavior.
- Sequence/merge rule: Freeze this checkpoint before either story enters implementation. The Story `1-2-sign-in-and-sign-out` draft must reference `COORD-1-1-02` and may not redefine the non-sensitive User shape or auth mechanism independently.

### COORD-1-1-03: Frontend verification tooling

- Tasks: `TASK-1-1-11`, `TASK-1-1-12`
- Status: `open`
- Decision owner: `unassigned`
- Resolution: `pending`
- Pending action: Assign DISCOVERY-1-1-01 to a separate enablement item and select the smallest component/E2E tooling compatible with Vue/Vite and the team's execution environment.
- Sequence/merge rule: Treat shared test configuration as an external reserved boundary; its owner lands tooling before Story 1.1 verification tasks enter `doing`.

## Planning and implementation verification

**Planning checks:**

- Run `bmad-review` with adversarial, edge-case, structure, and prose lenses.
- Validate every task dependency references an existing task and the graph is
  acyclic.
- Confirm every canonical acceptance criterion is covered by backend,
  frontend, integration, and verification tasks where applicable.
- Confirm all tasks remain `todo`, owners/branches remain `unassigned`, the
  draft stays nested, and `sprint-status.yaml` remains `backlog`.
- Confirm every decision and coordination record has an owner, approved
  resolution, and dated approval evidence before publication.

**Implementation commands after approval:**

- `cd apps/api && php artisan test` -- expected: registration unit, feature,
  integration, and existing API tests pass.
- `cd apps/api && vendor/bin/pint --test` -- expected: backend formatting passes.
- `cd apps/web && npm run type-check && npm run lint && npm run build` --
  expected: frontend static checks and production build pass.
- Frontend component and E2E commands remain blocked by DECISION-1-1-09 and
  must be recorded here before the story can become `ready-for-dev`.

## Spec Change Log

- 2026-09-08: Initial planning draft created from the canonical Story 1.1,
  repository baseline, PRD, architecture spine, and sprint workflow. No product
  decision has been approved by this entry.
- 2026-09-08: BMAD review separated auth transport and throttling tasks,
  externalized shared verification tooling, added auditable decision and
  coordination states, and covered ambiguous completion, CSRF/transport,
  caching, origin topology, contract fixtures, and sensitive-log edge cases.
