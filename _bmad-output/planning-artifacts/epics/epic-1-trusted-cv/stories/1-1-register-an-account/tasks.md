# Story 1.1: Register an account — Tasks

Return to the [story overview](README.md). Story lifecycle comes from `sprint-status.yaml`; task lifecycle is maintained only in this file.

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
