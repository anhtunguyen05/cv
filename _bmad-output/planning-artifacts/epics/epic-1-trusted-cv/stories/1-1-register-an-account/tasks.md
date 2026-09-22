# Story 1.1: Register an account — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

## Tasks & Acceptance

**Execution:**

- [x] TASK-1-1-01: Freeze registration and User contract fixtures
  - Status: `done`
  - Owner: `Codex`
  - Branch/worktree: `feat/registration-user-contracts`
  - Depends on: `none`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: 01. Resolve the approved subset of `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009` into one versioned registration/current-account contract; 02. promote the approved stable contract under `docs/contracts/` without duplicating the global HTTP/error standards; 03. add one executable fixture corpus consumed by both Laravel and Vue verification; 04. map every fixture row to the canonical ACs and the shared IDs below. The fixture boundary must cover CSRF/session bootstrap and expiry, valid guest registration, duplicate and concurrent duplicate email, every invalid/missing/password-policy input class, authenticated-caller registration, throttling, malformed transport, lost-response reconciliation, current-account success/unauthenticated failure, status/error/details paths, private/no-store response behavior, and credential/redaction assertions. Do not freeze unresolved values by assumption.
  - Source boundary: planning inputs are `docs/contracts/common/http.md`, `docs/standards/{api,errors,security,validation,testing}.md`, the Epic 1 package, and the source consumers in `apps/api/{app,bootstrap,config,routes,database,tests}` and `apps/web/src/{features/auth,shared/api,app/router,pages/auth}`. The current scaffold mismatch is evidence for the fixture: web auth currently calls `/auth/*`, expects a bearer `token`, stores it in Pinia, and the API exposes only `/api/health`; the target contract is `/api/v1`, Sanctum stateful HttpOnly cookies, CSRF, and `data.user` without credential material. The approved fixture paths are `docs/contracts/auth/registration.md` and `docs/contracts/auth/fixtures/registration-v1.json`. Do not encode the unresolved PostgreSQL-vs-MySQL verification mismatch (`docs/standards/testing.md` versus `ARCHITECTURE-SPINE.md`/Epic test strategy) in the contract fixture.
  - Coordination: `E1-COORD-AUTH-001`
  - Blocked by: none for planning; implementation start still requires the reserved `E1-COORD-AUTH-001` branch/worktree and the user-delegated Codex owner to be explicitly recorded.
  - Outcome: 01. Add executable auth/User contract fixtures: one approved contract document plus one versioned fixture corpus is the sole cross-layer source for registration and current-account request/response/error examples; fixture rows contain no real credentials, hashes, cookies, CSRF tokens, or bearer tokens.
  - Acceptance: 01. Add executable auth/User contract fixtures: every approved auth matrix row is represented exactly once (or has an explicit reusable fixture reference), each row names its AC/Global/Epic/Decision references, the public User projection excludes `password`, `password_hash`, `remember_token`, session/CSRF material and internal security metadata, and no frontend/backend consumer needs a local contract variant. The task remains blocked when any required decision is `pending`.
  - Verification: 01. Add executable auth/User contract fixtures: `jq empty docs/contracts/auth/fixtures/registration-v1.json` passed; jq consumer assertions passed for the public User keys, 13 unique fixture IDs, non-empty AC mappings, required operation/request/expect fields, and the absence of forbidden credential fields from the public projection; `git diff --check` passed. Manual review confirms the stable `/api/v1` envelope, status/code/details/header cases and source-to-target mismatch against the global standards. Evidence paths: `docs/contracts/auth/registration.md` and `docs/contracts/auth/fixtures/registration-v1.json`.

- [ ] TASK-1-1-02: Deliver registration and account backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: 01. Configure Laravel Sanctum SPA authentication: dependency, auth/session/CORS/CSRF config | 02. Implement registration identity and persistence rules: registration application/domain service, User migration/model/repository | 03. Expose register and current-account API operations: Form Requests, controllers, resources, routes, error mapping | 04. Enforce registration abuse and privacy controls: limiter, trusted proxy/store, duplicate privacy, safe logs | 05. Verify registration backend contract: API unit/feature/MySQL integration suites
  - Coordination: `E1-COORD-AUTH-001`; one backend integration owner coordinates independent auth/session setup and User persistence after the shared fixtures, then integrates API/privacy controls and the complete backend evidence set
  - Blocked by: approved `TASK-1-1-01` fixture checkpoint and assignment of one backend integration owner; `E1-DEC-001` and `E1-DEC-002` are resolved.
  - Outcome: 01. Configure Laravel Sanctum SPA authentication: Configure Laravel Sanctum SPA authentication. | 02. Implement registration identity and persistence rules: Implement registration identity and persistence rules. | 03. Expose register and current-account API operations: Expose register and current-account API operations. | 04. Enforce registration abuse and privacy controls: Enforce registration abuse and privacy controls. | 05. Verify registration backend contract: Verify registration backend contract.
  - Acceptance: 01. Configure Laravel Sanctum SPA authentication: approved origins and session transitions work without browser tokens. | 02. Implement registration identity and persistence rules: atomic unique User creation with approved normalization and hashing. | 03. Expose register and current-account API operations: all approved responses match fixtures and exclude credentials. | 04. Enforce registration abuse and privacy controls: approved keys/outcomes/windows and safe `429` behavior apply consistently. | 05. Verify registration backend contract: full backend behavior, transaction, concurrency and sensitive-data checks pass.
  - Verification: 01. Configure Laravel Sanctum SPA authentication: focused feature tests for bootstrap, regeneration, expiry and redaction. | 02. Implement registration identity and persistence rules: PHPUnit plus MySQL duplicate/concurrency/rollback checks. | 03. Expose register and current-account API operations: Laravel feature/contract tests including malformed and authenticated-caller paths. | 04. Enforce registration abuse and privacy controls: feature tests for limits, expiry, spoofing, duplicate timing/content and redaction. | 05. Verify registration backend contract: focused and complete PHPUnit suites against declared databases.

- [ ] TASK-1-1-03: Deliver and verify registration journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-1-01`
  - Covers: `AC-1-1-register-an-account-01`, `AC-1-1-register-an-account-02`, `AC-1-1-register-an-account-03`, `AC-1-1-register-an-account-04`, `AC-1-1-register-an-account-05`
  - Scope: 01. Implement frontend auth transport and registration adapter: shared API client and auth API/schema/error mapping | 02. Build registration state and accessible page: auth mutation/state, form, route and error presentation | 03. Verify registration components: registration component/state tests | 04. Verify registration end to end: registration/current-account browser scenarios
  - Coordination: `E1-COORD-AUTH-001`, `E1-COORD-TEST-001`
  - Blocked by: approved `TASK-1-1-01` fixture checkpoint, `E1-DEC-008` frontend verification enablement, and the backend acceptance gate from `TASK-1-1-02`; `E1-DEC-001`, `E1-DEC-002`, and `E1-DEC-007` are resolved.
  - Outcome: 01. Implement frontend auth transport and registration adapter: Implement frontend auth transport and registration adapter. | 02. Build registration state and accessible page: Build registration state and accessible page. | 03. Verify registration components: Verify registration components. | 04. Verify registration end to end: Verify registration end to end.
  - Acceptance: 01. Implement frontend auth transport and registration adapter: browser sends only approved fields/credentials and maps every fixture. | 02. Build registration state and accessible page: keyboard-usable flow handles pending, validation, throttle, lost success and navigation safely. | 03. Verify registration components: known-bad UI/error/retry/accessibility states are pinned by tests. | 04. Verify registration end to end: happy path and meaningful failures pass without test-order dependence. | Integrated journey acceptance closes only after `TASK-1-1-02` is done with backend evidence.
  - Verification: 01. Implement frontend auth transport and registration adapter: type-check and adapter/transport tests. | 02. Build registration state and accessible page: manual keyboard review until component tooling is available. | 03. Verify registration components: approved Vitest command plus type-check/lint/build. | 04. Verify registration end to end: approved Playwright command against disposable MySQL data. | Run the cross-layer journey check after `TASK-1-1-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-1-1-01` depends on `none`.
- `TASK-1-1-02` depends on `TASK-1-1-01`; auth/session configuration and User identity/persistence can proceed independently after the shared fixtures, followed by integrated API/privacy controls and backend verification.
- `TASK-1-1-03` depends on `TASK-1-1-01`; its frontend or evidence work can proceed alongside `TASK-1-1-02`, and integrated acceptance closes after `TASK-1-1-02` is done.


Shared auth and test files remain reserved by the Epic coordination records.

## Coordination and verification gate
Use `E1-COORD-AUTH-001` for every shared auth/User file and `E1-COORD-TEST-001` for reusable test tooling. Task group 03 is the acceptance gate and requires evidence for every canonical AC; local contract variants are rejected.
